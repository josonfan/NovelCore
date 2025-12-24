<?php
declare(strict_types=1);

namespace app\service;

use app\model\Order as OrderModel;
use app\service\ConfigService;
use app\service\UserService;
use pay\PayService as ChannelPayService;
use think\exception\ValidateException;
use think\facade\Db;

class OrderService
{
    public static function add(int $userId, string $orderType, int $goodId): array
    {
        if ($goodId <= 0) {
            throw new ValidateException('商品ID不能为空');
        }        
        $orderType = trim($orderType);
        if (!in_array($orderType, ['chapter', 'novel', 'vip'], true)) {
            throw new ValidateException('参数错误');
        }
        $payload = self::getPayload($orderType, $goodId);
        $payload['user_id'] = $userId;
        $m = new OrderModel();
        $m->writeById(0, $payload,true);
        return $payload;
    }
    /**
     * 获取订单商品信息
     * @param string $orderType 订单类型
     * @param int $goodId 商品ID
     * @return array
     */
    public static function getPayload(string $orderType, int $goodId): array
    {
        switch ($orderType) {
            case 'chapter':
                throw new ValidateException('章节订单暂不支持');
                // $good = ChapterService::info($goodId, 'id,novel_id,price');
                break;
            case 'novel':
                throw new ValidateException('小说订单暂不支持');
                // $good = NovelService::info($goodId, 'id,price');
                break;
            case 'vip':
                $good = VipService::info($goodId, 'id,name,descript,days,price as amount,old_price');
                break;
            default:
                $good = [];
                break;
        }
        $amount = (float)($good['amount'] ?? 0);
        if ($amount <= 0) {
            throw new ValidateException('参数错误');
        }
        $client = (string)request()->header('user-agent', '');
        $orderNo = self::generateOrderNo();
        return [
            'order_no'   => $orderNo,
            'amount'      => $amount,
            'good_id'    => (int)$good['id'],
            'good_info'  => json_encode($good, JSON_UNESCAPED_UNICODE),             
            'status'     => 'pending',
            'order_type' => $orderType,
            'extra'      => ['created_by' => 'api', 'client' => $client ?? '', 'days' => (int)($good['days'] ?? 0)],
        ];
    }
    public static function affirmBuy(int $userId, string $orderNo, int $payChannelId,$returnUrl=''): array
    {       
        $orderNo = trim($orderNo);
        if ($orderNo === '' || $payChannelId <= 0) {
            throw new ValidateException('参数错误');
        }
        $m = new OrderModel();
        $order = $m->where('order_no', $orderNo)->where('user_id', $userId)->find();
        $data = $order ? $order->toArray() : [];
        if (!$data) {
            throw new ValidateException('订单不存在');
        }
        if($data['status']!=='pending'){
            throw new ValidateException('订单状态错误');
        }
        $PayChannelService = new PaymentChannelService();
        $channel = $PayChannelService->info($payChannelId);
        $data['pay_channel_id'] = $payChannelId;
        $userView = UserService::info($userId, 'id,nickname');  
        
        $notifyUrl = rtrim((string)config('site.base_api_url', ''), '/') . '/Order/notify/'.$channel['icon_iden'].'/'.$channel['id'];
        
        $payOrder = [
            'order_sn' => $orderNo,
            'price'   => (float)$data['amount'],
        ];
        $payParams = ChannelPayService::Pay($channel, $payOrder, $userView, $notifyUrl, $returnUrl);
        $m->writeById((int)$data['id'], [
            'extra' => array_merge((array)($data['extra'] ?? []), ['prepay' => $payParams]),
            'status' => 'processing',
        ]);
        return [
            'order_no' => $orderNo,
            'status'   => 'processing',
            'amount'   => (float)$data['amount'],
            'channel'  => (string)($data['pay_channel'] ?? ''),
            'pay_params' => $payParams,
        ];
    }

    public static function notify(array $payload): array
    {
        $icon_iden = (string)($payload['icon_iden'] ?? '');
        $channel_id = (int)($payload['channel_id'] ?? 0);
        unset($payload['icon_iden'], $payload['channel_id']);
        $channel = PaymentChannelService::info($channel_id);
        if (empty($icon_iden) || empty($channel)) {
            return ['saved' => false, 'status' => 'error', 'ok_msg' => 'error'];
        }
        try {
            $info = ChannelPayService::getCheckSign($channel, $payload);
            if (empty($info)) {
                throw new ValidateException('参数错误');
            }
            $ok = self::callbackOk($info);
            return ['saved' => (bool)$ok, 'status' => $ok ? 'paid' : 'error', 'ok_msg' => (string)($info['ok_msg'] ?? 'OK')];
        } catch (ValidateException $e) {
            trace($e->getMessage(), 'error');
            return ['saved' => false, 'status' => 'error', 'ok_msg' => 'error', 'message' => $e->getMessage()];
        }
    }
    public static function callbackOk(array $info): bool
    {
        try {
            Db::startTrans();
            $orderNo = $info['order_sn'] ?? '';
            $m = new OrderModel();
            $order = $m->where('order_no', $orderNo)->find();
            $data = $order ? $order->toArray() : [];
            if (!$data) {
                throw new ValidateException('订单不存在');
            }
            if($data['status']==='paid'){
                return true;
            }
            $update = [
                'status' => 'paid',
                'extra' => array_merge((array)($data['extra'] ?? []), ['notify' => $info]),
                'paid_at' => date('Y-m-d H:i:s'),
            ];
            if($m->writeById((int)$data['id'], $update,true)){
                switch ($data['order_type']) {
                    case 'novel':
                        
                        break;
                    case 'vip':
                        VipService::addUserVip((int)$data['user_id'], (int)$data['good_id'], (int)($data['extra']['days'] ?? 0), (string)$orderNo, '购买会员');
                        break;
                    default:
                        break;
                }
                Db::commit();
                return true;
            }
            throw new ValidateException('订单更新失败');
        } catch (ValidateException $e) {
            Db::rollback();
            throw new ValidateException($e->getMessage());
        } catch (\Exception $e) {
            Db::rollback();
            throw new ValidateException($e->getMessage());
        }
    }
    protected static function generateOrderNo(): string
    {
        return 'NO' . date('YmdHis') . substr((string)mt_rand(100000, 999999), -6);
    }

    public static function info(int $userId, string $orderNo): array
    {
        $orderNo = trim($orderNo);
        if ($userId <= 0 || $orderNo === '') {
            throw new ValidateException('参数错误');
        }
        $m = new OrderModel();
        $rid = $m->where('order_no', $orderNo)->where('user_id', $userId)->value('id');
        $fields = 'order_no,user_id,order_type,good_id,amount,status,paid_at,created_at,updated_at';
        $data = $m->infoById((int)$rid,$fields);
        if (!$data) {
            throw new ValidateException('订单不存在');
        }
        return $data;
    }
}

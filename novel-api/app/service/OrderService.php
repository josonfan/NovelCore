<?php
declare(strict_types=1);

namespace app\service;

use app\model\Order as OrderModel;
use app\service\ConfigService;
use app\service\UserService;
use pay\PayService as ChannelPayService;
use think\exception\ValidateException;

class OrderService
{
    public static function add(int $userId, string $orderType, array $payload): array
    {
        if ($userId <= 0) {
            throw new ValidateException('未登录或令牌无效');
        }
        $orderType = trim($orderType);
        if (!in_array($orderType, ['chapter', 'novel', 'vip'], true)) {
            throw new ValidateException('参数错误');
        }
        $amount = (float)($payload['amount'] ?? 0);
        if ($amount <= 0) {
            throw new ValidateException('参数错误');
        }
        $novelId = isset($payload['novel_id']) ? (int)$payload['novel_id'] : null;
        $chapterId = isset($payload['chapter_id']) ? (int)$payload['chapter_id'] : null;
        $vipPlan = isset($payload['vip_plan']) ? trim((string)$payload['vip_plan']) : null;
        $payChannel = isset($payload['pay_channel']) ? trim((string)$payload['pay_channel']) : null;
        if ($orderType === 'chapter' && (!$chapterId || $chapterId <= 0)) {
            throw new ValidateException('参数错误');
        }
        if ($orderType === 'novel' && (!$novelId || $novelId <= 0)) {
            throw new ValidateException('参数错误');
        }
        if ($orderType === 'vip' && (!$vipPlan || !in_array($vipPlan, ['month','quarter','year'], true))) {
            throw new ValidateException('参数错误');
        }
        $orderNo = self::generateOrderNo();
        (new OrderModel())->writeById(0, [
            'order_no'   => $orderNo,
            'user_id'    => $userId,
            'order_type' => $orderType,
            'novel_id'   => $novelId,
            'chapter_id' => $chapterId,
            'vip_plan'   => $vipPlan,
            'amount'     => $amount,
            'pay_channel'=> $payChannel,
            'status'     => 'pending',
            'extra'      => ['created_by' => 'api', 'client' => $payload['client'] ?? ''],
        ]);
        return ['order_no' => $orderNo];
    }

    public static function affirmBuy(int $userId, string $orderNo): array
    {       
        $orderNo = trim($orderNo);
        if ($orderNo === '') {
            throw new ValidateException('参数错误');
        }
        $m = new OrderModel();
        $order = $m->where('order_no', $orderNo)->where('user_id', $userId)->find();
        $data = $order ? $order->toArray() : [];
        if (!$data) {
            throw new ValidateException('订单不存在');
        }
        if (($data['status'] ?? '') === 'paid') {
            return [
                'order_no' => $orderNo,
                'status'   => 'paid',
                'amount'   => (float)$data['amount'],
                'channel'  => (string)($data['pay_channel'] ?? ''),
                'pay_params' => [],
            ];
        }
        $channelId = (string)($data['pay_channel'] ?? '');
        if ($channelId === '') {
            throw new ValidateException('支付渠道不存在');
        }
        $channels = ConfigService::get('config:payment', []);
        $channel = is_array($channels) ? ($channels[$channelId] ?? []) : [];
        if (empty($channel)) {
            throw new ValidateException('支付渠道不存在或已下线');
        }
        $userView = UserService::info($userId, 'id,nickname');
        $notifyUrl = rtrim((string)config('site.base_api_url', ''), '/') . '/api/Order/notify';
        $returnUrl = '';
        $payOrder = [
            'order_no' => $orderNo,
            'amount'   => (float)$data['amount'],
            'channel'  => $channelId,
            'type'     => (string)($data['order_type'] ?? ''),
        ];
        $payParams = ChannelPayService::Pay($channel, $payOrder, $userView, $notifyUrl, $returnUrl);
        $m->writeById((int)$data['id'], [
            'extra' => array_merge((array)($data['extra'] ?? []), ['prepay' => $payParams]),
        ]);
        return [
            'order_no' => $orderNo,
            'status'   => 'pending',
            'amount'   => (float)$data['amount'],
            'channel'  => (string)($data['pay_channel'] ?? ''),
            'pay_params' => $payParams,
        ];
    }

    public static function notify(array $payload): array
    {
        $orderNo = trim((string)($payload['order_no'] ?? ''));
        if ($orderNo === '') {
            throw new ValidateException('参数错误');
        }
        $status = (string)($payload['status'] ?? '');
        $transactionId = (string)($payload['transaction_id'] ?? '');
        $paid = $status === 'paid';
        $m = new OrderModel();
        $order = $m->where('order_no', $orderNo)->find();
        $data = $order ? $order->toArray() : [];
        if (!$data) {
            throw new ValidateException('订单不存在');
        }
        $update = [
            'status' => $paid ? 'paid' : (($status === 'cancelled') ? 'cancelled' : 'pending'),
            'extra' => array_merge((array)($data['extra'] ?? []), ['notify' => $payload]),
        ];
        if ($paid) {
            $update['paid_at'] = date('Y-m-d H:i:s');
        }
        $m->writeById((int)$data['id'], $update);
        return ['saved' => true, 'status' => $update['status']];
    }

    protected static function generateOrderNo(): string
    {
        return 'NO' . date('YmdHis') . substr((string)mt_rand(100000, 999999), -6);
    }
}

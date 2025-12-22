<?php
namespace app\admin\service;

use app\admin\model\PaymentChannel;
use think\exception\ValidateException;

class PaymentChannelService
{
    public static function list(array $where = [], string $field = '*', string $orderby = 'sort desc, id desc', int $limit = 10, int $page = 1): array
    {
        $m = new PaymentChannel();
        return $m->getList($where, $field, $orderby, $limit, $page);
    }

    public static function detail(int $id, string $field = '*')
    {
        $m = new PaymentChannel();
        $info = $m->infoById($id, $field);
        if (empty($info)) {
            throw new \Exception('支付渠道不存在');
        }
        return $info;
    }

    public static function create(array $data)
    {
        try {
            validate(\app\admin\validate\PaymentChannel::class)->scene('create')->check($data);
            $m = new PaymentChannel($data);
            $m->created_at = time();
            $m->writeById((int)$m['id'], $m->toArray());
            return $m;
        } catch (ValidateException $e) {
            throw new ValidateException($e->getError());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public static function update(int $id, array $data): bool
    {
        try {
            validate(\app\admin\validate\PaymentChannel::class)->scene('update')->check($data);
            $info = self::detail($id);
            foreach ($data as $key => $value) {
                $info[$key] = $value;
            }
            $m = new PaymentChannel();
            return $m->writeById($id, $info);
        } catch (ValidateException $e) {
            throw new ValidateException($e->getError());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public static function delete(int $id): bool
    {
        $m = new PaymentChannel();
        $info = self::detail($id);
        if ($info['status'] == 1) {
            throw new \Exception('启用的支付渠道不能删除');
        }
        return $m->deleteById($id);
    }

    public static function toggle(int $id, int $status): bool
    {
        try {
            validate(\app\admin\validate\PaymentChannel::class)->scene('toggle')->check(['id' => $id, 'status' => $status]);
            $m = new PaymentChannel();
            return $m->writeById($id, ['status' => $status]);
        } catch (ValidateException $e) {
            throw new ValidateException($e->getError());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}

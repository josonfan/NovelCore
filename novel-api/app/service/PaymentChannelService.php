<?php
declare(strict_types=1);

namespace app\service;

use app\model\PaymentChannel as PaymentChannelModel;
use think\exception\ValidateException;

class PaymentChannelService
{
    public static function list(array $where = [], string $field = '*', string $orderby = 'sort desc, id desc', int $limit = 10, int $page = 1): array
    {
        $m = new PaymentChannelModel();
        return $m->getList($where, $field, $orderby, $limit, $page);
    }

    public static function info(int $id, string $field = '*'): array
    {
        $m = new PaymentChannelModel();
        return $m->infoById($id, $field);
    }

    public static function store(array $data): array
    {
        $name = trim((string)($data['name'] ?? ''));
        $payType = trim((string)($data['pay_type'] ?? ''));
        $iconIden = trim((string)($data['icon_iden'] ?? ''));
        $status = (int)($data['status'] ?? 1);
        if ($name === '' || $payType === '' || $iconIden === '') {
            throw new ValidateException('参数错误');
        }
        (new PaymentChannelModel())->writeById(0, [
            'name' => $name,
            'pay_type' => $payType,
            'icon_iden' => $iconIden,
            'status' => $status,
            'is_default' => (int)($data['is_default'] ?? 0),
            'is_usdt' => (int)($data['is_usdt'] ?? 0),
            'limit_price' => (float)($data['limit_price'] ?? 0),
            'pay_url' => (string)($data['pay_url'] ?? ''),
            'sup_order_url' => (string)($data['sup_order_url'] ?? ''),
            'pay_id' => (string)($data['pay_id'] ?? ''),
            'skey' => (string)($data['skey'] ?? ''),
            'md5_key' => (string)($data['md5_key'] ?? ''),
            'pay_bankcode' => (string)($data['pay_bankcode'] ?? ''),
            'sort' => (int)($data['sort'] ?? 50),
            'is_web' => (int)($data['is_web'] ?? 0),
            'not_pc' => (int)($data['not_pc'] ?? 0),
            'remarks' => (string)($data['remarks'] ?? ''),
            'pay_rules' => $data['pay_rules'] ?? null,
        ]);
        return ['ok' => true];
    }

    public static function update(int $id, array $data): array
    {
        if ($id <= 0) {
            throw new ValidateException('参数错误');
        }
        (new PaymentChannelModel())->writeById($id, $data);
        return ['ok' => true];
    }

    public static function delete(int $id): array
    {
        if ($id <= 0) {
            throw new ValidateException('参数错误');
        }
        (new PaymentChannelModel())->deleteById($id);
        return ['ok' => true];
    }
}

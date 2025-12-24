<?php
declare(strict_types=1);

namespace app\service;

use app\model\Vip as VipModel;
use think\exception\ValidateException;
use think\facade\Db;



class VipService
{
    public static function list(array $where = [], string $field = '*', string $orderby = 'sort desc, id desc', int $limit = 10, int $page = 1): array
    {
        $m = new VipModel();
        return $m->getList($where, $field, $orderby, $limit, $page);
    }

    public static function info(int $id, string $field = '*'): array
    {
        $m = new VipModel();
        $info =  $m->infoById($id, $field);
        if (!$info) {
            throw new ValidateException('vip套餐不存在');
        }
        return $info;
    }

    public static function addUserVip(int $userId, int $vipId, int $days, string $orderNo = '', string $remarks = '购买会员'): bool
    {
        
        try {
            if ($userId <= 0 || $vipId <= 0 || $days <= 0) {
            throw new ValidateException('参数错误');
            }
            $vip = self::info($vipId, 'id,days');
            $addDays = $days > 0 ? $days : (int)($vip['days'] ?? 0);
            if ($addDays <= 0) {
                throw new ValidateException('参数错误');
            }        
            $now = time();
            $user = \app\service\UserService::info($userId, 'id,vip_expire');
            $currentExpire = (int)($user['vip_expire'] ?? 0);
            $base = $currentExpire > $now ? $currentExpire : $now;
            $newExpire = $base + $addDays * 86400;
            Db::startTrans();
            (new \app\model\User())->writeById($userId, ['vip_expire' => $newExpire],true);
            (new \app\model\VipTimeLog())->writeById(0,[
                'user_id' => $userId,
                'vip_expire_before' => $currentExpire,
                'vip_expire_time' => $addDays,
                'vip_expire_end' => $newExpire,
                'source' => 2,
                'change_type' => 1,
                'order_no' => $orderNo,
                'remarks' => $remarks,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],true);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw new ValidateException($e->getMessage());
        }
        return true;
    }
}

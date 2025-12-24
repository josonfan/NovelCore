<?php
namespace app\admin\service;

use app\admin\model\Vip;
use think\exception\ValidateException;

class VipService
{
    public static function list(array $where = [], string $field = '*', string $orderby = 'sort desc, id desc', int $limit = 10, int $page = 1): array
    {
        $m = new Vip();
        return $m->getList($where, $field, $orderby, $limit, $page);
    }

    public static function detail(int $id, string $field = '*')
    {
        $m = new Vip();
        $info = $m->infoById($id, $field);
        if (empty($info)) {
            throw new ValidateException('会员套餐不存在');
        }
        return $info;
    }

    public static function create(array $data)
    {
        try {
            validate(\app\admin\validate\Vip::class)->scene('create')->check($data);
            $m = new Vip($data);
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
            validate(\app\admin\validate\Vip::class)->scene('update')->check(array_merge($data, ['id' => $id]));
            $info = self::detail($id);
            foreach ($data as $key => $value) {
                $info[$key] = $value;
            }
            $m = new Vip();            
            return $m->writeById($id, $info);
        } catch (ValidateException $e) {
            throw new ValidateException($e->getError());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public static function delete(int $id): bool
    {
        $m = new Vip();
        return $m->deleteById($id);
    }

    public static function setStatus(int $id, int $status): bool
    {
        try {
            validate(\app\admin\validate\Vip::class)->scene('status')->check(['id' => $id, 'status' => $status]);
            $m = new Vip();
            return $m->writeById($id, ['status' => $status]);
        } catch (ValidateException $e) {
            throw new ValidateException($e->getError());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}

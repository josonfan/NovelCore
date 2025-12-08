<?php
namespace app\admin\service;

use app\admin\model\DomainList;
use think\exception\ValidateException;
use think\facade\Cache;

class DomainListService
{
    public static function list(array $where = [], string $field = '*', string $orderby = 'id desc', int $limit = 10, int $page = 1): array
    {
        $m = new DomainList();
        return $m->getList($where, $field, $orderby, $limit, $page);
    }

    public static function detail(int $id, string $field = '*')
    {
        $m = new DomainList();
        return $m->infoById($id, $field);
    }

    public static function create(array $data)
    {
        try {
            validate(\app\admin\validate\Domain::class)->scene('create')->check($data);
            $m = new DomainList($data);
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
            validate(\app\admin\validate\Domain::class)->scene('update')->check($data);
            $m = new DomainList();
            return $m->writeById($id, $data);
        } catch (ValidateException $e) {
            throw new ValidateException($e->getError());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public static function delete(int $id): bool
    {
        $m = new DomainList();        
        $pk = $m->getPk();
        $ok = (bool)$m->where($pk, $id)->delete();
        \think\facade\Cache::delete(env('DATABASE.PREFIX', 'blad_') . 'domain_list_' . $id);
        return $ok;
    }
}

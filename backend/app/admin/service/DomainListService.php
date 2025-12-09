<?php
namespace app\admin\service;

use app\admin\model\DomainList;
use think\exception\ValidateException;
use think\facade\Cache;

class DomainListService
{
    /**
     * 域名列表
     * @param array $where 过滤条件
     * @param string $field 字段列表
     * @param string $orderby 排序
     * @param int $limit 每页数量
     * @param int $page 页码
     * @return array
     */
    public static function list(array $where = [], string $field = '*', string $orderby = 'id desc', int $limit = 10, int $page = 1): array
    {
        $m = new DomainList();
        return $m->getList($where, $field, $orderby, $limit, $page);
    }

    /**
     * 域名详情
     * @param int $id 主键ID
     * @param string $field 字段列表
     * @return mixed
     */
    public static function detail(int $id, string $field = '*')
    {
        $m = new DomainList();
        return $m->infoById($id, $field);
    }

    /**
     * 创建域名
     * @param array $data 域名数据
     * @return DomainList
     */
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

    /**
     * 更新域名
     * @param int $id 主键ID
     * @param array $data 更新数据
     * @return bool
     */
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

    /**
     * 删除域名
     * @param int $id 主键ID
     * @return bool
     */
    public static function delete(int $id): bool
    {
        $m = new DomainList();
        $pk = $m->getPk();
        $ok = (bool)$m->where($pk, $id)->delete();
        \think\facade\Cache::delete(env('DATABASE.PREFIX', 'blad_') . 'domain_list_' . $id);
        return $ok;
    }
}

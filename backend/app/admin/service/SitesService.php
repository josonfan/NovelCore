<?php
namespace app\admin\service;

use app\admin\model\Sites;
use think\exception\ValidateException;

class SitesService
{
    /**
     * 站点列表
     * @param array $where 过滤条件
     * @param string $field 字段列表
     * @param string $orderby 排序
     * @param int $limit 每页数量
     * @param int $page 页码
     * @return array
     */
    public static function list(array $where = [], string $field = '*', string $orderby = 'id desc', int $limit = 10, int $page = 1): array
    {
        $m = new Sites();
        return $m->getList($where, $field, $orderby, $limit, $page);
    }

    /**
     * 站点详情
     * @param int $id 主键ID
     * @param string $field 字段列表
     * @return mixed
     */
    public static function detail(int $id, string $field = '*')
    {
        $m = new Sites();
        return $m->infoById($id, $field);
    }

    /**
     * 创建站点
     * @param array $data 站点数据
     * @return Sites
     */
    public static function create(array $data)
    {
        try {
            $exists = (new Sites())->where('base_api_url', $data['base_api_url'] ?? '')->find();
            if ($exists) {
                $id = (int)$exists['id'];
                unset($data['code']);
                validate(\app\admin\validate\Site::class)->scene('update')->check($data);
                $m = new Sites();
                $m->writeById($id, $data);
                $info = $m->infoById($id, 'id,name,code,base_api_url,primary_domain,is_active,remark,created_at,updated_at');
                return new Sites($info ?: ['id' => $id]);
            } else {
                validate(\app\admin\validate\Site::class)->scene('create')->check($data);
                $m = new Sites($data);
                $m->created_at = time();
                $m->writeById((int)$m['id'], $m->toArray());
                return $m;
            }
        } catch (ValidateException $e) {
            throw new ValidateException($e->getError());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * 更新站点
     * @param int $id 主键ID
     * @param array $data 更新数据
     * @return bool
     */
    public static function update(int $id, array $data): bool
    {
        try {
            validate(\app\admin\validate\Site::class)->scene('update')->check($data);
            $m = new Sites();
            return $m->writeById($id, $data);
        } catch (ValidateException $e) {
            throw new ValidateException($e->getError());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * 删除站点
     * @param int $id 主键ID
     * @return bool
     */
    public static function delete(int $id): bool
    {
        $m = new Sites();
        $pk = $m->getPk();
        $ok = (bool)$m->where($pk, $id)->delete();
        \think\facade\Cache::delete(env('DATABASE.PREFIX', 'blad_') . 'sites_' . $id);
        return $ok;
    }

    /**
     * 启停站点
     * @param int $id 主键ID
     * @param int $isActive 是否启用(1/0)
     * @return bool
     */
    public static function toggle(int $id, int $isActive): bool
    {
        $m = new Sites();
        return $m->writeById($id, ['is_active' => $isActive]);
    }
}

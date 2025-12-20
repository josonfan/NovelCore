<?php
namespace app\admin\service;

use app\admin\model\SiteUsers;
use think\exception\ValidateException;

class SiteUsersService
{
    /**
     * 用户列表
     * @param array $where
     * @param string $field
     * @param string $orderby
     * @param int $limit
     * @param int $page
     * @return array
     */
    public static function list(array $where = [], string $field = '*', string $orderby = 'id desc', int $limit = 10, int $page = 1): array
    {
        $m = new SiteUsers();
        return $m->getList($where, $field, $orderby, $limit, $page);
    }

    /**
     * 用户详情
     * @param int $id
     * @param string $field
     * @return mixed
     */
    public static function detail(int $id, string $field = '*')
    {
        $m = new SiteUsers();
        return $m->infoById($id, $field);
    }

    /**
     * 设置状态
     * @param int $id
     * @param int $status
     * @return bool
     */
    public static function setStatus(int $id, int $status): bool
    {
        try {
            validate(\app\admin\validate\SiteUsers::class)->scene('status')->check(['id' => $id, 'status' => $status]);
            $m = new SiteUsers();
            return $m->writeById($id, ['status' => $status]);
        } catch (ValidateException $e) {
            throw new ValidateException($e->getError());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}

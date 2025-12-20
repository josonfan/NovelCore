<?php
namespace app\admin\service;

use app\admin\model\SiteComments;
use think\exception\ValidateException;

class SiteCommentsService
{
    /**
     * 评论列表
     * @param array $where
     * @param string $field
     * @param string $orderby
     * @param int $limit
     * @param int $page
     * @return array
     */
    public static function list(array $where = [], string $field = '*', string $orderby = 'id desc', int $limit = 10, int $page = 1): array
    {
        $m = new SiteComments();
        return $m->getList($where, $field, $orderby, $limit, $page);
    }

    /**
     * 评论详情
     * @param int $id
     * @param string $field
     * @return mixed
     */
    public static function detail(int $id, string $field = '*')
    {
        $m = new SiteComments();
        return $m->infoById($id, $field);
    }

    /**
     * 审核评论
     * @param int $id
     * @param int $status 0=待审核, 1=通过, 2=拒绝
     * @param string $auditReason 审核原因
     * @return bool
     */
    public static function audit(int $id, int $status, string $auditReason = ''): bool
    {
        try {
            validate(\app\admin\validate\SiteComments::class)->scene('audit')->check(['id' => $id, 'status' => $status, 'audit_reason' => $auditReason]);
            $m = new SiteComments();
            // 审核时同时更新审核来源为后台审核(1)
            return $m->writeById($id, [
                'status' => $status,
                'review_source' => 1,
                'audit_reason' => $auditReason
            ]);
        } catch (ValidateException $e) {
            throw new ValidateException($e->getError());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}

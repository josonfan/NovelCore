<?php
declare(strict_types=1);

namespace app\service;

use app\exception\BusinessException;
use app\model\Chapter;
use app\model\User;
use think\db\exception\DataNotFoundException;



/**
 * 章节访问权限校验（当前为占位逻辑，后续接入订单/VIP）。
 */
class ChapterService
{
    /**
     * 校验用户是否可阅读章节：
     * - is_free=1 直接放行；
     * - is_free=0 时：
     *   - 如章节标记 is_vip=1，则要求用户已登录且 vip_expire 未过期；
     *   - 其他情况预留给订单/付费逻辑，当前仅要求已登录。
     */
    public function checkUserCanRead(?User $user, Chapter $chapter): void
    {
        if ((int) $chapter->is_free === 1) {
            return;
        }

        if (!$user) {
            throw new BusinessException('请先登录后阅读本章节', 403);
        }

        if ((int) $chapter->is_vip === 1) {
            if ((int) $user->vip_expire <= time()) {
                throw new BusinessException('VIP 已过期，无法阅读付费章节', 403);
            }
            return;
        }

        // 非免费且非 VIP 章节的细粒度权限留待后续（订单、解锁等）。
    }
        
    /**
     * 章节列表
     * @param array $where 查询条件
     * @param string $field 查询字段
     * @param string $orderby 排序字段
     * @param int $limit 每页数量
     * @param int $page 当前页码
     * @return array 章节列表
     */
    public static function list(array $where = [], string $field = '*', string $orderby = '', int $limit = 10, int $page = 1): array
    {
        $m = new Chapter();
        return $m->getList($where, $field, $orderby, $limit, $page);
    }
    /**
     * 根据 UUID 获取章节详情
     * @param string $chapterId 章节 UUID
     * @return array 章节详情
     */
    public static function getInfoByUuid(string $chapterId, string $fields = '*'): array
    {
        $m = new Chapter();
        $pk = $m->getPk();
        $id = $m->where('chapter_uuid', $chapterId)->value($pk);
        if (!$id) {
            throw new DataNotFoundException('章节不存在');
        }
        return self::info($id, $fields);       ;
    }

    /**
     * 根据 ID 获取章节详情
     * @param int $id 章节 ID
     * @param string $fields 查询字段
     * @return array 章节详情
     */
    public static function info(int $id, string $fields = '*'): array
    {
        $m = new Chapter();
        $chapter = $m->infoById($id, $fields);
        if (empty($chapter)) {
            throw new DataNotFoundException('章节不存在');
        }
        return $chapter;
    }
}

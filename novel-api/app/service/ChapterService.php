<?php
declare(strict_types=1);

namespace app\service;

use app\exception\BusinessException;
use app\model\Chapter;
use app\model\User;

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
}

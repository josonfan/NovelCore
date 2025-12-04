<?php
declare(strict_types=1);

namespace app\service;

use app\model\Domain;

/**
 * 域名池管理：按类型返回可用域名，后续可接入健康检查/线路选择。
 */
class DomainService
{
    /**
     * 获取指定类型的启用域名列表，按 priority DESC。
     */
    public function getActiveDomainsByType(string $type): array
    {
        return Domain::where('type', $type)
            ->where('is_active', 1)
            ->order('priority', 'desc')
            ->column('domain');
    }

    /**
     * 选择最佳域名：当前返回第一个，未来可基于测速、地域等做智能选择。
     */
    public function pickBestDomain(string $type, array $context = []): string
    {
        $domains = $this->getActiveDomainsByType($type);
        return $domains[0] ?? '';
    }
}

<?php
namespace app\admin\service;

use app\admin\model\StorageConfig;
use think\exception\ValidateException;

class StorageConfigService
{
    public static function getBySiteId($siteId)
    {
        $m = new StorageConfig();
        $query = $m->where('site_id', $siteId);
        $info = $query->find();
        return $info ? $info->toArray() : [];
    }
    public static function list(array $where = [], string $field = '*', string $orderby = 'id desc', int $limit = 10, int $page = 1): array
    {
        $m = new StorageConfig();
        return $m->getList($where, $field, $orderby, $limit, $page);
    }

    public static function detail(int $id, string $field = '*')
    {
        $m = new StorageConfig();
        return $m->infoById($id, $field);
    }

    

    public static function save(array $data)
    {
        $siteId = (int)($data['site_id'] ?? 0);
        if ($siteId <= 0) {
            throw new \think\exception\ValidateException('site_id 必填');
        }
        $exists = self::getBySiteId($siteId);
        $m = new StorageConfig();
        if (!empty($exists)) {
            validate(\app\admin\validate\StorageConfig::class)->scene('update')->check($data);
            $id = (int)$exists['id'];
            $m->writeById($id, $data);
            $info = $m->infoById($id);
            return ['mode' => 'updated', 'data' => $info];
        }
        validate(\app\admin\validate\StorageConfig::class)->scene('create')->check($data);
        $m = new StorageConfig($data);
        $m->writeById((int)$m['id'], $m->toArray());
        return ['mode' => 'created', 'data' => $m->toArray()];
    }
}

<?php
namespace app\admin\service;

use app\admin\model\EmailConfig;
use think\exception\ValidateException;

class EmailConfigService
{
    public static function getBySiteId($siteId)
    {
        $m = new EmailConfig();
        $info = $m->where('site_id', $siteId)->find();
        return $info ? $info->toArray() : [];
    }
    public static function list(array $where = [], string $field = '*', string $orderby = 'id desc', int $limit = 10, int $page = 1): array
    {
        $m = new EmailConfig();
        return $m->getList($where, $field, $orderby, $limit, $page);
    }

    public static function detail(int $id, string $field = '*')
    {
        $m = new EmailConfig();
        return $m->infoById($id, $field);
    }

    

    public static function save(array $data)
    {
        $siteId = (int)($data['site_id'] ?? 0);
        
        $exists = self::getBySiteId($siteId);
        $m = new EmailConfig();
        if (!empty($exists)) {
            validate(\app\admin\validate\EmailConfig::class)->scene('update')->check($data);
            $id = (int)$exists['id'];
            $m->writeById($id, $data);
            $info = $m->infoById($id);
            return ['mode' => 'updated', 'data' => $info];
        }
        validate(\app\admin\validate\EmailConfig::class)->scene('create')->check($data);
        $m = new EmailConfig($data);
        $m->writeById((int)$m['id'], $m->toArray());
        return ['mode' => 'created', 'data' => $m->toArray()];
    }
}

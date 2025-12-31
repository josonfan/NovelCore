<?php
declare(strict_types=1);

namespace app\service;

use app\model\UserDeviceLog;
use app\model\UserLoginLog;

class AdminDataService
{
    public static function deviceLogsList(): array
    {
        $page  = request()->param('page', 1, 'intval');
        $limit = request()->param('limit', 20, 'intval');
        $limit = min(100, max(1, (int)$limit));
        $page  = max(1, (int)$page);
        $raw = [];
        $uid = request()->param('user_id', null, 'intval');
        if ($uid !== null) $raw['user_id'] = (int)$uid;
        $deviceId = request()->param('device_id', '', 'trim');
        if ($deviceId !== '') $raw['device_id'] = $deviceId;
        $brand = request()->param('device_brand', '', 'trim');
        if ($brand !== '') $raw['device_brand'] = $brand;
        $model = request()->param('device_model', '', 'trim');
        if ($model !== '') $raw['device_model'] = $model;
        $os = request()->param('os', '', 'trim');
        if ($os !== '') $raw['os'] = $os;
        $clientVersion = request()->param('client_version', '', 'trim');
        if ($clientVersion !== '') $raw['client_version'] = $clientVersion;
        $isSuspicious = request()->param('is_suspicious', null, 'intval');
        if ($isSuspicious !== null) $raw['is_suspicious'] = (int)$isSuspicious;
        $ip = request()->param('ip', '', 'trim');
        if ($ip !== '') $raw['ip'] = $ip;
        $where = formatWhere($raw);
        $fields = 'id,user_id,device_id,device_brand,device_model,os,client_version,is_suspicious,ip,created_at';
        $orderby = 'created_at desc, id desc';
        $m = new UserDeviceLog();
        return $m->getList($where, $fields, $orderby, (int)$limit, (int)$page);
    }

    public static function loginLogsList(): array
    {
        $page  = request()->param('page', 1, 'intval');
        $limit = request()->param('limit', 20, 'intval');
        $limit = min(100, max(1, (int)$limit));
        $page  = max(1, (int)$page);
        $raw = [];
        $uid = request()->param('user_id', null, 'intval');
        if ($uid !== null) $raw['user_id'] = (int)$uid;
        $ip = request()->param('ip', '', 'trim');
        if ($ip !== '') $raw['ip'] = $ip;
        $deviceId = request()->param('device_id', '', 'trim');
        if ($deviceId !== '') $raw['device_id'] = $deviceId;
        $country = request()->param('country', '', 'trim');
        if ($country !== '') $raw['country'] = $country;
        $province = request()->param('province', '', 'trim');
        if ($province !== '') $raw['province'] = $province;
        $city = request()->param('city', '', 'trim');
        if ($city !== '') $raw['city'] = $city;
        $isp = request()->param('isp', '', 'trim');
        if ($isp !== '') $raw['isp'] = $isp;
        $where = formatWhere($raw);
        $fields = 'id,user_id,ip,country,province,city,isp,device_id,login_time';
        $orderby = 'login_time desc, id desc';
        $m = new UserLoginLog();
        return $m->getList($where, $fields, $orderby, (int)$limit, (int)$page);
    }
}


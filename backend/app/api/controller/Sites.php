<?php
namespace app\api\controller;

use app\admin\service\SitesService;

class Sites
{
    public function register()
    {
        $postField = 'name,code,base_api_url,primary_domain,api_token,remark';
        $request = app()->request;
        $data = $request->only(explode(',', $postField), 'post', null);
        $data['is_active'] = 1;
        try {
            $m = SitesService::create($data);
            $out = getArrayByFields($m->toArray(), 'id,name,code,base_api_url,primary_domain,is_active,remark,created_at,updated_at');
            return json(['code' => 200, 'msg' => '站点注册成功', 'data' => $out]);
        } catch (\think\exception\ValidateException $e) {
            return json(['code' => 422, 'msg' => $e->getError(), 'data' => []]);
        } catch (\Throwable $e) {
            return json(['code' => 500, 'msg' => $e->getMessage(), 'data' => []]);
        }
    }
}

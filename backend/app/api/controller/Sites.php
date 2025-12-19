<?php
namespace app\api\controller;

use app\admin\service\SitesService;

class Sites extends Common
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
            return $this->ajaxReturn(200, '站点注册成功', $out);
        } catch (\think\exception\ValidateException $e) {
            return $this->ajaxReturn(422, $e->getError(), []);
        } catch (\Throwable $e) {
            return $this->ajaxReturn(500, $e->getMessage(), []);
        }
    }
}

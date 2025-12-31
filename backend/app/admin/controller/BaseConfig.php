<?php
namespace app\admin\controller;

use app\admin\service\BaseConfigService;

class BaseConfig extends Backend
{
    /**
     * 获取配置详情
     */
    public function detail()
    {
        $siteId = (int)$this->request->param('site_id');
        $configName = (string)$this->request->param('config_name');
        
        if (empty($siteId) || empty($configName)) {
            return $this->ajaxReturn(400, '参数错误');
        }

        $res = BaseConfigService::detail($siteId, $configName);
        return $this->ajaxReturn(200, '成功', $res);
    }

    /**
     * 保存配置
     */
    public function save()
    {
        $data = $this->request->only(['site_id', 'config_name', 'config_data'], 'post');
        
        try {
            $res = BaseConfigService::save($data);
            return $this->ajaxReturn(200, '保存成功', $res);
        } catch (\Exception $e) {
            return $this->ajaxReturn(400, $e->getMessage());
        }
    }
}

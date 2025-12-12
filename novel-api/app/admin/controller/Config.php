<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\BaseController;
use app\model\SystemConfig;
use app\service\ConfigService;
use think\Request;

class Config extends BaseController
{
    /**
     * 保存存储配置
     *
     * 路由：`POST /api/admin/config/storage`
     * 鉴权：需 Admin Token
     * 参数：JSON 结构体
     * 返回：`{ config_key }`
     *
     * @param Request $request
     * @return \think\Response
     */
    public function saveStorage(Request $request)
    {
        return $this->saveConfig('config:storage', $request->post());
    }

    /**
     * 保存邮箱配置
     *
     * 路由：`POST /api/admin/config/email`
     * 鉴权：需 Admin Token
     * 参数：JSON 结构体
     * 返回：`{ config_key }`
     *
     * @param Request $request
     * @return \think\Response
     */
    public function saveEmail(Request $request)
    {
        return $this->saveConfig('config:email', $request->post());
    }

    /**
     * 保存搜索配置
     *
     * 路由：`POST /api/admin/config/search`
     * 鉴权：需 Admin Token
     * 参数：JSON 结构体
     * 返回：`{ config_key }`
     *
     * @param Request $request
     * @return \think\Response
     */
    public function saveSearch(Request $request)
    {
        return $this->saveConfig('config:search', $request->post());
    }

    public function saveComment(Request $request)
    {
        return $this->saveConfig('config:comment', $request->post());
    }

    public function saveAi(Request $request)
    {
        return $this->saveConfig('config:ai', $request->post());
    }

    public function saveCustomerService(Request $request)
    {
        return $this->saveConfig('config:customer-service', $request->post());
    }

    /**
     * 保存推荐配置
     *
     * 路由：`POST /api/admin/config/recommendation`
     * 鉴权：需 Admin Token
     * 参数：JSON 结构体
     * 返回：`{ config_key }`
     *
     * @param Request $request
     * @return \think\Response
     */
    public function saveRecommendation(Request $request)
    {
        return $this->saveConfig('config:recommendation', $request->post());
    }

    /**
     * 保存域名配置
     *
     * 路由：`POST /api/admin/config/domains`
     * 鉴权：需 Admin Token
     * 参数：JSON 结构体
     * 返回：`{ config_key }`
     *
     * @param Request $request
     * @return \think\Response
     */
    public function saveDomains(Request $request)
    {
        return $this->saveConfig('config:domains', $request->post());
    }

    protected function saveConfig(string $key, array $data)
    {
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            return api_response(400, '配置序列化失败', []);
        }
        $config = SystemConfig::find($key);
        if ($config) {
            (new SystemConfig())->writeById($key, [
                'config_value' => $json,
            ]);
        } else {
            (new SystemConfig())->writeById(0, [
                'config_key'   => $key,
                'config_value' => $json,
            ]);
        }
        ConfigService::setCache($key, $json);
        return api_response(200, '配置已更新', ['config_key' => $key]);
    }
}

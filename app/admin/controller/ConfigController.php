<?php
declare(strict_types=1);

namespace app\admin\controller;

use app\BaseController;
use app\model\SystemConfig;
use app\service\ConfigService;
use think\facade\Cache;
use think\Request;

/**
 * 后台推送配置下发，写入 system_config 并同步缓存。
 */
class ConfigController extends BaseController
{
    public function saveStorage(Request $request)
    {
        return $this->saveConfig('config:storage', $request->post());
    }

    public function saveEmail(Request $request)
    {
        return $this->saveConfig('config:email', $request->post());
    }

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

    public function saveRecommendation(Request $request)
    {
        return $this->saveConfig('config:recommendation', $request->post());
    }

    public function saveDomains(Request $request)
    {
        return $this->saveConfig('config:domains', $request->post());
    }

    /**
     * 保存配置到数据库并写入缓存。
     */
    protected function saveConfig(string $key, array $data)
    {
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            return json_error('配置序列化失败', 400);
        }

        $config = SystemConfig::find($key);
        if ($config) {
            $config->config_value = $json;
            $config->save();
        } else {
            $config = new SystemConfig();
            $config->save([
                'config_key'   => $key,
                'config_value' => $json,
            ]);
        }

        ConfigService::setCache($key, $json);

        return json_success(['config_key' => $key], '配置已更新');
    }
}

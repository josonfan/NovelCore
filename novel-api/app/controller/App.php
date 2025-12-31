<?php
declare(strict_types=1);

namespace app\controller;

use app\service\ConfigService;
use app\service\UserService;

class App extends Common
{
    /**
     * App 初始化
     * 路由：POST /api/App/init
     * 鉴权：无需登录（NoAuth，若有 token 则返回用户信息）
     * 入参：无
     * 返回：data { config, user? }
     */
    public function init()
    {
        $configService = app(ConfigService::class);
        
        // 读取公共配置
        $siteConfig = $configService->get('base_config', []); // 站点基础配置
        $csConfig   = $configService->get('customer_service_config', []); // 客服配置
        $appConfig  = $configService->get('app', []); // App 版本与升级配置
        
        $data = [
            'config' => [
                'site'             => $siteConfig,
                'customer_service' => $csConfig,
                'app'              => $appConfig,
            ]
        ];

        // 如果已登录，返回用户信息
        $userId = (int) ($this->request->user_id ?? 0);
        if ($userId > 0) {
            $fields = 'id,username,nickname,avatar,vip_expire,status';
            $user = UserService::info($userId, $fields);
            if ($user) {
                unset($user['id']); // 移除内部 ID
                $data['user'] = $user;
            }
        }

        return $this->ajaxReturn(200, '初始化成功', $data);
    }
}

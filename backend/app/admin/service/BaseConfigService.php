<?php
namespace app\admin\service;

use app\admin\model\BaseConfig;
use app\common\service\BaseService;
use think\exception\ValidateException;

class BaseConfigService extends BaseService
{
    /**
     * 获取配置详情
     * @param int $siteId 站点ID
     * @param string $configName 配置名称
     * @return array|null
     */
    public static function detail(int $siteId, string $configName)
    {
        $info = BaseConfig::where('site_id', $siteId)
            ->where('config_name', $configName)
            ->find();
        
        if (!$info) {
            return null;
        }
        return $info->toArray();
    }

    /**
     * 保存配置
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public static function save(array $data)
    {
        try {
            validate(\app\admin\validate\BaseConfig::class)->scene('save')->check($data);
            
            $siteId = (int)$data['site_id'];
            $configName = trim($data['config_name']);
            $configData = $data['config_data'] ?? [];
            $newData = [
                'site_id' => $siteId,
                'config_name' => $configName,
                'config_data' => $configData
            ];
            // 检查是否存在
            $id = BaseConfig::where('site_id', $siteId)
                ->where('config_name', $configName)
                ->value('id')??1;

            (new BaseConfig())->writeById($id, $newData);
        } catch (ValidateException $e) {
            throw new ValidateException($e->getError());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return $newData;
    }
}

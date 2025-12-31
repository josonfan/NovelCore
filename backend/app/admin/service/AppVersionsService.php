<?php

namespace app\admin\service;

use app\common\service\BaseService;
use app\admin\model\AppVersions;
use think\exception\ValidateException;

class AppVersionsService extends BaseService
{
    /**
     * 列表
     */
    public static function list($data)
    {
        $where = [];
        if (!empty($data['site_id'])) {
            $where[] = ['site_id', '=', $data['site_id']];
        }
        if (!empty($data['platform'])) {
            $where[] = ['platform', '=', $data['platform']];
        }
        
        $limit = $data['limit'] ?? 20;
        $model = new AppVersions();
        $list = $model->getList($where, '*', 'id desc', $limit, $data['page'] ?? 1);
        return $list;
    }

    /**
     * 详情
     */
    public static function detail($id)
    {
        $model = new AppVersions();
        return $model->infoById($id);
    }

    /**
     * 保存（新增/编辑）
     */
    public static function save($data)
    {
        try {
            validate(\app\admin\validate\AppVersions::class)->scene('save')->check($data);
            
            $id = isset($data['id']) ? (int)$data['id'] : 0;
            
            $model = new AppVersions();
            $model->writeById($id, $data);
            $LastVersion = $model->where('site_id',$data['site_id'])
                ->where('platform',$data['platform'])
                ->order('version_code','desc')
                ->limit(1)
                ->find();
            if(!empty($LastVersion)){
                $LastVersion = $LastVersion->toArray();
            }
            if (!empty($id)) {                
                $info = $model->infoById($LastVersion['id']);
            }else{                
                if(empty($data['status'])){
                    $data['status'] = 1;
                    $info = $data;
                }else{
                    if(empty($LastVersion['version_code'])){
                        $LastVersion['version_code'] = 0;
                    }
                    if($data['version_code'] > $LastVersion['version_code']){
                        $info = $data;
                    }else{
                        $info = $LastVersion;
                    }
                }          
            }
            $config_name = 'app_versions_' . $data['platform'].'_config';
            BaseConfigService::save([
                'site_id' => $data['site_id'],
                'config_name' => $config_name,
                'config_data' => $info,
            ]);
            return true;
        } catch (ValidateException $e) {
            throw new ValidateException($e->getError());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
    /**
     * 删除
     */
    public static function delete($id)
    {
        $model = new AppVersions();
        $info = $model->infoById($id);
        $LastVersion = $model->where('site_id',$info['site_id'])
                ->where('platform',$info['platform'])
                ->order('version_code','desc')
                ->limit(1)
                ->find();
        $res = $model->deleteById($id);
        $config_name = 'app_versions_' . $info['platform'].'_config';
        BaseConfigService::save([
            'site_id' => $info['site_id'],
            'config_name' => $config_name,
            'config_data' => $LastVersion,
        ]);
        return $res;
    }

    /**
     * 启停
     */
    public static function toggle($id, $status)
    {
        $model = new AppVersions();
        $info = $model->infoById($id);
        $info['status'] = $status;
        $res = $model->writeById($id, $info);
        $config_name = 'app_versions_' . $info['platform'].'_config';
        BaseConfigService::save([
            'site_id' => $info['site_id'],
            'config_name' => $config_name,
            'config_data' => $info,
        ]);
        return $res;
    }
}

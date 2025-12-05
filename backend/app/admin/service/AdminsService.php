<?php
namespace app\admin\service;

use app\common\service\BaseService;
use app\admin\model\Admins;
use think\exception\ValidateException;



class AdminsService extends BaseService
{
    // 列表
    public static function index(){
        $list = Admins::select();
        return $list;
    }
    /**
     * 添加用户
     *
     * @param array $data
     * @return AdminUser
     * @throws \Exception
     */
    public static function add(array $data)
    {
        try{
            validate(\app\admin\validate\Admins::class)->scene('add')->check($data);
            // 密码加密
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            if(empty($data['nickname'])) $data['nickname'] = $data['username'];
            $data['created_at'] = time();
            $user = new Admins($data);
            $user->writeById((int)$user['id'], $user->toArray());
            return $user;
        }catch(ValidateException $e){
            throw new ValidateException($e->getError());
        }catch(\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }
}

<?php
namespace app\admin\service;

use app\common\service\BaseService;
use app\admin\model\Admins;
use think\exception\ValidateException;



class AdminsService extends BaseService
{
    /**
     * 管理员列表（简版）
     * @return \think\Collection
     */
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
    /**
     * 获取用户信息
     *
     * @param integer $uid
     * @param string $fields
     * @return array
     */
    public static function infoById(int $uid, string $fields = '*')
    {
        $m = new \app\admin\model\Admins();
        $user = $m->infoById($uid, $fields);
        if(empty($user)){
            throw new \Exception('用户不存在');
        }
        return $user;
    }
    /**
     * 编辑用户
     * @param int $uid 用户ID
     * @param array $data 编辑数据
     * @return bool
     */
    public static function edit(int $uid, array $data)
    {
        try{
            
            $user = self::infoById($uid);
            foreach($data as $k => $v){
                $user[$k] = $v;
            }
            $m = new \app\admin\model\Admins();
            $m->writeById($uid, $user);
            return true;
        }catch(ValidateException $e){
            throw new ValidateException($e->getError());
        }catch(\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }
    /**
     * 修改密码
     * @param int $uid 用户ID
     * @param array $data 编辑数据
     * @return bool
     */
    public static function changePassword(int $uid, array $data)
    {
        try{
            validate(\app\admin\validate\Admins::class)->scene('changePassword')->check($data);
            $user = self::infoById($uid);
            if(!password_verify($data['password'], $user['password'])){
                throw new ValidateException('旧密码错误');
            }
            if($data['new_password'] != $data['confirm_password']){
                throw new ValidateException('两次密码不一致');
            }
            $user['password'] = password_hash($data['new_password'], PASSWORD_DEFAULT);
            $m = new \app\admin\model\Admins();
            $m->writeById($uid, $user);
            return true;
        }catch(ValidateException $e){
            throw new ValidateException($e->getError());
        }catch(\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }
}

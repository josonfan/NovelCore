<?php
namespace app\admin\service;

use think\exception\ValidateException;
use think\facade\Db;
use app\admin\model\Admins;
use app\common\service\JwtService;
use think\facade\Cache;
use think\Exception;
use think\facade\Log;
class LoginService
{
    // 登录
    public static function login($data){
        try{
            validate(\app\admin\validate\Login::class)->scene('login')->check($data);
            $username = $data['username'];
            $password = $data['password'];
            $model = new Admins();
            // 验证用户名和密码是否正确
            $user = $model->where('username', $username)->find();
            if (!$user) throw new ValidateException('账户不存在');
            if (!password_verify($password, $user['password'])) throw new ValidateException('用户名或密码错误');
            $user->last_login_at = date('Y-m-d H:i:s');
            $model->writeById((int)$user['id'], $user->toArray());
            $user = getArrayByFields($user,'id,username,nickname,last_login_at,status,created_at');
            // 登录成功
            return $user;
        }catch(ValidateException $e){
            throw new ValidateException ($e->getError());
        }catch(\Exception $e){
            throw new \Exception ($e->getMessage());
        }
    }
    /**
     * 退出登录
     *
     * @return boolean
     */
    public static function logout(): bool
    {
        $request = app()->request;
        $token = $request->header('token') ?: $request->header('authorization');
        if (!$token) {
            return true;
        }
        $claims = JwtService::decode((string)$token);
        $jti = (string)($claims['jti'] ?? '');
        $expClaim = $claims['exp'] ?? null;
        if ($expClaim instanceof \DateTimeInterface) {
            $expTs = $expClaim->getTimestamp();
        } elseif (is_numeric($expClaim)) {
            $expTs = (int)$expClaim;
        } else {
            $expTs = 0;
        }
        if ($jti === '') {
            return true;
        }
        $ttl = $expTs > 0 ? max(0, $expTs - time()) : (int)env('JWT.TTL', 3600);
        Cache::set('jwt_blacklist_' . $jti, 1, $ttl);
        return true;
    }
}

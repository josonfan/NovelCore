<?php
declare(strict_types=1);

namespace app\service;

use app\model\User;
use think\Request;
use think\exception\ValidateException;

class UserAuthService
{
    public static function register(array $data)
    {
        try {
            validate(\app\validate\User::class)->scene('register')->check($data);
            $plain = (string)($data['password'] ?? '');
            $data['password'] = password_hash($plain, PASSWORD_BCRYPT);
            unset($data['confirm_password']);
            $data['client_version'] = request()->header('Client-Version', '');
            $data['device'] = request()->header('Device', '');
            $data['nickname'] = $data['username'];
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
            $user = (new User())->writeById(0, $data);
        }catch(ValidateException $e){
            throw new ValidateException($e->getMessage());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }        
        return $user;
    }

    public static function login(array $data,string $field)
    {
        try {
            $username = trim((string) $data['username']);
            $password = (string) $data['password'];
            $user = User::where('username', $username)->find();
            if (!$user || !(int)$user->status) {
                throw new ValidateException('用户名不存在');
            }
            if (!password_verify($password, (string)$user->password)) {
                throw new ValidateException('密码错误');
            }

        } catch (ValidateException $e) {
            throw new ValidateException($e->getMessage());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        } 
        $jwt = new JwtService();
        $token = $jwt->generateToken($user);
        $ttl = (int) config('jwt.ttl', 3600);
        try {
            \app\service\UserService::writeLoginLog((int)$user->id, request()->ip(), (string)request()->header('X-Device-Id', ''));
        } catch (\Throwable $e) {
        }
        return [
            'token'        => $token,
            'token_expire' => time() + $ttl,
            'user' => UserService::info($user->id,$field),
        ];
    }

    public static function logout(): bool
    {
        $authorization = trim((string) request()->header('Authorization', ''));
        if ($authorization !== '') {
            $token = str_starts_with($authorization, 'Bearer ') ? trim(substr($authorization, 7)) : $authorization;
            (new JwtService())->deleteToken($token);
        }
        return true;
    }

    public static function info(Request $request): array
    {
        $user = $request->user ?? null;
        if (!$user) {
            throw new \app\exception\BusinessException('未登录或令牌无效', 401);
        }
        return [
            'id'         => (int)$user->id,
            'username'   => (string)$user->username,
            'nickname'   => (string)($user->nickname ?? ''),
            'avatar'     => (string)($user->avatar ?? ''),
            'vip_expire' => (int)($user->vip_expire ?? 0),
            'created_at' => (string)($user->created_at ?? ''),
        ];
    }
}

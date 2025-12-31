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
            $data['device'] = request()->header('Client-Type', '');
            $data['nickname'] = $data['username'];
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
            $user = (new User())->writeById(0, $data);
        }catch(ValidateException $e){
            throw new ValidateException($e->getMessage());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }        
        try {
            \app\service\StatsService::onUserRegistered();
        } catch (\Throwable $e) {
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
        try {
            $deviceId = (string)request()->header('X-Device-Id', '');
            if ($deviceId !== '') {
                $brand = (string)request()->header('Device-Brand', '');
                $model = (string)request()->header('Device-Model', '');
                $os = (string)request()->header('OS', '');
                $clientVersion = (string)request()->header('Client-Version', '');
                $ip = (string)request()->ip();
                $mdl = new \app\model\UserDeviceLog();
                $existingId = $mdl->where('user_id', (int)$user->id)->where('device_id', $deviceId)->value('id');
                $payload = [
                    'user_id'       => (int)$user->id,
                    'device_id'     => $deviceId,
                    'device_brand'  => $brand,
                    'device_model'  => $model,
                    'os'            => $os,
                    'client_version'=> $clientVersion,
                    'ip'            => $ip,
                ];
                if ($existingId) {
                    $mdl->writeById((int)$existingId, $payload);
                } else {
                    $mdl->writeById(0, $payload);
                }
            }
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

    /**
     * 重置密码
     * @param string $email
     * @param string $password
     * @return bool
     * @throws ValidateException
     */
    public static function resetPassword(string $email, string $password): bool
    {
        $user = User::where('email', $email)->find();
        if (!$user) {
            throw new ValidateException('该邮箱未注册用户');
        }

        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        // 使用 CacheModel 封装的 writeById 更新 (或者直接 save 如果 writeById 支持部分更新)
        // 这里 User 模型继承 BaseModel, 而 project rules 说 "写入/更新：调用模型实例的 writeById($id, $data)"
        // BaseModel 应该有 writeById
        
        // 由于 UserAuthService 引用了 app\model\User
        (new User())->writeById((int)$user->id, ['password' => $passwordHash]);
        
        return true;
    }

    /**
     * 更新密码
     * @param int $userId
     * @param string $password
     * @return bool
     * @throws ValidateException
     */
    public static function updatePassword(int $userId, string $password): bool
    {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        // 使用 CacheModel 封装的 writeById 更新 (或者直接 save 如果 writeById 支持部分更新)
        // 这里 User 模型继承 BaseModel, 而 project rules 说 "写入/更新：调用模型实例的 writeById($id, $data)"
        // BaseModel 应该有 writeById
        
        // 由于 UserAuthService 引用了 app\model\User
        (new User())->writeById($userId, ['password' => $passwordHash]);
        
        return true;
    }
}

<?php
declare(strict_types=1);

namespace app\service;

use app\model\UserLoginLog;
use app\model\UserDeviceLog;

use app\model\User as UserModel;
use think\exception\ValidateException;

class UserService
{
    public static function list(array $where = [], string $field = '*', string $orderby = 'id desc', int $limit = 10, int $page = 1): array
    {
        $m = new UserModel();
        return $m->getList($where, $field, $orderby, $limit, $page);
    }

    public static function info($id, string $field = '*'): array
    {
        $storage = new StorageService();
        $m = new UserModel();
        $user = $m->infoById($id, $field);
        $user['avatar'] = $storage->getPublicUrl($user['avatar'] ?? '');
        $user['uid'] = getUidByID((int)$user['id']);
        return $user;
    }
    public static function writeLoginLog(int $userId, ?string $ip, string $deviceId = ''): void
    {
        try {
            (new UserLoginLog())->writeById(0, [
                'user_id' => $userId,
                'ip' => $ip,
                'device_id' => $deviceId,
                'login_time' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
        }
    }

    public static function register(string $username, string $password, string $email = ''): array
    {
        if ($username === '' || strlen($username) < 4) {
            throw new ValidateException('用户名长度至少 4 位');
        }
        if ($password === '' || strlen($password) < 6) {
            throw new ValidateException('密码长度至少 6 位');
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new ValidateException('邮箱格式不正确');
        }
        $exists = UserModel::where('username', $username)->value('id');
        if ($exists) {
            throw new ValidateException('用户名已存在');
        }
        $userData = [
            'username'   => $username,
            'nickname'   => $username,
            'avatar'     => '',
            'password'   => password_hash($password, PASSWORD_BCRYPT),
            'status'     => 1,
            'vip_expire' => 0,
        ];
        if ($email !== '') {
            $userData['email'] = $email;
        }
        $user = new UserModel();
        $user->writeById(0, $userData);
        return ['uid' => getUidByID((int)$user->id)];
    }

    public static function authenticate(string $username, string $password): array
    {
        if ($username === '' || $password === '') {
            throw new ValidateException('用户名或密码不能为空');
        }
        $userId = UserModel::where('username', $username)->value('id');
        $userData = $userId ? (new UserModel())->infoById((int)$userId, 'id,username,password,status,vip_expire') : [];
        if (!$userId || !password_verify($password, (string)($userData['password'] ?? ''))) {
            throw new ValidateException('用户名或密码错误');
        }
        if ((int)($userData['status'] ?? 0) !== 1) {
            throw new ValidateException('账号已被禁用');
        }
        return $userData;
    }

    public static function ensureUserExists(int $userId): void
    {
        $exists = self::info($userId);
        if (!$exists) {
            throw new ValidateException('用户不存在');
        }
    }

    public static function update(int $userId, array $data): array
    {
        self::ensureUserExists($userId);
        $nickname = isset($data['nickname']) ? trim((string)$data['nickname']) : null;
        $email = isset($data['email']) ? trim((string)$data['email']) : null;
        $avatar = isset($data['avatar']) ? trim((string)$data['avatar']) : null;
        $update = [];
        if ($nickname !== null) {
            if ($nickname === '' || mb_strlen($nickname) > 32) {
                throw new ValidateException('昵称长度不合法');
            }
            $update['nickname'] = $nickname;
        }
        if ($email !== null) {
            if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new ValidateException('邮箱格式不正确');
            }
            $update['email'] = $email;
        }
        if ($avatar !== null) {
            $update['avatar'] = (new StorageService())->filterDomain($avatar);
        }
        if (!$update) {
            throw new ValidateException('参数错误');
        }
        (new UserModel())->writeById($userId, $update);
        return self::info($userId, 'id,username,email,nickname,avatar,status');
    }

    public static function getLoginLogs(int $userId, int $page, int $pageSize): array
    {
        $m = new UserLoginLog();
        return $m->getList([
            'user_id' => $userId,
        ], '*', 'id desc', $pageSize, $page);
    }
    public static function getDeviceLogs(int $userId, int $page, int $pageSize): array
    {
        $m = new UserDeviceLog();
        return $m->getList([
            'user_id' => $userId,
        ], '*', 'id desc', $pageSize, $page);
    }
    
    
}

<?php
declare(strict_types=1);

namespace app\controller\api;

use app\BaseController;
use app\exception\BusinessException;
use app\model\User;
use app\model\UserLoginLog;
use app\service\JwtService;
use think\Request;

/**
 * 用户模块接口：注册、登录、退出、用户信息。
 */
class UserController extends BaseController
{
    /**
     * 用户注册。
     */
    public function register(Request $request)
    {
        $username = trim((string) $request->post('username', ''));
        $password = (string) $request->post('password', '');
        $email    = trim((string) $request->post('email', ''));
        $invite   = trim((string) $request->post('invite_code', ''));

        if ($username === '' || strlen($username) < 4) {
            return json_error('用户名长度至少 4 位');
        }

        if ($password === '' || strlen($password) < 6) {
            return json_error('密码长度至少 6 位');
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return json_error('邮箱格式不正确');
        }

        $exists = User::where('username', $username)->value('id');
        if ($exists) {
            return json_error('用户名已存在');
        }

        $userData = [
            'username'   => $username,
            'nickname'   => $username,
            'avatar'     => '',
            'password'   => password_hash($password, PASSWORD_BCRYPT),
            'status'     => 1,
            'vip_expire' => 0,
        ];

        // 提醒：如需邮箱登录，请在数据库执行：
        // ALTER TABLE `users` ADD COLUMN `email` VARCHAR(128) NULL COMMENT '邮箱';
        if ($email !== '') {
            $userData['email'] = $email;
            // TODO: 注册时可发送邮箱验证码，接入 MailService::sendVerificationCode
        }

        // 邀请码暂不处理，可在后续扩展
        $user = new User();
        $user->save($userData);

        return json_success(['uid' => $user->id], '注册成功');
    }

    /**
     * 用户登录。
     */
    public function login(Request $request, JwtService $jwtService)
    {
        $username = trim((string) $request->post('username', ''));
        $password = (string) $request->post('password', '');
        $deviceId = $request->header('X-Device-Id', '');
        $ip       = $request->ip();

        if ($username === '' || $password === '') {
            return json_error('用户名或密码不能为空');
        }

        $user = User::where('username', $username)->find();
        if (!$user || !password_verify($password, (string) $user->password)) {
            return json_error('用户名或密码错误');
        }

        if ((int) $user->status !== 1) {
            return json_error('账号已被禁用');
        }

        $token = $jwtService->generateToken($user);
        $ttl   = (int) config('jwt.ttl', 3600);

        $this->writeLoginLog($user->id, $ip, $deviceId);

        return json_success([
            'uid'          => $user->id,
            'username'     => $user->username,
            'token'        => $token,
            'token_expire' => $ttl,
        ], '登录成功');
    }

    /**
     * 退出登录（前端丢弃 Token 即可）。
     */
    public function logout()
    {
        return json_success([], '退出成功');
    }

    /**
     * 获取用户信息。
     */
    public function info(Request $request)
    {
        /** @var User|null $user */
        $user = $request->user ?? null;
        if (!$user) {
            return json_error('未登录或令牌无效', 401)->code(401);
        }

        $fieldsParam = trim((string) $request->get('fields', ''));
        $defaultFields = ['id', 'username', 'nickname', 'avatar', 'vip_expire', 'status', 'email'];
        $fields = $fieldsParam === '' ? $defaultFields : array_filter(array_map('trim', explode(',', $fieldsParam)));

        // 避免泄露密码等敏感字段
        $fields = array_diff($fields, ['password']);
        $data = [];
        foreach ($fields as $field) {
            $data[$field === 'id' ? 'uid' : $field] = $user->getAttr($field);
        }

        // 确保 uid 键存在
        if (!isset($data['uid'])) {
            $data['uid'] = $user->id;
        }

        return json_success($data, '获取成功');
    }

    /**
     * 写入登录日志。
     */
    protected function writeLoginLog(int $userId, ?string $ip, string $deviceId = ''): void
    {
        try {
            $log = new UserLoginLog();
            $log->save([
                'user_id'   => $userId,
                'ip'        => $ip,
                'device_id' => $deviceId,
                'login_time'=> date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            // 日志写入失败不影响主流程
        }
    }
}

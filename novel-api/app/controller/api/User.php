<?php
declare(strict_types=1);

namespace app\controller\api;

use app\BaseController;
use app\exception\BusinessException;
use app\model\User as UserModel;
use app\service\UserService;
use app\service\JwtService;
use think\Request;

class User extends BaseController
{
    /**
     * 用户注册
     *
     * 路由：`POST /api/register`
     * 鉴权：无需登录
     * 参数：`username`、`password`、`email?`
     * 返回：`{ uid }`（采用异步持久化，需队列消费者）
     *
     * @param Request $request
     * @return \think\Response
     */
    public function register(Request $request)
    {
        $username = trim((string) $request->post('username', ''));
        $password = (string) $request->post('password', '');
        $email    = trim((string) $request->post('email', ''));
        $invite   = trim((string) $request->post('invite_code', ''));
        $result = UserService::register($username, $password, $email);
        return api_response(200, '注册成功', $result);
    }

    /**
     * 用户登录
     *
     * 路由：`POST /api/login`
     * 鉴权：无需登录
     * 参数：`username`、`password`
     * 返回：`{ uid, username, token, token_expire }`
     *
     * @param Request $request
     * @param JwtService $jwtService
     * @return \think\Response
     */
    public function login(Request $request, JwtService $jwtService)
    {
        $username = trim((string) $request->post('username', ''));
        $password = (string) $request->post('password', '');
        $deviceId = $request->header('X-Device-Id', '');
        $ip       = $request->ip();
        $userData = UserService::authenticate($username, $password);
        $token = $jwtService->generateToken(new UserModel($userData));
        $ttl   = (int) config('jwt.ttl', 3600);
        UserService::writeLoginLog((int)$userData['id'], $ip, $deviceId);
        return api_response(200, '登录成功', [
            'uid'          => (int)$userData['id'],
            'username'     => (string)$userData['username'],
            'token'        => $token,
            'token_expire' => $ttl,
        ]);
    }

    /**
     * 退出登录
     *
     * 路由：`POST /api/logout`
     * 鉴权：需登录
     * 返回：空对象
     *
     * @return \think\Response
     */
    public function logout()
    {
        return api_response(200, '退出成功', []);
    }

    /**
     * 获取用户信息
     *
     * 路由：`GET /api/user/info`
     * 鉴权：需登录（`Authorization: Bearer <token>`）
     * 参数：`fields?` 逗号分隔字段（默认基础字段，排除 `password`）
     * 返回：用户字段视图，`id` 映射为 `uid`
     *
     * @param Request $request
     * @return \think\Response
     */
    public function info(Request $request)
    {
        $user = $request->user ?? null;
        if (!$user) {
            return api_response(401, '未登录或令牌无效', [])->code(401);
        }
        $fieldsParam = trim((string) $request->get('fields', ''));
        $defaultFields = ['id', 'username', 'nickname', 'avatar', 'vip_expire', 'status', 'email'];
        $fields = $fieldsParam === '' ? $defaultFields : array_filter(array_map('trim', explode(',', $fieldsParam)));
        $fields = array_diff($fields, ['password']);
        $data = [];
        foreach ($fields as $field) {
            $data[$field === 'id' ? 'uid' : $field] = $user->getAttr($field);
        }
        if (!isset($data['uid'])) {
            $data['uid'] = $user->id;
        }
        return api_response(200, '获取成功', $data);
    }

    
}

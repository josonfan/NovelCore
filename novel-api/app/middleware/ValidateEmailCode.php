<?php
declare(strict_types=1);

namespace app\middleware;

use think\Response;
use think\exception\ValidateException;
use app\exception\BusinessException;
use app\service\UserService;
/**
 * 验证邮箱验证码中间件
 */
class ValidateEmailCode
{
    /**
     * 处理请求
     *
     * @param \think\Request $request
     * @param \Closure       $next
     * @return Response
     */
    public function handle($request, \Closure $next)
    {
        // 仅针对需要验证码的接口进行校验
        
        $email = $request->param('email', '', 'trim');
        $code = $request->param('code', '', 'trim');
        $type = $request->param('type', 'bind', 'trim');
        
        if(empty($email)){
            $username = $request->param('username', '', 'trim');
            if(empty($username)){
                throw new ValidateException('参数错误');
            }
            $email = UserService::getEmail($username);
        }
        if (empty($email) || empty($code)) {
            throw new ValidateException('参数错误');
        }
        if (!\app\service\MailService::verifyCode($email, $code, $type)) {
            throw new BusinessException('验证码错误或已过期', 400);
        }            
        
        return $next($request);
    }
}

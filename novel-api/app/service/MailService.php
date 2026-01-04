<?php
declare(strict_types=1);

namespace app\service;

use utils\AwsSendEmail;
use think\exception\ValidateException;
use think\facade\Cache;
use think\facade\Log;

/**
 * 邮件服务（集成 AwsSendEmail）
 */
class MailService
{
    /**
     * 发送验证码邮件
     * 
     * @param string $email 邮箱地址
     * @param string $type 业务类型 (bind, register, reset_pwd)
     * @return bool
     */
    public static function sendVerificationCode(string $email, string $type = 'bind'): bool
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new ValidateException(lang('邮箱格式错误'));
        }        
        // 生成验证码
        $code = (string) random_int(100000, 999999);
        
        // 构建邮件内容
        $title = lang('邮箱验证码标题', ['site_name' => config('site.name')]);
        $content = lang('邮箱验证码内容', ['code' => $code]);        
        // 调用 AwsSendEmail 发送
        try {
            $mailer = new AwsSendEmail();
            $res = $mailer->SendEmailVerificationCode($title, $email, $content);
            if ($res === false) {
                throw new \Exception(lang('发送失败'));
            }
        } catch (\Throwable $e) {
            Log::error("Email send failed to $email: " . $e->getMessage());
            throw new ValidateException(lang('邮件发送失败，请检查邮箱是否正确或联系客服'));
        }

        // 缓存验证码 (10分钟有效)
        $cacheKey = "email_code:{$type}:{$email}";
        Cache::set($cacheKey, $code, 600);

        return true;
    }

    /**
     * 校验验证码
     * 
     * @param string $email 邮箱
     * @param string $code 验证码
     * @param string $type 业务类型
     * @return bool
     */
    public static function verifyCode(string $email, string $code, string $type = 'bind'): bool
    {
        $cacheKey = "email_code:{$type}:{$email}";
        $cachedCode = Cache::get($cacheKey);
        
        if (!$cachedCode || $cachedCode !== $code) {
            return false;
        }
        
        // 验证成功后删除验证码 (防止重复使用)
        Cache::delete($cacheKey);
        return true;
    }
}

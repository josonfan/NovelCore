<?php
declare(strict_types=1);

namespace app\service;

use think\facade\Log;

/**
 * 邮件服务占位（Amazon SES）。
 */
class MailService
{
    /**
     * 发送验证码邮件：目前仅记录日志，未来接入 SES SDK。
     */
    public function sendVerificationCode(string $email, string $code): void
    {
        // TODO: 使用 AWS SDK for SES 发送邮件
        Log::info(sprintf('MailService mock send code %s to %s', $code, $email));
    }
}

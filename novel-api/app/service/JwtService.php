<?php
declare(strict_types=1);

namespace app\service;

use app\exception\BusinessException;
use app\model\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use think\facade\Cache;

/**
 * JWT 服务：生成与解析登录 Token。
 */
class JwtService
{
    /**
     * 生成登录 Token，包含 uid、vip_expire、iat、exp。
     */
    public function generateToken(User $user): string
    {
        $now = time();
        $ttl = (int) config('jwt.ttl', 3600);
        $payload = [
            'uid'        => $user->id,
            'vip_expire' => (int) $user->vip_expire,
            'iat'        => $now,
            'exp'        => $now + $ttl,
        ];

        return JWT::encode($payload, $this->getSecret(), 'HS256');
    }

    /**
     * 解析并验证 Token，失败抛业务异常。
     *
     * @throws BusinessException
     */
    public function parseToken(string $token): array
    {
        try {
            if ($this->isRevoked($token)) {
                throw new BusinessException('令牌已注销', 401);
            }
            $decoded = JWT::decode($token, new Key($this->getSecret(), 'HS256'));
            return (array) $decoded;
        } catch (\Throwable $e) {
            throw new BusinessException('令牌无效或已过期', 401);
        }
    }

    /**
     * 从 Token 获取用户 ID，便捷方法。
     */
    public function getUserIdFromToken(string $token): int
    {
        $payload = $this->parseToken($token);
        return (int) ($payload['uid'] ?? 0);
    }

    /**
     * 获取签名密钥。
     */
    protected function getSecret(): string
    {
        $secret = (string) config('jwt.secret', '');
        if ($secret === '') {
            throw new BusinessException('JWT 密钥未配置', 500);
        }
        return $secret;
    }

    /**
     * 注销 Token（加入黑名单）
     */
    public function deleteToken(string $token): bool
    {
        $key = $this->blacklistKey($token);
        try {
            $decoded = JWT::decode($token, new Key($this->getSecret(), 'HS256'));
            $payload = (array) $decoded;
            $exp = (int) ($payload['exp'] ?? 0);
            $ttl = max(0, $exp - time());
            if ($ttl <= 0) {
                return true;
            }
            return Cache::set($key, 1, $ttl);
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected function isRevoked(string $token): bool
    {
        $key = $this->blacklistKey($token);
        return (bool) Cache::get($key);
    }

    protected function blacklistKey(string $token): string
    {
        return 'jwt:blacklist:' . hash('sha256', $token);
    }
}

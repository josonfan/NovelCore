<?php
declare(strict_types=1);

namespace app\service;

/**
 * 存储封装（B2 占位），后续可接入 SDK 实现上传/私有下载等。
 */
class StorageService
{
    /**
     * 根据存储路径构建公共可访问 URL。
     * 优先使用配置/环境中的 CDN 域名（IMAGE_DOMAIN），否则返回原始路径。
     */
    public function getPublicUrl(string $path): string
    {
        $config = (new ConfigService())->get('storage_config');
        $path = ltrim($path, '/');
        if ($path === '' || str_starts_with($path, 'http')) {
            return $path;
        }

        $cdn = rtrim((string) $config['base_url'], '/');
        if ($cdn !== '') {
            return $cdn . '/' . $path;
        }

        return $path;
    }
    /**
     * 过滤 URL 中的 CDN 域名，返回相对路径。
     */
    public function filterDomain(string $url): string
    {
        $config = (new ConfigService())->get('storage_config');
        $cdn = rtrim((string) $config['base_url'], '/');
        if ($cdn !== '') {
            $url = str_replace($cdn, '', $url);
        }
        return ltrim($url, '/');
    }
    /**
     * 预留上传接口：未来接入 B2 SDK，支持直传/分片等。
     */
    public function uploadFile($file, string $dir): string
    {
        // TODO: 集成 B2 上传逻辑，返回存储路径
        return '';
    }
}

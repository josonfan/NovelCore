<?php
declare(strict_types=1);

namespace app\service;

use GuzzleHttp\Client;

class SiteRegisterService
{
    public static function registerIfNeeded(): void
    {
        try {
            $root = app()->getRootPath();
            $statusFile = rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'site.installed';
            if (is_file($statusFile)) {
                return;
            }
            $adminApiUrl = (string) config('server.admin_api_url', '');
            if ($adminApiUrl === '') {
                return;
            }

            $payload = [
                'name'           => (string) config('site.name', ''),
                'code'           => (string) config('site.code', ''),
                'base_api_url'   => (string) config('site.base_api_url', ''),
                'primary_domain' => (string) config('site.primary_domain', ''),
                'api_token'      => (string) config('site.api_token', ''),
                'remark'         => (string) config('site.remark', ''),
            ];

            if ($payload['name'] === '' || $payload['code'] === '' || $payload['base_api_url'] === '' || $payload['api_token'] === '') {
                return;
            }

            $client = new Client([
                'timeout' => 8,
            ]);
            $url = rtrim($adminApiUrl, '/') . '/Sites/register';
            
            $resp = $client->post($url, [
                'json' => $payload,
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
            ]);
            $ok = $resp->getStatusCode() >= 200 && $resp->getStatusCode() < 300;
            if ($ok) {
                $content = json_encode([
                    'registered' => 1,
                    'registered_at' => date('Y-m-d H:i:s'),
                    'name' => $payload['name'],
                    'code' => $payload['code'],
                    'base_api_url' => $payload['base_api_url'],
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                @file_put_contents($statusFile, $content !== false ? $content : '1');
            }
        } catch (\Throwable $e) {
            
        }
    }
}

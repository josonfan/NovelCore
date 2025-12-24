<?php
namespace storage;

use app\common\model\StorageConfig;
use Aws\S3\S3Client;

class StorageClient
{
    protected array $cfg;
    protected ?S3Client $s3 = null;

    public static function forSite(): self
    {
        $m = new StorageConfig();
        $targetId = $siteId ?? 0;
        $id = $m->where('site_id', $targetId)->value('id'); 
        if (empty($id)) {
            throw new \Exception('未配置存储配置');
        }       
        $cfg = $m->infoById($id); 
        $inst = new self();
        $inst->cfg = $cfg;
        $inst->boot();
        return $inst;
    }

    protected function boot(): void
    {
        $provider = (string)($this->cfg['provider'] ?? 'b2');
        $key = (string)($this->cfg['access_key_id'] ?? '');
        $secret = (string)($this->cfg['secret_key'] ?? '');
        $regionName = (string)($this->cfg['bucket_region'] ?? 'us-east-1');
        $endpoint = null;
        if (!empty($this->cfg['endpoint'])) {
            $endpoint = (string)$this->cfg['endpoint'];
        } else {
            if ($provider === 'b2') {
                $endpoint = (string)(env('STORAGE.B2_ENDPOINT', '') ?: null);
            } elseif ($provider === 's3') {
                $endpoint = (string)(env('STORAGE.S3_ENDPOINT', '') ?: null);
            }
        }
        $opts = [
            'version' => 'latest',
            'region' => $regionName ?: 'us-east-1',
            'credentials' => ['key' => $key, 'secret' => $secret],
        ];
        if (!empty($endpoint)) {
            $opts['endpoint'] = $endpoint;
            $opts['use_path_style_endpoint'] = true;
        }
        $this->s3 = new S3Client($opts);
    }

    public function uploadFile(string $key, string $filePath, ?string $contentType = null): array
    {
        $ext = strtolower(pathinfo($key, PATHINFO_EXTENSION));
        $allowed = $this->allowedSuffixes();
        if (!empty($allowed) && $ext && !in_array($ext, $allowed, true)) {
            throw new \InvalidArgumentException('forbidden_suffix');
        }
        $bucket = (string)($this->cfg['bucket_name'] ?? '');
        $ct = $contentType ?: @mime_content_type($filePath) ?: 'application/octet-stream';
        $this->s3->putObject([
            'Bucket' => $bucket,
            'Key' => $key,
            'Body' => fopen($filePath, 'rb'),
            'ContentType' => $ct,
            'ACL' => 'public-read',
        ]);
        $base = rtrim((string)($this->cfg['base_url'] ?? ''), '/');
        $url = $base !== '' ? ($base . '/' . ltrim($key, '/')) : $this->s3->getObjectUrl($bucket, $key);
        return ['key' => $key, 'url' => $url];
    }

    public function allowedSuffixes(): array
    {
        $raw = (string)($this->cfg['allowed_suffix'] ?? '');
        if ($raw === '') return [];
        $parts = array_filter(array_map(function($s){ return strtolower(trim($s)); }, explode(',', $raw)));
        return array_values(array_unique($parts));
    }
    /**
     * 根据存储路径构建公共可访问 URL。
     * 优先使用配置/环境中的 CDN 域名（IMAGE_DOMAIN），否则返回原始路径。
     */
    public function getPublicUrl(string $path): string
    {
        $config = $this->cfg;
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
}

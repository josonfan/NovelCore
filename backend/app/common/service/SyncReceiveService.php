<?php
declare(strict_types=1);

namespace app\common\service;

class SyncReceiveService
{
    public static function handle(string $raw): bool
    {
        try {
            $data = json_decode($raw, true);
            if (!is_array($data)) {
                return false;
            }
            $type = (string) ($data['type'] ?? '');
            $op   = strtolower((string) ($data['operation'] ?? ''));
            $id   = $data['id'] ?? null;
            $payload = (array) ($data['data'] ?? []);
            if ($type === '' || $op === '') {
                return false;
            }
            $base_api_url = $data['base_api_url'] ?? '';
            if (empty($base_api_url)) {
                return false;
            }
            $siteId = (new \app\common\model\Sites())->where('base_api_url',$base_api_url)->value('id');
            if(empty($siteId)){
                return false;
            }      
            if (empty($payload) && !in_array($op, ['delete', 'remove'], true)) {
                return false;
            }
            $payload['site_id'] = $siteId;
            
            $modelClass = self::resolveModelClass($type);
            if ($modelClass === null) {
                return false;
            }
            $model = new $modelClass();
            $pk = $model->getPk();
            if (isset($payload[$pk]) && $id === null) {
                $id = $payload[$pk];
            }
            $payload = self::formatPayload($model,$type, $payload, $siteId);
            if(!empty($payload[$pk])&&(int)$id!==(int)$payload[$pk]){
                $id = $payload[$pk];
            }
            $ok = false;            
            switch ($op) {
                case 'update':
                case 'upsert':
                case 'save':
                case 'create':
                case 'insert':
                    $ok = $model->writeById($id ?? 0, $payload);
                    break;
                case 'delete':
                case 'remove':
                    if (empty($id)) {
                        $ok = false;
                    } else {
                        $ok = $model->deleteById($id);
                    }
                    break;
                default:
                    $ok = false;
            }
            // 仍然落盘审计
            // self::save($raw);
            return (bool)$ok;
        } catch (\Throwable $e) {
            return false;
        }
    }
    private static function formatPayload($model,string $type, array $payload,$siteId): array
    {
        
        $pk = $model->getPk();
        $unset = [];
        switch ($type) {
            case 'user':
                $payload['user_id'] = $payload['id'];
                $payload[$pk] = $model->where('site_id',$siteId)->where('user_id',$payload['user_id'])->value('id');
                if(empty($payload[$pk])){
                    $payload[$pk] = 0;
                }else{
                    $payload[$pk] = (int)$payload[$pk];
                }
                
                break;
            case 'comment':
                $payload['comment_id'] = $payload['id'];
                $payload[$pk] = $model->where('site_id',$siteId)->where('comment_id',$payload['comment_id'])->value('id');
                
                if(empty($payload[$pk])){
                    $payload[$pk] = 0;
                }else{
                    $payload[$pk] = (int)$payload[$pk];
                }
                
                break;
            
            default:
                break;
        }
        foreach ($unset as $key) {            
            unset($payload[$key]);
        }        
        return $payload;
    }
    private static function resolveModelClass(string $type): ?string
    {
        return match ($type) {
            'user' => \app\common\model\SiteUsers::class,
            'comment' => \app\common\model\SiteComments::class,            
            default      => null,
        };
    }

    public static function save(string $raw): bool
    {
        try {
            $dir = rtrim(app()->getRuntimePath(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'sync';
            if (!is_dir($dir)) {
                @mkdir($dir, 0777, true);
            }
            $file = $dir . DIRECTORY_SEPARATOR . 'receive-' . date('Ymd-His') . '-' . substr(sha1((string) microtime(true)), 0, 6) . '.json';
            $ok = @file_put_contents($file, $raw !== '' ? $raw : '{}');
            return $ok !== false;
        } catch (\Throwable $e) {
            return false;
        }
    }
}

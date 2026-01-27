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
            $code = $data['code'] ?? '';
            if (empty($code)) {
                return false;
            }
            $siteId = (new \app\common\model\Sites())->where('code',$code)->value('id');
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
            $payload['updated_at'] = date('Y-m-d H:i:s');
            $ok = false;            
            switch ($op) {
                case 'update':
                case 'upsert':
                case 'save':
                case 'create':
                case 'insert':
                    $ok = $model->writeById($id ?? 0, $payload, false);
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
            case 'order':
                $payload['order_id'] = $payload['id'];
                $payload[$pk] = $model->where('site_id',$siteId)->where('order_id',$payload['order_id'])->value('id');
                
                if(empty($payload[$pk])){
                    $payload[$pk] = 0;
                }else{
                    $payload[$pk] = (int)$payload[$pk];
                }
                
                break;
            case 'stats':
                $payload['stats_id'] = $payload['id'];
                $payload[$pk] = $model->where('site_id',$siteId)->where('stats_id',$payload['stats_id'])->value('id');
                
                if(empty($payload[$pk])){
                    $payload[$pk] = 0;
                }else{
                    $payload[$pk] = (int)$payload[$pk];
                }
                
                break;
            case 'ticket':
                $payload['ticket_id'] = $payload['id'];
                $payload[$pk] = $model->where('site_id',$siteId)->where('ticket_id',$payload['ticket_id'])->value('id');
                
                if(empty($payload[$pk])){
                    $payload[$pk] = 0;
                }else{
                    $payload[$pk] = (int)$payload[$pk];
                }
                
                break;
            case 'ticket_attachment':
                $payload['ticket_attachment_id'] = $payload['id'];
                $payload[$pk] = $model->where('site_id',$siteId)->where('ticket_no',$payload['ticket_no'])->value('id');
                
                if(empty($payload[$pk])){
                    $payload[$pk] = 0;
                }else{
                    $payload[$pk] = (int)$payload[$pk];
                }
                
                break;
            case 'ticket_reply':
                $payload['ticket_reply_id'] = $payload['id'];
                $payload[$pk] = $model->where('site_id',$siteId)->where('ticket_reply_id',$payload['ticket_reply_id'])->value('id');
                
                if(empty($payload[$pk])){
                    $payload[$pk] = 0;
                }else{
                    $payload[$pk] = (int)$payload[$pk];
                }
                
                break;
            case 'feedback':
                $payload['feedback_id'] = $payload['id'];
                $payload[$pk] = $model->where('site_id',$siteId)->where('feedback_id',$payload['feedback_id'])->value('id');
                
                if(empty($payload[$pk])){
                    $payload[$pk] = 0;
                }else{
                    $payload[$pk] = (int)$payload[$pk];
                }
                
                break;
            case 'feedback_attachment':
                $payload['feedback_attachment_id'] = $payload['id'];
                $payload[$pk] = $model->where('site_id',$siteId)->where('feedback_attachment_id',$payload['feedback_attachment_id'])->value('id');
                
                if(empty($payload[$pk])){
                    $payload[$pk] = 0;
                }else{
                    $payload[$pk] = (int)$payload[$pk];
                }
                
                break;
            case 'novel':
                $site_count_json = $model->where('novel_uuid',$payload['novel_uuid'])->value('site_count_json');
                $data = [];
                if(!empty($site_count_json)){
                    $site_count_json = json_decode($site_count_json,true);
                }else{
                    $site_count_json = [];
                }
                $site_count_json[$siteId] = [
                    'like_count' => (int)$payload['like_count'],
                    'fav_count' => (int)$payload['fav_count'],
                    'view_count' => (int)$payload['view_count'],
                ];
                $like_count = 0;
                $fav_count = 0;
                $view_count = 0;
                $data['site_count_json'] = json_encode($site_count_json,JSON_UNESCAPED_UNICODE);
                foreach ($site_count_json as $key => $value) {
                    $like_count = $like_count+(int)$value['like_count'];
                    $fav_count = $fav_count+(int)$value['fav_count'];
                    $view_count = $view_count+(int)$value['view_count'];
                }
                $data['like_count'] = $like_count;
                $data['fav_count'] = $fav_count;
                $data['view_count'] = $view_count;
                $data[$pk] = $payload[$pk];                
                $payload = $data;
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
            'order' => \app\common\model\SiteOrders::class,
            'stats' => \app\common\model\SiteStats::class,
            'ticket' => \app\common\model\SiteTickets::class,
            'ticket_attachment' => \app\common\model\SiteTicketAttachments::class,
            'ticket_reply' => \app\common\model\SiteTicketReplies::class,
            'feedback' => \app\common\model\SiteFeedbacks::class,
            'feedback_attachment' => \app\common\model\SiteFeedbackAttachments::class,
            'novel' => \app\common\model\Novels::class,
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

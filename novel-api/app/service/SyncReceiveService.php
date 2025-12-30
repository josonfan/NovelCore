<?php
declare(strict_types=1);

namespace app\service;

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
            if (empty($payload) && !in_array($op, ['delete', 'remove'], true)) {
                return false;
            }
            $config_type = '';
            if (str_ends_with($type, '_config')) {
                $config_type = $type;
                $type = 'system_config';
            }
            $modelClass = self::resolveModelClass($type);
            if ($modelClass === null) {
                return false;
            }
            $model = new $modelClass();
            $pk = $model->getPk();
            if (isset($payload[$pk]) && $id === null) {
                $id = $payload[$pk];
            }
            $payload = self::formatPayload($type, $payload,$config_type);
            if(!empty($payload[$pk])&&(int)$id!==(int)$payload[$pk]){
                $id = $payload[$pk];
            }
            if ($type === 'system_config') {
                $id = $config_type;
                $payload=[
                    'config_key' => $id,
                    'config_value' => json_encode($payload,JSON_UNESCAPED_UNICODE),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
            }
            $payload['updated_at'] = date('Y-m-d H:i:s');
            $ok = false;            
            switch ($op) {
                case 'update':
                case 'upsert':
                case 'save':
                case 'create':
                case 'insert':                    
                    $ok = $model->writeById($id ?? 0, $payload, false, false);
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
            
            trace($e->getMessage());
            return false;
        }
    }
    private static function formatPayload(string $type, array $payload,string $config_type=''): array
    {
        $unset = [];
        switch ($type) {
            case 'categories':
                break;
            case 'tags':
                break;
            case 'novels':
                $unset = [ 'audit_status','audit_remark','audit_admin_id','audit_at'];    
                if(empty($payload['author_id'])){
                    $payload['author_id'] = 0;
                }            
                break;
            case 'chapters':
                $unset = ['audit_status','audit_remark','audit_admin_id','audit_at','updated_at','content'];    
                break;
            case 'domain_list':   
                $unset = ['site_id','updated_at'];
                break;
            case 'system_config':
                $unset = ['site_id','updated_at'];
                break;
            case 'site_users':
                $payload['id'] =$payload['user_id'];
                $unset = ['user_id','site_id','last_synced_at'];
                break;
            case 'site_comments':
                $payload['id'] =$payload['comment_id'];
                $unset = ['comment_id','site_id','last_synced_at'];
                break;
            case 'payment_channel':
                break;
            case 'vip':
                break;
            case 'novel_tags':
                break;  
            case 'site_tickets':
                $payload['id'] =$payload['ticket_id'];
                $unset = ['ticket_id','site_id','last_synced_at'];
                break;
            case 'site_ticket_attachments':
                $payload['id'] =$payload['ticket_attachment_id'];
                $unset = ['ticket_attachment_id','site_id','last_synced_at'];
                break;
            case 'site_feedbacks':
                $payload['id'] =$payload['feedback_id'];
                $unset = ['feedback_id','site_id','last_synced_at'];
                break;
            case 'site_feedback_attachments':
                $payload['id'] =$payload['feedback_attachment_id'];
                $unset = ['feedback_attachment_id','site_id','last_synced_at'];
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
            'categories' => \app\model\Category::class,
            'tags'       => \app\model\Tag::class,
            'novels'     => \app\model\Novel::class,
            'chapters'   => \app\model\Chapter::class,
            'chapter_contents' => \app\model\ChapterContent::class,
            'domains'    => \app\model\Domain::class,
            'novel_tags' => \app\model\NovelTag::class,
            'domain_list' => \app\model\Domain::class,
            'system_config' => \app\model\SystemConfig::class,
            'site_users' => \app\model\User::class,
            'site_comments' => \app\model\Comment::class,
            'payment_channel' => \app\model\PaymentChannel::class,
            'vip' => \app\model\Vip::class,
            'site_tickets' => \app\model\Ticket::class,
            'site_ticket_attachments' => \app\model\TicketAttachment::class,
            'site_feedbacks' => \app\model\Feedback::class,
            'site_feedback_attachments' => \app\model\FeedbackAttachment::class,
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

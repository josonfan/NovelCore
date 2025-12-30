<?php
declare(strict_types=1);

namespace app\service;

use app\model\Feedback;
use app\model\FeedbackAttachment;
use think\exception\ValidateException;
use think\facade\Db;

class FeedbackService
{
    protected const ALLOWED_TYPES = [
        'suggestion',
        'usage_issue',
        'pay_member',
    ];

    public static function create(?int $userId, array $payload, array $headers = []): array
    {
        $type = trim((string)($payload['type'] ?? ''));
        $content = trim((string)($payload['content'] ?? ''));
        $contact = trim((string)($payload['contact'] ?? ''));
        $attachments = is_array($payload['attachments'] ?? []) ? (array)$payload['attachments'] : [];

        if ($type === '' || $content === '') {
            throw new ValidateException('参数错误');
        }
        if (!in_array($type, self::ALLOWED_TYPES, true)) {
            throw new ValidateException('参数错误');
        }
        if (count($attachments) > 3) {
            $attachments = array_slice($attachments, 0, 3);
        }

        $deviceInfo = self::buildDeviceInfo($headers);
        $appVersion = (string)($headers['client-version'] ?? ($headers['Client-Version'] ?? ''));
        $clientType = (string)($headers['client-type'] ?? ($headers['Client-Type'] ?? ''));

        $feedbackNo = 'F' . date('YmdHis') . str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);

        Db::startTrans();
        try {
            $feedbackData = [
                'feedback_no' => $feedbackNo,
                'user_id'     => $userId ?: null,
                'type'        => $type,
                'content'     => $content,
                'contact'     => $contact ?: null,
                'device_info' => $deviceInfo ?: null,
                'app_version' => $appVersion ?: null,
                'client_type' => $clientType ?: null,
                'status'      => 0,
                'reply_content'   => null,
                'reply_admin_id'  => null,
                'reply_at'        => null,
                'image_count'     => count($attachments)??0,
            ];
            (int) (new Feedback())->writeById(0, $feedbackData);

            $imageCount = 0;
            if (!empty($attachments)) {
                $storage = new StorageService();
                foreach ($attachments as $url) {
                    $urlStr = $storage->filterDomain(trim((string)$url));
                    if ($urlStr === '') {
                        continue;
                    }
                    $typeStr = self::inferType($urlStr);
                    if ($typeStr === 'image') {
                        $imageCount++;
                    }
                    (new FeedbackAttachment())->writeById(0, [
                        'feedback_no' => $feedbackNo,
                        'file_url'    => $urlStr,
                        'file_type'   => $typeStr,
                    ]);
                }
            }            
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollback();
            throw $e;
        }

        return $feedbackData;
    }

    public static function list(int $userId, int $page = 1, int $limit = 10, ?int $status = null): array
    {
        $page = max(1, $page);
        $limit = min(50, max(1, $limit));
        $m = new Feedback();
        $where = ['user_id' => $userId];
        if ($status !== null && $status >= 0) {
            $where['status'] = $status;
        }
        return $m->getList(formatWhere($where), 'id,feedback_no,type,status,reply_content,reply_at,image_count,created_at', 'created_at desc, id desc', $limit, $page);
    }

    public static function info(int $userId, int $id): array
    {
        $m = new Feedback();
        $feedback = $m->infoById($id, 'id,feedback_no,user_id,type,content,contact,device_info,app_version,client_type,status,reply_content,reply_admin_id,reply_at,image_count,created_at');
        if (empty($feedback) || (int)$feedback['user_id'] !== $userId) {
            throw new ValidateException('资源不存在');
        }
        $rows = FeedbackAttachment::where('feedback_no', $feedback['feedback_no'])
            ->order('id', 'asc')
            ->column(['file_url','file_type','mime_type','size_bytes','created_at'], 'id');
        $storage = new StorageService();
        $feedback['attachments'] = array_values(array_map(function($row) use ($storage) {
            return [
                'file_url'  => $storage->getPublicUrl($row['file_url']),
                'file_type' => $row['file_type'],
                'mime_type' => $row['mime_type'] ?? null,
                'size_bytes'=> $row['size_bytes'] ?? null,
                'created_at'=> $row['created_at'],
            ];
        }, $rows));
        return $feedback;
    }

    protected static function inferType(string $url): string
    {
        $ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
        $imageExt = ['jpg','jpeg','png','gif','webp'];
        $videoExt = ['mp4','mov','webm','mkv'];
        if (in_array($ext, $imageExt, true)) {
            return 'image';
        }
        if (in_array($ext, $videoExt, true)) {
            return 'video';
        }
        return 'image';
    }

    protected static function buildDeviceInfo(array $headers): string
    {
        $brand = (string)($headers['device-brand'] ?? ($headers['Device-Brand'] ?? ''));
        $model = (string)($headers['device-model'] ?? ($headers['Device-Model'] ?? ''));
        $os    = (string)($headers['os'] ?? ($headers['OS'] ?? ''));
        $parts = array_filter([$os, $brand, $model], fn($v) => trim((string)$v) !== '');
        return implode(' · ', $parts);
    }
    public static function types(): array
    {
        return [
            ['code' =>'suggestion', 'name' =>  '功能建议'],
            ['code' =>'usage_issue', 'name' =>  '使用问题'],
            ['code' =>'pay_member', 'name' =>  '充值会员'],
        ];
    }

    public static function statuses(): array
    {
        return [
            ['code' => 0, 'name' => '待处理'],
            ['code' => 1, 'name' => '处理中'],
            ['code' => 2, 'name' => '已解决'],
            ['code' => 3, 'name' => '已关闭'],
            ['code' => 4, 'name' => '已拒绝'],
        ];
    }
     
}


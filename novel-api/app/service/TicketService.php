<?php
declare(strict_types=1);

namespace app\service;

use app\model\Ticket;
use app\model\TicketAttachment;
use think\exception\ValidateException;
use think\facade\Db;

class TicketService
{
    protected const ALLOWED_TYPES = [
        'pay_issue',       // 支付问题
        'usage_issue',     // 使用问题
        'content_report',  // 内容举报
        'suggestion',      // 建议反馈
    ];

    public static function create(int $userId, array $payload): array
    {
        $type = trim((string)($payload['type'] ?? ''));
        $title = trim((string)($payload['title'] ?? ''));
        $description = trim((string)($payload['description'] ?? ''));
        $contact = trim((string)($payload['contact'] ?? ''));
        $workId = isset($payload['work_id']) ? (int)$payload['work_id'] : null;
        $attachments = $payload['attachments'] ?? [];

        if ($type === '' || $title === '' || $description === '' || $contact === '') {
            throw new ValidateException('参数错误');
        }
        if (!in_array($type, self::ALLOWED_TYPES, true)) {
            throw new ValidateException('参数错误');
        }

        $ticketNo = 'T' . date('YmdHis') . str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);

        Db::startTrans();
        try {
            $ticketData = [
                'ticket_no'  => $ticketNo,
                'user_id'    => $userId,
                'type'       => $type,
                'title'      => $title,
                'description'=> $description,
                'work_id'    => $workId,
                'contact'    => $contact,
                'priority'   => (int)($payload['priority'] ?? 0),
                'status'     => 0,
            ];
            $ticketId = (int) (new Ticket())->writeById(0, $ticketData);
            
            if (!empty($attachments)) {
                $items = is_array($attachments) ? $attachments : [];
                $storage = new StorageService();
                foreach ($items as $url) {
                    $urlStr = $storage->filterDomain(trim((string)$url));
                    if ($urlStr === '') {
                        continue;
                    }
                    (new TicketAttachment())->writeById(0, [
                        'ticket_no' => $ticketNo,
                        'file_url'  => $urlStr,
                        'file_type' => self::inferType($urlStr),
                    ]);
                }
            }
            Db::commit();
        } catch (\Throwable $e) {
            Db::rollback();
            throw $e;
        }

        return $ticketData;
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

    public static function list(int $userId, int $page = 1, int $limit = 10, ?string $type = null, ?int $status = null): array
    {
        $page = max(1, $page);
        $limit = min(50, max(1, $limit));
        $m = new Ticket();
        $raw = [
            'user_id' => $userId,
        ];
        if ($type !== null && $type !== '' && in_array($type, self::ALLOWED_TYPES, true)) {
            $raw['type'] = $type;
        }
        if ($status !== null && $status >= 0) {
            $raw['status'] = $status;
        }
        $where = formatWhere($raw);
        $res = $m->getList($where, 'id,ticket_no,type,title,priority,status,reply_content,reply_at,created_at', 'created_at desc, id desc', $limit, $page);
        return $res;
    }

    public static function info(int $userId, int $id): array
    {
        $m = new Ticket();
        $ticket = $m->infoById($id, 'id,ticket_no,user_id,type,title,description,work_id,contact,priority,status,reply_content,reply_admin_id,reply_at,created_at');
        if (empty($ticket) || (int)$ticket['user_id'] !== $userId) {
            throw new ValidateException('资源不存在');
        }
        $attachments = TicketAttachment::where('ticket_no', $ticket['ticket_no'])
            ->order('id', 'asc')
            ->column(['file_url','file_type','mime_type','size_bytes','created_at'], 'id');
        
        $ticket['attachments'] = array_values(array_map(function($row){
            $storage = new StorageService();
            return [
                'file_url'  => $storage->getPublicUrl($row['file_url']),
                'file_type' => $row['file_type'],
                'mime_type' => $row['mime_type'] ?? null,
                'size_bytes'=> $row['size_bytes'] ?? null,
                'created_at'=> $row['created_at'],
            ];
        }, $attachments));
        return $ticket;
    }

    public static function types(): array
    {
        return [
            ['code' => 'pay_issue', 'name' => '支付问题'],
            ['code' => 'usage_issue', 'name' => '使用问题'],
            ['code' => 'content_report', 'name' => '内容举报'],
            ['code' => 'suggestion', 'name' => '建议反馈'],
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

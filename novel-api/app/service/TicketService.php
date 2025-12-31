<?php
declare(strict_types=1);

namespace app\service;

use app\model\Ticket;
use app\model\TicketAttachment;
use app\model\TicketReply;
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

    public static function reply(int $userId, int $ticketId, string $content, array $attachments = []): array
    {
        $content = trim($content);
        if ($content === '' && empty($attachments)) {
            throw new ValidateException('回复内容不能为空');
        }
        
        $ticketModel = new Ticket();
        $ticket = $ticketModel->infoById($ticketId, 'id,user_id,status');
        if (!$ticket || (int)$ticket['user_id'] !== $userId) {
            throw new ValidateException('工单不存在');
        }

        // 状态：0待处理 1处理中 2已解决 3已关闭 4已拒绝
        if (in_array((int)$ticket['status'], [3, 4], true)) {
             throw new ValidateException('该工单已结束，无法回复');
        }

        // 处理附件
        $validAttachments = [];
        if (!empty($attachments)) {
            $storage = new StorageService();
            foreach ($attachments as $url) {
                $urlStr = $storage->filterDomain(trim((string)$url));
                if ($urlStr !== '') {
                    $validAttachments[] = $urlStr;
                }
            }
        }

        $replyData = [
            'ticket_id'   => $ticketId,
            'user_id'     => $userId,
            'user_type'   => 1, // User
            'content'     => $content,
            'attachments' => empty($validAttachments) ? null : json_encode($validAttachments),
            'created_at'  => date('Y-m-d H:i:s'),
        ];
        
        (new TicketReply())->writeById(0, $replyData);
        
        return $replyData;
    }

    public static function getReplies(int $userId, int $ticketId): array
    {
        $ticketModel = new Ticket();
        $ticket = $ticketModel->infoById($ticketId, 'id,user_id');
        if (!$ticket || (int)$ticket['user_id'] !== $userId) {
            throw new ValidateException('工单不存在');
        }

        $replies = TicketReply::where('ticket_id', $ticketId)
            ->order('id', 'asc')
            ->select()
            ->toArray();

        $storage = new StorageService();
        return array_map(function($row) use ($storage) {
            $attachments = [];
            if (!empty($row['attachments'])) {
                
                if (is_array($row['attachments'])) {
                    foreach ($row['attachments'] as $url) {
                        $attachments[] = $storage->getPublicUrl($url);
                    }
                }
            }
            return [
                'id'        => $row['id'],
                'user_type' => (int)$row['user_type'], // 1=User, 2=Admin, 3=System
                'content'   => $row['content'],
                'attachments' => $attachments,
                'created_at' => $row['created_at'],
            ];
        }, $replies);
    }

    public static function evaluate(int $userId, int $ticketId, int $score, string $content): bool
    {
        $ticketModel = new Ticket();
        $ticket = $ticketModel->infoById($ticketId, 'id,user_id,status,score');
        if (!$ticket || (int)$ticket['user_id'] !== $userId) {
            throw new ValidateException('工单不存在');
        }
        
        if ((int)($ticket['score'] ?? 0) > 0) {
             throw new ValidateException('您已评价过该工单');
        }
        
        $ticketModel->writeById($ticketId, [
            'score' => max(1, min(5, $score)),
            'evaluation' => trim($content),
            'evaluation_at' => date('Y-m-d H:i:s'),
        ]);
        
        return true;
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
        $ticket = $m->infoById($id, 'id,ticket_no,user_id,type,title,description,work_id,contact,priority,status,reply_content,reply_admin_id,reply_at,created_at,score,evaluation,evaluation_at');
        if (empty($ticket) || (int)$ticket['user_id'] !== $userId) {
            throw new ValidateException('资源不存在');
        }
        $attachments = TicketAttachment::where('ticket_no', $ticket['ticket_no'])
            ->order('id', 'asc')
            ->column(['file_url','file_type','mime_type','size_bytes','created_at'], 'id');
        
        $storage = new StorageService();
        $ticket['attachments'] = array_values(array_map(function($row) use ($storage) {
            return [
                'file_url'  => $storage->getPublicUrl($row['file_url']),
                'file_type' => $row['file_type'],
                'mime_type' => $row['mime_type'] ?? null,
                'size_bytes'=> $row['size_bytes'] ?? null,
                'created_at'=> $row['created_at'],
            ];
        }, $attachments));

        // 获取沟通记录
        $replies = TicketReply::where('ticket_id', $ticket['id'])
            ->order('id', 'asc')
            ->select()
            ->toArray();

        $ticket['replies'] = array_map(function($row) use ($storage) {
            $attachments = [];
            if (!empty($row['attachments'])) {
                $arr = json_decode($row['attachments'], true);
                if (is_array($arr)) {
                    foreach ($arr as $url) {
                        $attachments[] = $storage->getPublicUrl($url);
                    }
                }
            }
            return [
                'id'        => $row['id'],
                'user_type' => (int)$row['user_type'], // 1=User, 2=Admin, 3=System
                'content'   => $row['content'],
                'attachments' => $attachments,
                'created_at' => $row['created_at'],
            ];
        }, $replies);

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

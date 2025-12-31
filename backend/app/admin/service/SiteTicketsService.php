<?php
namespace app\admin\service;

use app\admin\model\SiteTickets;
use app\admin\model\SiteTicketAttachments;
use app\admin\model\SiteTicketReplies;
use think\exception\ValidateException;
use storage\StorageClient;

class SiteTicketsService
{
    public static function list(array $where = [], string $field = '*', string $orderby = 'id desc', int $limit = 10, int $page = 1): array
    {
        $m = new SiteTickets();
        return $m->getList($where, $field, $orderby, $limit, $page);
    }

    public static function detail(int $id, string $field = '*'): array
    {
        $m = new SiteTickets();
        $info = $m->infoById($id, $field);
        if (empty($info)) {
            return [];
        }
        $atts = (new SiteTicketAttachments())
            ->where('ticket_no', (string)$info['ticket_no'])
            ->order('id asc')
            ->select()
            ->toArray();
        
        foreach ($atts as $key => $att) {
            $atts[$key]['file_url'] = StorageClient::forSite()->getPublicUrl((string) ($att['file_url'] ?? ''));
        }
        $info['attachments'] = $atts;
        return $info;
    }

    public static function process(int $id, int $status, string $replyContent = '', int $adminId = 0): bool
    {
        try {
            validate(\app\admin\validate\SiteTicketProcess::class)->scene('process')->check(['id' => $id, 'status' => $status, 'reply_content' => $replyContent]);
            $m = new SiteTickets();
            $data = ['status' => $status];
            if ($replyContent !== '') {
                $data['reply_content'] = $replyContent;
                $data['reply_admin_id'] = (int)$adminId;
                $data['reply_at'] = date('Y-m-d H:i:s');
            }
            return $m->writeById($id, $data);
        } catch (ValidateException $e) {
            throw new ValidateException($e->getError());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public static function reply(int $id, string $content, array $attachments = [], int $adminId = 0): bool
    {
        try {
            validate(\app\admin\validate\SiteTicketReply::class)->scene('reply')->check(['id' => $id, 'content' => $content]);
            
            $ticketModel = new SiteTickets();
            $ticket = $ticketModel->infoById($id);
            if (empty($ticket)) {
                throw new \Exception('工单不存在');
            }

            $replyData = [
                'site_id' => $ticket['site_id'],
                'ticket_id' => $ticket['ticket_id'],
                'user_id' => $adminId,
                'user_type' => 2, // 1:User, 2:Admin, 3:System
                'content' => $content,
                'attachments' => json_encode($attachments, JSON_UNESCAPED_UNICODE),
                'ticket_replies_id' => 0,
            ];

            $replyModel = new SiteTicketReplies();
            $replyModel->writeById(0, $replyData);

            $updateData = [
                'reply_content' => $content,
                'reply_admin_id' => $adminId,
                'reply_at' => date('Y-m-d H:i:s'),
            ];
            if ($ticket['status'] == 0) {
                $updateData['status'] = 1;
            }
            $ticketModel->writeById($id, $updateData);

            return true;
        } catch (ValidateException $e) {
            throw new ValidateException($e->getError());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public static function replies(int $id): array
    {
        $ticketModel = new SiteTickets();
        $ticket = $ticketModel->infoById($id);
        if (empty($ticket)) {
            throw new \Exception('工单不存在');
        }
        $ticketId = $ticket['ticket_id'];
        $list = (new SiteTicketReplies())
            ->where('ticket_id', $ticketId)
            ->order('created_at asc')
            ->select()
            ->toArray();

        foreach ($list as &$item) {
            // 处理附件URL
            $attachments = $item['attachments'];
            if (is_string($attachments)) {
                $attachments = json_decode($attachments, true);
            }
            if (!is_array($attachments)) {
                $attachments = [];
            }
            foreach ($attachments as &$url) {
                $url = StorageClient::forSite()->getPublicUrl($url);
            }
            $item['attachments'] = $attachments;

            // 补充用户显示名称（可选，根据 user_type 和 user_id）
            // 这里简单处理，前端根据 user_type 判断显示 "用户" 还是 "管理员"
            $item['user_type_text'] = match ($item['user_type']) {
                1 => '用户',
                2 => '管理员',
                3 => '系统',
                default => '未知',
            };
        }
        return $list;
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

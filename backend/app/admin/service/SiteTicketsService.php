<?php
namespace app\admin\service;

use app\admin\model\SiteTickets;
use app\admin\model\SiteTicketAttachments;
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

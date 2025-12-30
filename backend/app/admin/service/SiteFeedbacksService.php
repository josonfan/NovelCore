<?php
namespace app\admin\service;

use app\admin\model\SiteFeedbacks;
use app\admin\model\SiteFeedbackAttachments;
use think\exception\ValidateException;
use storage\StorageClient;

class SiteFeedbacksService
{
    /**
     * 获取反馈列表
     */
    public static function list(array $where = [], string $field = '*', string $orderby = 'id desc', int $limit = 10, int $page = 1): array
    {
        $m = new SiteFeedbacks();
        return $m->getList($where, $field, $orderby, $limit, $page);
    }

    /**
     * 获取反馈详情
     */
    public static function detail(int $id, string $field = '*'): array
    {
        $m = new SiteFeedbacks();
        $info = $m->infoById($id, $field);
        if (empty($info)) {
            return [];
        }
        
        // 查询附件
        $atts = (new SiteFeedbackAttachments())
            ->where('feedback_no', $info['feedback_no'])
            ->order('id asc')
            ->select()
            ->toArray();
        
        foreach ($atts as $key => $att) {
            $atts[$key]['file_url'] = StorageClient::forSite()->getPublicUrl((string) ($att['file_url'] ?? ''));
        }
        $info['attachments'] = $atts;
        return $info;
    }

    /**
     * 处理反馈
     */
    public static function process(int $id, int $status, string $replyContent = '', int $adminId = 0): bool
    {
        try {
            validate(\app\admin\validate\SiteFeedbacks::class)->scene('process')->check(['id' => $id, 'status' => $status, 'reply_content' => $replyContent]);
            $m = new SiteFeedbacks();
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

    /**
     * 反馈类型字典
     */
    public static function types(): array
    {
        return [
            ['code' => 'suggestion', 'name' => '功能建议'],
            ['code' => 'usage_issue', 'name' => '使用问题'],
            ['code' => 'pay_member', 'name' => '支付与会员'],
        ];
    }

    /**
     * 状态字典
     */
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

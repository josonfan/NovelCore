<?php
declare(strict_types=1);

namespace app\controller;


class Sync extends Common
{
    /**
     * 接收后台推送
     * 路由：POST /api/Sync/receive
     * 鉴权：需 AdminPushAuth（Header：X-Api-Token）
     * 入参：JSON body（type, id, operation, data）
     * 返回：data { saved:boolean }
     */
    public function receive()
    {
        $raw = (string) $this->request->getContent();
        $ok = \app\service\SyncReceiveService::handle($raw);
        return $this->ajaxReturn($ok ? 200 : 422, $ok ? '成功' : '处理失败', ['saved' => $ok]);
    }
}

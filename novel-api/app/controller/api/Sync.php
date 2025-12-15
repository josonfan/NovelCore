<?php
declare(strict_types=1);

namespace app\controller\api;

use app\BaseController;
use think\Request;

class Sync extends BaseController
{
    public function receive(Request $request)
    {
        $raw = (string) $request->getContent();
        $ok = \app\service\SyncReceiveService::handle($raw);
        return api_response($ok ? 200 : 422, $ok ? '成功' : '处理失败', ['saved' => $ok]);
    }
}

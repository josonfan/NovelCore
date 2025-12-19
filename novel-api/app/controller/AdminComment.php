<?php
declare(strict_types=1);

namespace app\controller;

class AdminComment extends Common
{
    public function updateStatus(int $id)
    {
        return app(\app\service\AdminCommentService::class)::updateStatus($id, $this->request);
    }
}

<?php
declare(strict_types=1);

namespace app\controller;


class AdminNovel extends Common
{
    public function index()
    {
        return app(\app\service\AdminNovelService::class)::index();
    }
}

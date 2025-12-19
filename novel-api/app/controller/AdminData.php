<?php
declare(strict_types=1);

namespace app\controller;


class AdminData extends Common
{
    public function searchLogsList()
    {
        return app(\app\service\AdminDataService::class)::searchLogsList();
    }
    public function favoritesList()
    {
        return app(\app\service\AdminDataService::class)::favoritesList();
    }
    public function readingHistoryList()
    {
        return app(\app\service\AdminDataService::class)::readingHistoryList();
    }
    public function readLogsList()
    {
        return app(\app\service\AdminDataService::class)::readLogsList();
    }
    public function deviceLogsList()
    {
        return app(\app\service\AdminDataService::class)::deviceLogsList();
    }
    public function loginLogsList()
    {
        return app(\app\service\AdminDataService::class)::loginLogsList();
    }
}

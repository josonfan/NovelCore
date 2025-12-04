<?php
declare(strict_types=1);

namespace app\service\search;

use app\service\ConfigService;

class SearchServiceFactory
{
    public static function make(ConfigService $configService): SearchServiceInterface
    {
        $engine = env('SEARCH_ENGINE', 'mysql');

        if ($engine === 'elasticsearch') {
            $config = $configService->getSearchConfig();
            if (empty($config) || ($config['provider'] ?? '') !== 'elasticsearch' || empty($config['host'])) {
                return new MysqlSearchService($configService);
            }
            return new EsSearchService($configService, $config);
        }

        return new MysqlSearchService($configService);
    }
}

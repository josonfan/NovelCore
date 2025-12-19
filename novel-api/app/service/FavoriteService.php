<?php
declare(strict_types=1);

namespace app\service;

use app\model\Novel;
use app\model\UserNovelFavorite;

class FavoriteService
{
    public static function getListByUser(int $userId, int $page = 1, int $limit = 10): array
    {
        $model = new UserNovelFavorite();
        $res = $model->getList(
            ['user_id' => $userId],
            'id,novel_id',
            'id desc',
            $limit,
            $page
        );
        $storage = new \app\service\StorageService();
        $fields = 'novel_uuid as id,title,cover,intro,word_count,status,is_vip,updated_at';
        $novelModel = new Novel();
        $list = [];
        foreach ($res['list'] as $fav) {
            $row = $novelModel->infoById((int)$fav['novel_id'], $fields);
            $row['cover'] = $storage->getPublicUrl((string) ($row['cover'] ?? ''));
            $row['favorite_id'] = $fav['id'];
            $list[] = $row;
        }
        return ['list' => $list, 'count' => (int) $res['count']];
    }
}

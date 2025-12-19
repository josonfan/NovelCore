<?php
declare(strict_types=1);

namespace app\service;

use app\model\Novel;
use app\model\UserNovelFavorite;

class FavoriteService
{
    public static function getListByUser(int $userId, int $page = 1, int $limit = 10): array
    {
        $paginator = UserNovelFavorite::where('user_id', $userId)
            ->order('id', 'desc')
            ->paginate(['list_rows' => $limit, 'page' => $page]);
        $storage = new \app\service\StorageService();
        $fields = 'novel_uuid as id,title,cover,intro,word_count,status,is_vip,updated_at';
        $novelModel = new Novel();
        $list = [];
        foreach ($paginator->items() as $fav) {
            $row = $novelModel->infoById((int)$fav['novel_id'], $fields);
            $row['cover'] = $storage->getPublicUrl((string) ($row['cover'] ?? ''));
            $row['favorite_id'] = $fav['id'];
            $list[] = $row;
        }
        return ['list' => $list, 'total' => (int) $paginator->total()];
    }
}

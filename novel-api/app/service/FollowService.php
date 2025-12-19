<?php
declare(strict_types=1);

namespace app\service;

use app\model\UserFollowAuthor;
use app\model\UserFollowNovel;

class FollowService
{
    public static function followAuthor(int $userId, int $authorId): array
    {
        $existsId = UserFollowAuthor::where('user_id', $userId)
            ->where('author_id', $authorId)
            ->value('id');
        if (!$existsId) {
            (new UserFollowAuthor())->writeById(0, [
                'user_id'   => $userId,
                'author_id' => $authorId,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
        return ['author_id' => $authorId, 'followed' => true];
    }

    public static function unfollowAuthor(int $userId, int $authorId): array
    {
        $followId = UserFollowAuthor::where('user_id', $userId)
            ->where('author_id', $authorId)
            ->value('id');
        if ($followId) {
            (new UserFollowAuthor())->deleteById((int)$followId);
        }
        return ['author_id' => $authorId, 'followed' => false];
    }

    public static function followNovel(int $userId, string $novelUuid): array
    {
        $novel = NovelService::getInfoByUuid($novelUuid, 'id,novel_uuid');
        $existsId = UserFollowNovel::where('user_id', $userId)
            ->where('novel_id', (int)$novel['id'])
            ->value('id');
        if (!$existsId) {
            (new UserFollowNovel())->writeById(0, [
                'user_id'  => $userId,
                'novel_id' => (int)$novel['id'],
            ]);
        }
        return ['novel_id' => (string)$novel['novel_uuid'], 'followed' => true];
    }

    public static function unfollowNovel(int $userId, string $novelUuid): array
    {
        $novel = NovelService::getInfoByUuid($novelUuid, 'id,novel_uuid');
        $followId = UserFollowNovel::where('user_id', $userId)
            ->where('novel_id', (int)$novel['id'])
            ->value('id');
        if ($followId) {
            (new UserFollowNovel())->deleteById((int)$followId);
        }
        return ['novel_id' => (string)$novel['novel_uuid'], 'followed' => false];
    }
}


<?php
declare(strict_types=1);

namespace app\service;

use app\model\Comment;
use app\model\User as UserModel;
use app\service\ConfigService;
use think\facade\Cache;
use GuzzleHttp\Client;
use think\exception\ValidateException;

class CommentService
{
    public static function format(Comment $comment): array
    {
        $user = $comment->user;
        return [
            'id' => $comment->id,
            'novel_id' => $comment->novel_id,
            'chapter_id' => $comment->chapter_id,
            'parent_id' => $comment->parent_id,
            'root_id' => $comment->root_id,
            'content' => $comment->content,
            'is_r18' => (int) $comment->is_r18,
            'like_count' => (int) $comment->like_count,
            'status' => (int) $comment->status,
            'review_source' => (int) $comment->review_source,
            'created_at' => $comment->created_at,
            'user' => $user ? [
                'uid' => $user->id,
                'nickname' => $user->nickname,
                'avatar' => $user->avatar,
            ] : null,
        ];
    }

    public static function report($comment): void
    {
        $endpoint = (string) config('moderation.endpoint', '');
        if ($endpoint === '') {
            return;
        }
        $token = (string) config('moderation.token', '');
        $client = new Client(['timeout' => 3]);
        $payload = [
            'type' => 'comment',
            'id' => is_object($comment) ? $comment->id : ($comment['id'] ?? null),
            'content' => is_object($comment) ? $comment->content : ($comment['content'] ?? null),
            'novel_id' => is_object($comment) ? $comment->novel_id : ($comment['novel_id'] ?? null),
            'chapter_id' => is_object($comment) ? $comment->chapter_id : ($comment['chapter_id'] ?? null),
            'user_id' => is_object($comment) ? $comment->user_id : ($comment['user_id'] ?? null),
            'is_r18' => is_object($comment) ? $comment->is_r18 : ($comment['is_r18'] ?? 0),
        ];
        try {
            $client->post(rtrim($endpoint, '/'), [
                'headers' => [
                    'Authorization' => $token ? 'Bearer ' . $token : '',
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]);
        } catch (\Throwable $e) {
        }
    }

    public static function validateStore(int $novelId, int $userId, string $content, int $parentId = 0): array
    {
        $config = ConfigService::get('comment_review_config');
        $content = trim($content);
        if ($content === '') {
            throw new ValidateException(lang('评论内容不能为空'));
        }

        $isEnabled = (int)($config['enabled'] ?? 0) === 1;
        $status = 1; // Default approved

        if ($isEnabled) {
             // 1. Max Length
             $maxLength = (int)($config['max_length'] ?? 0);
             if ($maxLength > 0 && mb_strlen($content) > $maxLength) {
                 throw new ValidateException(lang('评论内容超过最大长度限制', ['length' => $maxLength]));
             }

             // 2. Forbidden Words
             $forbidden = $config['forbidden_words_json'] ?? [];
             if (is_string($forbidden)) {
                 $decoded = json_decode($forbidden, true);
                 if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                     $forbidden = $decoded;
                 } else {
                     $forbidden = [];
                 }
             }
             if (is_array($forbidden)) {
                 foreach ($forbidden as $word) {
                     if ($word && str_contains($content, $word)) {
                         throw new ValidateException(lang('评论包含敏感词'));
                     }
                 }
             }

             // 3. Rate Limit (Max per minute)
             $maxPerMinute = (int)($config['max_per_minute'] ?? 0);
             if ($maxPerMinute > 0) {
                 $count = Comment::where('user_id', $userId)
                     ->whereTime('created_at', '-1 minute')
                     ->count();
                 if ($count >= $maxPerMinute) {
                     throw new ValidateException(lang('评论太频繁'));
                 }
             }

             // 4. Duplicate Check (Redis)
             $key = 'nc:comment:dup:' . $userId . ':' . md5($content . $parentId);
             if (Cache::has($key)) {
                 throw new ValidateException(lang('请勿重复发表评论'));
             }
             Cache::set($key, 1, 60);

             // 5. Require Approval
             if ((int)($config['require_approval'] ?? 0) === 1) {
                 $status = 0; // Pending
             }
        }

        $rootId = null;
        if ($parentId > 0) {
            $exists = Comment::where('novel_id', $novelId)
                ->where('id', $parentId)
                ->value('id');
            if (!$exists) {
                throw new ValidateException(lang('父评论不存在'));
            }
            $info = (new Comment())->infoById((int)$parentId, 'id,root_id');
            $rootId = ($info['root_id'] ?? null) ?: ($info['id'] ?? null);
        }
        return ['content' => $content, 'root_id' => $rootId, 'status' => $status];
    }

    public static function list(int $novelPk, int $page = 1, int $limit = 10, string $order = 'desc'): array
    {
        $order = strtolower($order) === 'asc' ? 'asc' : 'desc';
        $query = Comment::where('novel_id', $novelPk)->where('status', 1)->order('id', $order);
        $count = (int) $query->count('id');
        $ids = $query->page($page, $limit)->column('id');
        $rows = [];
        $commentModel = new Comment();
        foreach ($ids as $id) {
            $rows[] = $commentModel->infoById((int)$id, 'id,novel_id,chapter_id,parent_id,root_id,content,is_r18,like_count,status,review_source,created_at,user_id');
        }
        return ['list' => $rows, 'count' => $count];
    }
}

<?php
declare(strict_types=1);

namespace app\controller\api;

use app\BaseController;
use app\model\Chapter;
use app\model\Novel;
use app\model\UserReadLog;
use app\model\UserReadingHistory;
use app\service\UserStatsService;
use think\Request;

/**
 * 阅读进度与阅读记录接口。
 */
class ReadingController extends BaseController
{
    /**
     * 获取用户阅读进度（需要登录，小说 uuid）。
     */
    public function getProgress(string $novelId, Request $request)
    {
        $user = $request->user;
        $novel = $this->findNovelByUuid($novelId);
        if (!$novel) {
            return json_error('小说不存在', 404)->code(404);
        }

        $history = UserReadingHistory::where('user_id', $user->id)
            ->where('novel_id', $novel->id)
            ->find();

        if (!$history) {
            return json_success([
                'novel_id'   => $novel->novel_uuid,
                'chapter_id' => null,
                'title'      => null,
                'progress'   => 0.0,
            ], '未找到阅读进度');
        }

        $chapter = Chapter::find($history->chapter_id);
        return json_success([
            'novel_id'   => $novel->novel_uuid,
            // 对外使用 chapter_uuid
            'chapter_id' => $chapter?->chapter_uuid,
            'title'      => $chapter?->title,
            'progress'   => (float) $history->progress,
        ], '获取成功');
    }

    /**
     * 保存阅读进度（需要登录），chapter_id 为 chapter_uuid。
     */
    public function saveProgress(string $novelId, Request $request)
    {
        $user = $request->user;
        $novel = $this->findNovelByUuid($novelId);
        if (!$novel) {
            return json_error('小说不存在', 404)->code(404);
        }

        $chapterUuid = (string) $request->post('chapter_id', '');
        $progress    = (float) $request->post('progress', 100);

        $chapter = Chapter::where('novel_id', $novel->id)
            ->where(function ($query) use ($chapterUuid) {
                $query->where('chapter_uuid', $chapterUuid);
                if (ctype_digit($chapterUuid)) {
                    $query->whereOr('id', (int) $chapterUuid);
                }
            })->find();

        if (!$chapter) {
            return json_error('章节不存在', 404)->code(404);
        }

        $history = UserReadingHistory::where('user_id', $user->id)
            ->where('novel_id', $novel->id)
            ->find();

        $data = [
            'user_id'     => $user->id,
            'novel_id'    => $novel->id,
            'chapter_id'  => $chapter->id,
            'progress'    => max(0, min(100, $progress)),
            'last_read_at'=> date('Y-m-d H:i:s'),
        ];

        if ($history) {
            $history->save($data);
        } else {
            UserReadingHistory::create($data);
            // 统计：首次阅读该小说，小说阅读数 +1
            UserStatsService::incReadNovelCount($user->id, 1);
        }

        return json_success([
            'novel_id'   => $novel->novel_uuid,
            'chapter_id' => $chapter->chapter_uuid,
            'title'      => $chapter->title,
            'progress'   => $data['progress'],
        ], '保存成功');
    }

    /**
     * 获取阅读记录（最近阅读历史），需要登录。
     */
    public function listHistory(Request $request)
    {
        $user = $request->user;
        $page     = max(1, (int) $request->get('page', 1));
        $pageSize = min(50, max(1, (int) $request->get('page_size', 10)));

        $query = UserReadingHistory::alias('h')
            ->join('novels n', 'h.novel_id = n.id')
            ->leftJoin('chapters c', 'h.chapter_id = c.id')
            ->where('h.user_id', $user->id)
            ->order('h.last_read_at', 'desc')
            ->field([
                'h.id',
                'h.novel_id',
                'h.chapter_id',
                'h.progress',
                'h.last_read_at',
                'n.novel_uuid',
                'n.title as novel_title',
                'n.cover',
                'n.status',
                'n.is_vip',
                'n.word_count',
                'n.updated_at',
                'c.chapter_uuid',
                'c.title as chapter_title',
            ]);

        $paginator = $query->paginate([
            'list_rows' => $pageSize,
            'page'      => $page,
        ]);

        $storage = app(\app\service\StorageService::class);
        $items = [];
        foreach ($paginator->items() as $item) {
            $items[] = [
                'novel_id'     => $item['novel_uuid'],
                'novel_title'  => $item['novel_title'],
                'cover'        => $storage->getPublicUrl((string) $item['cover']),
                'status'       => (int) $item['status'],
                'is_vip'       => (int) $item['is_vip'],
                'word_count'   => (int) $item['word_count'],
                'updated_at'   => $item['updated_at'],
                'chapter_id'   => $item['chapter_uuid'],
                'chapter_title'=> $item['chapter_title'],
                'progress'     => (float) $item['progress'],
                'last_read_at' => $item['last_read_at'],
            ];
        }

        return json_success([
            'total'     => $paginator->total(),
            'page'      => $paginator->currentPage(),
            'page_size' => $paginator->listRows(),
            'history'   => $items,
        ], '获取成功');
    }

    /**
     * 保存阅读记录：写入行为日志 + 更新阅读历史。
     * 日志属于埋点，后续可异步/队列处理。
     */
    public function saveHistory(Request $request)
    {
        $user = $request->user;
        $novelUuid   = (string) $request->post('novel_id', '');
        $chapterUuid = (string) $request->post('chapter_id', '');
        $finished    = (bool) $request->post('finished', true);
        $durationSec = (int) $request->post('duration_sec', 0);

        $novel = $this->findNovelByUuid($novelUuid);
        if (!$novel) {
            return json_error('小说不存在', 404)->code(404);
        }

        $chapter = Chapter::where('novel_id', $novel->id)
            ->where(function ($query) use ($chapterUuid) {
                $query->where('chapter_uuid', $chapterUuid);
                if (ctype_digit($chapterUuid)) {
                    $query->whereOr('id', (int) $chapterUuid);
                }
            })->find();

        if (!$chapter) {
            return json_error('章节不存在', 404)->code(404);
        }

        // 行为日志：简化处理，duration/end_time 后续可由前端补充
        UserReadLog::create([
            'user_id'      => $user->id,
            'novel_id'     => $novel->id,
            'chapter_id'   => $chapter->id,
            'start_time'   => date('Y-m-d H:i:s'),
            'end_time'     => null,
            'duration_sec' => $durationSec,
            'device_id'    => $request->header('X-Device-Id', ''),
            'client_type'  => $request->header('User-Agent', ''),
            'ip'           => $request->ip(),
        ]);

        // 统计：阅读时长增量
        if ($durationSec > 0) {
            UserStatsService::incReadMinutes($user->id, $durationSec);
        }

        // 更新阅读历史
        $history = UserReadingHistory::where('user_id', $user->id)
            ->where('novel_id', $novel->id)
            ->find();

        $data = [
            'user_id'     => $user->id,
            'novel_id'    => $novel->id,
            'chapter_id'  => $chapter->id,
            'progress'    => $finished ? 100 : 0,
            'last_read_at'=> date('Y-m-d H:i:s'),
        ];

        if ($history) {
            $history->save($data);
        } else {
            UserReadingHistory::create($data);
            // 统计：首次阅读该小说，小说阅读数 +1
            UserStatsService::incReadNovelCount($user->id, 1);
        }

        return json_success([
            'novel_id'   => $novel->novel_uuid,
            'chapter_id' => $chapter->chapter_uuid,
            'progress'   => $data['progress'],
            'last_read_at'=> $data['last_read_at'],
        ], '保存成功');
    }

    /**
     * 按 uuid 查找小说，必要时兼容自增 id。
     */
    protected function findNovelByUuid(string $novelId): ?Novel
    {
        $novel = Novel::where('novel_uuid', $novelId)->find();
        if (!$novel && ctype_digit($novelId)) {
            $novel = Novel::find((int) $novelId);
        }
        return $novel;
    }

}

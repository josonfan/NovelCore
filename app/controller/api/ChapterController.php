<?php
declare(strict_types=1);

namespace app\controller\api;

use app\BaseController;
use app\exception\BusinessException;
use app\model\Chapter;
use app\model\Novel;
use app\model\UserReadLog;
use app\service\ChapterService;
use think\Request;

/**
 * 章节接口，外部 ID 使用 chapter_uuid。
 */
class ChapterController extends BaseController
{
    /**
     * 章节列表，按 sort_order 升序。
     */
    public function index(string $novelId, Request $request)
    {
        $novel = $this->findNovelByUuid($novelId);
        if (!$novel) {
            return json_error('小说不存在', 404)->code(404);
        }

        $page     = max(1, (int) $request->get('page', 1));
        $pageSize = min(200, max(1, (int) $request->get('page_size', 100)));

        $query = Chapter::where('novel_id', $novel->id)->order('sort_order', 'asc');
        $paginator = $query->paginate([
            'list_rows' => $pageSize,
            'page'      => $page,
        ]);

        $chapters = [];
        foreach ($paginator->items() as $chapter) {
            $chapters[] = [
                // 对外 ID 使用 chapter_uuid
                'id'         => $chapter->chapter_uuid,
                'title'      => $chapter->title,
                'is_free'    => (int) $chapter->is_free,
                'is_vip'     => (int) $chapter->is_vip,
                'price'      => (int) $chapter->price,
                'word_count' => (int) $chapter->word_count,
                'sort_order' => (int) $chapter->sort_order,
            ];
        }

        return json_success([
            'total'      => $paginator->total(),
            'page'       => $paginator->currentPage(),
            'page_size'  => $paginator->listRows(),
            'chapters'   => $chapters,
        ], '获取成功');
    }

    /**
     * 获取章节内容，含前后章节 uuid。
     */
    public function show(string $novelId, string $chapterId, Request $request, ChapterService $chapterService)
    {
        $novel = $this->findNovelByUuid($novelId);
        if (!$novel) {
            return json_error('小说不存在', 404)->code(404);
        }

        $chapter = Chapter::where('novel_id', $novel->id)
            ->where('chapter_uuid', $chapterId)
            ->find();

        if (!$chapter && ctype_digit($chapterId)) {
            $chapter = Chapter::where('novel_id', $novel->id)->find((int) $chapterId);
        }

        if (!$chapter) {
            return json_error('章节不存在', 404)->code(404);
        }

        // 简单权限校验，后续会接入订单/VIP 逻辑
        $currentUser = $request->user ?? null;
        try {
            $chapterService->checkUserCanRead($currentUser, $chapter);
        } catch (BusinessException $e) {
            return json_error($e->getMessage(), $e->getCode())->code($e->getCode());
        }

        $content = $chapter->content()->value('content');

        // 计算前后章节 uuid，便于前端跳转
        $prevId = Chapter::where('novel_id', $novel->id)
            ->where('sort_order', '<', $chapter->sort_order)
            ->order('sort_order', 'desc')
            ->value('chapter_uuid');

        $nextId = Chapter::where('novel_id', $novel->id)
            ->where('sort_order', '>', $chapter->sort_order)
            ->order('sort_order', 'asc')
            ->value('chapter_uuid');

        // 阅读行为埋点：当前实现为直接写日志，后续可异步/队列处理
        $currentUser = $request->user ?? null;
        if ($currentUser) {
            UserReadLog::create([
                'user_id'    => $currentUser->id,
                'novel_id'   => $novel->id,
                'chapter_id' => $chapter->id,
                'start_time' => date('Y-m-d H:i:s'),
                'end_time'   => null,
                'duration_sec' => 0,
                'device_id'  => $request->header('X-Device-Id', ''),
                'client_type'=> $request->header('User-Agent', ''),
                'ip'         => $request->ip(),
            ]);
        }

        return json_success([
            'novel_id'   => $novel->novel_uuid,
            'chapter_id' => $chapter->chapter_uuid,
            'title'      => $chapter->title,
            'content'    => $content ?: '',
            'is_free'    => (int) $chapter->is_free,
            'is_vip'     => (int) $chapter->is_vip,
            'prev_id'    => $prevId ?: null,
            'next_id'    => $nextId ?: null,
        ], '获取成功');
    }

    /**
     * 通过 uuid（必要时兼容自增 id）查找小说。
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

<?php
declare(strict_types=1);

namespace app\controller\api;

use app\BaseController;
use app\exception\BusinessException;
use app\model\Chapter as ChapterModel;
use app\model\Novel;
use app\model\UserReadLog;
use app\service\ChapterService;
use app\service\ContentService;
use think\Request;

class Chapter extends BaseController
{
    /**
     * 章节列表
     *
     * 路由：`GET|POST /api/novels/:novelId/chapters`
     * 鉴权：无需登录
     * 参数：`:novelId` 使用外部 `novel_uuid`
     * 分页：`page`、`limit`（默认100，最大200）
     * 返回：列表与总数
     *
     * @param string $novelId
     * @param Request $request
     * @return \think\Response
     */
    public function index(string $novelId, Request $request)
    {
        $novel = ContentService::findNovelByExternalId($novelId);
        if (!$novel) {
            return api_response(404, '小说不存在', [])->code(404);
        }
        $page     = max(1, (int) $request->param('page', 1));
        $pageSize = min(200, max(1, (int) $request->param('limit', 100)));
        $result = \app\service\ContentService::listChapters($novel->id, $page, $pageSize);
        return api_response(200, '成功', $result['list'], (int)$result['total']);
    }

    /**
     * 章节内容
     *
     * 路由：`GET|POST /api/novels/:novelId/chapters/:chapterId`
     * 鉴权：登录可记录阅读日志，VIP章节需权限校验
     * 参数：`:novelId` 外部 `novel_uuid`，`:chapterId` 外部 `chapter_uuid`
     * 返回：章节内容与前后导航
     *
     * @param string $novelId
     * @param string $chapterId
     * @param Request $request
     * @param ChapterService $chapterService
     * @return \think\Response
     */
    public function show(string $novelId, string $chapterId, Request $request, ChapterService $chapterService)
    {
        $novel = ContentService::findNovelByExternalId($novelId);
        if (!$novel) {
            return api_response(404, '小说不存在', [])->code(404);
        }
        $chapter = ContentService::findChapterByExternalId($novel, $chapterId);
        if (!$chapter) {
            return api_response(404, '章节不存在', [])->code(404);
        }
        $currentUser = $request->user ?? null;
        try {
            $chapterService->checkUserCanRead($currentUser, $chapter);
        } catch (BusinessException $e) {
            return api_response($e->getCode(), $e->getMessage(), [])->code($e->getCode());
        }
        $content = $chapter->content()->value('content');
        $prevId = ChapterModel::where('novel_id', $novel->id)
            ->where('sort_order', '<', $chapter->sort_order)
            ->order('sort_order', 'desc')
            ->value('chapter_uuid');
        $nextId = ChapterModel::where('novel_id', $novel->id)
            ->where('sort_order', '>', $chapter->sort_order)
            ->order('sort_order', 'asc')
            ->value('chapter_uuid');
        $currentUser = $request->user ?? null;
        if ($currentUser) {
            (new UserReadLog())->writeById(0, [
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
        return api_response(200, '获取成功', [
            'novel_id'   => $novel->novel_uuid,
            'chapter_id' => $chapter->chapter_uuid,
            'title'      => $chapter->title,
            'content'    => $content ?: '',
            'is_free'    => (int) $chapter->is_free,
            'is_vip'     => (int) $chapter->is_vip,
            'prev_id'    => $prevId ?: null,
            'next_id'    => $nextId ?: null,
        ]);
    }

    
}

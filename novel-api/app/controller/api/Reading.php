<?php
declare(strict_types=1);

namespace app\controller\api;

use app\BaseController;
use app\model\Chapter;
use app\model\Novel;
use app\model\UserReadLog;
use app\model\UserReadingHistory;
use app\service\UserStatsService;
use app\service\ContentService;
use think\Request;

class Reading extends BaseController
{
    /**
     * 获取阅读进度
     *
     * 路由：`GET /api/novels/:novelId/progress`
     * 鉴权：需登录
     * 参数：`:novelId` 外部 `novel_uuid`
     * 返回：最新阅读章节与进度百分比
     *
     * @param string $novelId
     * @param Request $request
     * @return \think\Response
     */
    public function getProgress(string $novelId, Request $request)
    {
        $user = $request->user;
        $novel = ContentService::getNovelOrFail($novelId);
        $historyId = UserReadingHistory::where('user_id', $user->id)
            ->where('novel_id', $novel->id)
            ->value('id');
        if (!$historyId) {
        return api_response(200, '未找到阅读进度', [
            'novel_id'   => $novel->novel_uuid,
            'chapter_id' => null,
            'title'      => null,
            'progress'   => 0.0,
        ]);
        }
        $chapterInfo = (new Chapter())->infoById((int)UserReadingHistory::where('user_id', $user->id)
            ->where('novel_id', $novel->id)
            ->value('chapter_id'), 'chapter_uuid as chapter_id,title');
        return api_response(200, '获取成功', [
            'novel_id'   => $novel->novel_uuid,
            'chapter_id' => $chapterInfo['chapter_id'] ?? null,
            'title'      => $chapterInfo['title'] ?? null,
            'progress'   => (float) UserReadingHistory::where('user_id', $user->id)
                ->where('novel_id', $novel->id)
                ->value('progress'),
        ]);
    }

    /**
     * 保存阅读进度
     *
     * 路由：`POST /api/novels/:novelId/progress`
     * 鉴权：需登录
     * 参数：`chapter_id` 外部 `chapter_uuid`，`progress` 百分比（0-100）
     * 返回：保存后的进度视图
     *
     * @param string $novelId
     * @param Request $request
     * @return \think\Response
     */
    public function saveProgress(string $novelId, Request $request)
    {
        $user = $request->user;
        $novel = ContentService::getNovelOrFail($novelId);
        $chapterUuid = (string) $request->post('chapter_id', '');
        $progress    = (float) $request->post('progress', 100);
        $chapter = ContentService::getChapterOrFail($novel, $chapterUuid);
        $historyId = UserReadingHistory::where('user_id', $user->id)
            ->where('novel_id', $novel->id)
            ->value('id');
        $data = [
            'user_id'     => $user->id,
            'novel_id'    => $novel->id,
            'chapter_id'  => $chapter->id,
            'progress'    => max(0, min(100, $progress)),
            'last_read_at'=> date('Y-m-d H:i:s'),
        ];
        if ($historyId) {
            (new UserReadingHistory())->writeById((int)$historyId, $data);
        } else {
            (new UserReadingHistory())->writeById(0, $data);
            UserStatsService::incReadNovelCount($user->id, 1);
        }
        return api_response(200, '保存成功', [
            'novel_id'   => $novel->novel_uuid,
            'chapter_id' => $chapter->chapter_uuid,
            'title'      => $chapter->title,
            'progress'   => $data['progress'],
        ]);
    }

    /**
     * 阅读历史列表
     *
     * 路由：`GET /api/user/reading-history`
     * 鉴权：需登录
     * 分页：`page`、`limit`（默认10，最大50）
     * 返回：列表与总数
     *
     * @param Request $request
     * @return \think\Response
     */
    public function listHistory(Request $request)
    {
        $user = $request->user;
        $page     = max(1, (int) $request->param('page', 1));
        $pageSize = min(50, max(1, (int) $request->param('limit', 10)));
        $result = \app\service\ContentService::listReadingHistory($user->id, $page, $pageSize);
        return api_response(200, '成功', $result['list'], (int)$result['total']);
    }

    /**
     * 写入阅读历史/行为日志
     *
     * 路由：`POST /api/user/reading-history`
     * 鉴权：需登录
     * 参数：`novel_id` 外部 `novel_uuid`，`chapter_id` 外部 `chapter_uuid`，`finished?`，`duration_sec?`
     * 返回：视图数据（含进度与时间）
     *
     * @param Request $request
     * @return \think\Response
     */
    public function saveHistory(Request $request)
    {
        $user = $request->user;
        $novelUuid   = (string) $request->post('novel_id', '');
        $chapterUuid = (string) $request->post('chapter_id', '');
        $finished    = (bool) $request->post('finished', true);
        $durationSec = (int) $request->post('duration_sec', 0);
        $novel = ContentService::getNovelOrFail($novelUuid);
        $chapter = ContentService::getChapterOrFail($novel, $chapterUuid);
        (new UserReadLog())->writeById(0, [
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
        if ($durationSec > 0) {
            UserStatsService::incReadMinutes($user->id, $durationSec);
        }
        $historyId = UserReadingHistory::where('user_id', $user->id)
            ->where('novel_id', $novel->id)
            ->value('id');
        $data = [
            'user_id'     => $user->id,
            'novel_id'    => $novel->id,
            'chapter_id'  => $chapter->id,
            'progress'    => $finished ? 100 : 0,
            'last_read_at'=> date('Y-m-d H:i:s'),
        ];
        if ($historyId) {
            (new UserReadingHistory())->writeById((int)$historyId, $data);
        } else {
            (new UserReadingHistory())->writeById(0, $data);
            UserStatsService::incReadNovelCount($user->id, 1);
        }
        return api_response(200, '保存成功', [
            'novel_id'   => $novel->novel_uuid,
            'chapter_id' => $chapter->chapter_uuid,
            'progress'   => $data['progress'],
            'last_read_at'=> $data['last_read_at'],
        ]);
    }

    
}

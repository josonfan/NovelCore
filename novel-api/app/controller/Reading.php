<?php
declare(strict_types=1);

namespace app\controller;

use app\model\UserReadLog;
use app\model\UserReadingHistory;
use app\service\ChapterService;
use app\service\UserStatsService;
use app\service\ContentService;

class Reading extends Common
{
    /**
     * 获取阅读进度
     * 路由：POST /api/Reading/getProgress
     * 鉴权：需登录
     * 入参：novelId（外部 novel_uuid）
     * 返回：data { novel_id, chapter_id?, title?, progress }
     */
    public function getProgress()
    {
        $novelId = $this->request->param('novelId', '', 'trim');
        $userId = (int) ($this->request->user_id ?? 0);
        $novel = \app\service\NovelService::getInfoByUuid($novelId, 'id,novel_uuid');
        $historyId = UserReadingHistory::where('user_id', $userId)
            ->where('novel_id', (int)$novel['id'])
            ->value('id');
        if (!$historyId) {
            return $this->ajaxReturn(200, '未找到阅读进度', [
                'novel_id'   => (string)$novel['novel_uuid'],
                'chapter_id' => null,
                'title'      => null,
                'progress'   => 0.0,
            ]);
        }
        $chapterInfo = (new \app\model\Chapter())->infoById((int)UserReadingHistory::where('user_id', $userId)
            ->where('novel_id', (int)$novel['id'])
            ->value('chapter_id'), 'chapter_uuid as chapter_id,title');
        return $this->ajaxReturn(200, '获取成功', [
            'novel_id'   => (string)$novel['novel_uuid'],
            'chapter_id' => $chapterInfo['chapter_id'] ?? null,
            'title'      => $chapterInfo['title'] ?? null,
            'progress'   => (float) UserReadingHistory::where('user_id', $userId)
                ->where('novel_id', (int)$novel['id'])
                ->value('progress'),
        ]);
    }

    /**
     * 保存阅读进度
     * 路由：POST /api/Reading/saveProgress
     * 鉴权：需登录
     * 入参：novelId, chapter_id（外部 chapter_uuid）, progress（0-100）
     * 返回：data { novel_id, chapter_id, title, progress }
     */
    public function saveProgress()
    {
        $novelId = $this->request->param('novelId', '', 'trim');
        $userId = (int) ($this->request->user_id ?? 0);
        $novel = \app\service\NovelService::getInfoByUuid($novelId, 'id,novel_uuid');
        $chapterUuid = (string) $this->request->post('chapterId', '');
        $progress    = (int) $this->request->post('progress', 100);
        // 查找章节（基于小说主键与外部章节UUID）
        $chapter = ChapterService::getInfoByUuid($chapterUuid, 'id,chapter_uuid,title');
        
        if (!$chapter) {
            throw new \think\exception\ValidateException('章节不存在');
        }
        $historyId = UserReadingHistory::where('user_id', $userId)
            ->where('novel_id', (int)$novel['id'])
            ->value('id');
        $data = [
            'user_id'     => $userId,
            'novel_id'    => (int)$novel['id'],
            'chapter_id'  => $chapter['id'],
            'progress'    => max(0, min(100, $progress)),
            'last_read_at'=> date('Y-m-d H:i:s'),
        ];
        if ($historyId) {
            (new UserReadingHistory())->writeById((int)$historyId, $data);
        } else {
            (new UserReadingHistory())->writeById(0, $data);
            UserStatsService::incReadNovelCount($userId, 1);
        }
        return $this->ajaxReturn(200, '保存成功', [
            'novel_id'   => (string)$novel['novel_uuid'],
            'chapter_id' => $chapter['chapter_uuid'],
            'title'      => $chapter['title'],
            'progress'   => $data['progress'],
        ]);
    }

    /**
     * 阅读历史列表
     * 路由：POST /api/Reading/listHistory
     * 鉴权：需登录
     * 入参：page, limit
     * 返回：data { list, count }
     */
    public function listHistory()
    {
        $userId = $this->request->user_id;
        $page     = $this->request->param('page', 1, 'intval');
        $pageSize = $this->request->param('limit', 10, 'intval');
        $result = \app\service\ReadingHistoryService::getListByUser($userId, $page, $pageSize);
        return $this->ajaxReturn(200, '获取成功', [
            'list'  => $result['list'],
            'count' => (int)$result['count'],
        ]);
    }

    /**
     * 写入阅读历史
     * 路由：POST /api/Reading/saveHistory
     * 鉴权：需登录
     * 入参：novel_id（外部 novel_uuid）, chapter_id（外部 chapter_uuid）, finished, duration_sec
     * 返回：data { novel_id, chapter_id, progress, last_read_at }
     */
    public function saveHistory()
    {
        $userId = (int) $this->request->user_id;
        $novelUuid   = (string) $this->request->post('novelId', '');
        $chapterUuid = (string) $this->request->post('chapterId', '');
        $finished    = (bool) $this->request->post('finished', true);
        $durationSec = (int) $this->request->post('duration_sec', 0);
        $data = \app\service\ReadingService::writeHistory($userId, $novelUuid, $chapterUuid, $finished, $durationSec);
        return $this->ajaxReturn(200, '保存成功', $data);
    }
}

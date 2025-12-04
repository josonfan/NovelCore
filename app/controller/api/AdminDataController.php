<?php
declare(strict_types=1);

namespace app\controller\api;

use app\BaseController;
use app\model\UserDeviceLog;
use app\model\UserLoginLog;
use app\model\UserNovelFavorite;
use app\model\UserReadLog;
use app\model\UserReadingHistory;
use app\model\UserSearchLog;
use think\Request;

/**
 * 后台只读数据查询接口，受 AdminAuth 保护。
 */
class AdminDataController extends BaseController
{
    /**
     * 用户搜索日志列表，可选按 uid / 日期过滤。
     */
    public function searchLogsList(Request $request)
    {
        $uid = (int) $request->get('uid', 0);
        $date = trim((string) $request->get('date', ''));

        $query = UserSearchLog::order('id', 'desc');
        if ($uid > 0) {
            $query->where('user_id', $uid);
        }
        if ($date !== '') {
            $query->whereDay('created_at', $date);
        }

        $list = $query->limit(500)->select();
        return json_success($list);
    }

    /**
     * 用户收藏列表，必填 uid。
     */
    public function favoritesList(Request $request)
    {
        $uid = (int) $request->get('uid', 0);
        if ($uid <= 0) {
            return json_error('缺少 uid 参数', 400);
        }

        $page     = max(1, (int) $request->get('page', 1));
        $pageSize = min(200, max(1, (int) $request->get('limit', 100)));

        $paginator = UserNovelFavorite::where('user_id', $uid)
            ->order('id', 'desc')
            ->paginate([
                'list_rows' => $pageSize,
                'page'      => $page,
            ]);

        return json_success([
            'total' => $paginator->total(),
            'page'  => $paginator->currentPage(),
            'list'  => $paginator->items(),
        ]);
    }

    /**
     * 阅读历史（进度）列表，必填 uid。
     */
    public function readingHistoryList(Request $request)
    {
        $uid = (int) $request->get('uid', 0);
        if ($uid <= 0) {
            return json_error('缺少 uid 参数', 400);
        }

        $list = UserReadingHistory::where('user_id', $uid)
            ->order('last_read_at', 'desc')
            ->select();

        return json_success($list);
    }

    /**
     * 阅读行为日志列表，需 uid 或 date 至少一个。
     */
    public function readLogsList(Request $request)
    {
        $uid = (int) $request->get('uid', 0);
        $date = trim((string) $request->get('date', ''));

        if ($uid <= 0 && $date === '') {
            return json_error('请提供 uid 或 date 参数', 400);
        }

        $page     = max(1, (int) $request->get('page', 1));
        $pageSize = min(200, max(1, (int) $request->get('limit', 100)));

        $query = UserReadLog::order('id', 'desc');
        if ($uid > 0) {
            $query->where('user_id', $uid);
        }
        if ($date !== '') {
            $query->whereDay('start_time', $date);
        }

        $paginator = $query->paginate([
            'list_rows' => $pageSize,
            'page'      => $page,
        ]);

        return json_success([
            'total' => $paginator->total(),
            'page'  => $paginator->currentPage(),
            'list'  => $paginator->items(),
        ]);
    }

    /**
     * 设备日志列表，必填 uid。
     */
    public function deviceLogsList(Request $request)
    {
        $uid = (int) $request->get('uid', 0);
        if ($uid <= 0) {
            return json_error('缺少 uid 参数', 400);
        }

        $list = UserDeviceLog::where('user_id', $uid)
            ->order('id', 'desc')
            ->select();

        return json_success($list);
    }

    /**
     * 登录日志列表，必填 uid，可选 limit。
     */
    public function loginLogsList(Request $request)
    {
        $uid = (int) $request->get('uid', 0);
        if ($uid <= 0) {
            return json_error('缺少 uid 参数', 400);
        }

        $limit = min(500, max(1, (int) $request->get('limit', 100)));

        $list = UserLoginLog::where('user_id', $uid)
            ->order('login_time', 'desc')
            ->limit($limit)
            ->select();

        return json_success($list);
    }
}

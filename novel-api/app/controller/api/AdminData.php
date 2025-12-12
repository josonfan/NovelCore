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

class AdminData extends BaseController
{
    /**
     * 搜索日志列表
     *
     * 路由：`GET /api/admin/search-logs`
     * 鉴权：需 Admin Token
     * 过滤：`uid?`、`date?`（YYYY-MM-DD）
     * 返回：列表与总数（最多500条）
     *
     * @param Request $request
     * @return \think\Response
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
        $ids = $query->limit(500)->column('id');
        $fields = 'id,user_id,keyword,filters,created_at';
        $model = new UserSearchLog();
        $list = [];
        foreach ($ids as $id) {
            $list[] = $model->infoById((int)$id, $fields);
        }
        return api_response(200, '成功', $list, count($list));
    }

    /**
     * 用户收藏列表（后台）
     *
     * 路由：`GET /api/admin/favorites`
     * 鉴权：需 Admin Token
     * 参数：`uid` 必填
     * 分页：`page`、`limit`（默认100，最大200）
     * 返回：列表与总数
     *
     * @param Request $request
     * @return \think\Response
     */
    public function favoritesList(Request $request)
    {
        $uid = (int) $request->get('uid', 0);
        if ($uid <= 0) {
            return api_response(400, '缺少 uid 参数', []);
        }
        $page     = max(1, (int) $request->get('page', 1));
        $pageSize = min(200, max(1, (int) $request->get('limit', 100)));
        $paginator = UserNovelFavorite::where('user_id', $uid)
            ->order('id', 'desc')
            ->paginate(['list_rows' => $pageSize, 'page' => $page]);
        return api_response(200, '成功', $paginator->items(), (int)$paginator->total());
    }

    /**
     * 阅读历史列表（后台）
     *
     * 路由：`GET /api/admin/reading-history`
     * 鉴权：需 Admin Token
     * 参数：`uid` 必填
     * 返回：列表与总数
     *
     * @param Request $request
     * @return \think\Response
     */
    public function readingHistoryList(Request $request)
    {
        $uid = (int) $request->get('uid', 0);
        if ($uid <= 0) {
            return api_response(400, '缺少 uid 参数', []);
        }
        $ids = UserReadingHistory::where('user_id', $uid)
            ->order('last_read_at', 'desc')
            ->column('id');
        $fields = 'id,user_id,novel_id,chapter_id,progress,last_read_at';
        $model = new UserReadingHistory();
        $list = [];
        foreach ($ids as $id) {
            $list[] = $model->infoById((int)$id, $fields);
        }
        return api_response(200, '成功', $list, count($list));
    }

    /**
     * 阅读行为日志列表（后台）
     *
     * 路由：`GET /api/admin/read-logs`
     * 鉴权：需 Admin Token
     * 过滤：`uid?` 或 `date?` 至少一个
     * 分页：`page`、`limit`（默认100，最大200）
     * 返回：列表与总数
     *
     * @param Request $request
     * @return \think\Response
     */
    public function readLogsList(Request $request)
    {
        $uid = (int) $request->get('uid', 0);
        $date = trim((string) $request->get('date', ''));
        if ($uid <= 0 && $date === '') {
            return api_response(400, '请提供 uid 或 date 参数', []);
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
        $paginator = $query->paginate(['list_rows' => $pageSize, 'page' => $page]);
        return api_response(200, '成功', $paginator->items(), (int)$paginator->total());
    }

    public function deviceLogsList(Request $request)
    {
        $uid = (int) $request->get('uid', 0);
        if ($uid <= 0) {
            return api_response(400, '缺少 uid 参数', []);
        }
        $ids = UserDeviceLog::where('user_id', $uid)
            ->order('id', 'desc')
            ->column('id');
        $fields = 'id,user_id,device_id,device_brand,device_model,os,client_version,is_suspicious,ip,created_at';
        $model = new UserDeviceLog();
        $list = [];
        foreach ($ids as $id) {
            $list[] = $model->infoById((int)$id, $fields);
        }
        return api_response(200, '成功', $list, count($list));
    }

    /**
     * 登录日志列表（后台）
     *
     * 路由：`GET /api/admin/login-logs`
     * 鉴权：需 Admin Token
     * 参数：`uid` 必填；`limit` 默认100，最大500
     * 返回：列表与总数
     *
     * @param Request $request
     * @return \think\Response
     */
    public function loginLogsList(Request $request)
    {
        $uid = (int) $request->get('uid', 0);
        if ($uid <= 0) {
            return api_response(400, '缺少 uid 参数', []);
        }
        $limit = min(500, max(1, (int) $request->get('limit', 100)));
        $ids = UserLoginLog::where('user_id', $uid)
            ->order('login_time', 'desc')
            ->limit($limit)
            ->column('id');
        $fields = 'id,user_id,ip,country,province,city,isp,device_id,login_time';
        $model = new UserLoginLog();
        $list = [];
        foreach ($ids as $id) {
            $list[] = $model->infoById((int)$id, $fields);
        }
        return api_response(200, '成功', $list, count($list));
    }
}

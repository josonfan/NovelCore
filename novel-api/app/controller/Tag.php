<?php
declare(strict_types=1);

namespace app\controller;

use app\service\TagService;

class Tag extends Common
{
    /**
     * 标签列表
     * 路由：POST /api/Tag/index
     * 鉴权：无需登录
     * 入参：page, limit, type?
     * 返回：data { list, count }
     */
    public function index()
    {
        $page   = $this->request->param('page', 1, 'intval');
        $limit  = $this->request->param('limit', 10, 'intval');
        $type   = $this->request->param('type', '', 'trim');
        $where  = [];
        if ($type !== '') {
            $where['type'] = ['=', $type];
        }
        $field   = 'id,name,slug,type,created_at';
        $orderby = 'id desc';
        $res = TagService::list(formatWhere($where), $field, $orderby, $limit, $page);
        return $this->ajaxReturn(200, '获取成功', $res);
    }

    /**
     * 标签详情
     * 路由：POST /api/Tag/info
     * 鉴权：无需登录
     * 入参：id
     * 返回：data 对象（主表字段）
     */
    public function info()
    {
        $id = $this->request->param('id', 0, 'intval');
        $field = 'id,name,slug,type,created_at';
        $res = TagService::info($id, $field);
        return $this->ajaxReturn(200, '获取成功', $res);
    }
}

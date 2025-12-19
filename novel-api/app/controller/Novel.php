<?php
declare(strict_types=1);

namespace app\controller;

use app\service\NovelService;
use think\exception\ValidateException;

class Novel extends Common
{
    /**
     * 小说列表
     *
     * 路由：POST /api/Novel/index
     * 鉴权：无需登录
     * 筛选：`category_id`、`tag_id`、`order`
     * 分页：`page`、`limit`（默认10，最大50）
     * 返回：data { list: 主表字段集合, count }
     *
     * @return \think\Response
     */
    public function index()
    {       
        $page     = $this->request->param('page', 1, 'intval');
        $limit = $this->request->param('limit', 20, 'intval');       
        $where = [];
        $where['category_id'] = $this->request->param('category_id', 0, 'intval');
        if(empty($where['category_id']))unset($where['category_id']);
        $orderby  = 'created_at desc, id desc';
        $fields = 'novel_uuid as id,title,category_id,cover,intro,status,is_vip,word_count,updated_at';
        $result = NovelService::getList(formatWhere($where), $fields, $orderby, $page, $limit);       
        return $this->ajaxReturn(200, '获取成功', $result);
    }

    /**
     * 小说详情（外部ID）
     *
     * 路由：POST /api/Novel/show
     * 鉴权：无需登录
     * 参数：novelId 使用 `novel_uuid`（兼容内部数值ID）
     * 返回：data 主表字段视图
     *
     * @return \think\Response
     */
    public function show()
    {
        $novelId = (string)$this->request->param('novelId', '');      
        if (empty($novelId)) {
            throw new ValidateException('小说ID不能为空');
        }
        $fields = 'novel_uuid as id,title,cover,intro,status,is_vip,word_count,updated_at';
        $row = \app\service\NovelService::getInfoByUuid($novelId, $fields);        
        return $this->ajaxReturn(200, '获取成功', $row);
    }
}

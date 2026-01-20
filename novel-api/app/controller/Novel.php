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
        $order = $this->request->param('order', 'newest', 'trim');
        $where = [];
        $where['category_id'] = $this->request->param('category_id', 0, 'intval');
        if(empty($where['category_id']))unset($where['category_id']);
        $tagParam = $this->request->param('tag_ids');
        $tagIds = [];
        if (is_string($tagParam)) {
            $tagIds = array_values(array_filter(array_map('intval', explode(',', $tagParam))));
        } elseif (is_array($tagParam)) {
            $tagIds = array_values(array_filter(array_map('intval', $tagParam)));
        }
        if (!empty($tagIds)) {
            $novelIds = \app\model\NovelTag::where('tag_id', 'in', $tagIds)->column('novel_id');
            $novelIds = array_values(array_unique(array_map('intval', $novelIds)));
            $where['id'] = ['in', !empty($novelIds) ? $novelIds : [-1]];
        }
        $status = $this->request->param('status', 0, 'intval');
        if($status){
            $where['status'] = $status;
        }
        $is_r18 = $this->request->param('is_r18', 0, 'intval');
        if($is_r18){
            $where['is_r18'] = $is_r18;
        }
        switch($order){
            case 'newest':
                $orderby  = 'created_at desc, id desc';
                break;
            case 'hotest':                
                $orderby  = 'view_count desc, id desc';
                break;
            default:
                $orderby  = 'created_at desc, id desc';
                break;
        }
        $fields = 'novel_uuid as id,title,category_id,cover,tags_json,intro,author_name,is_r18,status,is_vip,word_count,updated_at';
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
        $fields = 'novel_uuid as id,title,category_id,cover,intro,tags_json,status,is_vip,is_r18,view_count,word_count,like_count,updated_at';
        $row = \app\service\NovelService::getInfoByUuid($novelId, $fields);
        return $this->ajaxReturn(200, '获取成功', $row);
    }
    /**
     * 小说用户状态（点赞、收藏）
     *
     * 路由：POST /api/Novel/userStatus
     * 鉴权：登录用户
     * 参数：novelId 使用 `novel_uuid`（兼容内部数值ID）
     * 返回：data { is_liked: 0/1, is_favorited: 0/1, reading_progress }
     *
     * @return \think\Response
     */
    public function userStatus()
    {
        $novelId = (string)$this->request->param('novelId', '');      
        if (empty($novelId)) {
            throw new ValidateException('小说ID不能为空');
        }
        $userId = (int)($this->request->user_id ?? 0);
        $status = \app\service\NovelService::getUserStatus($novelId, $userId);
        $status['reading_progress'] = \app\service\ReadingService::getProgress($userId, $novelId);
        return $this->ajaxReturn(200, '获取成功', $status);
    }
}

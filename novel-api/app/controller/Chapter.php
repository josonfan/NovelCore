<?php
declare(strict_types=1);

namespace app\controller;

use app\exception\BusinessException;
use app\model\Chapter as ChapterModel;
use app\service\NovelService;
use app\service\ChapterService;
use app\service\ContentService;
use think\exception\ValidateException;  

class Chapter extends Common
{
    /**
     * 章节列表
     * 路由：POST /api/Chapter/index
     * 鉴权：无需登录
     * 入参：novelId（外部 novel_uuid）, page, limit
     * 返回：data { list, count }
     */
    public function index()
    {
       
        $page     = $this->request->param('page', 1, 'intval');
        $limit = $this->request->param('limit', 20, 'intval');
        $novelId = $this->request->param('novelId', '', 'trim');
        if(empty($novelId)){
            throw new ValidateException('参数错误');
        }
        $novel = NovelService::getInfoByUuid($novelId,'id');
        $where =[];
        $where['novel_id'] = $novel['id'];
        $fields = 'id,chapter_uuid,novel_id,title,content_short,sort_order,is_free,is_vip,created_at';
        $orderby = 'sort_order asc';
        $result = ChapterService::list(formatWhere($where), $fields, $orderby, $limit,$page);
        return $this->ajaxReturn(200, '获取成功', $result);
    }

    /**
     * 章节详情
     * 路由：POST /api/Chapter/show
     * 鉴权：无需登录（VIP章节由服务层校验权限）
     * 入参：novelId（外部 novel_uuid）, chapterId（外部 chapter_uuid）
     * 返回：data { novel_id, chapter_id, title, content, is_free, is_vip, prev_id?, next_id? }
     */
    public function show()
    {  
        $chapterId = $this->request->param('chapterId', '', 'trim');
        if(empty($chapterId)){
            throw new ValidateException('参数错误');
        }
        $fields = 'id,chapter_uuid,novel_id,title,content_short,sort_order,is_free,is_vip,created_at';
        $chapter = ChapterService::getInfoByUuid($chapterId, $fields);
        return $this->ajaxReturn(200, '获取成功', $chapter);
        
    }
}

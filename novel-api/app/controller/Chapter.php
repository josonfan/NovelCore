<?php
declare(strict_types=1);

namespace app\controller;

use app\exception\BusinessException;
use app\model\Chapter as ChapterModel;
use app\service\NovelService;
use app\service\ChapterService;
use app\service\ContentService;
use think\exception\ValidateException;
use app\model\UserReadingHistory;



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
        if($this->request->user_id && !empty($result['list'])){
            //这里实现用户阅读进度的获取
            $chapterIds = array_column($result['list'], 'id');
            $readingProgress = UserReadingHistory::where('user_id', $this->request->user_id)
            ->where('chapter_id','in', $chapterIds)->column('id,progress','chapter_id');
            $result['list'] = array_map(function($item) use ($readingProgress){
                $item['progress'] = (int)($readingProgress[$item['id']]['progress'] ?? 0);
                // //是否已读
                // $item['isRead'] = isset($readingProgress[$item['id']]) ? true : false;
                return $item;
            }, $result['list']);
        }
        //增加一个阅读总进度
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
        if(empty($chapter)){
            throw new BusinessException('章节不存在');
        }
        $user_id = $this->request->user_id;
        $user = (new \app\service\UserService())->info($user_id);
        $isCheckUserCanRead = true;
        if ((int) $chapter['is_free'] === 1) {
            return;
        }elseif((int) $chapter['is_vip'] === 1){
            if ((int) $user['vip_expire'] <= time()) {
                $isCheckUserCanRead = false;
            }
        }

        if($isCheckUserCanRead){
           $content = ContentService::getByChapterId($chapter['id'],'content');
        $chapter['content'] = $content['content'];
        }else{
            $chapter['content'] = '';
        }   
        return $this->ajaxReturn(200, '获取成功', $chapter);
        
    }
    /**
     * 章节内容
     * 路由：POST /api/Chapter/content
     * 鉴权：无需登录（VIP章节由服务层校验权限）
     * 入参：chapterId（外部 chapter_uuid）
     * 返回：data { chapter_id, content }
     */
    public function content()
    {
        $chapterId = $this->request->param('chapterId', '', 'trim');
        if(empty($chapterId)){
            throw new ValidateException('参数错误');
        }
        $fields = 'id,chapter_uuid,novel_id,title,content_short,sort_order,is_free,is_vip,created_at';
        $chapter = ChapterService::getInfoByUuid($chapterId, $fields);
        if(empty($chapter)){
            throw new BusinessException('章节不存在');
        }
        $user_id = $this->request->user_id;
        if(!empty($user_id)){
            $user = (new \app\service\UserService())->info($user_id);
        }else{
            $user = null;
        }
        ChapterService::checkUserCanRead($user, $chapter);
        $content = ContentService::getByChapterId($chapter['id'],'content');
        return $this->ajaxReturn(200, '获取成功', $content);
    }
}

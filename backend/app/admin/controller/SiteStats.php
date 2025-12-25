<?php
namespace app\admin\controller;

use app\admin\service\SiteStatsService;

class SiteStats extends Backend
{
    /**
     * 站点统计列表
     */
    public function index()
    {
        $page = $this->request->param('page', 1, 'intval');
        $limit = $this->request->param('limit', 10, 'intval');
        $where = [];
        $siteId = $this->request->param('site_id');
        if ($siteId !== null && $siteId !== '') {
            $where['site_id'] = (int)$siteId;
        }
        $startDate = $this->request->param('start_date');
        $endDate = $this->request->param('end_date');
        if (!empty($startDate) && !empty($endDate)) {
            $where['stat_date'] = ['between', [$startDate, $endDate]];
        } elseif (!empty($startDate)) {
            $where['stat_date'] = ['>=', $startDate];
        } elseif (!empty($endDate)) {
            $where['stat_date'] = ['<=', $endDate];
        }
        $field = 'id,site_id,stats_id,stat_date,user_count,read_count,order_count,order_amount,created_at,updated_at,last_synced_at';
        $orderby = 'stat_date desc, id desc';
        $res = SiteStatsService::list(formatWhere($where), $field, $orderby, $limit, $page);
        return $this->ajaxReturn(200, '成功', $res);
    }
    /**
     * 站点统计详情
     */ 
    public function detail()
    {
        $id = (int)($this->request->param('id') ?? 0);
        $field = 'id,site_id,stats_id,stat_date,user_count,read_count,order_count,order_amount,created_at,updated_at,last_synced_at';
        $info = SiteStatsService::detail($id, $field);
        if (empty($info)) {
            return $this->ajaxReturn(404, '记录不存在');
        }
        return $this->ajaxReturn(200, '成功', $info);
    }
    /**
     * 站点统计摘要
     */ 
    public function summary()
    {
        $filters = [
            'site_id' => $this->request->param('site_id', null),
            'start_date' => $this->request->param('start_date', null),
            'end_date' => $this->request->param('end_date', null),
        ];
        $data = SiteStatsService::summary($filters);
        return $this->ajaxReturn(200, '成功', $data);
    }
    /**
     * 站点统计时间序列
     */ 
    public function series()
    {
        $filters = [
            'site_id' => $this->request->param('site_id', null),
            'start_date' => $this->request->param('start_date', null),
            'end_date' => $this->request->param('end_date', null),
        ];
        $data = SiteStatsService::series($filters);
        return $this->ajaxReturn(200, '成功', $data);
    }
}

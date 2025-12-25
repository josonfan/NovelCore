<?php
namespace app\admin\service;

use app\admin\model\SiteStats;
use think\facade\Db;

class SiteStatsService
{
    public static function list(array $where = [], string $field = '*', string $orderby = 'stat_date desc, id desc', int $limit = 10, int $page = 1): array
    {
        $m = new SiteStats();
        return $m->getList($where, $field, $orderby, $limit, $page);
    }

    public static function detail(int $id, string $field = '*')
    {
        $m = new SiteStats();
        return $m->infoById($id, $field);
    }

    public static function summary(array $filters = []): array
    {
        $query = Db::name('site_stats');
        if (!empty($filters['site_id'])) {
            $query->where('site_id', (int)$filters['site_id']);
        }
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->where('stat_date', 'between', [$filters['start_date'], $filters['end_date']]);
        } elseif (!empty($filters['start_date'])) {
            $query->where('stat_date', '>=', $filters['start_date']);
        } elseif (!empty($filters['end_date'])) {
            $query->where('stat_date', '<=', $filters['end_date']);
        }
        
        $readCount = (int)$query->sum('read_count');
        $orderCount = (int)$query->sum('order_count');
        $orderAmount = (float)$query->sum('order_amount');
        
        $days = (int)$query->count();

        $userTotal = 0;
        if (!empty($filters['site_id'])) {
            $latest = $query->order('stat_date desc, id desc')->find();
            $userTotal = $latest ? (int)$latest['user_count'] : 0;
        } else {
            $sites = $query->distinct(true)->column('site_id');
            foreach ($sites as $sid) {
                $latest = Db::name('site_stats')
                    ->where('site_id', (int)$sid)
                    ->when(!empty($filters['start_date']), function($q) use ($filters){ $q->where('stat_date','>=',$filters['start_date']); })
                    ->when(!empty($filters['end_date']), function($q) use ($filters){ $q->where('stat_date','<=',$filters['end_date']); })
                    ->order('stat_date desc, id desc')->find();
                $userTotal += $latest ? (int)$latest['user_count'] : 0;
            }
        }

        return [
            'user_count' => $userTotal,
            'read_count' => $readCount,
            'order_count' => $orderCount,
            'order_amount' => $orderAmount,
            'days' => $days,
        ];
    }

    public static function series(array $filters = []): array
    {
        $query = Db::name('site_stats')->field([
            'stat_date',
            'SUM(user_count) as user_count',
            'SUM(read_count) as read_count',
            'SUM(order_count) as order_count',
            'SUM(order_amount) as order_amount',
        ]);
        if (!empty($filters['site_id'])) {
            $query->where('site_id', (int)$filters['site_id']);
        }
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->where('stat_date', 'between', [$filters['start_date'], $filters['end_date']]);
        } elseif (!empty($filters['start_date'])) {
            $query->where('stat_date', '>=', $filters['start_date']);
        } elseif (!empty($filters['end_date'])) {
            $query->where('stat_date', '<=', $filters['end_date']);
        }
        $rows = $query->group('stat_date')->order('stat_date asc')->select()->toArray();
        $map = [];
        foreach ($rows as $r) {
            $map[$r['stat_date']] = [
                'user_count' => (int)$r['user_count'],
                'read_count' => (int)$r['read_count'],
                'order_count' => (int)$r['order_count'],
                'order_amount' => (float)$r['order_amount'],
            ];
        }
        $start = !empty($filters['start_date']) ? $filters['start_date'] : (isset($rows[0]) ? $rows[0]['stat_date'] : date('Y-m-d', strtotime('-6 day')));
        $end = !empty($filters['end_date']) ? $filters['end_date'] : (isset($rows[count($rows)-1]) ? $rows[count($rows)-1]['stat_date'] : date('Y-m-d'));
        if (strtotime($start) > strtotime($end)) {
            $tmp = $start; $start = $end; $end = $tmp;
        }
        $dates = [];
        $user = [];
        $read = [];
        $order = [];
        $amount = [];
        $cur = strtotime($start);
        $endTs = strtotime($end);
        while ($cur <= $endTs) {
            $d = date('Y-m-d', $cur);
            $dates[] = $d;
            $val = $map[$d] ?? ['user_count'=>0,'read_count'=>0,'order_count'=>0,'order_amount'=>0.0];
            $user[] = (int)$val['user_count'];
            $read[] = (int)$val['read_count'];
            $order[] = (int)$val['order_count'];
            $amount[] = (float)$val['order_amount'];
            $cur = strtotime('+1 day', $cur);
        }
        return [
            'dates' => $dates,
            'user_count' => $user,
            'read_count' => $read,
            'order_count' => $order,
            'order_amount' => $amount,
        ];
    }
}

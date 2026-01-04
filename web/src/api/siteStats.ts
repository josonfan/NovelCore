import { http } from './http'

/**
 * 站点统计数据类型
 */
export interface SiteStat {
  id: string
  site_id: number
  stats_id: string
  stat_date: string
  user_count: number
  read_count: number
  order_count: number
  order_amount: string
  created_at: string
  updated_at: string
  last_synced_at: string
}

/**
 * 站点统计列表查询参数
 */
export interface SiteStatsListParams {
  page: number
  limit: number
  site_id?: string | number
  start_date?: string
  end_date?: string
}

/**
 * 站点统计列表响应
 */
export interface SiteStatsListResponse {
  list: SiteStat[]
  count: number
}

/**
 * 获取站点统计列表
 */
export async function fetchSiteStatsList(params: SiteStatsListParams): Promise<SiteStatsListResponse> {
  const res = await http.post('SiteStats/index', params)
  return res.data?.data || { list: [], count: 0 }
}

/**
 * 获取站点统计详情
 */
export async function fetchSiteStatsDetail(id: number | string): Promise<SiteStat | null> {
  const res = await http.post('SiteStats/detail', { id })
  return res.data?.data || null
}



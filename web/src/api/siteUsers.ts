import { http } from './http'

/**
 * 站点用户数据类型
 */
export interface SiteUser {
  id: string | number
  site_id: number
  user_id: string
  username: string
  nickname: string
  avatar: string
  status: number
  device: string
  email: string
  client_version: string
  vip_expire: number
  created_at: string
  updated_at: string
  last_synced_at: string
}

/**
 * 用户列表查询参数
 */
export interface SiteUserListParams {
  page: number
  limit: number
  site_id?: string | number
  keyword?: string
}

/**
 * 用户列表响应
 */
export interface SiteUserListResponse {
  list: SiteUser[]
  count: number
}

/**
 * 获取站点用户列表
 */
export async function fetchSiteUserList(params: SiteUserListParams): Promise<SiteUserListResponse> {
  const res = await http.post('SiteUsers/index', params)
  return res.data?.data || { list: [], count: 0 }
}

/**
 * 获取用户详情
 */
export async function fetchSiteUserDetail(id: number | string): Promise<SiteUser | null> {
  const res = await http.post('SiteUsers/detail', { id })
  return res.data?.data || null
}

/**
 * 设置用户状态
 */
export async function setUserStatus(
  id: number | string,
  status: number
): Promise<{ code?: number; msg?: string; data?: { success: boolean } }> {
  const res = await http.post('SiteUsers/setStatus', { id, status })
  return res.data
}

/**
 * 用户状态映射
 */
export const USER_STATUS_MAP: Record<number, { text: string; type: 'success' | 'warning' | 'danger' | 'info' }> = {
  0: { text: '禁用', type: 'danger' },
  1: { text: '正常', type: 'success' },
}

/**
 * 状态信息类型
 */
export interface UserStatusInfo {
  text: string
  type: 'success' | 'warning' | 'danger' | 'info'
}

/**
 * 获取状态信息函数类型
 */
export interface GetUserStatusInfoFn {
  (status: number): UserStatusInfo
}

/**
 * 格式化VIP到期时间函数类型
 */
export interface FormatVipExpireFn {
  (expire: number): string
}

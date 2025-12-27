import { http } from './http'

/**
 * 评论数据类型
 */
export interface SiteComment {
  id: string | number
  site_id: number
  comment_id: string
  novel_id: string
  chapter_id: string | null
  user_id: string
  parent_id: string | null
  root_id: string | null
  content: string
  is_r18: number
  status: number
  review_source: number
  like_count: number
  created_at: string
  updated_at: string
  last_synced_at: string
}

/**
 * 评论列表查询参数
 */
export interface CommentListParams {
  page: number
  limit: number
  site_id?: string | number
  novel_id?: string | number
}

/**
 * 评论列表响应
 */
export interface CommentListResponse {
  list: SiteComment[]
  count: number
}

/**
 * 获取评论列表
 */
export async function fetchCommentList(params: CommentListParams): Promise<CommentListResponse> {
  const res = await http.post('SiteComments/index', params)
  return res.data?.data || { list: [], count: 0 }
}

/**
 * 获取评论详情
 */
export async function fetchCommentDetail(id: number | string): Promise<SiteComment | null> {
  const res = await http.post('SiteComments/detail', { id })
  return res.data?.data || null
}

/**
 * 审核评论
 */
export async function auditComment(
  id: number | string,
  status: number,
  audit_reason: string = ''
): Promise<{ code?: number; msg?: string; data?: { success: boolean } }> {
  const res = await http.post('SiteComments/audit', { id, status, audit_reason })
  return res.data
}

/**
 * 删除评论
 */
export async function deleteComment(id: number | string): Promise<{ code?: number; msg?: string }> {
  const res = await http.post('SiteComments/delete', { id })
  return res.data
}

/**
 * 状态信息类型
 */
export interface StatusInfo {
  text: string
  type: 'success' | 'warning' | 'danger' | 'info'
}

/**
 * 获取状态信息函数类型
 */
export interface GetStatusInfoFn {
  (status: number): StatusInfo
}

/**
 * 获取审核来源文本函数类型
 */
export interface GetReviewSourceTextFn {
  (source: number): string
}

/**
 * 评论状态映射
 */
export const COMMENT_STATUS_MAP: Record<number, StatusInfo> = {
  0: { text: '待审核', type: 'warning' },
  1: { text: '已通过', type: 'success' },
  2: { text: '已拒绝', type: 'danger' },
}

/**
 * 审核来源映射
 */
export const REVIEW_SOURCE_MAP: Record<number, string> = {
  0: '用户提交',
  1: '自动审核',
  2: '人工审核',
}

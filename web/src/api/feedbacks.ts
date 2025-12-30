import { http } from './http'

/**
 * 反馈附件类型
 */
export interface FeedbackAttachment {
  id: string
  site_id: number
  feedback_attachment_id: string
  feedback_no: string
  file_url: string
  file_type: string
  mime_type: string | null
  size_bytes: number | null
  created_at: string
  updated_at: string
}

/**
 * 反馈数据类型
 */
export interface Feedback {
  id: string
  site_id: number
  feedback_no: string
  user_id: string
  type: string
  content: string
  contact: string
  device_info?: string
  app_version?: string
  client_type?: string
  status: number
  reply_content: string | null
  reply_admin_id: string | null
  reply_at: string | null
  image_count: number
  created_at: string
  updated_at: string
  attachments?: FeedbackAttachment[]
}

/**
 * 反馈类型选项
 */
export interface FeedbackType {
  code: string
  name: string
}

/**
 * 反馈状态选项
 */
export interface FeedbackStatus {
  code: number
  name: string
}

/**
 * 反馈列表查询参数
 */
export interface FeedbackListParams {
  page: number
  limit: number
  site_id?: string | number
  user_id?: string
  ticket_no?: string
  status?: string | number
  type?: string
}

/**
 * 反馈列表响应
 */
export interface FeedbackListResponse {
  list: Feedback[]
  count: number
}

/**
 * 反馈处理参数
 */
export interface FeedbackProcessParams {
  id: string | number
  status: number
  reply_content: string
}

/**
 * 获取反馈列表
 */
export async function fetchFeedbackList(params: FeedbackListParams): Promise<FeedbackListResponse> {
  const res = await http.post('SiteFeedbacks/index', params)
  return res.data?.data || { list: [], count: 0 }
}

/**
 * 获取反馈详情
 */
export async function fetchFeedbackDetail(id: number | string): Promise<Feedback | null> {
  const res = await http.post('SiteFeedbacks/detail', { id })
  return res.data?.data || null
}

/**
 * 获取反馈类型列表
 */
export async function fetchFeedbackTypes(): Promise<FeedbackType[]> {
  const res = await http.post('SiteFeedbacks/types')
  return res.data?.data || []
}

/**
 * 获取反馈状态列表
 */
export async function fetchFeedbackStatuses(): Promise<FeedbackStatus[]> {
  const res = await http.post('SiteFeedbacks/statuses')
  return res.data?.data || []
}

/**
 * 处理反馈
 */
export async function processFeedback(
  params: FeedbackProcessParams
): Promise<{ code?: number; msg?: string; data?: { id: number; success: boolean } }> {
  const res = await http.post('SiteFeedbacks/process', params)
  return res.data
}

/**
 * 反馈状态颜色映射
 */
export const FEEDBACK_STATUS_TYPE_MAP: Record<number, 'warning' | 'primary' | 'success' | 'info' | 'danger'> = {
  0: 'warning',   // 待处理
  1: 'primary',   // 处理中
  2: 'success',   // 已解决
  3: 'info',      // 已关闭
  4: 'danger',    // 已拒绝
}

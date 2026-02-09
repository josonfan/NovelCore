import { http } from './http'

/**
 * 工单附件类型
 */
export interface TicketAttachment {
  id: string
  site_id: number
  ticket_attachment_id: string
  ticket_no: string
  file_url: string
  file_type: string
  mime_type: string | null
  size_bytes: number | null
  created_at: string
  updated_at: string
}

/**
 * 工单数据类型
 */
export interface Ticket {
  id: string
  site_id: number
  ticket_id: string
  ticket_no: string
  user_id: string
  type: string
  title: string
  description: string
  work_id: string
  contact: string
  priority: number
  status: number
  reply_content: string | null
  reply_admin_id: string | null
  reply_at: string | null
  created_at: string
  updated_at: string
  attachments?: TicketAttachment[]
}

/**
 * 工单类型选项
 */
export interface TicketType {
  code: string
  name: string
}

/**
 * 工单状态选项
 */
export interface TicketStatus {
  code: number
  name: string
}

/**
 * 工单列表查询参数
 */
export interface TicketListParams {
  page: number
  limit: number
  site_id?: string | number
  user_id?: string
  ticket_no?: string
  status?: string | number
  type?: string
}

/**
 * 工单列表响应
 */
export interface TicketListResponse {
  list: Ticket[]
  count: number
}

/**
 * 工单处理参数
 */
export interface TicketProcessParams {
  id: string | number
  status: number
  reply_content: string
}

/**
 * 工单回复类型
 */
export interface TicketReply {
  id: string
  ticket_id: string
  user_id: string
  user_type: 1 | 2  // 1=用户, 2=管理员
  user_type_text: string  // "用户" | "管理员"
  content: string
  attachments: string[]  // 图片URL数组
  created_at: string
}

/**
 * 回复工单参数
 */
export interface TicketReplyParams {
  id: string           // 工单ID
  content: string      // 回复内容
  attachments: string[] // 图片URL数组
  status: number       // 0=待处理, 1=处理中, 3=已关闭
}

/**
 * 获取工单列表
 */
export async function fetchTicketList(params: TicketListParams): Promise<TicketListResponse> {
  const res = await http.post('SiteTickets/index', params)
  return res.data?.data || { list: [], count: 0 }
}

/**
 * 获取工单详情
 */
export async function fetchTicketDetail(id: number | string): Promise<Ticket | null> {
  const res = await http.post('SiteTickets/detail', { id })
  return res.data?.data || null
}

/**
 * 获取工单类型列表
 */
export async function fetchTicketTypes(): Promise<TicketType[]> {
  const res = await http.post('SiteTickets/types')
  return res.data?.data || []
}

/**
 * 获取工单状态列表
 */
export async function fetchTicketStatuses(): Promise<TicketStatus[]> {
  const res = await http.post('SiteTickets/statuses')
  return res.data?.data || []
}

/**
 * 处理工单
 */
export async function processTicket(
  params: TicketProcessParams
): Promise<{ code?: number; msg?: string; data?: { id: number; success: boolean } }> {
  const res = await http.post('SiteTickets/process', params)
  return res.data
}

/**
 * 获取工单回复列表
 */
export async function fetchTicketReplies(id: string): Promise<TicketReply[]> {
  const res = await http.post('SiteTickets/replies', { id })
  return res.data?.data || []
}

/**
 * 回复工单
 */
export async function replyTicket(
  params: TicketReplyParams
): Promise<{ code?: number; msg?: string; data?: unknown }> {
  const res = await http.post('SiteTickets/reply', params)
  return res.data
}

/**
 * 回复状态选项
 */
export const REPLY_STATUS_OPTIONS = [
  { code: 0, name: '待处理' },
  { code: 1, name: '处理中' },
  { code: 3, name: '已关闭' },
] as const

/**
 * 工单状态颜色映射
 */
export const TICKET_STATUS_TYPE_MAP: Record<number, 'warning' | 'primary' | 'success' | 'info' | 'danger'> = {
  0: 'warning',   // 待处理
  1: 'primary',   // 处理中
  2: 'success',   // 已解决
  3: 'info',      // 已关闭
  4: 'danger',    // 已拒绝
}

/**
 * 工单优先级映射
 */
export const TICKET_PRIORITY_MAP: Record<number, { text: string; type: 'success' | 'warning' | 'danger' | 'info' }> = {
  0: { text: '普通', type: 'info' },
  1: { text: '重要', type: 'warning' },
  2: { text: '紧急', type: 'danger' },
}

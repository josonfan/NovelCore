import { ref, watch } from 'vue'
import { ElMessage } from 'element-plus'
import {
  fetchTicketList,
  fetchTicketDetail,
  fetchTicketTypes,
  fetchTicketStatuses,
  fetchTicketReplies,
  TICKET_STATUS_TYPE_MAP,
  TICKET_PRIORITY_MAP,
} from '../api/tickets'
import type {
  Ticket,
  TicketType,
  TicketStatus,
  TicketListParams,
  TicketReply,
} from '../api/tickets'

export interface TicketFilters {
  siteId: string
  ticketNo: string
  userId: string
  status: string
  type: string
}

/**
 * 工单列表管理
 */
export function useTicketList() {
  const loading = ref(false)
  const page = ref(1)
  const limit = ref(10)
  const total = ref(0)
  const rows = ref<Ticket[]>([])

  // 筛选条件
  const filters = ref<TicketFilters>({
    siteId: '',
    ticketNo: '',
    userId: '',
    status: '',
    type: '',
  })

  // 详情/处理面板
  const showDetail = ref(false)
  const detail = ref<Ticket | null>(null)
  const replies = ref<TicketReply[]>([])

  // 工单类型和状态选项
  const ticketTypes = ref<TicketType[]>([])
  const ticketStatuses = ref<TicketStatus[]>([])

  /**
   * 加载工单类型和状态
   */
  async function loadOptions() {
    try {
      const [types, statuses] = await Promise.all([
        fetchTicketTypes(),
        fetchTicketStatuses(),
      ])
      ticketTypes.value = types
      ticketStatuses.value = statuses
    } catch {
      console.error('加载工单选项失败')
    }
  }

  /**
   * 加载工单列表
   */
  async function load() {
    if (!filters.value.siteId) {
      rows.value = []
      total.value = 0
      return
    }

    loading.value = true
    try {
      const params: TicketListParams = {
        page: page.value,
        limit: limit.value,
        site_id: filters.value.siteId,
      }
      if (filters.value.ticketNo) {
        params.ticket_no = filters.value.ticketNo
      }
      if (filters.value.userId) {
        params.user_id = filters.value.userId
      }
      if (filters.value.status !== '') {
        params.status = filters.value.status
      }
      if (filters.value.type) {
        params.type = filters.value.type
      }
      const data = await fetchTicketList(params)
      rows.value = data?.list || []
      total.value = data?.count || 0
    } finally {
      loading.value = false
    }
  }

  /**
   * 刷新列表
   */
  function refresh() {
    load()
  }

  /**
   * 翻页
   */
  function onPageChange(p: number) {
    page.value = p
    load()
  }

  /**
   * 修改每页数量
   */
  function onSizeChange(s: number) {
    limit.value = s
    page.value = 1
    load()
  }

  /**
   * 加载回复列表
   */
  async function loadReplies(id: string) {
    try {
      replies.value = await fetchTicketReplies(id)
    } catch {
      replies.value = []
      console.error('加载回复列表失败')
    }
  }

  /**
   * 查看详情（同时加载回复列表）
   */
  async function handleDetail(row: Ticket) {
    try {
      const [d] = await Promise.all([
        fetchTicketDetail(row.id),
        loadReplies(row.id),
      ])
      detail.value = d
      showDetail.value = true
    } catch {
      ElMessage.error('获取详情失败')
    }
  }

  /**
   * 回复成功后刷新
   */
  async function onReplied() {
    if (detail.value) {
      // 重新加载详情和回复
      const [d] = await Promise.all([
        fetchTicketDetail(detail.value.id),
        loadReplies(detail.value.id),
      ])
      detail.value = d
      // 刷新列表
      load()
    }
  }

  /**
   * 获取状态信息
   */
  function getStatusInfo(status: number): { text: string; type: 'warning' | 'primary' | 'success' | 'info' | 'danger' } {
    const statusItem = ticketStatuses.value.find(s => s.code === status)
    return {
      text: statusItem?.name || '未知',
      type: TICKET_STATUS_TYPE_MAP[status] || 'info',
    }
  }

  /**
   * 获取类型名称
   */
  function getTypeName(typeCode: string): string {
    const typeItem = ticketTypes.value.find(t => t.code === typeCode)
    return typeItem?.name || typeCode
  }

  /**
   * 获取优先级信息
   */
  function getPriorityInfo(priority: number): { text: string; type: 'success' | 'warning' | 'danger' | 'info' } {
    return TICKET_PRIORITY_MAP[priority] || { text: '未知', type: 'info' as const }
  }

  // 监听筛选条件变化
  watch(
    () => filters.value.siteId,
    () => {
      page.value = 1
      load()
    }
  )

  return {
    // 状态
    loading,
    page,
    limit,
    total,
    rows,
    filters,
    showDetail,
    detail,
    replies,
    ticketTypes,
    ticketStatuses,

    // 方法
    loadOptions,
    load,
    refresh,
    onPageChange,
    onSizeChange,
    handleDetail,
    onReplied,
    getStatusInfo,
    getTypeName,
    getPriorityInfo,
  }
}

export type { Ticket, TicketType, TicketStatus }

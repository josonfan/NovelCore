import { ref } from 'vue'
import { ElMessage } from 'element-plus'
import {
  fetchSiteOrdersList,
  fetchSiteOrderDetail,
  type SiteOrderItem,
  type OrderStatus,
} from '../api/siteOrders'

export type { SiteOrderItem, OrderStatus }

export interface OrderFilters {
  order_no?: string
  user_id?: string
  status?: OrderStatus
  site_id?: string | number
}

export function useOrderList() {
  const loading = ref(false)
  const rows = ref<SiteOrderItem[]>([])
  const total = ref(0)
  const page = ref(1)
  const limit = ref(10)
  const filters = ref<OrderFilters>({})

  // 详情弹窗
  const showDetail = ref(false)
  const detail = ref<SiteOrderItem | null>(null)

  async function load() {
    loading.value = true
    try {
      const data = await fetchSiteOrdersList({
        page: page.value,
        limit: limit.value,
        order_no: filters.value.order_no,
        user_id: filters.value.user_id,
        status: filters.value.status,
        site_id: filters.value.site_id,
      })
      rows.value = data?.list || []
      total.value = data?.count || 0
    } catch (e: unknown) {
      const resp = (e as { response?: { data?: { message?: string; msg?: string } } })?.response
        ?.data
      ElMessage.error(resp?.message || resp?.msg || '加载失败')
      rows.value = []
      total.value = 0
    } finally {
      loading.value = false
    }
  }

  function onPageChange(p: number) {
    page.value = p
    load()
  }

  function onSizeChange(s: number) {
    limit.value = s
    page.value = 1
    load()
  }

  function reload() {
    page.value = 1
    load()
  }

  async function openDetail(row: SiteOrderItem) {
    try {
      const d = await fetchSiteOrderDetail(row.id)
      detail.value = d
      showDetail.value = true
    } catch (e: unknown) {
      const resp = (e as { response?: { data?: { message?: string; msg?: string } } })?.response
        ?.data
      ElMessage.error(resp?.message || resp?.msg || '获取详情失败')
    }
  }

  return {
    loading,
    rows,
    total,
    page,
    limit,
    filters,
    showDetail,
    detail,
    load,
    onPageChange,
    onSizeChange,
    reload,
    openDetail,
  }
}

// 状态工具函数
export function getStatusText(s?: OrderStatus): string {
  const map: Record<OrderStatus, string> = {
    pending: '待处理',
    processing: '处理中',
    paid: '已支付',
    cancelled: '已取消',
    refunded: '已退款',
  }
  return s ? map[s] || '-' : '-'
}

export function getStatusType(s?: OrderStatus): 'success' | 'warning' | 'info' | 'danger' {
  const map: Record<OrderStatus, 'success' | 'warning' | 'info' | 'danger'> = {
    paid: 'success',
    processing: 'warning',
    pending: 'info',
    cancelled: 'danger',
    refunded: 'danger',
  }
  return s ? map[s] || 'info' : 'info'
}

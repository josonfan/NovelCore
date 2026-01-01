import { ref, watch } from 'vue'
import { ElMessage } from 'element-plus'
import {
  fetchSiteStatsList,
  fetchSiteStatsDetail,
} from '../api/siteStats'
import type {
  SiteStat,
  SiteStatsListParams,
} from '../api/siteStats'

export interface SiteStatsFilters {
  siteId: string
  startDate: string
  endDate: string
}

/**
 * 获取默认日期范围（最近7天）
 */
function getDefaultDateRange(): [string, string] {
  const end = new Date()
  const start = new Date()
  start.setDate(start.getDate() - 6)
  
  const formatDate = (d: Date) => {
    const year = d.getFullYear()
    const month = String(d.getMonth() + 1).padStart(2, '0')
    const day = String(d.getDate()).padStart(2, '0')
    return `${year}-${month}-${day}`
  }
  
  return [formatDate(start), formatDate(end)]
}

/**
 * 站点统计列表管理
 */
export function useSiteStatsList() {
  const loading = ref(false)
  const page = ref(1)
  const limit = ref(10)
  const total = ref(0)
  const rows = ref<SiteStat[]>([])

  // 默认日期范围
  const [defaultStart, defaultEnd] = getDefaultDateRange()

  // 筛选条件
  const filters = ref<SiteStatsFilters>({
    siteId: '',
    startDate: defaultStart,
    endDate: defaultEnd,
  })

  // 详情
  const showDetail = ref(false)
  const detail = ref<SiteStat | null>(null)

  /**
   * 加载统计列表
   */
  async function load() {
    if (!filters.value.siteId) {
      rows.value = []
      total.value = 0
      return
    }

    loading.value = true
    try {
      const params: SiteStatsListParams = {
        page: page.value,
        limit: limit.value,
        site_id: filters.value.siteId,
      }
      if (filters.value.startDate) {
        params.start_date = filters.value.startDate
      }
      if (filters.value.endDate) {
        params.end_date = filters.value.endDate
      }
      const data = await fetchSiteStatsList(params)
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
   * 查看详情
   */
  async function handleDetail(row: SiteStat) {
    try {
      const d = await fetchSiteStatsDetail(row.id)
      detail.value = d
      showDetail.value = true
    } catch {
      ElMessage.error('获取详情失败')
    }
  }

  /**
   * 格式化金额
   */
  function formatAmount(amount: string | number): string {
    const num = typeof amount === 'string' ? parseFloat(amount) : amount
    return `¥${num.toFixed(2)}`
  }

  // 监听站点变化
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

    // 方法
    load,
    refresh,
    onPageChange,
    onSizeChange,
    handleDetail,
    formatAmount,
  }
}

export type { SiteStat }


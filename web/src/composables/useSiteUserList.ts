import { ref, watch } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import {
  fetchSiteUserList,
  fetchSiteUserDetail,
  setUserStatus,
  USER_STATUS_MAP,
} from '../api/siteUsers'
import type { SiteUser, UserStatusInfo } from '../api/siteUsers'

export interface SiteUserFilters {
  siteId: string
  keyword: string
}

/**
 * 站点用户列表管理
 */
export function useSiteUserList() {
  const loading = ref(false)
  const page = ref(1)
  const limit = ref(10)
  const total = ref(0)
  const rows = ref<SiteUser[]>([])

  // 筛选条件
  const filters = ref<SiteUserFilters>({
    siteId: '',
    keyword: '',
  })

  // 详情
  const showDetail = ref(false)
  const detail = ref<SiteUser | null>(null)

  /**
   * 加载用户列表
   */
  async function load() {
    if (!filters.value.siteId) {
      rows.value = []
      total.value = 0
      return
    }

    loading.value = true
    try {
      const params: Record<string, unknown> = {
        page: page.value,
        limit: limit.value,
        site_id: filters.value.siteId,
      }
      if (filters.value.keyword) {
        params.keyword = filters.value.keyword
      }
      const data = await fetchSiteUserList(params as Parameters<typeof fetchSiteUserList>[0])
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
  async function handleDetail(row: SiteUser) {
    try {
      const d = await fetchSiteUserDetail(row.id)
      detail.value = d
      showDetail.value = true
    } catch {
      ElMessage.error('获取详情失败')
    }
  }

  /**
   * 获取状态信息
   */
  function getStatusInfo(status: number): UserStatusInfo {
    return USER_STATUS_MAP[status] || { text: '未知', type: 'info' as const }
  }

  /**
   * 格式化 VIP 到期时间
   */
  function formatVipExpire(expire: number): string {
    if (!expire || expire === 0) return '非VIP'
    const date = new Date(expire * 1000)
    return date.toLocaleDateString('zh-CN')
  }

  /**
   * 切换用户状态（启用/禁用）
   */
  async function handleToggleStatus(row: SiteUser) {
    const isDisabling = row.status === 1
    const actionText = isDisabling ? '禁用' : '启用'
    const newStatus = isDisabling ? 0 : 1

    try {
      await ElMessageBox.confirm(
        `确认${actionText}用户「${row.nickname || row.username}」？`,
        '提示',
        {
          type: 'warning',
          confirmButtonText: `确认${actionText}`,
          cancelButtonText: '取消',
        }
      )
      const res = await setUserStatus(row.id, newStatus)
      if (res?.code === 200) {
        ElMessage.success(`已${actionText}`)
        load()
      } else {
        ElMessage.error(res?.msg || '操作失败')
      }
    } catch {
      // 用户取消
    }
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

    // 方法
    load,
    refresh,
    onPageChange,
    onSizeChange,
    handleDetail,
    handleToggleStatus,
    getStatusInfo,
    formatVipExpire,
  }
}

export type { SiteUser }

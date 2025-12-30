import { ref, watch } from 'vue'
import { ElMessage } from 'element-plus'
import {
  fetchFeedbackList,
  fetchFeedbackDetail,
  fetchFeedbackTypes,
  fetchFeedbackStatuses,
  processFeedback,
  FEEDBACK_STATUS_TYPE_MAP,
} from '../api/feedbacks'
import type {
  Feedback,
  FeedbackType,
  FeedbackStatus,
  FeedbackListParams,
  FeedbackProcessParams,
} from '../api/feedbacks'

export interface FeedbackFilters {
  siteId: string
  feedbackNo: string
  userId: string
  status: string
  type: string
}

/**
 * 用户建议列表管理
 */
export function useFeedbackList() {
  const loading = ref(false)
  const page = ref(1)
  const limit = ref(10)
  const total = ref(0)
  const rows = ref<Feedback[]>([])

  // 筛选条件
  const filters = ref<FeedbackFilters>({
    siteId: '',
    feedbackNo: '',
    userId: '',
    status: '',
    type: '',
  })

  // 详情
  const showDetail = ref(false)
  const detail = ref<Feedback | null>(null)

  // 处理反馈弹窗
  const showProcess = ref(false)
  const processForm = ref({
    id: '',
    status: 2,
    reply_content: '',
  })

  // 反馈类型和状态选项
  const feedbackTypes = ref<FeedbackType[]>([])
  const feedbackStatuses = ref<FeedbackStatus[]>([])

  /**
   * 加载反馈类型和状态
   */
  async function loadOptions() {
    try {
      const [types, statuses] = await Promise.all([
        fetchFeedbackTypes(),
        fetchFeedbackStatuses(),
      ])
      feedbackTypes.value = types
      feedbackStatuses.value = statuses
    } catch {
      console.error('加载反馈选项失败')
    }
  }

  /**
   * 加载反馈列表
   */
  async function load() {
    if (!filters.value.siteId) {
      rows.value = []
      total.value = 0
      return
    }

    loading.value = true
    try {
      const params: FeedbackListParams = {
        page: page.value,
        limit: limit.value,
        site_id: filters.value.siteId,
      }
      if (filters.value.feedbackNo) {
        params.ticket_no = filters.value.feedbackNo
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
      const data = await fetchFeedbackList(params)
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
  async function handleDetail(row: Feedback) {
    try {
      const d = await fetchFeedbackDetail(row.id)
      detail.value = d
      showDetail.value = true
    } catch {
      ElMessage.error('获取详情失败')
    }
  }

  /**
   * 打开处理反馈弹窗
   */
  function handleProcess(row: Feedback) {
    processForm.value = {
      id: row.id,
      status: row.status === 0 ? 1 : 2, // 默认：待处理→处理中，否则→已解决
      reply_content: '',
    }
    showProcess.value = true
  }

  /**
   * 提交处理反馈
   */
  async function submitProcess() {
    if (!processForm.value.reply_content.trim()) {
      ElMessage.warning('请输入回复内容')
      return false
    }

    try {
      const params: FeedbackProcessParams = {
        id: processForm.value.id,
        status: processForm.value.status,
        reply_content: processForm.value.reply_content,
      }
      const res = await processFeedback(params)
      if (res?.code === 200) {
        ElMessage.success('处理成功')
        showProcess.value = false
        load()
        return true
      } else {
        ElMessage.error(res?.msg || '处理失败')
        return false
      }
    } catch {
      ElMessage.error('处理失败')
      return false
    }
  }

  /**
   * 获取状态信息
   */
  function getStatusInfo(status: number): { text: string; type: 'warning' | 'primary' | 'success' | 'info' | 'danger' } {
    const statusItem = feedbackStatuses.value.find(s => s.code === status)
    return {
      text: statusItem?.name || '未知',
      type: FEEDBACK_STATUS_TYPE_MAP[status] || 'info',
    }
  }

  /**
   * 获取类型名称
   */
  function getTypeName(typeCode: string): string {
    const typeItem = feedbackTypes.value.find(t => t.code === typeCode)
    return typeItem?.name || typeCode
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
    showProcess,
    processForm,
    feedbackTypes,
    feedbackStatuses,

    // 方法
    loadOptions,
    load,
    refresh,
    onPageChange,
    onSizeChange,
    handleDetail,
    handleProcess,
    submitProcess,
    getStatusInfo,
    getTypeName,
  }
}

export type { Feedback, FeedbackType, FeedbackStatus }

import { ref, watch } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import {
  fetchCommentList,
  fetchCommentDetail,
  auditComment,
  deleteComment,
  COMMENT_STATUS_MAP,
  REVIEW_SOURCE_MAP,
} from '../api/comments'
import type { SiteComment, CommentListParams } from '../api/comments'

export interface CommentFilters {
  siteId: string
  novelId: string
}

/**
 * 评论列表管理
 */
export function useCommentList() {
  const loading = ref(false)
  const page = ref(1)
  const limit = ref(10)
  const total = ref(0)
  const rows = ref<SiteComment[]>([])

  // 筛选条件
  const filters = ref<CommentFilters>({
    siteId: '',
    novelId: '',
  })

  // 详情
  const showDetail = ref(false)
  const detail = ref<SiteComment | null>(null)

  /**
   * 加载评论列表
   */
  async function load() {
    if (!filters.value.siteId) {
      rows.value = []
      total.value = 0
      return
    }

    loading.value = true
    try {
      const params: CommentListParams = {
        page: page.value,
        limit: limit.value,
        site_id: filters.value.siteId,
      }
      if (filters.value.novelId) {
        params.novel_id = filters.value.novelId
      }
      const data = await fetchCommentList(params)
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
  async function handleDetail(row: SiteComment) {
    try {
      const d = await fetchCommentDetail(row.id)
      detail.value = d
      showDetail.value = true
    } catch {
      ElMessage.error('获取详情失败')
    }
  }

  /**
   * 审核通过
   */
  async function handleApprove(row: SiteComment) {
    try {
      await ElMessageBox.confirm('确认通过该评论？', '审核', {
        type: 'info',
        confirmButtonText: '通过',
        cancelButtonText: '取消',
      })
      const res = await auditComment(row.id, 1, '')
      if (res?.code === 200) {
        ElMessage.success('已通过')
        load()
      } else {
        ElMessage.error(res?.msg || '操作失败')
      }
    } catch {
      // 用户取消
    }
  }

  /**
   * 审核拒绝
   */
  async function handleReject(row: SiteComment) {
    try {
      const { value: reason } = await ElMessageBox.prompt('请输入拒绝理由', '审核拒绝', {
        confirmButtonText: '确认拒绝',
        cancelButtonText: '取消',
        inputPlaceholder: '请输入拒绝理由（可选）',
        type: 'warning',
      })
      const res = await auditComment(row.id, 2, reason || '')
      if (res?.code === 200) {
        ElMessage.success('已拒绝')
        load()
      } else {
        ElMessage.error(res?.msg || '操作失败')
      }
    } catch {
      // 用户取消
    }
  }

  /**
   * 删除评论
   */
  async function handleDelete(row: SiteComment) {
    try {
      await ElMessageBox.confirm('确认删除该评论？删除后无法恢复。', '提示', {
        type: 'warning',
        confirmButtonText: '确认删除',
        cancelButtonText: '取消',
      })
      const res = await deleteComment(row.id)
      if (res?.code === 200) {
        ElMessage.success('已删除')
        load()
      } else {
        ElMessage.error(res?.msg || '删除失败')
      }
    } catch {
      // 用户取消
    }
  }

  /**
   * 获取状态信息
   */
  function getStatusInfo(status: number) {
    return COMMENT_STATUS_MAP[status] || { text: '未知', type: 'info' as const }
  }

  /**
   * 获取审核来源文本
   */
  function getReviewSourceText(source: number) {
    return REVIEW_SOURCE_MAP[source] || '未知'
  }

  // 监听筛选条件变化
  watch(
    () => filters.value.siteId,
    () => {
      page.value = 1
      load()
    }
  )

  watch(
    () => filters.value.novelId,
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
    handleApprove,
    handleReject,
    handleDelete,
    getStatusInfo,
    getReviewSourceText,
  }
}

export type { SiteComment }

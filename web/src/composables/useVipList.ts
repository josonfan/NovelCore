import { ref, computed } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import {
  fetchVipList,
  fetchVipDetail,
  toggleVip,
  deleteVip,
  type VipItem,
} from '../api/vip'

export type { VipItem }

export function useVipList() {
  const loading = ref(false)
  const rows = ref<VipItem[]>([])
  const total = ref(0)
  const page = ref(1)
  const limit = ref(10)
  const keyword = ref('')

  // 详情弹窗
  const showDetail = ref(false)
  const detail = ref<VipItem | null>(null)

  const filtered = computed(() => {
    if (!keyword.value) return rows.value
    const k = keyword.value.toLowerCase()
    return rows.value.filter(
      (x) =>
        (x.name || '').toLowerCase().includes(k) ||
        (x.descript || '').toLowerCase().includes(k)
    )
  })

  async function load() {
    loading.value = true
    try {
      const data = await fetchVipList({
        page: page.value,
        limit: limit.value,
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

  async function onToggle(row: VipItem) {
    try {
      const newStatus = row.status === 1 ? 0 : 1
      const res = await toggleVip(row.id, newStatus)
      if (res?.code === 200) {
        ElMessage.success('已更新')
        load()
      } else {
        ElMessage.error(res?.msg || '操作失败')
      }
    } catch (e: unknown) {
      const resp = (e as { response?: { data?: { message?: string; msg?: string } } })?.response
        ?.data
      ElMessage.error(resp?.message || resp?.msg || '操作失败')
    }
  }

  async function onDelete(row: VipItem) {
    try {
      await ElMessageBox.confirm('确认删除该套餐？', '提示', { type: 'warning' })
      const res = await deleteVip(row.id)
      if (res?.code === 200) {
        ElMessage.success('已删除')
        load()
      } else {
        ElMessage.error(res?.msg || '删除失败')
      }
    } catch {
      // cancelled
    }
  }

  async function openDetail(row: VipItem) {
    try {
      const d = await fetchVipDetail(row.id)
      detail.value = d
      showDetail.value = true
    } catch {
      detail.value = row
      showDetail.value = true
    }
  }

  return {
    loading,
    rows,
    total,
    page,
    limit,
    keyword,
    filtered,
    showDetail,
    detail,
    load,
    onPageChange,
    onSizeChange,
    onToggle,
    onDelete,
    openDetail,
  }
}

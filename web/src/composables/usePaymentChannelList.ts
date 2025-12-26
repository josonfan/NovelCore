import { ref, computed } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import {
  fetchPaymentChannelList,
  fetchPaymentChannelDetail,
  togglePaymentChannel,
  deletePaymentChannel,
  type PaymentChannel,
} from '../api/paymentChannels'

export type { PaymentChannel }

export function usePaymentChannelList() {
  const loading = ref(false)
  const rows = ref<PaymentChannel[]>([])
  const total = ref(0)
  const page = ref(1)
  const limit = ref(10)
  const keyword = ref('')

  // 详情弹窗
  const showDetail = ref(false)
  const detail = ref<PaymentChannel | null>(null)

  const filtered = computed(() => {
    if (!keyword.value) return rows.value
    const k = keyword.value.toLowerCase()
    return rows.value.filter(
      (x) =>
        (x.name || '').toLowerCase().includes(k) ||
        (x.pay_url || '').toLowerCase().includes(k)
    )
  })

  async function load() {
    loading.value = true
    try {
      const data = await fetchPaymentChannelList({
        page: page.value,
        limit: limit.value,
      })
      rows.value = data?.list || []
      total.value = data?.count || 0
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

  async function onToggle(row: PaymentChannel) {
    try {
      const newStatus = row.status === 1 ? 0 : 1
      const res = await togglePaymentChannel(row.id, newStatus)
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

  async function onDelete(row: PaymentChannel) {
    try {
      await ElMessageBox.confirm('确认删除该渠道？', '提示', { type: 'warning' })
      const res = await deletePaymentChannel(row.id)
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

  async function openDetail(row: PaymentChannel) {
    try {
      const d = await fetchPaymentChannelDetail(row.id)
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

import { ref } from 'vue'
import { auditNovel, type Novel } from '../api/novels'
import { ElMessage } from 'element-plus'

export interface AuditData {
  ids: Array<number | string>
  status: number
  reason: string
}

export function useNovelAudit(onSuccess?: () => void) {
  const showAudit = ref(false)
  const audit = ref<AuditData>({ ids: [], status: 1, reason: '' })

  function openAudit(ids?: Array<number | string>, selected?: Novel[]) {
    const arr =
      Array.isArray(ids) && ids.length ? ids : (selected || []).map((r) => r.id)
    if (!arr.length) {
      ElMessage.error('请先选择要审核的小说')
      return
    }
    audit.value = { ids: arr, status: 1, reason: '' }
    showAudit.value = true
  }

  async function submitAudit() {
    try {
      if (!audit.value.ids.length) {
        ElMessage.error('未选择小说')
        return
      }
      let ok = 0
      if (audit.value.ids.length === 1) {
        const res = await auditNovel({
          id: audit.value.ids[0],
          audit_status: audit.value.status,
          reason: audit.value.reason,
        })
        ok = res?.code === 200 ? 1 : 0
        if (res?.code !== 200) {
          ElMessage.error(res?.msg || '审核失败')
          return
        }
      } else {
        const results = await Promise.all(
          audit.value.ids.map((id) =>
            auditNovel({
              id,
              audit_status: audit.value.status,
              reason: audit.value.reason,
            }).catch((e: any) => e?.response?.data || { code: 500 })
          )
        )
        ok = results.filter((r: any) => r?.code === 200).length
        if (ok === 0) {
          ElMessage.error('审核失败')
          return
        }
      }
      ElMessage.success(`已审核 ${ok}/${audit.value.ids.length}`)
      showAudit.value = false
      onSuccess?.()
    } catch (e: any) {
      const resp = e?.response?.data
      ElMessage.error(resp?.message || resp?.msg || '审核失败')
    }
  }

  function closeAudit() {
    showAudit.value = false
  }

  return {
    showAudit,
    audit,
    openAudit,
    submitAudit,
    closeAudit,
  }
}

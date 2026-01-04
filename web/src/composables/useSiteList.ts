import { ref, computed } from 'vue'
import { fetchSiteList, deleteSite, toggleSite, initSite, type Site } from '../api/sites'
import { ElMessage, ElMessageBox } from 'element-plus'

export function useSiteList() {
  const loading = ref(false)
  const rows = ref<Site[]>([])
  const total = ref(0)
  const page = ref(1)
  const limit = ref(10)
  const kw = ref('')

  const filtered = computed(() => {
    if (!kw.value) return rows.value
    const k = kw.value.toLowerCase()
    return rows.value.filter(
      (x) =>
        (x.name || '').toLowerCase().includes(k) ||
        (x.code || '').toLowerCase().includes(k)
    )
  })

  async function load() {
    loading.value = true
    try {
      const data = await fetchSiteList({ page: page.value, limit: limit.value })
      rows.value = data?.list || []
      total.value = data?.count || 0
    } finally {
      loading.value = false
    }
  }

  function onPage(p: number) {
    page.value = p
    load()
  }

  function onSize(s: number) {
    limit.value = s
    page.value = 1
    load()
  }

  async function handleToggle(row: Site) {
    try {
      const res = await toggleSite(row.id, row.is_active === 1 ? 0 : 1)
      if (res?.code === 200) {
        ElMessage.success('已更新')
        load()
      } else {
        ElMessage.error(res?.msg || '操作失败')
      }
    } catch (e) {
      console.error(e)
      ElMessage.error('操作失败')
    }
  }

  async function handleDelete(row: Site) {
    try {
      await ElMessageBox.confirm('确认删除该站点？', '提示', { type: 'warning' })
      const res = await deleteSite(row.id)
      if (res?.code === 200) {
        ElMessage.success('已删除')
        load()
      } else {
        ElMessage.error(res?.msg || '删除失败')
      }
    } catch (e) {
      console.error(e)
      // user cancelled or error
    }
  }

  async function handleInit(row: Site) {
    try {
      const res = await initSite(row.id)
      if (res?.code === 200) {
        ElMessage.success(res?.msg || '初始化成功')
      } else {
        ElMessage.error(res?.msg || '初始化失败')
      }
    } catch (e: unknown) {
      const err = e as { response?: { data?: { message?: string; msg?: string } } }
      const resp = err?.response?.data
      ElMessage.error(resp?.message || resp?.msg || '初始化失败')
    }
  }

  return {
    loading,
    rows,
    total,
    page,
    limit,
    kw,
    filtered,
    load,
    onPage,
    onSize,
    handleToggle,
    handleDelete,
    handleInit,
  }
}

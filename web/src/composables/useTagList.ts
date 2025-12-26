import { ref, computed } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { fetchTagList, deleteTag, type Tag } from '../api/tags'

export type { Tag }

export function useTagList() {
  const loading = ref(false)
  const rows = ref<Tag[]>([])
  const total = ref(0)
  const page = ref(1)
  const limit = ref(10)
  const keyword = ref('')

  const filtered = computed(() => {
    if (!keyword.value) return rows.value
    const k = keyword.value.toLowerCase()
    return rows.value.filter(
      (x) =>
        (x.name || '').toLowerCase().includes(k) ||
        (x.slug || x.code || '').toLowerCase().includes(k)
    )
  })

  async function load() {
    loading.value = true
    try {
      const data = await fetchTagList({
        page: page.value,
        limit: limit.value,
        kw: keyword.value,
      })
      rows.value = data.list || []
      total.value = Number(data.count || 0)
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

  async function onDelete(row: Tag) {
    try {
      await ElMessageBox.confirm('确定删除该标签？', '提示', { type: 'warning' })
      const res = await deleteTag(row.id)
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

  return {
    loading,
    rows,
    total,
    page,
    limit,
    keyword,
    filtered,
    load,
    onPageChange,
    onSizeChange,
    onDelete,
  }
}

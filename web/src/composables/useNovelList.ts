import { ref, computed } from 'vue'
import { fetchNovelList, deleteNovel, type Novel, type NovelListParams } from '../api/novels'
import { fetchCategoryOptions } from '../api/categories'
import { ElMessage, ElMessageBox } from 'element-plus'

export interface NovelFilters {
  category_id: string
  status: string
  is_vip: string
  is_r18: string
}

export function useNovelList() {
  const loading = ref(true)
  const rows = ref<Novel[]>([])
  const total = ref(0)
  const page = ref(1)
  const limit = ref(10)
  const kw = ref('')
  const categories = ref<Array<{ id: number | string; name: string }>>([])
  const selected = ref<Novel[]>([])

  // 筛选条件
  const filters = ref<NovelFilters>({
    category_id: '',
    status: '',
    is_vip: '',
    is_r18: '',
  })

  const filtered = computed(() => {
    if (!kw.value) return rows.value
    const k = kw.value.toLowerCase()
    return rows.value.filter(
      (x) =>
        (x.title || '').toLowerCase().includes(k) ||
        (x.author || '').toLowerCase().includes(k)
    )
  })

  async function load() {
    loading.value = true
    try {
      const params: NovelListParams = {
        page: page.value,
        limit: limit.value,
      }
      if (kw.value) params.kw = kw.value
      if (filters.value.category_id) params.category_id = filters.value.category_id
      if (filters.value.status !== '') params.status = filters.value.status
      if (filters.value.is_vip !== '') params.is_vip = filters.value.is_vip
      if (filters.value.is_r18 !== '') params.is_r18 = filters.value.is_r18
      const data = await fetchNovelList(params)
      rows.value = data.list || []
      total.value = Number(data.count || 0)
      try {
        const cats = await fetchCategoryOptions(1)
        categories.value = cats || []
      } catch (e) {
        console.error(e)
        // ignore category fetch error
      }
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

  function onFilterChange() {
    page.value = 1
    load()
  }

  function onSelect(selectedRows: Novel[]) {
    selected.value = selectedRows || []
  }

  async function handleDelete(row: Novel) {
    try {
      await ElMessageBox.confirm('确定删除该小说？', '提示', { type: 'warning' })
      const res = await deleteNovel(row.id)
      if (res?.code === 200) {
        ElMessage.success('已删除')
        load()
      } else {
        ElMessage.error(res?.msg || '删除失败')
      }
    } catch (e) {
      console.error(e)
      // user cancelled
    }
  }

  return {
    loading,
    rows,
    total,
    page,
    limit,
    kw,
    categories,
    selected,
    filtered,
    filters,
    load,
    onPage,
    onSize,
    onSelect,
    onFilterChange,
    handleDelete,
  }
}

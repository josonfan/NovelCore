import { ref, computed } from 'vue'
import {
  fetchCategoryList,
  deleteCategory,
  type Category,
} from '../api/categories'
import { ElMessage, ElMessageBox } from 'element-plus'

export interface CategoryListParams {
  page?: number
  limit?: number
  kw?: string
}

export function useCategoryList() {
  const loading = ref(true)
  const rows = ref<Category[]>([])
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
      const params: CategoryListParams = {
        page: page.value,
        limit: limit.value,
      }
      if (kw.value) params.kw = kw.value
      const data = await fetchCategoryList(params)
      rows.value = data.list || []
      total.value = Number(data.count || 0)
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

  async function handleDelete(row: Category) {
    try {
      await ElMessageBox.confirm('确定删除该分类？', '提示', { type: 'warning' })
      const res = await deleteCategory(row.id)
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
    handleDelete,
  }
}

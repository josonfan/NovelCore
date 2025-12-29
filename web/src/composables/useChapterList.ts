import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import {
  fetchChapterList,
  deleteChapter,
  type Chapter,
} from '../api/chapters'
import { fetchNovelDetail } from '../api/novels'
import { ElMessage, ElMessageBox } from 'element-plus'

export interface ChapterListParams {
  page?: number
  limit?: number
  novel_id?: number | string
}

export function useChapterList() {
  const route = useRoute()
  const router = useRouter()

  const loading = ref(true)
  const rows = ref<Chapter[]>([])
  const total = ref(0)
  const page = ref(1)
  const limit = ref(10)
  const kw = ref('')
  const novelTitle = ref('')

  const novelId = computed(() => String(route.params.id || route.query.novel_id || ''))

  const filtered = computed(() => {
    if (!kw.value) return rows.value
    const k = kw.value.toLowerCase()
    return rows.value.filter(
      (x) =>
        (x.title || '').toLowerCase().includes(k) ||
        String(x.novel_id || '').includes(k)
    )
  })

  async function load() {
    loading.value = true
    try {
      const params: ChapterListParams = {
        page: page.value,
        limit: limit.value,
      }
      if (novelId.value) params.novel_id = novelId.value
      const data = await fetchChapterList(params)
      rows.value = data.list || []
      total.value = Number(data.count || 0)
    } finally {
      loading.value = false
    }
  }

  async function loadNovelTitle() {
    if (!novelId.value) return
    try {
      const d = await fetchNovelDetail(Number(novelId.value))
      novelTitle.value = d?.title || ''
    } catch {
      // 忽略错误
    }
  }

  function checkNovelId() {
    if (!novelId.value) {
      router.push({ name: 'content-novels' })
      return false
    }
    return true
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

  async function handleDelete(row: Chapter) {
    try {
      await ElMessageBox.confirm('确定删除该章节？', '提示', { type: 'warning' })
      const res = await deleteChapter(row.id)
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
    novelId,
    novelTitle,
    filtered,
    load,
    loadNovelTitle,
    checkNovelId,
    onPage,
    onSize,
    handleDelete,
  }
}





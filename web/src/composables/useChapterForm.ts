import { ref } from 'vue'
import {
  createChapter,
  updateChapter,
  fetchChapterDetail,
  fetchChapterContent,
  type Chapter,
} from '../api/chapters'
import { ElMessage } from 'element-plus'

export interface ChapterFormData {
  id?: number | string
  novel_id: string
  title: string
  content_short: string
  seo_title: string
  seo_keywords: string
  seo_description: string
  is_free: string
  is_vip: string
  price: string
  word_count: string
  sort_order: string
  content: string
}

export function useChapterForm(reload: () => void, novelId: () => string) {
  const showForm = ref(false)
  const formMode = ref<'add' | 'edit'>('add')
  const activeTab = ref('basic')
  const form = ref<ChapterFormData>({
    novel_id: '',
    title: '',
    content_short: '',
    seo_title: '',
    seo_keywords: '',
    seo_description: '',
    is_free: '1',
    is_vip: '0',
    price: '0',
    word_count: '0',
    sort_order: '1',
    content: '',
  })

  function openAdd() {
    formMode.value = 'add'
    form.value = {
      novel_id: novelId() || '',
      title: '',
      content_short: '',
      seo_title: '',
      seo_keywords: '',
      seo_description: '',
      is_free: '1',
      is_vip: '0',
      price: '0',
      word_count: '0',
      sort_order: '1',
      content: '',
    }
    activeTab.value = 'basic'
    showForm.value = true
  }

  async function openEdit(row: Chapter) {
    formMode.value = 'edit'
    try {
      const d = await fetchChapterDetail(row.id)
      form.value = {
        id: row.id,
        novel_id: String(d?.novel_id ?? row.novel_id ?? ''),
        title: d?.title ?? row.title ?? '',
        content_short: d?.content_short ?? '',
        seo_title: d?.seo_title ?? '',
        seo_keywords: d?.seo_keywords ?? '',
        seo_description: d?.seo_description ?? '',
        is_free: d?.is_free ?? '1',
        is_vip: d?.is_vip ?? '0',
        price: d?.price ?? '0',
        word_count: d?.word_count ?? '0',
        sort_order: d?.sort_order ?? '1',
        content: '',
      }
    } catch {
      form.value = {
        id: row.id,
        novel_id: String(row.novel_id ?? ''),
        title: row.title ?? '',
        content_short: row.content_short ?? '',
        seo_title: row.seo_title ?? '',
        seo_keywords: row.seo_keywords ?? '',
        seo_description: row.seo_description ?? '',
        is_free: row.is_free ?? '1',
        is_vip: row.is_vip ?? '0',
        price: row.price ?? '0',
        word_count: row.word_count ?? '0',
        sort_order: row.sort_order ?? '1',
        content: '',
      }
    }
    // 加载内容
    try {
      const c = await fetchChapterContent(row.id)
      form.value.content = c?.content || ''
    } catch {
      // 忽略错误
    }
    activeTab.value = 'basic'
    showForm.value = true
  }

  async function saveForm() {
    try {
      if (!form.value.novel_id || !form.value.title) {
        ElMessage.error('请填写小说ID与标题')
        return
      }
      const payload = {
        novel_id: form.value.novel_id,
        title: form.value.title,
        content_short: form.value.content_short,
        seo_title: form.value.seo_title,
        seo_keywords: form.value.seo_keywords,
        seo_description: form.value.seo_description,
        is_free: form.value.is_free,
        is_vip: form.value.is_vip,
        price: form.value.price,
        word_count: form.value.word_count,
        sort_order: form.value.sort_order,
        content: form.value.content,
      }
      const res =
        formMode.value === 'add'
          ? await createChapter(payload)
          : await updateChapter(form.value.id!, payload)
      if (res?.code === 200) {
        ElMessage.success('已保存')
        showForm.value = false
        reload()
      } else {
        ElMessage.error(res?.msg || '保存失败')
      }
    } catch (e: unknown) {
      const resp = (e as { response?: { data?: { message?: string; msg?: string } } })?.response?.data
      ElMessage.error(resp?.message || resp?.msg || '保存失败')
    }
  }

  return {
    showForm,
    formMode,
    activeTab,
    form,
    openAdd,
    openEdit,
    saveForm,
  }
}

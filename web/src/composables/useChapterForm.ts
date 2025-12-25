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
  index: number
  content: string
}

export function useChapterForm(reload: () => void, novelId: () => string) {
  const showForm = ref(false)
  const formMode = ref<'add' | 'edit'>('add')
  const activeTab = ref('basic')
  const form = ref<ChapterFormData>({
    novel_id: '',
    title: '',
    index: 1,
    content: '',
  })

  function openAdd() {
    formMode.value = 'add'
    form.value = {
      novel_id: novelId() || '',
      title: '',
      index: 1,
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
        index: Number(d?.index ?? row.index ?? 1),
        content: '',
      }
    } catch {
      form.value = {
        id: row.id,
        novel_id: String(row.novel_id ?? ''),
        title: row.title ?? '',
        index: Number(row.index ?? 1),
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
        index: form.value.index,
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

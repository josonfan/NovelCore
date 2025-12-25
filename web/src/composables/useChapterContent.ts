import { ref } from 'vue'
import { fetchChapterContent, updateChapter, type Chapter } from '../api/chapters'
import { ElMessage } from 'element-plus'

export interface ChapterContentForm {
  id: number | string
  title: string
  content: string
}

export function useChapterContent(reload: () => void) {
  const showContent = ref(false)
  const contentForm = ref<ChapterContentForm>({
    id: '',
    title: '',
    content: '',
  })

  async function openContent(row: Chapter) {
    try {
      const data = await fetchChapterContent(row.id)
      contentForm.value = {
        id: row.id,
        title: data?.title || row.title || '',
        content: data?.content || '',
      }
      showContent.value = true
    } catch (e: unknown) {
      const resp = (e as { response?: { data?: { message?: string; msg?: string } } })?.response?.data
      ElMessage.error(resp?.message || resp?.msg || '获取内容失败')
    }
  }

  async function saveContent() {
    try {
      if (!contentForm.value.id) return
      const res = await updateChapter(contentForm.value.id, {
        title: contentForm.value.title,
        content: contentForm.value.content,
      })
      if (res?.code === 200) {
        ElMessage.success('内容已保存')
        showContent.value = false
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
    showContent,
    contentForm,
    openContent,
    saveContent,
  }
}

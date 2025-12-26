import { ref } from 'vue'
import { fetchChapterContent, updateChapter, type Chapter } from '../api/chapters'
import { ElMessage } from 'element-plus'

export interface ChapterContentForm {
  id: number | string
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

export function useChapterContent(reload: () => void) {
  const showContent = ref(false)
  const contentForm = ref<ChapterContentForm>({
    id: '',
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

  async function openContent(row: Chapter) {
    try {
      const data = await fetchChapterContent(row.id)
      contentForm.value = {
        id: row.id,
        novel_id: String(row.novel_id ?? ''),
        title: data?.title || row.title || '',
        content_short: row.content_short ?? '',
        seo_title: row.seo_title ?? '',
        seo_keywords: row.seo_keywords ?? '',
        seo_description: row.seo_description ?? '',
        is_free: row.is_free ?? '1',
        is_vip: row.is_vip ?? '0',
        price: row.price ?? '0',
        word_count: row.word_count ?? '0',
        sort_order: row.sort_order ?? '1',
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
        novel_id: contentForm.value.novel_id,
        title: contentForm.value.title,
        content_short: contentForm.value.content_short,
        seo_title: contentForm.value.seo_title,
        seo_keywords: contentForm.value.seo_keywords,
        seo_description: contentForm.value.seo_description,
        is_free: contentForm.value.is_free,
        is_vip: contentForm.value.is_vip,
        price: contentForm.value.price,
        word_count: contentForm.value.word_count,
        sort_order: contentForm.value.sort_order,
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

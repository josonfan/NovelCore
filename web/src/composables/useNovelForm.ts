import { ref } from 'vue'
import { createNovel, updateNovel, fetchNovelDetail, type Novel } from '../api/novels'
import { ElMessage } from 'element-plus'

export interface NovelFormData {
  id?: number | string
  title: string
  author_id: number | string
  author_name: string
  cover: string
  intro: string
  category_id: number | string
  status: number
  is_r18: number
  is_vip: number
  audit_status: number
  seo_title: string
  seo_keywords: string
  seo_description: string
}

const defaultForm = (): NovelFormData => ({
  title: '',
  author_id: '',
  author_name: '',
  cover: '',
  intro: '',
  category_id: '',
  status: 0,
  is_r18: 0,
  is_vip: 0,
  audit_status: 0,
  seo_title: '',
  seo_keywords: '',
  seo_description: '',
})

export function useNovelForm(onSuccess?: () => void) {
  const showForm = ref(false)
  const formMode = ref<'add' | 'edit'>('add')
  const form = ref<NovelFormData>(defaultForm())
  const activeTab = ref('basic')

  function openAdd() {
    formMode.value = 'add'
    form.value = defaultForm()
    activeTab.value = 'basic'
    showForm.value = true
  }

  async function openEdit(row: Novel) {
    formMode.value = 'edit'
    try {
      const d = await fetchNovelDetail(row.id)
      form.value = {
        id: d.id,
        title: d.title,
        author_id: d.author_id ?? row.author_id ?? '',
        author_name: d.author_name ?? row.author_name ?? '',
        cover: d.cover || '',
        intro: d.intro ?? '',
        category_id: d.category_id ?? row.category_id ?? '',
        status: Number(d.status ?? row.status ?? 0),
        is_r18: Number(d.is_r18 ?? row.is_r18 ?? 0),
        is_vip: Number(d.is_vip ?? row.is_vip ?? 0),
        audit_status: Number(d.audit_status ?? row.audit_status ?? 0),
        seo_title: d.seo_title || '',
        seo_keywords: d.seo_keywords || '',
        seo_description: d.seo_description || '',
      }
    } catch (e) {
      console.error(e)
      form.value = {
        id: row.id,
        title: row.title,
        author_id: row.author_id ?? '',
        author_name: row.author_name ?? '',
        cover: row.cover || '',
        intro: row.intro ?? '',
        category_id: row.category_id ?? '',
        status: Number(row.status ?? 0),
        is_r18: Number(row.is_r18 ?? 0),
        is_vip: Number(row.is_vip ?? 0),
        audit_status: Number(row.audit_status ?? 0),
        seo_title: row.seo_title || '',
        seo_keywords: row.seo_keywords || '',
        seo_description: row.seo_description || '',
      }
    }
    activeTab.value = 'basic'
    showForm.value = true
  }

  async function saveForm() {
    try {
      if (!form.value.title) {
        ElMessage.error('请填写标题')
        return
      }
      const payload = {
        title: form.value.title,
        author_id: form.value.author_id,
        author_name: form.value.author_name,
        cover: form.value.cover,
        intro: form.value.intro,
        category_id: form.value.category_id,
        status: form.value.status,
        is_r18: form.value.is_r18,
        is_vip: form.value.is_vip,
        seo_title: form.value.seo_title,
        seo_keywords: form.value.seo_keywords,
        seo_description: form.value.seo_description,
      }
      const res =
        formMode.value === 'add'
          ? await createNovel(payload)
          : await updateNovel(form.value.id!, payload)
      if (res?.code === 200) {
        ElMessage.success('已保存')
        showForm.value = false
        onSuccess?.()
      } else {
        ElMessage.error(res?.msg || '保存失败')
      }
    } catch (e: unknown) {
      const err = e as { response?: { data?: { message?: string; msg?: string } } }
      const resp = err?.response?.data
      ElMessage.error(resp?.message || resp?.msg || '保存失败')
    }
  }

  function closeForm() {
    showForm.value = false
  }

  return {
    showForm,
    formMode,
    form,
    activeTab,
    openAdd,
    openEdit,
    saveForm,
    closeForm,
  }
}

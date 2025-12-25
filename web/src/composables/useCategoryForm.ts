import { ref } from 'vue'
import {
  createCategory,
  updateCategory,
  fetchCategoryDetail,
  type Category,
} from '../api/categories'
import { ElMessage } from 'element-plus'

export interface CategoryFormData {
  id?: number | string
  name: string
  slug: string
  is_active: number
  seo_title: string
  seo_keywords: string
  seo_description: string
}

export function useCategoryForm(reload: () => void) {
  const showForm = ref(false)
  const formMode = ref<'add' | 'edit'>('add')
  const activeTab = ref('basic')
  const form = ref<CategoryFormData>({
    name: '',
    slug: '',
    is_active: 1,
    seo_title: '',
    seo_keywords: '',
    seo_description: '',
  })

  function openAdd() {
    formMode.value = 'add'
    form.value = {
      name: '',
      slug: '',
      is_active: 1,
      seo_title: '',
      seo_keywords: '',
      seo_description: '',
    }
    activeTab.value = 'basic'
    showForm.value = true
  }

  async function openEdit(row: Category) {
    formMode.value = 'edit'
    try {
      const d = await fetchCategoryDetail(row.id)
      form.value = {
        id: d.id,
        name: d.name || '',
        slug: (d as any).slug || d.code || '',
        is_active: Number((d as any).is_active ?? row.is_active ?? 1),
        seo_title: (d as any).seo_title || '',
        seo_keywords: (d as any).seo_keywords || '',
        seo_description: (d as any).seo_description || '',
      }
    } catch {
      form.value = {
        id: row.id,
        name: row.name || '',
        slug: (row as any).slug || row.code || '',
        is_active: Number(row.is_active ?? 1),
        seo_title: '',
        seo_keywords: '',
        seo_description: '',
      }
    }
    activeTab.value = 'basic'
    showForm.value = true
  }

  async function saveForm() {
    try {
      if (!form.value.name) {
        ElMessage.error('请填写名称')
        return
      }
      const payload = {
        name: form.value.name,
        slug: form.value.slug,
        code: form.value.slug,
        is_active: form.value.is_active,
        seo_title: form.value.seo_title,
        seo_keywords: form.value.seo_keywords,
        seo_description: form.value.seo_description,
      }
      const res =
        formMode.value === 'add'
          ? await createCategory(payload)
          : await updateCategory(form.value.id!, payload)
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

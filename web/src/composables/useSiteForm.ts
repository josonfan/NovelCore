import { ref } from 'vue'
import { createSite, updateSite, type Site } from '../api/sites'
import { ElMessage } from 'element-plus'

export interface SiteFormData {
  id?: number
  name: string
  code: string
  base_api_url: string
  primary_domain: string
  api_token: string
  is_active: number
  remark: string
}

const defaultForm = (): SiteFormData => ({
  name: '',
  code: '',
  base_api_url: '',
  primary_domain: '',
  api_token: '',
  is_active: 1,
  remark: '',
})

export function useSiteForm(onSuccess?: () => void) {
  const showForm = ref(false)
  const formMode = ref<'add' | 'edit'>('add')
  const form = ref<SiteFormData>(defaultForm())

  function openAdd() {
    formMode.value = 'add'
    form.value = defaultForm()
    showForm.value = true
  }

  function openEdit(row: Site) {
    formMode.value = 'edit'
    form.value = {
      id: row.id,
      name: row.name,
      code: row.code,
      base_api_url: row.base_api_url,
      primary_domain: row.primary_domain || '',
      api_token: '',
      is_active: row.is_active,
      remark: row.remark || '',
    }
    showForm.value = true
  }

  async function saveForm() {
    try {
      if (!form.value.name) {
        ElMessage.error('请填写名称')
        return
      }
      if (formMode.value === 'add') {
        const res = await createSite(form.value)
        if (res?.code === 200) {
          ElMessage.success('已创建')
          showForm.value = false
          onSuccess?.()
        } else {
          ElMessage.error(res?.msg || '创建失败')
        }
      } else {
        const payload = {
          name: form.value.name,
          base_api_url: form.value.base_api_url,
          primary_domain: form.value.primary_domain,
          is_active: form.value.is_active,
          remark: form.value.remark,
        }
        const res = await updateSite(form.value.id!, payload)
        if (res?.code === 200) {
          ElMessage.success('已更新')
          showForm.value = false
          onSuccess?.()
        } else {
          ElMessage.error(res?.msg || '更新失败')
        }
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
    openAdd,
    openEdit,
    saveForm,
    closeForm,
  }
}

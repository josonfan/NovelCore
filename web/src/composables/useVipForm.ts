import { ref } from 'vue'
import { ElMessage } from 'element-plus'
import {
  createVip,
  updateVip,
  fetchVipDetail,
  type VipItem,
} from '../api/vip'

export interface VipFormData {
  id?: number
  name: string
  status: number
  is_hot: number
  days: number
  price: string
  old_price: string
  sort: number
  descript: string
}

function getDefaultForm(): VipFormData {
  return {
    name: '',
    status: 1,
    is_hot: 2,
    days: 1,
    price: '0.00',
    old_price: '',
    sort: 0,
    descript: '',
  }
}

export function useVipForm(reload: () => void) {
  const showForm = ref(false)
  const formMode = ref<'add' | 'edit'>('add')
  const form = ref<VipFormData>(getDefaultForm())

  function openAdd() {
    formMode.value = 'add'
    form.value = getDefaultForm()
    showForm.value = true
  }

  async function openEdit(row: VipItem) {
    formMode.value = 'edit'
    try {
      const d = await fetchVipDetail(row.id)
      if (d) {
        form.value = {
          id: d.id,
          name: d.name ?? '',
          status: Number(d.status ?? 1),
          is_hot: Number(d.is_hot ?? 2),
          days: Number(d.days ?? 1),
          price: String(d.price ?? '0.00'),
          old_price: String(d.old_price ?? ''),
          sort: Number(d.sort ?? 0),
          descript: d.descript ?? '',
        }
      } else {
        throw new Error('No data')
      }
    } catch {
      form.value = {
        id: row.id,
        name: row.name ?? '',
        status: Number(row.status ?? 1),
        is_hot: Number(row.is_hot ?? 2),
        days: Number(row.days ?? 1),
        price: String(row.price ?? '0.00'),
        old_price: String(row.old_price ?? ''),
        sort: Number(row.sort ?? 0),
        descript: row.descript ?? '',
      }
    }
    showForm.value = true
  }

  async function saveForm() {
    try {
      if (!form.value.name) {
        ElMessage.error('请填写套餐名称')
        return
      }
      const { id, ...payload } = form.value
      if (formMode.value === 'add') {
        const res = await createVip(payload)
        if (res?.code === 200) {
          ElMessage.success('已创建')
          showForm.value = false
          reload()
        } else {
          ElMessage.error(res?.msg || '创建失败')
        }
      } else {
        const res = await updateVip(id!, payload)
        if (res?.code === 200) {
          ElMessage.success('已更新')
          showForm.value = false
          reload()
        } else {
          ElMessage.error(res?.msg || '更新失败')
        }
      }
    } catch (e: unknown) {
      const resp = (e as { response?: { data?: { message?: string; msg?: string } } })?.response
        ?.data
      ElMessage.error(resp?.message || resp?.msg || '保存失败')
    }
  }

  return {
    showForm,
    formMode,
    form,
    openAdd,
    openEdit,
    saveForm,
  }
}

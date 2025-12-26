import { ref } from 'vue'
import { ElMessage } from 'element-plus'
import {
  createPaymentChannel,
  updatePaymentChannel,
  fetchPaymentChannelDetail,
  type PaymentChannel,
} from '../api/paymentChannels'

export interface PaymentChannelFormData {
  id?: number
  name: string
  status: number
  is_usdt: number
  is_default: number
  is_web: number
  not_pc: number
  pay_url: string
  sup_order_url: string
  icon_iden: string
  sort: number
  limit_price: string
  cycle_price: string
  pay_rules: string
  remarks: string
  pay_id: string
  skey: string
  md5_key: string
  pay_bankcode: string
}

function getDefaultForm(): PaymentChannelFormData {
  return {
    name: '',
    status: 1,
    is_usdt: 2,
    is_default: 2,
    is_web: 2,
    not_pc: 2,
    pay_url: '',
    sup_order_url: '',
    icon_iden: '',
    sort: 0,
    limit_price: '0.00',
    cycle_price: '0.00',
    pay_rules: '',
    remarks: '',
    pay_id: '',
    skey: '',
    md5_key: '',
    pay_bankcode: '',
  }
}

export function usePaymentChannelForm(reload: () => void) {
  const showForm = ref(false)
  const formMode = ref<'add' | 'edit'>('add')
  const form = ref<PaymentChannelFormData>(getDefaultForm())

  function openAdd() {
    formMode.value = 'add'
    form.value = getDefaultForm()
    showForm.value = true
  }

  async function openEdit(row: PaymentChannel) {
    formMode.value = 'edit'
    try {
      const d = await fetchPaymentChannelDetail(row.id)
      if (d) {
        form.value = {
          id: d.id,
          name: d.name ?? '',
          status: Number(d.status ?? 1),
          is_usdt: Number(d.is_usdt ?? 2),
          is_default: Number(d.is_default ?? 2),
          is_web: Number(d.is_web ?? 2),
          not_pc: Number(d.not_pc ?? 2),
          pay_url: d.pay_url ?? '',
          sup_order_url: d.sup_order_url ?? '',
          icon_iden: d.icon_iden ?? '',
          sort: Number(d.sort ?? 0),
          limit_price: String(d.limit_price ?? '0.00'),
          cycle_price: String(d.cycle_price ?? '0.00'),
          pay_rules: d.pay_rules ?? '',
          remarks: d.remarks ?? '',
          pay_id: d.pay_id ?? '',
          skey: d.skey ?? '',
          md5_key: d.md5_key ?? '',
          pay_bankcode: d.pay_bankcode ?? '',
        }
      } else {
        throw new Error('No data')
      }
    } catch {
      form.value = {
        id: row.id,
        name: row.name ?? '',
        status: Number(row.status ?? 1),
        is_usdt: Number(row.is_usdt ?? 2),
        is_default: Number(row.is_default ?? 2),
        is_web: Number(row.is_web ?? 2),
        not_pc: Number(row.not_pc ?? 2),
        pay_url: row.pay_url ?? '',
        sup_order_url: row.sup_order_url ?? '',
        icon_iden: row.icon_iden ?? '',
        sort: Number(row.sort ?? 0),
        limit_price: String(row.limit_price ?? '0.00'),
        cycle_price: String(row.cycle_price ?? '0.00'),
        pay_rules: row.pay_rules ?? '',
        remarks: row.remarks ?? '',
        pay_id: row.pay_id ?? '',
        skey: row.skey ?? '',
        md5_key: row.md5_key ?? '',
        pay_bankcode: row.pay_bankcode ?? '',
      }
    }
    showForm.value = true
  }

  async function saveForm() {
    try {
      if (!form.value.name) {
        ElMessage.error('请填写渠道名称')
        return
      }
      const { id, ...payload } = form.value
      if (formMode.value === 'add') {
        const res = await createPaymentChannel(payload)
        if (res?.code === 200) {
          ElMessage.success('已创建')
          showForm.value = false
          reload()
        } else {
          ElMessage.error(res?.msg || '创建失败')
        }
      } else {
        const res = await updatePaymentChannel(id!, payload)
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

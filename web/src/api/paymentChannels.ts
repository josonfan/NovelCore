import { http } from './http'

export type PaymentChannel = {
  id: number
  name: string
  status: number
  is_usdt: number
  order_quantity?: number
  payment_quantity?: number
  place_order?: string
  payment?: string
  limit_price?: string
  cycle_price?: string
  is_default: number
  is_web: number
  not_pc: number
  icon_iden?: string
  pay_url?: string
  sup_order_url?: string
  pay_rules?: string
  remarks?: string
  sort?: number
  pay_id?: string
  skey?: string
  md5_key?: string
  pay_bankcode?: string
  created_at?: string
  updated_at?: string
}

export async function fetchPaymentChannelList(params: { page: number; limit: number }): Promise<{ list: PaymentChannel[]; count: number }> {
  const res = await http.post('PaymentChannel/index', params)
  return res.data?.data || { list: [], count: 0 }
}

export async function fetchPaymentChannelDetail(id: number | string): Promise<PaymentChannel | null> {
  const res = await http.post('PaymentChannel/detail', { id })
  return (res.data?.data as PaymentChannel) || null
}

export type PaymentChannelCreatePayload = {
  name: string
  status: number
  is_usdt: number
  is_default: number
  is_web: number
  not_pc: number
  pay_url?: string
  sup_order_url?: string
  icon_iden?: string
  sort?: number
  limit_price?: string
  cycle_price?: string
  pay_rules?: string
  remarks?: string
  pay_id?: string
  skey?: string
  md5_key?: string
  pay_bankcode?: string
}

export type PaymentChannelUpdatePayload = Partial<PaymentChannelCreatePayload>

export async function createPaymentChannel(payload: PaymentChannelCreatePayload) {
  const res = await http.post('PaymentChannel/create', payload)
  return res.data
}

export async function updatePaymentChannel(id: number | string, payload: PaymentChannelUpdatePayload) {
  const body = { id, ...payload }
  const res = await http.post('PaymentChannel/update', body)
  return res.data
}

export async function togglePaymentChannel(id: number | string, status: number) {
  const res = await http.post('PaymentChannel/toggle', { id, status })
  return res.data
}

export async function deletePaymentChannel(id: number | string) {
  const res = await http.post('PaymentChannel/delete', { id })
  return res.data
}

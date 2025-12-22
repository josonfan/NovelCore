import { http } from './http'

export type VipItem = {
  id: number
  name: string
  descript?: string
  days: number
  price: string
  old_price?: string
  sort?: number
  sold_num?: number
  status: number
  is_hot?: number
  sold_total?: string
  return_total?: string
  return_num?: number
  created_at?: string
  updated_at?: string
}

export async function fetchVipList(params: { page: number; limit: number }): Promise<{ list: VipItem[]; count: number }> {
  const res = await http.post('Vip/index', params)
  return res.data?.data || { list: [], count: 0 }
}

export async function fetchVipDetail(id: number | string): Promise<VipItem | null> {
  const res = await http.post('Vip/detail', { id })
  return (res.data?.data as VipItem) || null
}

export type VipCreatePayload = {
  name: string
  descript?: string
  days: number
  price: string
  old_price?: string
  sort?: number
  status: number
  is_hot?: number
}

export type VipUpdatePayload = Partial<VipCreatePayload>

export async function createVip(payload: VipCreatePayload) {
  const res = await http.post('Vip/create', payload)
  return res.data
}

export async function updateVip(id: number | string, payload: VipUpdatePayload) {
  const res = await http.post('Vip/update', { id, ...payload })
  return res.data
}

export async function toggleVip(id: number | string, status: number) {
  const res = await http.post('Vip/toggle', { id, status })
  return res.data
}

export async function deleteVip(id: number | string) {
  const res = await http.post('Vip/delete', { id })
  return res.data
}

import { http } from './http'

export type OrderStatus = 'pending' | 'processing' | 'paid' | 'cancelled' | 'refunded'

export type OrderGoodInfo = {
  id: number
  days?: number
  name?: string
  amount?: string
  descript?: string
  old_price?: string
}

export type OrderExtraInfo = {
  days?: number
  client?: string
  created_by?: string
}

export type SiteOrderItem = {
  id: number | string
  site_id: number
  order_id: string
  order_no: string
  user_id: string
  order_type: string
  good_id: string
  good_info?: OrderGoodInfo
  amount: string
  pay_channel_id?: string | null
  status: OrderStatus
  extra?: OrderExtraInfo
  created_at?: string
  updated_at?: string
  paid_at?: string | null
  last_synced_at?: string
}

export async function fetchSiteOrdersList(params: { page: number; limit: number; site_id?: number | string; user_id?: number | string; order_no?: string; status?: OrderStatus }): Promise<{ list: SiteOrderItem[]; count: number }> {
  const res = await http.post('SiteOrders/index', params)
  return res.data?.data || { list: [], count: 0 }
}

export async function fetchSiteOrderDetail(id: number | string): Promise<SiteOrderItem | null> {
  const res = await http.post('SiteOrders/detail', { id })
  return (res.data?.data as SiteOrderItem) || null
}

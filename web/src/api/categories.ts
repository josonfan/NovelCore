import { http } from './http'

export type Category = {
  id: number | string
  name: string
  code?: string
  parent_id?: number | string
  is_active?: number
}

export async function fetchCategoryList(params?: { page?: number; limit?: number }) {
  const res = await http.get('Categories/index', { params })
  return res.data?.data || { list: [], count: 0 }
}


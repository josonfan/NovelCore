import { http } from './http'

export type Tag = {
  id: number | string
  name: string
  code?: string
  is_active?: number
}

export async function fetchTagList(params?: { page?: number; limit?: number }) {
  const res = await http.get('Tags/index', { params })
  return res.data?.data || { list: [], count: 0 }
}


import { http } from './http'

export type Novel = {
  id: number | string
  title: string
  author?: string
  status?: number
}

export async function fetchNovelList(params?: { page?: number; limit?: number }) {
  const res = await http.get('Novels/index', { params })
  return res.data?.data || { list: [], count: 0 }
}


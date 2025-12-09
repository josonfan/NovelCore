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

export async function fetchNovelDetail(id: number | string) {
  const res = await http.post('Novels/detail', { id })
  return res.data?.data as Novel
}

export async function createNovel(payload: Partial<Novel>) {
  const res = await http.post('Novels/create', payload)
  return res.data
}

export async function updateNovel(id: number | string, payload: Partial<Novel>) {
  const res = await http.post('Novels/update', { id, ...payload })
  return res.data
}

export async function deleteNovel(id: number | string) {
  const res = await http.post('Novels/delete', { id })
  return res.data
}

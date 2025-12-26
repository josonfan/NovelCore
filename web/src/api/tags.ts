import { http } from './http'

export type Tag = {
  id: number | string
  name: string
  slug?: string
  code?: string
  is_active?: number
  seo_title?: string
  seo_keywords?: string
  seo_description?: string
  created_at?: string
  updated_at?: string
}

export async function fetchTagList(params?: { page?: number; limit?: number; kw?: string }) {
  const res = await http.get('Tags/index', { params })
  return res.data?.data || { list: [], count: 0 }
}

export async function fetchTagDetail(id: number | string) {
  const res = await http.post('Tags/detail', { id })
  return res.data?.data as Tag
}

export async function createTag(payload: Partial<Tag>) {
  const res = await http.post('Tags/create', payload)
  return res.data
}

export async function updateTag(id: number | string, payload: Partial<Tag>) {
  const res = await http.post('Tags/update', { id, ...payload })
  return res.data
}

export async function deleteTag(id: number | string) {
  const res = await http.post('Tags/delete', { id })
  return res.data
}

export async function fetchTagOptions(type: string) {
  const res = await http.post('Tags/options', { type })
  return res.data?.data as Array<{ type: string; label: string; children: Array<{ id: number | string; name: string }> }>
}

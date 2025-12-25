import { http } from './http'

export type Novel = {
  id: number | string
  title: string
  author_id?: number | string
  author_name?: string
  cover?: string
  intro?: string
  status?: number
  audit_status?: number
  category_id?: number | string
  is_r18?: number
  is_vip?: number
  tags?: Array<{ id: number | string; name: string; type?: string }>
  seo_title?: string
  seo_keywords?: string
  seo_description?: string
}

export interface NovelListParams {
  page?: number | string
  limit?: number | string
  kw?: string
  category_id?: number | string
  status?: number | string
  is_vip?: number | string
  is_r18?: number | string
}

export async function fetchNovelList(params?: NovelListParams) {
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

export async function reviewNovels(payload: { novel_ids: Array<number | string>; audit_status: number; reason?: string }) {
  const ids = (payload.novel_ids || []).map(String).join(',')
  const body: any = { novel_ids: ids, audit_status: payload.audit_status }
  if (payload.reason) body.audit_remark = payload.reason
  const res = await http.post('Novels/review', body)
  return res.data
}

export async function auditNovel(payload: { id: number | string; audit_status: number; reason?: string }) {
  const body: any = { id: payload.id, audit_status: payload.audit_status }
  if (payload.reason) body.audit_remark = payload.reason
  const res = await http.post('Novels/audit', body)
  return res.data
}

export async function bindNovelTags(novel_id: number | string, tag_ids: Array<number | string>) {
  const body = { novel_id, tag_ids: (tag_ids || []).map(String).join(',') }
  const res = await http.post('Novels/bindTags', body)
  return res.data
}

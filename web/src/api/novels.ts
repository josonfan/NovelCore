import { http } from './http'

export type Novel = {
  id: number | string
  title: string
  author?: string
  status?: number
  audit_status?: number
  category_id?: number | string
  tags?: Array<{ id: number | string; name: string; type?: string }>
}

export async function fetchNovelList(params?: { page?: number; limit?: number; kw?: string }) {
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

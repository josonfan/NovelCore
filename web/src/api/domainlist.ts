import { http } from './http'

export type DomainItem = {
  id: number
  site_id: number
  domain: string
  type: 'api'|'image'|'share'
  priority: number
  is_active: number
  remark?: string
  created_at?: string
  updated_at?: string
}

export async function fetchDomainList(params: { page: number; limit: number }) {
  const res = await http.post('DomainList/index', params)
  return res.data?.data || { list: [], count: 0 }
}

export async function fetchDomainDetail(id: number) {
  const res = await http.post('DomainList/detail', { id })
  return res.data?.data as DomainItem
}

export async function createDomain(payload: { site_id: number; domain: string; type: string; priority?: number; is_active: number; remark?: string }) {
  const res = await http.post('DomainList/create', payload)
  return res.data
}

export async function updateDomain(id: number, payload: { site_id?: number; domain?: string; type?: string; priority?: number; is_active?: number; remark?: string }) {
  const res = await http.post('DomainList/update', { id, ...payload })
  return res.data
}

export async function deleteDomain(id: number) {
  const res = await http.post('DomainList/delete', { id })
  return res.data
}


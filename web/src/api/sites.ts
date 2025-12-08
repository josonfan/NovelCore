import { http } from './http'

export type Site = {
  id: number
  name: string
  code: string
  base_api_url: string
  primary_domain?: string
  is_active: number
  remark?: string
  created_at?: string
  updated_at?: string
}

export async function fetchSiteList(params: { page: number; limit: number }) {
  const res = await http.post('Sites/index', params)
  return res.data?.data || { list: [], count: 0 }
}

export async function fetchSiteDetail(id: number) {
  const res = await http.post('Sites/detail', { id })
  return res.data?.data as Site
}

export async function createSite(payload: { name: string; code: string; base_api_url: string; primary_domain?: string; api_token: string; is_active: number; remark?: string }) {
  const res = await http.post('Sites/create', payload)
  return res.data
}

export async function updateSite(id: number, payload: { name?: string; base_api_url?: string; primary_domain?: string; is_active?: number; remark?: string }) {
  const res = await http.post('Sites/update', { id, ...payload })
  return res.data
}

export async function toggleSite(id: number, is_active: number) {
  const res = await http.post('Sites/toggle', { id, is_active })
  return res.data
}

export async function deleteSite(id: number) {
  const res = await http.post('Sites/delete', { id })
  return res.data
}


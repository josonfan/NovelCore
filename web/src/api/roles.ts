import { http } from './http'

export type Role = {
  id: number
  name: string
  description?: string
  created_at?: string
}

export async function fetchRoleList(params: { page: number; limit: number; kw?: string }) {
  const res = await http.post('Roles/index', params)
  return res.data?.data || { list: [], count: 0 }
}

export async function fetchRoleDetail(id: number) {
  const res = await http.post('Roles/detail', { id })
  return res.data?.data as Role
}

export async function createRole(payload: { name: string; description?: string }) {
  const res = await http.post('Roles/create', payload)
  return res.data
}

export async function assignRolePermissions(role_id: number, perm_ids: Array<number | string>) {
  const joined = (perm_ids || []).map(String).join(',')
  const res = await http.post('Roles/assignPermissions', { role_id, perm_ids: joined })
  return res.data
}

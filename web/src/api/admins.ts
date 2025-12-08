import { http } from './http'

export type Admin = {
  id: number
  username: string
  nickname?: string
  status: number
  last_login_at?: string
  created_at?: string
}

export async function fetchAdminList(params: { page: number; limit: number }) {
  const res = await http.post('Admins/index', params)
  return res.data?.data || { list: [], count: 0 }
}

export async function fetchAdminDetail(id: number) {
  const res = await http.post('Admins/detail', { id })
  return res.data?.data as Admin
}

export async function createAdmin(payload: { username: string; password: string }) {
  const res = await http.post('Admins/add', payload)
  return res.data
}

export async function assignAdminRoles(admin_id: number, role_ids: Array<number|string>) {
  const joined = (role_ids || []).map(String).join(',')
  const res = await http.post('Admins/assignRoles', { admin_id, role_ids: joined })
  return res.data
}


import { http } from './http'

export type Permission = {
  id: number
  name: string
  resource: string
  action: string
  field?: string
  created_at?: string
}

export async function fetchPermissionList() {
  const res = await http.get('Permissions/index')
  return res.data?.data as Permission[]
}

export async function createPermission(payload: { name: string; resource: string; action: string; field?: string }) {
  const res = await http.post('Permissions/create', payload)
  return res.data
}


import { http } from './http'

export interface MenuNode {
  id: number | string
  parent_id: number | string
  name: string
  code: string
  path?: string
  route?: string
  icon?: string
  type: 'menu' | 'button'
  visible: number
  is_active: number
  sort_order: number
  children?: MenuNode[]
}

export async function fetchMenuTree() {
  const res = await http.post('Menus/index')
  return res.data?.data as MenuNode[]
}

export async function fetchMenuList(payload: { page: number; limit: number; parent_id?: number | string }) {
  const res = await http.post('Menus/list', payload)
  return res.data?.data as { list: MenuNode[]; count: number }
}

export async function fetchMenuDetail(id: number | string) {
  const res = await http.post('Menus/detail', { id })
  return res.data?.data as MenuNode
}

export async function createMenu(payload: Partial<MenuNode>) {
  const res = await http.post('Menus/create', payload)
  return res.data
}

export async function updateMenu(id: number | string, payload: Partial<MenuNode>) {
  const res = await http.post('Menus/update', { id, ...payload })
  return res.data
}

export async function deleteMenu(id: number | string) {
  const res = await http.post('Menus/delete', { id })
  return res.data
}

export async function bindMenuPermissions(menu_id: number | string, perm_ids: Array<number | string>) {
  const res = await http.post('Menus/bindPermissions', { menu_id, perm_ids: (perm_ids || []).join(',') })
  return res.data
}

export async function fetchMenuOptions() {
  const res = await http.post('Menus/options')
  return res.data?.data as Array<{ id: number | string; name: string; parent_id: number | string; children?: any[] }>
}

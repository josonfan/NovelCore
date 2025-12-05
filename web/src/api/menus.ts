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

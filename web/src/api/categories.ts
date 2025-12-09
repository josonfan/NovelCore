import { http } from './http'

export type Category = {
  id: number | string
  name: string
  code?: string
  parent_id?: number | string
  is_active?: number
}

export async function fetchCategoryList(params?: { page?: number; limit?: number }) {
  const res = await http.get('Categories/index', { params })
  return res.data?.data || { list: [], count: 0 }
}

export async function fetchCategoryDetail(id: number | string) {
  const res = await http.post('Categories/detail', { id })
  return res.data?.data as Category
}

export async function createCategory(payload: Partial<Category>) {
  const res = await http.post('Categories/create', payload)
  return res.data
}

export async function updateCategory(id: number | string, payload: Partial<Category>) {
  const res = await http.post('Categories/update', { id, ...payload })
  return res.data
}

export async function deleteCategory(id: number | string) {
  const res = await http.post('Categories/delete', { id })
  return res.data
}

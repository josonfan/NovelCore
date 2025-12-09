import { http } from './http'

export type Chapter = {
  id: number | string
  novel_id?: number | string
  title: string
  index?: number
}

export async function fetchChapterList(params?: { page?: number; limit?: number; novel_id?: number | string }) {
  const res = await http.get('Chapters/index', { params })
  return res.data?.data || { list: [], count: 0 }
}

export async function fetchChapterDetail(id: number | string) {
  const res = await http.post('Chapters/detail', { id })
  return res.data?.data as Chapter
}

export async function createChapter(payload: Partial<Chapter>) {
  const res = await http.post('Chapters/create', payload)
  return res.data
}

export async function updateChapter(id: number | string, payload: Partial<Chapter>) {
  const res = await http.post('Chapters/update', { id, ...payload })
  return res.data
}

export async function deleteChapter(id: number | string) {
  const res = await http.post('Chapters/delete', { id })
  return res.data
}

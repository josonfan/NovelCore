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


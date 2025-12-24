import { http } from './http'

export type UploadResult = { key: string; url: string }

export async function uploadFile(file: File, opts?: { onProgress?: (p: number) => void }): Promise<UploadResult> {
  const fd = new FormData()
  fd.append('file', file)
  const res = await http.post('Upload/file', fd, {
    headers: { 'Content-Type': 'multipart/form-data' },
    onUploadProgress: (e) => {
      const p = e.total ? Math.round((e.loaded / e.total) * 100) : 0
      opts?.onProgress?.(p)
    },
  })
  return res.data?.data as UploadResult
}

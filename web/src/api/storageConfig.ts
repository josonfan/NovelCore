import { http } from './http'

export type StorageConfigPayload = {
  site_id: number
  provider: string
  access_key_id: string
  secret_key: string
  bucket_name: string
  bucket_region?: string
  endpoint?: string
  base_url: string
  allowed_suffix?: string
  is_active: number | string
}

export async function saveStorageConfig(payload: StorageConfigPayload) {
  const res = await http.post('StorageConfig/save', payload)
  return res.data
}

export async function fetchStorageConfigBySite(site_id: number) {
  const res = await http.post('StorageConfig/detailBySite', { site_id })
  return res.data?.data || null
}

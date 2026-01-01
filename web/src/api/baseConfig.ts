import { http } from './http'

export type BaseConfigData = {
  privacy_policy: string
  payment_agreement: string
  '18+_content': string
}

export type BaseConfigPayload = {
  site_id: number
  config_name: 'base_config'
  config_data: BaseConfigData
}

export async function fetchBaseConfigDetail(site_id: number) {
  const res = await http.post('BaseConfig/detail', {
    site_id,
    config_name: 'base_config',
  })
  const data = res.data?.data?.config_data || {}
  const normalized: BaseConfigData = {
    privacy_policy: data.privacy_policy ?? '',
    payment_agreement: data.payment_agreement ?? '',
    '18+_content': data['18+_content'] ?? data['18_content'] ?? '',
  }
  return {
    config_name: 'base_config' as const,
    config_data: normalized,
  }
}

export async function saveBaseConfig(payload: BaseConfigPayload) {
  const res = await http.post('BaseConfig/save', payload)
  return res.data
}

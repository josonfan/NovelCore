import { http } from './http'

export async function fetchEmailConfigBySite(site_id: number) {
  const res = await http.post('EmailConfig/detailBySite', {  site_id })
  return res.data?.data || null
}

export async function fetchSearchConfigBySite(site_id: number) {
  const res = await http.post('SearchConfig/detailBySite', {  site_id })
  return res.data?.data || null
}

export async function fetchAiConfigBySite(site_id: number) {
  const res = await http.post('AiConfig/detailBySite', {  site_id })
  return res.data?.data || null
}

export async function fetchRecommendationConfigBySite(site_id: number) {
  const res = await http.post('RecommendationConfig/detailBySite', {  site_id })
  return res.data?.data || null
}

export async function fetchCustomerServiceConfigBySite(site_id: number) {
  const res = await http.post('CustomerServiceConfig/detailBySite', {  site_id })
  return res.data?.data || null
}

export async function fetchCommentReviewConfigBySite(site_id: number) {
  const res = await http.post('CommentReviewConfig/detailBySite', {  site_id })
  return res.data?.data || null
}

export async function fetchTelegramAuditConfigBySite(site_id: number) {
  const res = await http.post('TelegramAuditConfig/detailBySite', {  site_id })
  return res.data?.data || null
}

export async function saveSearchConfig(payload: any) {
  const res = await http.post('SearchConfig/save', payload)
  return res.data
}

export async function saveAiConfig(payload: any) {
  const res = await http.post('AiConfig/save', payload)
  return res.data
}

export async function saveRecommendationConfig(payload: any) {
  const res = await http.post('RecommendationConfig/save', payload)
  return res.data
}

export async function saveCustomerServiceConfig(payload: any) {
  const res = await http.post('CustomerServiceConfig/save', payload)
  return res.data
}

export async function saveCommentReviewConfig(payload: any) {
  const res = await http.post('CommentReviewConfig/save', payload)
  return res.data
}

export async function saveTelegramAuditConfig(payload: any) {
  const res = await http.post('TelegramAuditConfig/save', payload)
  return res.data
}

export async function saveEmailConfig(payload: any) {
  const res = await http.post('EmailConfig/save', payload)
  return res.data
}

import { http } from './http'

export async function fetchProfile() {
  const res = await http.get('Login/info')
  return res.data?.data
}

export async function updateProfile(payload: { nickname?: string }) {
  const res = await http.post('Login/update', payload)
  return res.data
}

export async function changePassword(payload: { password: string; new_password: string; confirm_password: string }) {
  const res = await http.post('Login/changePassword', payload)
  return res.data
}

export async function logoutApi() {
  const res = await http.post('Login/logout')
  return res.data
}

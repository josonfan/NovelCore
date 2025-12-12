import axios, { AxiosHeaders } from 'axios'
import { useAuthStore } from '../store'
import router from '../router'
import { ElMessage } from 'element-plus'

export const http = axios.create({ baseURL: import.meta.env.VITE_API_BASE || '/' })

http.interceptors.request.use((config) => {
  const auth = useAuthStore()
  if (!auth.token) auth.load()
  if (auth.token) {
    const headers = new AxiosHeaders(config.headers)
    headers.set('Authorization', `Bearer ${auth.token}`)
    headers.set('token', auth.token)
    config.headers = headers
  }
  return config
})

http.interceptors.response.use(
  (res) => res,
  (err) => {
    const status = err?.response?.status
    const body = err?.response?.data
    const msg = body?.message || body?.msg || err.message || '请求失败'
    if (status === 401) {
      const auth = useAuthStore()
      auth.clear()
      ElMessage.error(msg)
      router.push('/login')
      return Promise.reject(err)
    }
    ElMessage.error(msg)
    return Promise.reject(err)
  }
)

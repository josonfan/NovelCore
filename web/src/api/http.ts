import axios from 'axios'
import { useAuthStore } from '../store'
import router from '../router'
import { ElMessage } from 'element-plus'

export const http = axios.create({ baseURL: import.meta.env.VITE_API_BASE || '/' })

http.interceptors.request.use((config) => {
  const auth = useAuthStore()
  if (!auth.token) auth.load()
  if (auth.token) {
    config.headers = { ...(config.headers || {}), Authorization: `Bearer ${auth.token}`, token: auth.token }
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

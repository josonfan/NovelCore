import axios, { AxiosHeaders } from 'axios'
import { useAuthStore } from '../store'
import router from '../router'
import { ElMessage } from 'element-plus'

export const http = axios.create({ baseURL: import.meta.env.DEV ? '/' : (import.meta.env.VITE_API_PROXY_TARGET || '/') })

http.interceptors.request.use((config) => {
  const auth = useAuthStore()
  if (!auth.token) auth.load()
  const headers = new AxiosHeaders(config.headers)
  headers.set('X-Requested-With', 'XMLHttpRequest')
  if (auth.token) {
    headers.set('Authorization', `Bearer ${auth.token}`)
  }
  config.headers = headers
  config.withCredentials = true
  return config
})

http.interceptors.response.use(
  (res) => res,
  async (err) => {
    const status = err?.response?.status
    const body = err?.response?.data
    const cfg = err?.config || {}
    const msg = body?.message || body?.msg || err.message || '请求失败'
    const base = import.meta.env.VITE_API_BASE || '/'
    if (status === 405 && String(cfg?.method || '').toLowerCase() === 'post' && !cfg.__retryAdminAlt && base.endsWith('/admin')) {
      const alt = base.replace(/\/admin$/, '/admin.php')
      return http.request({ ...cfg, baseURL: alt, __retryAdminAlt: true })
    }
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

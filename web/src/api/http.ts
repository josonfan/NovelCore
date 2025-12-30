import axios, { AxiosHeaders } from 'axios'
import { useAuthStore } from '../store'
import router from '../router'
import { ElMessage } from 'element-plus'

export const http = axios.create({ baseURL: import.meta.env.VITE_API_PROXY_TARGET })

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
  (res) => {
    const data = res.data
    // 处理业务响应码
    if (data && typeof data.code === 'number' && data.code !== 200) {
      const msg = data.msg || data.message || '请求失败'
      // 业务码 401 表示登录过期
      if (data.code === 401) {
        const auth = useAuthStore()
        auth.clear()
        ElMessage.error(msg)
        router.push('/login')
        return Promise.reject(new Error(msg))
      }
      // 其他非 200 业务码，提示错误信息
      ElMessage.error(msg)
      return Promise.reject(new Error(msg))
    }
    return res
  },
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
    // HTTP 状态码 401
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

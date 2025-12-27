import { createRouter, createWebHistory } from 'vue-router'
import { createRoutes } from './routes'

const router = createRouter({
  history: createWebHistory(),
  routes: createRoutes(),
})

/**
 * 路由守卫：认证检查
 */
router.beforeEach((to) => {
  const token = localStorage.getItem('token') || ''
  const isLoginPage = to.path === '/login'

  // 未登录且不在登录页，跳转到登录页
  if (!token && !isLoginPage) {
    return { path: '/login' }
  }

  // 已登录且在登录页，跳转到首页
  if (token && isLoginPage) {
    return { path: '/' }
  }

  return true
})

/**
 * 路由错误处理：忽略动态导入失败
 */
router.onError((err) => {
  const message = String(err || '')
  const isChunkError =
    message.includes('Failed to fetch dynamically imported module') ||
    message.includes('ERR_ABORTED')

  if (!isChunkError) {
    console.error('[Router Error]', err)
  }
})

export default router

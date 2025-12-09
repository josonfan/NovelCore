import { createRouter, createWebHistory } from 'vue-router'
import type { RouteRecordRaw } from 'vue-router'
import Login from '../pages/Login.vue'
import Dashboard from '../pages/Dashboard.vue'
import AppLayout from '../layouts/AppLayout.vue'
import SystemSiteConfig from '../pages/SystemSiteConfig.vue'
import { ROUTES } from './routes'

const routes: RouteRecordRaw[] = [
  { path: ROUTES.login.path, name: ROUTES.login.name, component: Login, meta: { title: ROUTES.login.title } },
  {
    path: '/',
    component: AppLayout,
    children: [
      { path: '', name: ROUTES.home.name, component: Dashboard, meta: { title: ROUTES.home.title } },
      { path: ROUTES.dashboard.path.slice(1), name: ROUTES.dashboard.name, component: Dashboard, meta: { title: ROUTES.dashboard.title } },
      { path: ROUTES.system.path.slice(1), name: ROUTES.system.name, component: () => import('../pages/System.vue'), meta: { title: ROUTES.system.title } },
      { path: ROUTES['system-menu'].path.slice(1), name: ROUTES['system-menu'].name, component: () => import('../pages/SystemMenu.vue'), meta: { title: ROUTES['system-menu'].title } },
      { path: ROUTES['system-roles'].path.slice(1), name: ROUTES['system-roles'].name, component: () => import('../pages/SystemRoles.vue'), meta: { title: ROUTES['system-roles'].title } },
      { path: ROUTES['system-permissions'].path.slice(1), name: ROUTES['system-permissions'].name, component: () => import('../pages/SystemPermissions.vue'), meta: { title: ROUTES['system-permissions'].title } },
      { path: 'system/site/:id/config', name: 'system-site-config', component: SystemSiteConfig, meta: { title: '站点配置' } },
      { path: ROUTES['system-sites'].path.slice(1), name: ROUTES['system-sites'].name, component: () => import('../pages/SystemSites.vue'), meta: { title: ROUTES['system-sites'].title } },
      { path: ROUTES['system-domain'].path.slice(1), name: ROUTES['system-domain'].name, component: () => import('../pages/SystemDomainList.vue'), meta: { title: ROUTES['system-domain'].title } },
      { path: ROUTES['system-admins'].path.slice(1), name: ROUTES['system-admins'].name, component: () => import('../pages/SystemAdmins.vue'), meta: { title: ROUTES['system-admins'].title } },
      { path: 'content/categories', name: 'content-categories', component: () => import('../pages/ContentCategories.vue'), meta: { title: '分类管理' } },
      { path: 'content/tags', name: 'content-tags', component: () => import('../pages/ContentTags.vue'), meta: { title: '标签管理' } },
      { path: 'content/novels', name: 'content-novels', component: () => import('../pages/ContentNovels.vue'), meta: { title: '小说管理' } },
      { path: 'content/chapters', name: 'content-chapters', component: () => import('../pages/ContentChapters.vue'), meta: { title: '章节管理' } },
    ],
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to) => {
  const token = localStorage.getItem('token') || ''
  if (!token && to.path !== '/login') {
    return { path: '/login' }
  }
  if (to.path === '/login' && token) {
    return { path: '/' }
  }
  return true
})

router.onError((err) => {
  const s = String(err || '')
  if (s.includes('Failed to fetch dynamically imported module') || s.includes('ERR_ABORTED')) return
  console.error(err)
})

export default router

import { createRouter, createWebHistory } from 'vue-router'
import type { RouteRecordRaw } from 'vue-router'
import Login from '../pages/Login.vue'
import Dashboard from '../pages/Dashboard.vue'
import AppLayout from '../layouts/AppLayout.vue'
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

export default router

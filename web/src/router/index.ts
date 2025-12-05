import { createRouter, createWebHistory } from 'vue-router'
import type { RouteRecordRaw } from 'vue-router'
import Login from '../pages/Login.vue'
import Dashboard from '../pages/Dashboard.vue'
import AppLayout from '../layouts/AppLayout.vue'

const routes: RouteRecordRaw[] = [
  { path: '/login', name: 'login', component: Login },
  {
    path: '/',
    component: AppLayout,
    children: [
      { path: '', name: 'home', component: Dashboard },
      { path: 'dashboard', name: 'dashboard', component: Dashboard },
      { path: 'system', name: 'system', component: () => import('../pages/System.vue') },
      { path: 'system/menu', name: 'system-menu', component: () => import('../pages/SystemMenu.vue') },
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

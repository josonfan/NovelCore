import type { RouteRecordRaw } from 'vue-router'

/**
 * 路由元信息
 */
export interface RouteMeta {
  path: string
  name: string
  title: string
  component?: () => Promise<unknown>
}

/**
 * 所有路由定义
 */
export const ROUTES = {
  // 认证
  login: { path: '/login', name: 'login', title: '登录' },

  // 首页
  home: { path: '/', name: 'home', title: '首页' },
  dashboard: { path: '/dashboard', name: 'dashboard', title: '首页' },

  // 系统管理
  system: { path: '/system', name: 'system', title: '系统管理' },
  'system-menu': { path: '/system/menu', name: 'system-menu', title: '菜单管理' },
  'system-roles': { path: '/system/roles', name: 'system-roles', title: '角色管理' },
  'system-permissions': { path: '/system/permissions', name: 'system-permissions', title: '权限管理' },
  'system-sites': { path: '/system/sites', name: 'system-sites', title: '站点管理' },
  'system-site-config': { path: '/system/site/:id/config', name: 'system-site-config', title: '站点配置' },
  'system-domain': { path: '/system/domain', name: 'system-domain', title: '域名管理' },
  'system-admins': { path: '/system/admins', name: 'system-admins', title: '管理员管理' },
  'system-config': { path: '/system/config', name: 'system-config', title: '系统配置' },
  'system-payments': { path: '/system/payments', name: 'system-payments', title: '支付渠道' },
  'system-vip': { path: '/system/vip', name: 'system-vip', title: 'VIP套餐' },

  // 内容管理
  'content-categories': { path: '/content/categories', name: 'content-categories', title: '分类管理' },
  'content-tags': { path: '/content/tags', name: 'content-tags', title: '标签管理' },
  'content-novels': { path: '/content/novels', name: 'content-novels', title: '小说管理' },
  'content-chapters': { path: '/content/chapters', name: 'content-chapters', title: '章节管理' },
  'content-novel-chapters': { path: '/content/novels/:id/chapters', name: 'content-novel-chapters', title: '章节管理' },

  // 财务管理
  'financial-payment-channel': { path: '/financial/payment_channel', name: 'financial-payment-channel', title: '支付渠道' },
  'financial-vip': { path: '/financial/vip', name: 'financial-vip', title: 'VIP套餐' },
  'financial-orders': { path: '/financial/orders', name: 'financial-orders', title: '订单列表' },

  // 站点管理
  'site-comments': { path: '/site/comments', name: 'site-comments', title: '用户评论' },
  'site-users': { path: '/site/users', name: 'site-users', title: '站点用户' },
} as const

export type RouteKey = keyof typeof ROUTES

/**
 * 根据 key 或 path 获取路径
 */
export function pathOf(keyOrPath: string): string {
  const key = keyOrPath.trim()
  if (key in ROUTES) {
    return ROUTES[key as RouteKey].path
  }
  if (!key) return '/'
  return key.startsWith('/') ? key : `/${key}`
}

/**
 * 根据 key 或 path 获取路由名称
 */
export function nameOf(keyOrPath: string): string {
  const key = keyOrPath.trim()
  if (key in ROUTES) {
    return ROUTES[key as RouteKey].name
  }
  return key || ''
}

/**
 * 获取路由标题
 */
export function titleOf(keyOrPath: string): string {
  const key = keyOrPath.trim()
  if (key in ROUTES) {
    return ROUTES[key as RouteKey].title
  }
  return ''
}

/**
 * 路由选项列表（用于下拉选择）
 */
export const ROUTE_OPTIONS: Array<{ label: string; value: string }> = Object.values(ROUTES).map((r) => ({
  label: r.title,
  value: r.path,
}))

/**
 * 页面组件映射
 */
const PAGE_COMPONENTS: Record<string, () => Promise<unknown>> = {
  login: () => import('../pages/Login.vue'),
  dashboard: () => import('../pages/Dashboard.vue'),
  system: () => import('../pages/System.vue'),
  'system-menu': () => import('../pages/SystemMenu.vue'),
  'system-roles': () => import('../pages/SystemRoles.vue'),
  'system-permissions': () => import('../pages/SystemPermissions.vue'),
  'system-sites': () => import('../pages/SystemSites.vue'),
  'system-site-config': () => import('../pages/SystemSiteConfig.vue'),
  'system-domain': () => import('../pages/SystemDomainList.vue'),
  'system-admins': () => import('../pages/SystemAdmins.vue'),
  'system-config': () => import('../pages/SystemConfig.vue'),
  'system-payments': () => import('../pages/SystemPayments.vue'),
  'system-vip': () => import('../pages/Vip.vue'),
  'content-categories': () => import('../pages/ContentCategories.vue'),
  'content-tags': () => import('../pages/ContentTags.vue'),
  'content-novels': () => import('../pages/ContentNovels.vue'),
  'content-chapters': () => import('../pages/ContentChapters.vue'),
  'content-novel-chapters': () => import('../pages/ContentChapters.vue'),
  'financial-payment-channel': () => import('../pages/SystemPayments.vue'),
  'financial-vip': () => import('../pages/Vip.vue'),
  'financial-orders': () => import('../pages/FinancialOrders.vue'),
  'site-comments': () => import('../pages/SiteComments.vue'),
  'site-users': () => import('../pages/SiteUsers.vue'),
}

/**
 * 创建子路由配置
 */
function createChildRoute(key: RouteKey): RouteRecordRaw {
  const route = ROUTES[key]
  const path = route.path.startsWith('/') ? route.path.slice(1) : route.path

  const config: RouteRecordRaw = {
    path,
    name: route.name,
    component: PAGE_COMPONENTS[key],
    meta: { title: route.title },
  }

  // 章节管理页面需要先选择小说
  if (key === 'content-chapters') {
    config.beforeEnter = () => ({ name: 'content-novels' })
  }

  return config
}

/**
 * 布局内的子路由 keys
 */
const LAYOUT_CHILD_KEYS: RouteKey[] = [
  'dashboard',
  'system',
  'system-menu',
  'system-roles',
  'system-permissions',
  'system-sites',
  'system-site-config',
  'system-domain',
  'system-admins',
  'system-config',
  'system-payments',
  'system-vip',
  'content-categories',
  'content-tags',
  'content-novels',
  'content-chapters',
  'content-novel-chapters',
  'financial-payment-channel',
  'financial-vip',
  'financial-orders',
  'site-comments',
  'site-users',
]

/**
 * 生成所有路由配置
 */
export function createRoutes(): RouteRecordRaw[] {
  return [
    // 登录页
    {
      path: ROUTES.login.path,
      name: ROUTES.login.name,
      component: PAGE_COMPONENTS.login,
      meta: { title: ROUTES.login.title },
    },
    // 主布局
    {
      path: '/',
      component: () => import('../layouts/AppLayout.vue'),
      children: [
        // 首页
        {
          path: '',
          name: ROUTES.home.name,
          component: PAGE_COMPONENTS.dashboard,
          meta: { title: ROUTES.home.title },
        },
        // 其他子路由
        ...LAYOUT_CHILD_KEYS.map(createChildRoute),
      ],
    },
  ]
}

export type RouteKey = 'login' | 'home' | 'dashboard' | 'system' | 'system-menu' | 'system-roles'

export const ROUTES: Record<RouteKey, { path: string; name: string; title: string }> = {
  login: { path: '/login', name: 'login', title: '登录' },
  home: { path: '/', name: 'home', title: '首页' },
  dashboard: { path: '/dashboard', name: 'dashboard', title: '首页' },
  system: { path: '/system', name: 'system', title: '系统管理' },
  'system-menu': { path: '/system/menu', name: 'system-menu', title: '菜单管理' },
  'system-roles': { path: '/system/roles', name: 'system-roles', title: '角色管理' },
}

export function pathOf(keyOrPath: string): string {
  const k = String(keyOrPath || '').trim()
  if ((ROUTES as any)[k]) return (ROUTES as any)[k].path
  if (!k) return '/'
  return k.startsWith('/') ? k : `/${k}`
}

export function nameOf(keyOrPath: string): string {
  const k = String(keyOrPath || '').trim()
  if ((ROUTES as any)[k]) return (ROUTES as any)[k].name
  return k || ''
}

export const ROUTE_OPTIONS: Array<{ label: string; value: string }> = Object.values(ROUTES).map((r) => ({
  label: r.title,
  value: r.path,
}))

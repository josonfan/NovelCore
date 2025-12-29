import { ref } from 'vue'
import { fetchContext } from '../api/auth'
import { useAuthStore } from '../store'
import { pathOf } from '../router/routes'
import type { MenuNode } from '../api/menus'

// 菜单名称到路径的映射
const MENU_PATH_MAP: Record<string, string> = {
  分类管理: '/content/categories',
  标签管理: '/content/tags',
  小说管理: '/content/novels',
  章节管理: '/content/chapters',
  系统配置: '/system/config',
  支付渠道: '/financial/payment_channel',
  VIP套餐: '/financial/vip',
  订单列表: '/financial/orders',
  用户评论: '/site/comments',
  站点用户: '/site/users',
}

export function useMenus() {
  const menus = ref<MenuNode[]>([])
  const loading = ref(true)

  /**
   * 解析菜单项索引（用于 el-menu router 模式）
   */
  function resolveIndex(menu: MenuNode): string {
    const path = menu.path || menu.route || ''
    if (path) return pathOf(path)

    const name = String(menu.name || '')
    return MENU_PATH_MAP[name] || pathOf('/')
  }

  /**
   * 规范化菜单树：过滤不可见/非激活的菜单项
   */
  function normalizeMenus(list: MenuNode[]): MenuNode[] {
    const arr = Array.isArray(list) ? list : []
    return arr
      .filter((m) => {
        const type = String(m?.type || 'menu').toLowerCase()
        const visible = Number(m?.visible ?? 1) !== 0
        const active = Number(m?.is_active ?? 1) !== 0
        return type === 'menu' && visible && active
      })
      .map((m) => ({
        ...m,
        children: normalizeMenus(m?.children || []),
      }))
  }

  /**
   * 加载用户上下文和菜单数据
   */
  async function loadMenus() {
    const auth = useAuthStore()
    try {
      const token = localStorage.getItem('token') || ''
      if (!token) return

      const ctx = await fetchContext()
      auth.setContext(ctx)
      menus.value = normalizeMenus(ctx?.menus || [])
    } finally {
      loading.value = false
    }
  }

  return {
    menus,
    loading,
    resolveIndex,
    loadMenus,
  }
}

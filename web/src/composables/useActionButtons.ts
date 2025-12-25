import { computed, type ComputedRef } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../store'
import * as Icons from '@element-plus/icons-vue'

export interface ActionButton {
  id: number | string
  name: string
  code?: string
  icon?: string
  type?: string
  visible?: number
  is_active?: number
  sort_order?: number
}

export function useActionButtons() {
  const router = useRouter()
  const auth = useAuthStore()

  const actionButtons: ComputedRef<ActionButton[]> = computed(() => {
    const path = router.currentRoute.value.path

    function matchNode(list: any[]): any | null {
      for (const m of list || []) {
        const p = m?.path || m?.route || ''
        // 匹配当前路径或路径前缀（支持动态路由）
        if (p === path || (p && path.startsWith(p + '/'))) return m
        const found = matchNode(m?.children || [])
        if (found) return found
      }
      return null
    }

    const root = matchNode((auth.context || {}).menus || [])
    const arr = (root?.children || []).filter(
      (x: any) =>
        String(x?.type || '') === 'button' &&
        Number(x?.visible ?? 1) !== 0 &&
        Number(x?.is_active ?? 1) !== 0
    )
    return arr.sort(
      (a: any, b: any) => Number(a?.sort_order ?? 0) - Number(b?.sort_order ?? 0)
    )
  })

  const actionButtonsRow = computed(() => {
    const staticCodes = ['edit', 'update', 'delete', 'remove', 'content', 'view', 'detail']
    return actionButtons.value.filter((x) => {
      const c = String(x?.code || x?.name || '').toLowerCase()
      return !staticCodes.includes(c)
    })
  })

  function resolveIcon(name?: string) {
    if (!name) return null
    const n = String(name).replace(/^el-icon-/, '')
    const pascal = n
      .split(/[-_\s]/)
      .map((s) => s.charAt(0).toUpperCase() + s.slice(1))
      .join('')
    return (Icons as any)[pascal] || null
  }

  function resolveBtnType(btn: ActionButton) {
    const code = String(btn?.code || '').toLowerCase()
    if (code === 'add' || code === 'create' || code === 'new') return 'primary'
    if (code === 'edit' || code === 'update') return 'primary'
    if (code === 'delete' || code === 'remove') return 'danger'
    if (code === 'view' || code === 'detail' || code === 'content') return 'success'
    if (code === 'refresh') return 'default'
    return 'default'
  }

  return {
    actionButtons,
    actionButtonsRow,
    resolveIcon,
    resolveBtnType,
  }
}

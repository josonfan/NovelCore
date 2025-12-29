import { ref, computed, onMounted } from 'vue'
import * as Icons from '@element-plus/icons-vue'

const STORAGE_KEY = 'nc-sidebar-collapsed'

export function useSidebar() {
  const collapsed = ref(false)

  const collapseIcon = computed(() =>
    collapsed.value ? (Icons as Record<string, unknown>).Expand : (Icons as Record<string, unknown>).Fold
  )

  function toggle() {
    collapsed.value = !collapsed.value
    try {
      localStorage.setItem(STORAGE_KEY, collapsed.value ? '1' : '0')
    } catch {
      // ignore storage errors
    }
  }

  function loadState() {
    try {
      collapsed.value = localStorage.getItem(STORAGE_KEY) === '1'
    } catch {
      // ignore storage errors
    }
  }

  onMounted(loadState)

  return {
    collapsed,
    collapseIcon,
    toggle,
  }
}

/**
 * 解析图标名称为 Element Plus 图标组件
 */
export function resolveIcon(name?: string) {
  if (!name) return null
  const normalized = name.replace(/^el-icon-/, '')
  const pascal = normalized
    .split(/[-_\s]/)
    .map((s) => s.charAt(0).toUpperCase() + s.slice(1))
    .join('')
  return (Icons as Record<string, unknown>)[pascal] || null
}

import { ref, computed } from 'vue'
import { ElMessage } from 'element-plus'
import { createMenu, updateMenu, bindMenuPermissions } from '../api/menus'
import { fetchPermissionList } from '../api/permissions'
import type { MenuNode } from '../api/menus'
import { pathOf } from '../router/routes'
import * as Icons from '@element-plus/icons-vue'

export interface MenuFormData {
  id?: number | string
  parent_id: string
  name: string
  code: string
  path: string
  route: string
  icon: string
  type: 'menu' | 'button'
  visible: number
  is_active: number
  sort_order: number
}

const DEFAULT_FORM: MenuFormData = {
  parent_id: '0',
  name: '',
  code: '',
  path: '',
  route: '',
  icon: '',
  type: 'menu',
  visible: 1,
  is_active: 1,
  sort_order: 0,
}

/**
 * 菜单表单逻辑
 */
export function useMenuForm(reload: () => void, reloadOptions?: () => void) {
  const showForm = ref(false)
  const formMode = ref<'add' | 'edit'>('add')
  const form = ref<MenuFormData>({ ...DEFAULT_FORM })

  // 权限绑定
  const showBind = ref(false)
  const bindMenuId = ref<number | undefined>(undefined)
  const permOptions = ref<Array<{ label: string; value: number }>>([])
  const bindPermIds = ref<number[]>([])

  /**
   * 图标预览组件
   */
  const previewIcon = computed(() => {
    const name = String(form.value.icon || '').replace(/^el-icon-/, '')
    const pascal = name
      .split(/[-_\s]/)
      .map((s) => s.charAt(0).toUpperCase() + s.slice(1))
      .join('')
    return (Icons as Record<string, unknown>)[pascal] || null
  })

  /**
   * 打开新建表单
   */
  function openAdd(parentId?: string | number) {
    formMode.value = 'add'
    form.value = {
      ...DEFAULT_FORM,
      parent_id: String(parentId || '0'),
    }
    showForm.value = true
  }

  /**
   * 打开编辑表单
   */
  function openEdit(row: MenuNode) {
    formMode.value = 'edit'
    form.value = {
      id: row.id,
      parent_id: String(row.parent_id || '0'),
      name: row.name || '',
      code: row.code || '',
      path: row.path || '',
      route: row.route || '',
      icon: row.icon || '',
      type: (row.type as 'menu' | 'button') || 'menu',
      visible: Number(row.visible ?? 1),
      is_active: Number(row.is_active ?? 1),
      sort_order: Number(row.sort_order || 0),
    }
    showForm.value = true
  }

  /**
   * 快速添加子菜单
   */
  function openAddChild(row: MenuNode) {
    formMode.value = 'add'
    form.value = {
      ...DEFAULT_FORM,
      parent_id: String(row.id),
    }
    showForm.value = true
  }

  /**
   * 保存表单
   */
  async function saveForm() {
    try {
      if (!form.value.name || !form.value.code) {
        ElMessage.error('请填写名称与编码')
        return
      }

      const payload = {
        ...form.value,
        parent_id: Number(form.value.parent_id),
        path: pathOf(form.value.path),
      }

      const res =
        formMode.value === 'add'
          ? await createMenu(payload)
          : await updateMenu(form.value.id!, payload)

      if (res?.code === 200) {
        ElMessage.success('已保存')
        showForm.value = false
        reload()
        reloadOptions?.()
      } else {
        ElMessage.error(res?.msg || '保存失败')
      }
    } catch (e: unknown) {
      const resp = (e as { response?: { data?: { message?: string; msg?: string } } })?.response
        ?.data
      ElMessage.error(resp?.message || resp?.msg || '保存失败')
    }
  }

  /**
   * 打开权限绑定
   */
  async function openBind(row: MenuNode) {
    bindMenuId.value = Number(row.id)
    try {
      const list = await fetchPermissionList()
      permOptions.value = (list || []).map((x: { id: number; name: string; resource: string; action: string; field?: string }) => ({
        label: `${x.name} (${x.resource}/${x.action}${x.field ? '/' + x.field : ''})`,
        value: Number(x.id),
      }))
      bindPermIds.value = []
      showBind.value = true
    } catch {
      ElMessage.error('获取权限列表失败')
    }
  }

  /**
   * 保存权限绑定
   */
  async function saveBind() {
    if (!bindMenuId.value) return
    try {
      const res = await bindMenuPermissions(bindMenuId.value, bindPermIds.value)
      if (res?.code === 200) {
        ElMessage.success('已绑定')
        showBind.value = false
      } else {
        ElMessage.error(res?.msg || '绑定失败')
      }
    } catch {
      ElMessage.error('绑定失败')
    }
  }

  return {
    // 表单状态
    showForm,
    formMode,
    form,
    previewIcon,

    // 表单方法
    openAdd,
    openEdit,
    openAddChild,
    saveForm,

    // 权限绑定状态
    showBind,
    bindMenuId,
    permOptions,
    bindPermIds,

    // 权限绑定方法
    openBind,
    saveBind,
  }
}

/**
 * 解析图标组件
 */
export function resolveMenuIcon(iconName?: string) {
  if (!iconName) return null
  const name = String(iconName).replace(/^el-icon-/, '')
  const pascal = name
    .split(/[-_\s]/)
    .map((s) => s.charAt(0).toUpperCase() + s.slice(1))
    .join('')
  return (Icons as Record<string, unknown>)[pascal] || null
}

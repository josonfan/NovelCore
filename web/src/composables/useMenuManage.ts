import { ref, computed, watch } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import {
  fetchMenuList,
  fetchMenuDetail,
  deleteMenu,
  fetchMenuOptions,
} from '../api/menus'
import type { MenuNode } from '../api/menus'

export interface MenuFilters {
  parentId: string | number
  keyword: string
}

/**
 * 菜单管理列表逻辑
 */
export function useMenuManage() {
  const loading = ref(false)
  const page = ref(1)
  const limit = ref(20)
  const total = ref(0)
  const rows = ref<MenuNode[]>([])
  const options = ref<MenuNode[]>([])

  // 筛选条件
  const filters = ref<MenuFilters>({
    parentId: '',
    keyword: '',
  })

  // 详情
  const showDetail = ref(false)
  const detail = ref<MenuNode | null>(null)

  /**
   * 将菜单列表转换为树形结构
   */
  function buildTree(list: MenuNode[], parentId: number = 0): MenuNode[] {
    return list
      .filter((item) => Number(item.parent_id) === parentId)
      .map((item) => ({
        ...item,
        children: buildTree(list, Number(item.id)),
      }))
      .sort((a, b) => (a.sort_order || 0) - (b.sort_order || 0))
  }

  /**
   * 获取当前父级名称
   */
  const currentParentLabel = computed(() => {
    const id = Number(filters.value.parentId)
    if (!id) return '顶级菜单'

    const find = (nodes: MenuNode[]): string | null => {
      for (const n of nodes || []) {
        if (Number(n.id) === id) return n.name || null
        const r = find(n.children || [])
        if (r) return r
      }
      return null
    }
    return find(options.value || []) || '顶级菜单'
  })

  /**
   * 过滤后的数据（支持关键词搜索）
   */
  const filteredRows = computed(() => {
    const kw = filters.value.keyword.toLowerCase()
    if (!kw) return rows.value

    const filterTree = (nodes: MenuNode[]): MenuNode[] => {
      return nodes.reduce<MenuNode[]>((acc, node) => {
        const nameMatch = (node.name || '').toLowerCase().includes(kw)
        const codeMatch = (node.code || '').toLowerCase().includes(kw)
        const filteredChildren = filterTree(node.children || [])

        if (nameMatch || codeMatch || filteredChildren.length > 0) {
          acc.push({
            ...node,
            children: filteredChildren.length > 0 ? filteredChildren : node.children,
          })
        }
        return acc
      }, [])
    }

    return filterTree(rows.value)
  })

  /**
   * 加载菜单列表
   */
  async function load() {
    loading.value = true
    try {
      const data = await fetchMenuList({
        page: page.value,
        limit: limit.value,
        parent_id: Number(filters.value.parentId),
      })
      const list = data?.list || []
      // 构建树形结构
      rows.value = buildTree(list, Number(filters.value.parentId))
      total.value = data?.count || 0
    } finally {
      loading.value = false
    }
  }

  /**
   * 加载菜单选项（用于下拉选择）
   */
  async function loadOptions() {
    try {
      const data = await fetchMenuOptions()
      options.value = data as MenuNode[] || []
    } catch {
      options.value = []
    }
  }

  /**
   * 刷新列表
   */
  function refresh() {
    load()
  }

  /**
   * 翻页
   */
  function onPageChange(p: number) {
    page.value = p
    load()
  }

  /**
   * 修改每页数量
   */
  function onSizeChange(s: number) {
    limit.value = s
    page.value = 1
    load()
  }

  /**
   * 删除菜单
   */
  async function handleDelete(row: MenuNode) {
    try {
      await ElMessageBox.confirm('确认删除该菜单？删除后子菜单也会被删除。', '提示', {
        type: 'warning',
        confirmButtonText: '确认删除',
        cancelButtonText: '取消',
      })
      const res = await deleteMenu(row.id)
      if (res?.code === 200) {
        ElMessage.success('已删除')
        load()
        loadOptions()
      } else {
        ElMessage.error(res?.msg || '删除失败')
      }
    } catch {
      // 用户取消
    }
  }

  /**
   * 查看详情
   */
  async function handleDetail(row: MenuNode) {
    try {
      const d = await fetchMenuDetail(row.id)
      detail.value = d
      showDetail.value = true
    } catch {
      ElMessage.error('获取详情失败')
    }
  }

  // 监听父级变化，重新加载
  watch(
    () => filters.value.parentId,
    () => {
      page.value = 1
      load()
    }
  )

  return {
    // 状态
    loading,
    page,
    limit,
    total,
    rows,
    options,
    filters,
    filteredRows,
    currentParentLabel,
    showDetail,
    detail,

    // 方法
    load,
    loadOptions,
    refresh,
    onPageChange,
    onSizeChange,
    handleDelete,
    handleDetail,
  }
}

/**
 * 构建树形选择器数据
 */
export function buildTreeSelectData(
  options: MenuNode[],
  includeRoot = true
): Array<{ id: string; label: string; children?: unknown[] }> {
  const mapNodes = (nodes: MenuNode[]): Array<{ id: string; label: string; children?: unknown[] }> =>
    (nodes || []).map((n) => ({
      id: String(n.id),
      label: String(n.name || ''),
      children: mapNodes(n.children || []),
    }))

  if (includeRoot) {
    return [{ id: '0', label: '顶级菜单', children: mapNodes(options) }]
  }
  return mapNodes(options)
}

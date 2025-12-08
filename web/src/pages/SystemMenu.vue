<template>
  <div class="wrap">
    <div class="content-area">
    <el-card shadow="never" class="toolbar">
      <div class="toolbar-grid">
        <div class="cell">
          <ParentFilterSelect
            v-model="parentId"
            :data="treeSelectDataFilter"
            :props="tPropsFilter"
            placeholder="选择上级菜单"
          />
        </div>
        <div class="cell">
          <el-input v-model="kw" :placeholder="t('common.search_placeholder')" clearable />
        </div>
        <div class="actions">
          <el-button @click="reload" :loading="loading">{{ t('common.refresh') }}</el-button>
          <el-button type="primary" @click="onAdd">新建</el-button>
        </div>
      </div>
      <div class="subline">当前：{{ currentLabel }} 下级，共 {{ total }} 条</div>
    </el-card>
    <el-card shadow="hover" class="table-card">
      <el-table :data="filtered" v-loading="loading" border size="small" stripe highlight-current-row>
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column label="名称" min-width="180">
          <template #default="{ row }">
            <span class="tbl-name">
              <el-icon v-if="iconComp(row.icon)" :size="16" class="tbl-icon"><component :is="iconComp(row.icon)" /></el-icon>
              <span class="tbl-text">{{ row.name }}</span>
            </span>
          </template>
        </el-table-column>
        <el-table-column prop="code" label="编码" min-width="140" />
        <el-table-column label="路由" min-width="160">
          <template #default="{ row }">
            {{ pathOf(row.path) }}
          </template>
        </el-table-column>
        <el-table-column prop="route" label="后端标识" min-width="140" />
        <el-table-column label="类型" width="100">
          <template #default="{ row }">
            <el-tag size="small" :type="row.type==='menu'?'success':'info'">{{ row.type==='menu'?'菜单':'按钮' }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="显示" width="80">
          <template #default="{ row }">
            <el-tag size="small" :type="row.visible===1?'info':'warning'">{{ row.visible===1?'显示':'隐藏' }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="启用" width="80">
          <template #default="{ row }">
            <el-tag size="small" :type="row.is_active===1?'success':'danger'">{{ row.is_active===1?'启用':'停用' }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="sort_order" label="排序" width="80" />
        <el-table-column prop="created_at" label="创建时间" min-width="160" />
        <el-table-column label="操作" width="160" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" link @click="onEdit(row)">编辑</el-button>
            <el-button type="danger" link @click="onDelete(row)">删除</el-button>
            <el-button link @click="onDetail(row)">详情</el-button>
          </template>
        </el-table-column>
      </el-table>
      <div class="pager">
        <el-pagination
          background
          layout="prev, pager, next, jumper, sizes, total"
          :page-size="limit"
          :current-page="page"
          :total="total"
          @current-change="onPage"
          @size-change="onSize"
          :page-sizes="[10,20,50]"
        />
      </div>
    </el-card>

    <el-dialog v-model="showForm" :title="formMode==='add'?'新建菜单':'编辑菜单'" width="560px">
      <el-form :model="form" label-width="100px">
        <el-form-item label="上级菜单">
          <ParentEditSelect
            ref="parentSelect"
            v-model="form.parent_id"
            :data="treeSelectDataForm"
            :props="tPropsForm"
            placeholder="请选择上级菜单"
          />
        </el-form-item>
        <el-form-item label="名称"><el-input v-model="form.name" /></el-form-item>
        <el-form-item label="编码"><el-input v-model="form.code" /></el-form-item>
        <el-form-item label="路由"><el-input v-model="form.path" /></el-form-item>
        <el-form-item label="后端标识"><el-input v-model="form.route" /></el-form-item>
        <el-form-item label="图标">
          <div style="display:flex; align-items:center; gap:8px; width:100%">
            <el-input v-model="form.icon" placeholder="请选择图标" readonly />
            <el-popover placement="bottom" width="480" trigger="click">
              <template #reference>
                <el-button>选择</el-button>
              </template>
              <IconPicker v-model="form.icon" />
            </el-popover>
            <el-icon v-if="form.icon" :size="18"><component :is="previewIcon" /></el-icon>
          </div>
        </el-form-item>
        <el-form-item label="类型">
          <el-radio-group v-model="form.type">
            <el-radio label="menu">菜单</el-radio>
            <el-radio label="button">按钮</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="显示"><el-switch v-model="form.visible" :active-value="1" :inactive-value="0" /></el-form-item>
        <el-form-item label="启用"><el-switch v-model="form.is_active" :active-value="1" :inactive-value="0" /></el-form-item>
        <el-form-item label="排序"><el-input v-model="form.sort_order" /></el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="showForm=false">取消</el-button>
        <el-button type="primary" @click="saveForm">保存</el-button>
      </template>
    </el-dialog>

    <el-dialog v-model="showDetail" title="菜单详情" width="560px">
      <el-descriptions :column="2" border>
        <el-descriptions-item label="ID">{{ detail?.id }}</el-descriptions-item>
        <el-descriptions-item label="父级ID">{{ detail?.parent_id }}</el-descriptions-item>
        <el-descriptions-item label="名称">{{ detail?.name }}</el-descriptions-item>
        <el-descriptions-item label="编码">{{ detail?.code }}</el-descriptions-item>
        <el-descriptions-item label="路由">{{ detail?.path }}</el-descriptions-item>
        <el-descriptions-item label="后端标识">{{ detail?.route }}</el-descriptions-item>
        <el-descriptions-item label="图标">{{ detail?.icon }}</el-descriptions-item>
        <el-descriptions-item label="类型">{{ detail?.type }}</el-descriptions-item>
        <el-descriptions-item label="显示">{{ detail?.visible }}</el-descriptions-item>
        <el-descriptions-item label="启用">{{ detail?.is_active }}</el-descriptions-item>
        <el-descriptions-item label="排序">{{ detail?.sort_order }}</el-descriptions-item>
        <el-descriptions-item label="创建时间">{{ detail?.created_at }}</el-descriptions-item>
        <el-descriptions-item label="更新时间">{{ detail?.updated_at }}</el-descriptions-item>
      </el-descriptions>
    </el-dialog>
    </div>
  </div>
  </template>

<script setup lang="ts">
import { ref, computed, onMounted, watch, nextTick } from 'vue'
import { fetchMenuList, fetchMenuDetail, createMenu, updateMenu, deleteMenu, fetchMenuOptions } from '../api/menus'
import { useI18n } from 'vue-i18n'
import { ElMessage, ElMessageBox } from 'element-plus'
import IconPicker from '../components/IconPicker.vue'
import * as Icons from '@element-plus/icons-vue'
import { pathOf } from '../router/routes'
import ParentFilterSelect from '../components/ParentFilterSelect.vue'
import ParentEditSelect from '../components/ParentEditSelect.vue'

const page = ref(1)
const limit = ref(10)
const total = ref(0)
const rows = ref<any[]>([])
const loading = ref(false)
const kw = ref('')
const parentId = ref<string | number>('0')
const currentLabel = computed(() => {
  const id = Number(parentId.value)
  if (!id) return '顶级菜单'
  const find = (nodes: any[]): string | null => {
    for (const n of nodes || []) {
      if (Number(n.id) === id) return n.name
      const r = find(n.children || [])
      if (r) return r
    }
    return null
  }
  return find(options.value || []) || '顶级菜单'
})
const { t } = useI18n()
const showForm = ref(false)
const formMode = ref<'add'|'edit'>('add')
const form = ref<any>({ parent_id: '0', name: '', code: '', path: '', route: '', icon: '', type: 'menu', visible: 1, is_active: 1, sort_order: 0 })
const parentSelect = ref()
const showDetail = ref(false)
const detail = ref<any>(null)
const options = ref<any[]>([])
const mapNodes = (nodes: any[]): any[] => (nodes || []).map((n: any) => ({ id: String(n.id), label: String(n.name || n.label || ''), children: mapNodes(n.children || []) }))
const treeSelectDataFilter = computed(() => [{ id: '0', label: '顶级菜单', children: mapNodes(options.value || []) }])
const treeSelectDataForm = computed(() => [{ id: '0', label: '顶级菜单', children: mapNodes(options.value || []) }])
const tPropsFilter = { value: 'id', label: 'label', children: 'children' }
const tPropsForm = { value: 'id', label: 'label', children: 'children' }
const previewIcon = computed(() => {
  const n = String(form.value.icon || '').replace(/^el-icon-/, '')
  const pascal = n.split(/[-_\s]/).map(s => s.charAt(0).toUpperCase()+s.slice(1)).join('')
  return (Icons as any)[pascal] || null
})
const filtered = computed(() => {
  if (!kw.value) return rows.value
  const k = kw.value.toLowerCase()
  return rows.value.filter((x) => (x.name || '').toLowerCase().includes(k) || (x.code || '').toLowerCase().includes(k))
})

function iconComp(value: string) {
  const n = String(value || '').replace(/^el-icon-/, '')
  const pascal = n.split(/[-_\s]/).map(s => s.charAt(0).toUpperCase() + s.slice(1)).join('')
  return (Icons as any)[pascal] || null
}

async function load() {
  loading.value = true
  try {
    const data = await fetchMenuList({ page: page.value, limit: limit.value, parent_id: Number(parentId.value) })
    rows.value = data?.list || []
    total.value = data?.count || 0
  } finally {
    loading.value = false
  }
}

async function loadOptions() {
  try {
    const data = await fetchMenuOptions()
    options.value = data || []
  } catch (_) {}
}


function onPage(p: number) {
  page.value = p
  load()
}
function onSize(s: number) {
  limit.value = s
  page.value = 1
  load()
}
function reload() { load() }

function onAdd() {
  formMode.value = 'add'
  form.value = { parent_id: String(parentId.value || '0'), name: '', code: '', path: '', route: '', icon: '', type: 'menu', visible: 1, is_active: 1, sort_order: 0 }
  showForm.value = true
  nextTick(() => { try { parentSelect.value?.focus?.() } catch (_) {} })
}

function onEdit(row: any) {
  formMode.value = 'edit'
  form.value = { ...row }
  showForm.value = true
  nextTick(() => { try { parentSelect.value?.focus?.() } catch (_) {} })
}

async function saveForm() {
  try {
    if (!form.value.name || !form.value.code) {
      ElMessage.error('请填写名称与编码')
      return
    }
    const payload = { ...form.value, parent_id: Number(form.value.parent_id), path: pathOf(form.value.path) }
    const res = formMode.value === 'add' ? await createMenu(payload) : await updateMenu(form.value.id, payload)
    if (res?.code === 200) {
      ElMessage.success('已保存')
      showForm.value = false
      load()
    } else {
      ElMessage.error(res?.msg || '保存失败')
    }
  } catch (e: any) {
    const resp = e?.response?.data
    ElMessage.error(resp?.message || resp?.msg || '保存失败')
  }
}

async function onDelete(row: any) {
  try {
    await ElMessageBox.confirm('确认删除该菜单？', '提示', { type: 'warning' })
    const res = await deleteMenu(row.id)
    if (res?.code === 200) {
      ElMessage.success('已删除')
      load()
    } else {
      ElMessage.error(res?.msg || '删除失败')
    }
  } catch (_) {}
}

async function onDetail(row: any) {
  const d = await fetchMenuDetail(row.id)
  detail.value = d
  showDetail.value = true
}

onMounted(load)
onMounted(loadOptions)

watch(parentId, () => {
  page.value = 1
  load()
})
</script>

<style scoped>
.wrap { display: grid; gap: 12px; grid-template-columns: 1fr; }
.content-area { display: grid; gap: 12px; }
.toolbar { display: grid; gap: 8px; }
.toolbar-grid { display: grid; grid-template-columns: 260px 260px 1fr; gap: 12px; align-items: center; }
.toolbar-grid .actions { justify-self: end; display: inline-flex; gap: 8px; }
.subline { color: var(--nc-muted); font-size: 12px; }
.table-card { }
.pager { display: flex; justify-content: flex-end; margin-top: 12px; }
.tbl-name { display: inline-flex; align-items: center; gap: 6px; line-height: 1; }
.tbl-icon { display: inline-flex; align-items: center; justify-content: center; line-height: 1; }
.tbl-text { display: inline-block; line-height: 1; }
:deep(.el-table__cell){ vertical-align: middle; }
:deep(.el-table .cell){ display: inline-flex; align-items: center; line-height: 1; }
:deep(.el-table__header .el-table__cell){ background: #f3f4f6; color: var(--nc-text); font-weight: 600; }
:deep(.el-table__cell){ padding: 10px 12px; }
:deep(.el-table__row:hover){ background: rgba(64,158,255,0.06); }
</style>

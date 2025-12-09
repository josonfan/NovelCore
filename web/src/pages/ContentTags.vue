<template>
  <div class="wrap">
    <el-card shadow="never" class="toolbar">
      <div class="toolbar-grid">
        <div class="cell">
          <el-input v-model="kw" placeholder="搜索名称" clearable />
        </div>
        <div class="actions">
          <template v-for="btn in actionButtons" :key="btn.id">
            <el-button @click="onAction(btn)" :type="resolveBtnType(btn)">
              <el-icon v-if="resolveIcon(btn.icon)" :size="16" style="margin-right:6px"><component :is="resolveIcon(btn.icon)" /></el-icon>
              {{ btn.name }}
            </el-button>
          </template>
          <el-button @click="reload" :loading="loading">刷新</el-button>
          <el-button type="primary" @click="onAdd">新建</el-button>
        </div>
      </div>
      <div class="subline">共 {{ total }} 条</div>
    </el-card>
    <el-card shadow="hover" class="table-card">
      <el-table :data="filtered" v-loading="loading" border size="small" stripe highlight-current-row>
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="name" label="名称" min-width="180" />
        <el-table-column label="Slug" min-width="160">
          <template #default="{ row }">
            {{ row.slug || row.code || '' }}
          </template>
        </el-table-column>
        <el-table-column label="状态" width="120">
          <template #default="{ row }">
            <el-tag :type="Number(row.is_active)===1 ? 'success' : 'danger'" size="small">
              {{ Number(row.is_active)===1 ? '启用' : '禁用' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="created_at" label="创建时间" min-width="160" />
        <el-table-column prop="updated_at" label="更新时间" min-width="160" />
        <el-table-column label="操作" width="260" fixed="right">
          <template #default="{ row }">
            <template v-for="btn in actionButtonsRow" :key="btn.id">
              <el-button :type="resolveBtnType(btn)" link @click="onActionRow(btn, row)">
                <el-icon v-if="resolveIcon(btn.icon)" :size="16" style="margin-right:6px"><component :is="resolveIcon(btn.icon)" /></el-icon>
                {{ btn.name }}
              </el-button>
            </template>
            <el-button type="primary" link @click="onEdit(row)">编辑</el-button>
            <el-button type="danger" link @click="onDelete(row)">删除</el-button>
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

    <el-dialog v-model="showForm" :title="formMode==='add'?'新建标签':'编辑标签'" width="520px">
      <el-form :model="form" label-width="100px">
        <el-tabs v-model="activeTab">
          <el-tab-pane label="基本信息" name="basic">
            <el-form-item label="名称"><el-input v-model="form.name" /></el-form-item>
            <el-form-item label="Slug"><el-input v-model="form.slug" /></el-form-item>
            <el-form-item label="启用"><el-switch v-model="form.is_active" :active-value="1" :inactive-value="0" /></el-form-item>
          </el-tab-pane>
          <el-tab-pane label="SEO" name="seo">
            <el-form-item label="SEO标题"><el-input v-model="form.seo_title" /></el-form-item>
            <el-form-item label="SEO关键字"><el-input v-model="form.seo_keywords" /></el-form-item>
            <el-form-item label="SEO描述"><el-input v-model="form.seo_description" type="textarea" /></el-form-item>
          </el-tab-pane>
        </el-tabs>
      </el-form>
      <template #footer>
        <el-button @click="showForm=false">取消</el-button>
        <el-button type="primary" @click="saveForm">保存</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import * as Icons from '@element-plus/icons-vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../store'
import { fetchTagList, createTag, updateTag, deleteTag, fetchTagDetail } from '../api/tags'

const loading = ref(true)
const rows = ref<any[]>([])
const total = ref(0)
const page = ref(1)
const limit = ref(10)
const kw = ref('')
const showForm = ref(false)
const formMode = ref<'add'|'edit'>('add')
const form = ref<any>({ name: '', slug: '', is_active: 1, seo_title: '', seo_keywords: '', seo_description: '' })
const activeTab = ref('basic')
const router = useRouter()
const auth = useAuthStore()
const actionButtons = computed(() => {
  const path = router.currentRoute.value.path
  function matchNode(list: any[]): any | null {
    for (const m of list || []) {
      const p = m?.path || m?.route || ''
      if (p === path || m?.name === '标签管理') return m
      const found = matchNode(m?.children || [])
      if (found) return found
    }
    return null
  }
  const root = matchNode((auth.context || {}).menus || [])
  const arr = (root?.children || []).filter((x: any) => String(x?.type || '') === 'button' && Number(x?.visible ?? 1) !== 0 && Number(x?.is_active ?? 1) !== 0)
  return arr.sort((a: any, b: any) => Number(a?.sort_order ?? 0) - Number(b?.sort_order ?? 0))
})
const actionButtonsRow = computed(() => {
  const staticCodes = ['edit','update','delete','remove']
  return actionButtons.value.filter((x: any) => {
    const c = String(x?.code || x?.name || '').toLowerCase()
    return !staticCodes.includes(c)
  })
})

const filtered = computed(() => {
  if (!kw.value) return rows.value
  const k = kw.value.toLowerCase()
  return rows.value.filter((x) => (x.name || '').toLowerCase().includes(k) || (x.slug || x.code || '').toLowerCase().includes(k))
})

async function load() {
  loading.value = true
  try {
    const data = await fetchTagList({ page: page.value, limit: limit.value, kw: kw.value })
    rows.value = data.list || []
    total.value = Number(data.count || 0)
  } finally {
    loading.value = false
  }
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

onMounted(load)

function resolveIcon(name?: string) {
  if (!name) return null
  const n = String(name).replace(/^el-icon-/, '')
  const pascal = n.split(/[-_\s]/).map((s) => s.charAt(0).toUpperCase() + s.slice(1)).join('')
  return (Icons as any)[pascal] || null
}

function resolveBtnType(btn: any){
  const code = String(btn?.code || '').toLowerCase()
  if (code === 'add' || code === 'create' || code === 'new') return 'primary'
  if (code === 'edit' || code === 'update') return 'primary'
  if (code === 'delete' || code === 'remove') return 'danger'
  if (code === 'view' || code === 'detail') return 'success'
  if (code === 'refresh') return 'default'
  return 'default'
}

function onAction(btn: any){
  const code = String(btn?.code || '').toLowerCase()
  const name = String(btn?.name || '').toLowerCase()
  const map: Record<string, Function> = { add: onAdd, create: onAdd, new: onAdd, refresh: reload }
  const fn = map[code] || map[name] || null
  if (fn) fn()
}

function onActionRow(btn: any, row: any){
  const code = String(btn?.code || '').toLowerCase()
  const name = String(btn?.name || '').toLowerCase()
  const rmap: Record<string, Function> = { edit: () => onEdit(row), update: () => onEdit(row), delete: () => onDelete(row), remove: () => onDelete(row), view: () => onEdit(row), detail: () => onEdit(row) }
  const fn = rmap[code] || rmap[name] || null
  if (fn) fn()
}

function reload(){ load() }

function onAdd(){
  formMode.value = 'add'
  form.value = { name: '', slug: '', is_active: 1, seo_title: '', seo_keywords: '', seo_description: '' }
  activeTab.value = 'basic'
  showForm.value = true
}

async function onEdit(row: any){
  formMode.value = 'edit'
  try {
    const d: any = await fetchTagDetail(row.id)
    form.value = { id: d.id, name: d.name, slug: d.slug || d.code || '', is_active: Number(d.is_active ?? row.is_active ?? 1), seo_title: d.seo_title || '', seo_keywords: d.seo_keywords || '', seo_description: d.seo_description || '' }
  } catch (_) {
    form.value = { id: row.id, name: row.name, slug: row.slug || row.code || '', is_active: row.is_active ?? 1, seo_title: '', seo_keywords: '', seo_description: '' }
  }
  activeTab.value = 'basic'
  showForm.value = true
}

async function saveForm(){
  try {
    if (!form.value.name) { ElMessage.error('请填写名称'); return }
    const payload = { name: form.value.name, slug: form.value.slug, code: form.value.slug, is_active: form.value.is_active, seo_title: form.value.seo_title, seo_keywords: form.value.seo_keywords, seo_description: form.value.seo_description }
    const res = formMode.value==='add' ? await createTag(payload) : await updateTag(form.value.id, payload)
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

async function onDelete(row: any){
  try {
    await ElMessageBox.confirm('确定删除该标签？', '提示', { type: 'warning' })
    const res = await deleteTag(row.id)
    if (res?.code === 200) { ElMessage.success('已删除'); load() } else { ElMessage.error(res?.msg || '删除失败') }
  } catch (_) {}
}
</script>

<style scoped>
 .wrap { display: grid; gap: 12px }
 .toolbar { display: grid; gap: 8px }
 .toolbar-grid { display: grid; grid-template-columns: 1fr auto; gap: 12px; align-items: center }
 .toolbar-grid .actions { justify-self: end; display: inline-flex; gap: 8px }
 .subline { color: var(--nc-muted); font-size: 12px }
 .pager { display: flex; justify-content: flex-end; padding-top: 12px }
</style>

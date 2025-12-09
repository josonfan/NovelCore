<template>
  <div class="wrap">
    <el-card shadow="never" class="toolbar">
      <template #header>
        章节管理<span v-if="novelTitle">（{{ novelTitle }}）</span>
      </template>
      <div class="toolbar-grid">
        <div class="cell">
          <el-input v-model="kw" placeholder="搜索标题/小说ID" clearable />
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
        <el-table-column prop="novel_id" label="小说ID" width="120" />
        <el-table-column prop="title" label="标题" min-width="220" />
        <el-table-column prop="index" label="序号" width="120" />
        <el-table-column prop="word_count" label="字数" width="120" />
        <el-table-column prop="updated_at" label="更新时间" min-width="160" />
        <el-table-column label="操作" width="320" fixed="right">
          <template #default="{ row }">
            <template v-for="btn in actionButtonsRow" :key="btn.id">
              <el-button :type="resolveBtnType(btn)" link @click="onActionRow(btn, row)">
                <el-icon v-if="resolveIcon(btn.icon)" :size="16" style="margin-right:6px"><component :is="resolveIcon(btn.icon)" /></el-icon>
                {{ btn.name }}
              </el-button>
            </template>
            <el-button type="primary" link @click="onEdit(row)">编辑</el-button>
            <el-button type="success" link @click="onViewContent(row)">查看内容</el-button>
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

    <el-dialog v-model="showForm" :title="formMode==='add'?'新建章节':'编辑章节'" width="560px">
      <el-form :model="form" label-width="100px">
        <el-tabs v-model="activeTab">
          <el-tab-pane label="基本信息" name="basic">
            <el-form-item v-if="!novelId" label="小说ID"><el-input v-model="form.novel_id" /></el-form-item>
            <el-form-item label="标题"><el-input v-model="form.title" /></el-form-item>
            <el-form-item label="序号"><el-input-number v-model="form.index" :min="1" /></el-form-item>
          </el-tab-pane>
          <el-tab-pane label="内容" name="content">
            <el-form-item label="内容">
              <RichTextEditor v-model="form.content" placeholder="请输入章节内容" />
            </el-form-item>
          </el-tab-pane>
        </el-tabs>
      </el-form>
      <template #footer>
        <el-button @click="showForm=false">取消</el-button>
        <el-button type="primary" @click="saveForm">保存</el-button>
      </template>
    </el-dialog>

    <el-dialog v-model="showContent" title="章节内容" width="800px">
      <el-form :model="contentForm" label-width="100px">
        <el-form-item label="标题"><el-input v-model="contentForm.title" /></el-form-item>
        <el-form-item label="内容">
          <RichTextEditor v-model="contentForm.content" placeholder="请输入章节内容" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="showContent=false">关闭</el-button>
        <el-button type="primary" @click="saveContent">保存内容</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import * as Icons from '@element-plus/icons-vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../store'
import RichTextEditor from '../components/RichTextEditor.vue'
 
import { fetchChapterList, createChapter, updateChapter, deleteChapter, fetchChapterContent, fetchChapterDetail } from '../api/chapters'

const loading = ref(true)
const rows = ref<any[]>([])
const total = ref(0)
const page = ref(1)
const limit = ref(10)
const kw = ref('')
const showForm = ref(false)
const formMode = ref<'add'|'edit'>('add')
const form = ref<any>({ novel_id: '', title: '', index: 1, content: '' })
const activeTab = ref('basic')
const router = useRouter()
const route = useRoute()
const novelId = computed(() => String(route.params.id || route.query.novel_id || ''))
const novelTitle = ref('')
const auth = useAuthStore()
const actionButtons = computed(() => {
  const path = router.currentRoute.value.path
  function matchNode(list: any[]): any | null {
    for (const m of list || []) {
      const p = m?.path || m?.route || ''
      if (p === path || m?.name === '章节管理') return m
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
  const staticCodes = ['edit','update','delete','remove','content','view','detail']
  return actionButtons.value.filter((x: any) => {
    const c = String(x?.code || x?.name || '').toLowerCase()
    return !staticCodes.includes(c)
  })
})
const showContent = ref(false)
const contentForm = ref<any>({ id: '', title: '', content: '' })

const filtered = computed(() => {
  if (!kw.value) return rows.value
  const k = kw.value.toLowerCase()
  return rows.value.filter((x) => (x.title || '').toLowerCase().includes(k) || String(x.novel_id || '').includes(k))
})

async function load() {
  loading.value = true
  try {
    const params: any = { page: page.value, limit: limit.value }
    if (novelId.value) params.novel_id = novelId.value
    const data = await fetchChapterList(params)
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

onMounted(() => { if (!novelId.value) { router.push({ name: 'content-novels' }); return } load() })
onMounted(async () => {
  if (novelId.value) {
    try {
      form.value.novel_id = novelId.value
      // 延迟加载标题，避免额外阻塞
      const { fetchNovelDetail } = await import('../api/novels')
      const d: any = await fetchNovelDetail(Number(novelId.value))
      novelTitle.value = d?.title || ''
    } catch (_) {}
  }
})

function reload(){ load() }

function onAdd(){
  formMode.value = 'add'
  form.value = { novel_id: novelId.value || '', title: '', index: 1, content: '' }
  activeTab.value = 'basic'
  showForm.value = true
}

async function onEdit(row: any){
  formMode.value = 'edit'
  try {
    const d: any = await fetchChapterDetail(row.id)
    form.value = { id: row.id, novel_id: d?.novel_id ?? row.novel_id, title: d?.title ?? row.title, index: Number(d?.index ?? row.index ?? 1), content: '' }
  } catch (_) {
    form.value = { id: row.id, novel_id: row.novel_id, title: row.title, index: row.index ?? 1, content: '' }
  }
  try {
    const c = await fetchChapterContent(row.id)
    form.value.content = c?.content || ''
  } catch (_) {}
  activeTab.value = 'basic'
  showForm.value = true
}

async function saveForm(){
  try {
    if (!form.value.novel_id || !form.value.title) { ElMessage.error('请填写小说ID与标题'); return }
    const payload = { novel_id: form.value.novel_id, title: form.value.title, index: form.value.index, content: form.value.content }
    const res = formMode.value==='add' ? await createChapter(payload) : await updateChapter(form.value.id, payload)
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
    await ElMessageBox.confirm('确定删除该章节？', '提示', { type: 'warning' })
    const res = await deleteChapter(row.id)
    if (res?.code === 200) { ElMessage.success('已删除'); load() } else { ElMessage.error(res?.msg || '删除失败') }
  } catch (_) {}
}

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
  if (code === 'content' || code === 'view' || code === 'detail') return 'success'
  if (code === 'refresh') return 'default'
  return 'default'
}

function onActionRow(btn: any, row: any){
  const code = String(btn?.code || '').toLowerCase()
  const name = String(btn?.name || '').toLowerCase()
  const rmap: Record<string, Function> = { edit: () => onEdit(row), update: () => onEdit(row), delete: () => onDelete(row), remove: () => onDelete(row), content: () => onViewContent(row), view: () => onViewContent(row), detail: () => onViewContent(row) }
  const fn = rmap[code] || rmap[name] || null
  if (fn) fn()
}

 

async function onViewContent(row: any){
  try {
    const data = await fetchChapterContent(row.id)
    contentForm.value = { id: row.id, title: data?.title || row.title, content: data?.content || '' }
    showContent.value = true
  } catch (e: any) {
    const resp = e?.response?.data
    ElMessage.error(resp?.message || resp?.msg || '获取内容失败')
  }
}

async function saveContent(){
  try {
    if (!contentForm.value.id) return
    const res = await updateChapter(contentForm.value.id, { title: contentForm.value.title, content: contentForm.value.content })
    if (res?.code === 200) {
      ElMessage.success('内容已保存')
      showContent.value = false
      load()
    } else {
      ElMessage.error(res?.msg || '保存失败')
    }
  } catch (e: any) {
    const resp = e?.response?.data
    ElMessage.error(resp?.message || resp?.msg || '保存失败')
  }
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

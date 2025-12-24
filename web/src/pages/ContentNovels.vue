<template>
  <div class="wrap">
    <el-card shadow="never" class="toolbar">
      <div class="toolbar-grid">
        <div class="cell">
          <el-input v-model="kw" placeholder="搜索标题/作者" clearable />
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
      <el-table :data="filtered" v-loading="loading" border size="small" stripe highlight-current-row @selection-change="onSelect">
        <el-table-column type="selection" width="48" />
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column label="封面" width="72">
          <template #default="{ row }">
            <div class="cover-thumb">
              <img :src="coverUrlOf(row) || placeholderUrl" alt="" @error="onImgError" />
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="title" label="标题" min-width="220" />
        <el-table-column prop="author" label="作者" min-width="160" />
        <el-table-column label="标签" min-width="220">
          <template #default="{ row }">
            <template v-for="t in (row.tags || [])" :key="t.id">
              <el-tag size="small" :type="tagTypeFor(t.type)" style="margin-right:4px;margin-bottom:4px">{{ t.name }}</el-tag>
            </template>
          </template>
        </el-table-column>
        <el-table-column label="连载状态" width="120">
          <template #default="{ row }">
            <el-tag :type="row.status===1 ? 'success' : 'warning'" size="small">
              {{ row.status===1 ? '已完结' : '连载中' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="审核状态" width="120">
          <template #default="{ row }">
            <el-tag :type="auditTagType(row.audit_status)" size="small">{{ auditLabel(row.audit_status) }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="created_at" label="创建时间" min-width="160" />
        <el-table-column prop="updated_at" label="更新时间" min-width="160" />
        <el-table-column label="操作" width="340" fixed="right">
          <template #default="{ row }">
            <template v-for="btn in actionButtonsRow" :key="btn.id">
              <el-button :type="resolveBtnType(btn)" link @click="onActionRow(btn, row)">
                <el-icon v-if="resolveIcon(btn.icon)" :size="16" style="margin-right:6px"><component :is="resolveIcon(btn.icon)" /></el-icon>
                {{ btn.name }}
              </el-button>
            </template>
            <el-button type="warning" link @click="openAudit([row.id])">审核</el-button>
            <el-button type="success" link @click="goChapters(row)">章节列表</el-button>
            <el-button link @click="openTags(row)">设置标签</el-button>
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

    <el-dialog v-model="showForm" :title="formMode==='add'?'新建小说':'编辑小说'" width="680px" draggable>
      <el-form :model="form" label-width="100px">
        <el-tabs v-model="activeTab">
          <el-tab-pane label="基本信息" name="basic">
            <el-form-item label="标题"><el-input v-model="form.title" /></el-form-item>
            <el-form-item label="作者"><el-input v-model="form.author" /></el-form-item>
            <el-form-item label="分类">
              <el-select v-model="form.category_id" placeholder="选择分类" style="width:180px">
                <el-option v-for="c in categories" :key="c.id" :label="c.name" :value="c.id" />
              </el-select>
            </el-form-item>
            <el-form-item label="连载状态"><el-select v-model="form.status" style="width:180px"><el-option :value="0" label="连载中" /><el-option :value="1" label="已完结" /></el-select></el-form-item>
            <el-form-item label="审核状态"><el-tag :type="auditTagType(form.audit_status)" size="small">{{ auditLabel(form.audit_status) }}</el-tag></el-form-item>
            <el-form-item label="封面图片">
              <div class="cover-field">
                <div class="cover-card">
                  <div v-if="previewUrl" class="cover-img"><img :src="previewUrl || placeholderUrl" alt="" @error="onImgError" /></div>
                  <div v-else class="cover-placeholder">
                    <el-icon :size="28"><component :is="(Icons as any).PictureFilled || (Icons as any).Picture" /></el-icon>
                  </div>
                  <div class="cover-actions">
                    <el-upload :show-file-list="false" :http-request="onUpload" :before-upload="beforeUpload" accept="image/*">
                      <el-button size="small" type="primary">更换</el-button>
                    </el-upload>
                    <el-button size="small" @click="clearCover" :disabled="!form.cover && !previewUrl">清除</el-button>
                  </div>
                  <el-progress v-if="uploadPct>0 && uploadPct<100" :percentage="uploadPct" :stroke-width="4" :show-text="false" class="cover-progress" />
                </div>
                <div class="cover-right">
                  <el-input v-model="form.cover" placeholder="相对路径，如 uploads/20251217/xxx.jpg" />
                  <div class="cover-tip">建议尺寸 240×320，大小 ≤ 2MB，仅保存相对路径</div>
                </div>
              </div>
            </el-form-item>
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

    <el-dialog v-model="showAudit" title="小说审核" width="520px">
      <el-form :model="audit" label-width="100px">
        <div style="margin-bottom:8px">待审核：{{ audit.ids.length }} 本</div>
        <el-form-item label="审核结果">
          <el-radio-group v-model="audit.status">
            <el-radio :label="1">已通过</el-radio>
            <el-radio :label="2">已拒绝</el-radio>
            <el-radio :label="3">已下线</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="备注">
          <el-input v-model="audit.reason" type="textarea" :autosize="{ minRows: 3, maxRows: 6 }" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="showAudit=false">取消</el-button>
        <el-button type="primary" @click="submitAudit">提交审核</el-button>
      </template>
    </el-dialog>

    <el-dialog v-model="showTags" title="设置标签" width="640px">
      <div v-loading="tagsLoading" style="display:grid;gap:12px">
        <div v-for="g in tagGroups" :key="g.type" style="border:1px solid var(--nc-border);border-radius:8px;padding:8px">
          <div style="font-weight:600;margin-bottom:6px">{{ g.label }}</div>
          <el-checkbox-group v-model="selectedTags[g.type]">
            <el-checkbox v-for="c in g.children" :key="c.id" :label="c.id">{{ c.name }}</el-checkbox>
          </el-checkbox-group>
        </div>
      </div>
      <template #footer>
        <el-button @click="showTags=false">取消</el-button>
        <el-button type="primary" @click="submitTags">保存标签</el-button>
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
import { fetchNovelList, createNovel, updateNovel, deleteNovel, fetchNovelDetail, reviewNovels, auditNovel, bindNovelTags } from '../api/novels'
import { uploadFile } from '../api/upload'
import { fetchTagOptions } from '../api/tags'
import { fetchCategoryOptions } from '../api/categories'

const loading = ref(true)
const rows = ref<any[]>([])
const total = ref(0)
const page = ref(1)
const limit = ref(10)
const kw = ref('')
const showForm = ref(false)
const formMode = ref<'add'|'edit'>('add')
const form = ref<any>({ title: '', author: '', category_id: '', status: 0, audit_status: 0, cover: '', seo_title: '', seo_keywords: '', seo_description: '' })
const previewUrl = computed(() => form.value._cover_url || '')
const uploadPct = ref(0)
const showAudit = ref(false)
const audit = ref<any>({ ids: [] as Array<number|string>, status: 1, reason: '' })
const selected = ref<any[]>([])
const categories = ref<any[]>([])
const activeTab = ref('basic')
const showTags = ref(false)
const currentTagNovelId = ref<number|string>('')
const tagGroups = ref<any[]>([])
const tagsLoading = ref(false)
const selectedTags = ref<Record<string, Array<number|string>>>({})
const router = useRouter()
const auth = useAuthStore()
const actionButtons = computed(() => {
  const path = router.currentRoute.value.path
  function matchNode(list: any[]): any | null {
    for (const m of list || []) {
      const p = m?.path || m?.route || ''
      if (p === path || m?.name === '小说管理') return m
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
  return rows.value.filter((x) => (x.title || '').toLowerCase().includes(k) || (x.author || '').toLowerCase().includes(k))
})

async function load() {
  loading.value = true
  try {
    const data = await fetchNovelList({ page: page.value, limit: limit.value, kw: kw.value })
    rows.value = data.list || []
    total.value = Number(data.count || 0)
    try {
      const cats = await fetchCategoryOptions(1)
      categories.value = cats || []
    } catch (_) {}
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

function tagTypeFor(type?: string){
  const m: Record<string, string> = { theme: 'success', plot: 'warning', role: 'info', r18: 'danger', status: 'warning', other: '' }
  const v = (type || '').toLowerCase()
  const t = m[v] || ''
  return (t as any)
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
  const rmap: Record<string, Function> = { edit: () => onEdit(row), update: () => onEdit(row), delete: () => onDelete(row), remove: () => onDelete(row), view: () => onEdit(row), detail: () => onEdit(row), review: () => openAudit([row.id]), audit: () => openAudit([row.id]), approve: () => { audit.value = { ids: [row.id], status: 1, reason: '' }; showAudit.value = true }, reject: () => { audit.value = { ids: [row.id], status: 2, reason: '' }; showAudit.value = true } }
  const fn = rmap[code] || rmap[name] || null
  if (fn) fn()
}

function reload(){ load() }

function onAdd(){
  formMode.value = 'add'
  form.value = { title: '', author: '', category_id: '', status: 0, audit_status: 0, cover: '', _cover_url: '', seo_title: '', seo_keywords: '', seo_description: '' }
  activeTab.value = 'basic'
  showForm.value = true
}

async function onEdit(row: any){
  formMode.value = 'edit'
  try {
    const d: any = await fetchNovelDetail(row.id)
    form.value = { id: d.id, title: d.title, author: d.author || '', category_id: d.category_id ?? row.category_id ?? '', status: Number(d.status ?? row.status ?? 0), audit_status: Number(d.audit_status ?? row.audit_status ?? 0), cover: d.cover || '', _cover_url: d.cover_url || '', seo_title: d.seo_title || '', seo_keywords: d.seo_keywords || '', seo_description: d.seo_description || '' }
  } catch (_) {
    form.value = { id: row.id, title: row.title, author: row.author, category_id: row.category_id ?? '', status: Number(row.status ?? 0), audit_status: Number(row.audit_status ?? 0), cover: row.cover || '', _cover_url: row.cover_url || '', seo_title: '', seo_keywords: '', seo_description: '' }
  }
  activeTab.value = 'basic'
  showForm.value = true
}

function auditLabel(s: any){
  const v = Number(s ?? 0)
  if (v === 1) return '已通过'
  if (v === 2) return '已拒绝'
  if (v === 3) return '已下线'
  return '待审'
}

function auditTagType(s: any){
  const v = Number(s ?? 0)
  if (v === 1) return 'success'
  if (v === 2) return 'danger'
  if (v === 3) return 'info'
  return 'warning'
}

function onSelect(rows: any[]){ selected.value = rows || [] }

function openAudit(ids?: Array<number|string>){
  const arr = Array.isArray(ids) && ids.length ? ids : selected.value.map((r) => r.id)
  if (!arr.length) { ElMessage.error('请先选择要审核的小说'); return }
  audit.value = { ids: arr, status: 1, reason: '' }
  showAudit.value = true
}

async function submitAudit(){
  try {
    if (!audit.value.ids.length) { ElMessage.error('未选择小说'); return }
    let ok = 0
    if (audit.value.ids.length === 1) {
      const res = await auditNovel({ id: audit.value.ids[0], audit_status: audit.value.status, reason: audit.value.reason })
      ok = res?.code === 200 ? 1 : 0
      if (res?.code !== 200) { ElMessage.error(res?.msg || '审核失败'); return }
    } else {
      const results = await Promise.all(
        audit.value.ids.map((id: any) => auditNovel({ id, audit_status: audit.value.status, reason: audit.value.reason }).catch((e: any) => e?.response?.data || { code: 500 }))
      )
      ok = results.filter((r: any) => r?.code === 200).length
      if (ok === 0) { ElMessage.error('审核失败'); return }
    }
    ElMessage.success(`已审核 ${ok}/${audit.value.ids.length}`)
    showAudit.value = false
    load()
  } catch (e: any) {
    const resp = e?.response?.data
    ElMessage.error(resp?.message || resp?.msg || '审核失败')
  }
}

function openTags(row: any){
  currentTagNovelId.value = row?.id
  showTags.value = true
  tagsLoading.value = true
  tagGroups.value = []
  selectedTags.value = {}
  const types = ['theme','plot','role','r18','status','other']
  Promise.all(types.map((t) => fetchTagOptions(t).catch(() => [])))
    .then((groups) => {
      const merged: any[] = []
      groups.forEach((arr: any) => {
        (arr || []).forEach((g: any) => merged.push(g))
      })
      tagGroups.value = merged
      const existing = Array.isArray(row?.tags) ? row.tags : []
      const selectedByType: Record<string, Array<number|string>> = {}
      existing.forEach((t: any) => {
        const ty = String(t?.type || 'other')
        if (!selectedByType[ty]) selectedByType[ty] = []
        selectedByType[ty].push(t.id)
      })
      merged.forEach((g: any) => {
        const ty = String(g.type || 'other')
        selectedTags.value[ty] = (selectedByType[ty] || [])
      })
    })
    .finally(() => { tagsLoading.value = false })
}

async function submitTags(){
  try {
    const ids: Array<number|string> = Object.values(selectedTags.value).flat() as any
    const res = await bindNovelTags(currentTagNovelId.value, ids)
    if (res?.code === 200) { ElMessage.success('标签已设置'); showTags.value = false } else { ElMessage.error(res?.msg || '设置失败') }
  } catch (e: any) {
    const resp = e?.response?.data
    ElMessage.error(resp?.message || resp?.msg || '设置失败')
  }
}

async function saveForm(){
  try {
    if (!form.value.title) { ElMessage.error('请填写标题'); return }
    const payload = { title: form.value.title, author: form.value.author, category_id: form.value.category_id, status: form.value.status, cover: form.value.cover, seo_title: form.value.seo_title, seo_keywords: form.value.seo_keywords, seo_description: form.value.seo_description }
    const res = formMode.value==='add' ? await createNovel(payload) : await updateNovel(form.value.id, payload)
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

async function onUpload(opt: any){
  try{
    const file: File = opt?.file
    if (!file) return opt?.onError?.(new Error('未选择文件'))
    const r = await uploadFile(file, { onProgress: (p) => { uploadPct.value = p } })
    form.value.cover = r?.key || ''
    form.value._cover_url = r?.url || ''
    ElMessage.success('上传成功')
    opt?.onSuccess?.(r)
  }catch(e:any){
    const resp=e?.response?.data
    ElMessage.error(resp?.message||resp?.msg||'上传失败')
    opt?.onError?.(e)
  } finally { setTimeout(() => { uploadPct.value = 0 }, 300) }
}

function clearCover(){ form.value.cover=''; form.value._cover_url='' }
function beforeUpload(file: File){
  const isImg = /^image\//.test(file.type || '')
  const okSize = file.size <= 2 * 1024 * 1024
  if (!isImg) ElMessage.error('仅支持图片文件')
  if (!okSize) ElMessage.error('图片大小需 ≤ 2MB')
  return isImg && okSize
}

  async function onDelete(row: any){
    try {
      await ElMessageBox.confirm('确定删除该小说？', '提示', { type: 'warning' })
      const res = await deleteNovel(row.id)
      if (res?.code === 200) { ElMessage.success('已删除'); load() } else { ElMessage.error(res?.msg || '删除失败') }
    } catch (_) {}
  }

  function goChapters(row: any){
    const id = row?.id
    if (!id) return
    router.push({ name: 'content-novel-chapters', params: { id } })
  }
  function coverUrlOf(row: any){
    const key = String(row?.cover || '')
    const full = String(row?.cover_url || '')
    if (full && /^https?:\/\//i.test(full)) return full
    if (key && /^https?:\/\//i.test(key)) return key
    const base = String((auth.context || {}).image_base_url || '')
    if (key && base) return `${base.replace(/\/$/, '')}/${key.replace(/^\//, '')}`
    return ''
  }
  const placeholderUrl = `data:image/svg+xml;utf8,` + encodeURIComponent(`<svg xmlns="http://www.w3.org/2000/svg" width="120" height="160"><rect width="100%" height="100%" fill="#f3f4f6"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#9ca3af" font-size="12">No Image</text></svg>`)
  function onImgError(e: Event){ const img = e?.target as HTMLImageElement; if (img && img.src !== placeholderUrl) img.src = placeholderUrl }
</script>

<style scoped>
 .wrap { display: grid; gap: 12px }
 .toolbar { display: grid; gap: 8px }
 .toolbar-grid { display: grid; grid-template-columns: 1fr auto; gap: 12px; align-items: center }
 .toolbar-grid .actions { justify-self: end; display: inline-flex; gap: 8px }
.subline { color: var(--nc-muted); font-size: 12px }
.pager { display: flex; justify-content: flex-end; padding-top: 12px }
.cover-field{ display: grid; grid-template-columns: auto 1fr; gap: 12px; align-items: start }
.cover-card{ position: relative; width: 120px; height: 160px; border: 1px dashed var(--nc-border); border-radius: 8px; background: #fafafa; display:flex; align-items:center; justify-content:center; overflow: hidden }
.cover-img, .cover-placeholder{ position: absolute; inset: 0; display:flex; align-items:center; justify-content:center }
.cover-img img{ width: 100%; height: 100%; object-fit: cover }
.cover-actions{ position: absolute; bottom: 6px; left: 6px; right: 6px; display:flex; gap:6px; justify-content: space-between; }
.cover-progress{ position: absolute; bottom: 0; left: 0; right: 0 }
.cover-right{ display: grid; gap: 6px; align-items: center }
.cover-tip{ color: var(--nc-muted); font-size: 12px }
.cover-thumb{ width: 48px; height: 64px; border: 1px solid var(--nc-border); border-radius: 4px; overflow: hidden; background: #fafafa }
.cover-thumb img{ width: 100%; height: 100%; object-fit: cover }
</style>

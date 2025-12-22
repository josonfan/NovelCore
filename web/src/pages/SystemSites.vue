<template>
  <div class="wrap">
    <el-card shadow="never" class="toolbar">
      <div class="toolbar-grid">
        <el-input v-model="kw" placeholder="搜索名称或编码" clearable />
        <div class="actions">
          <el-button @click="reload" :loading="loading">刷新</el-button>
          <el-button type="primary" @click="onAdd">新建</el-button>
        </div>
      </div>
      <div class="subline">共 {{ total }} 条</div>
    </el-card>
    <el-card shadow="hover" class="table-card">
      <el-table :data="filtered" v-loading="loading" border size="small" stripe>
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="name" label="名称" min-width="160" />
        <el-table-column prop="code" label="编码" min-width="140" />
        <el-table-column prop="base_api_url" label="基础API" min-width="200" />
        <el-table-column prop="primary_domain" label="主域名" min-width="160" />
        <el-table-column label="启用" width="80">
          <template #default="{ row }">
            <el-tag size="small" :type="row.is_active===1?'success':'danger'">{{ row.is_active===1?'启用':'停用' }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="remark" label="备注" min-width="160" />
        <el-table-column prop="created_at" label="创建时间" min-width="160" />
        <el-table-column label="操作" width="360" fixed="right">
          <template #default="{ row }">
            <el-button link @click="onDetail(row)">详情</el-button>
            <el-button link type="primary" @click="onEdit(row)">编辑</el-button>
            <el-button link type="warning" @click="onToggle(row)">{{ row.is_active===1?'停用':'启用' }}</el-button>
            <!-- <el-button link type="success" @click="onConfig(row)">站点配置</el-button> -->
            <el-button link type="success" @click="onInit(row)">初始化数据</el-button>
            <el-button link type="danger" @click="onDelete(row)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>
      <div class="pager">
        <el-pagination background layout="prev, pager, next, jumper, sizes, total" :page-size="limit" :current-page="page" :total="total" @current-change="onPage" @size-change="onSize" :page-sizes="[10,20,50]" />
      </div>
    </el-card>
    <el-dialog v-model="showForm" :title="formMode==='add'?'新建站点':'编辑站点'" width="560px">
      <el-form :model="form" label-width="100px">
        <el-form-item label="名称"><el-input v-model="form.name" /></el-form-item>
        <el-form-item label="编码"><el-input v-model="form.code" :disabled="formMode==='edit'" /></el-form-item>
        <el-form-item label="基础API"><el-input v-model="form.base_api_url" /></el-form-item>
        <el-form-item label="主域名"><el-input v-model="form.primary_domain" /></el-form-item>
        <el-form-item v-if="formMode==='add'" label="API令牌"><el-input v-model="form.api_token" /></el-form-item>
        <el-form-item label="启用"><el-switch v-model="form.is_active" :active-value="1" :inactive-value="0" /></el-form-item>
        <el-form-item label="备注"><el-input v-model="form.remark" /></el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="showForm=false">取消</el-button>
        <el-button type="primary" @click="saveForm">保存</el-button>
      </template>
    </el-dialog>

    <el-dialog v-model="showDetail" title="站点详情" width="800px">
      <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px">
        <el-button :loading="healthLoading" @click="checkHealth">检测状态</el-button>
        <template v-if="health">
          <el-tag :type="health.db ? 'success' : 'danger'">DB：{{ health.db ? '正常' : '异常' }}</el-tag>
          <el-tag :type="health.cache ? 'success' : 'danger'">Cache：{{ health.cache ? '正常' : '异常' }}</el-tag>
          <span style="color: var(--nc-muted); font-size: 12px">语言：{{ health.lang }}，时间：{{ formatTime(health.ts) }}</span>
        </template>
        <el-tag v-if="healthFailed" type="danger">检测失败</el-tag>
      </div>
      <div v-if="health && health.queue" style="display:grid; gap:8px; margin-bottom:8px">
        <div style="font-weight:600">队列状态</div>
        <div style="display:flex; flex-wrap:wrap; gap:8px">
          <el-tag v-for="(val, key) in health.queue" :key="key" :type="Number(val)===0 ? 'success' : 'warning'">{{ key }}：{{ val }}</el-tag>
        </div>
      </div>
      <el-descriptions :column="2" border>
        <el-descriptions-item label="ID">{{ detail?.id }}</el-descriptions-item>
        <el-descriptions-item label="名称">{{ detail?.name }}</el-descriptions-item>
        <el-descriptions-item label="编码">{{ detail?.code }}</el-descriptions-item>
        <el-descriptions-item label="基础API">{{ detail?.base_api_url }}</el-descriptions-item>
        <el-descriptions-item label="主域名">{{ detail?.primary_domain }}</el-descriptions-item>
        <el-descriptions-item label="启用">{{ detail?.is_active }}</el-descriptions-item>
        <el-descriptions-item label="备注">{{ detail?.remark }}</el-descriptions-item>
        <el-descriptions-item label="创建时间">{{ detail?.created_at }}</el-descriptions-item>
        <el-descriptions-item label="更新时间">{{ detail?.updated_at }}</el-descriptions-item>
      </el-descriptions>
      
    </el-dialog>

    <el-dialog v-model="showConfig" title="站点配置" width="860px">
      <SystemSiteConfig :siteId="currentSiteId" />
    </el-dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch, onUnmounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { useRouter } from 'vue-router'
import SystemSiteConfig from './SystemSiteConfig.vue'
import { fetchSiteList, fetchSiteDetail, createSite, updateSite, toggleSite, deleteSite, initSite } from '../api/sites'
import { http } from '../api/http'

const router = useRouter()
const page = ref(1)
const limit = ref(10)
const total = ref(0)
const rows = ref<any[]>([])
const loading = ref(false)
const kw = ref('')
const showForm = ref(false)
const formMode = ref<'add'|'edit'>('add')
const form = ref<any>({ name: '', code: '', base_api_url: '', primary_domain: '', api_token: '', is_active: 1, remark: '' })
const showDetail = ref(false)
const detail = ref<any>(null)
const showConfig = ref(false)
const currentSiteId = ref<number|string>('')
const health = ref<any>(null)
const healthLoading = ref(false)
const healthFailed = ref(false)
let healthTimer: any = null
function formatTime(ts: any){
  const n = Number(ts || 0)
  if (!n) return ''
  const d = new Date(n * 1000)
  const y = d.getFullYear()
  const m = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')
  const hh = String(d.getHours()).padStart(2, '0')
  const mm = String(d.getMinutes()).padStart(2, '0')
  const ss = String(d.getSeconds()).padStart(2, '0')
  return `${y}-${m}-${day} ${hh}:${mm}:${ss}`
}

function stopHealthTimer(){
  if (healthTimer) { clearInterval(healthTimer); healthTimer = null }
}

const filtered = computed(() => {
  if (!kw.value) return rows.value
  const k = kw.value.toLowerCase()
  return rows.value.filter((x) => (x.name || '').toLowerCase().includes(k) || (x.code || '').toLowerCase().includes(k))
})

async function load() {
  loading.value = true
  try {
    const data = await fetchSiteList({ page: page.value, limit: limit.value })
    rows.value = data?.list || []
    total.value = data?.count || 0
  } finally {
    loading.value = false
  }
}
function onPage(p: number){ page.value = p; load() }
function onSize(s: number){ limit.value = s; page.value = 1; load() }
function reload(){ load() }

function onAdd(){ formMode.value='add'; form.value={ name:'', code:'', base_api_url:'', primary_domain:'', api_token:'', is_active:1, remark:'' }; showForm.value=true }
function onEdit(row:any){ formMode.value='edit'; form.value={ ...row, api_token:'' }; showForm.value=true }

async function saveForm(){
  try{
    if(!form.value.name){ ElMessage.error('请填写名称'); return }
    if(formMode.value==='add'){
      const res = await createSite(form.value)
      if(res?.code===200){ ElMessage.success('已创建'); showForm.value=false; load() } else { ElMessage.error(res?.msg||'创建失败') }
    }else{
      const payload = { name: form.value.name, base_api_url: form.value.base_api_url, primary_domain: form.value.primary_domain, is_active: form.value.is_active, remark: form.value.remark }
      const res = await updateSite(form.value.id, payload)
      if(res?.code===200){ ElMessage.success('已更新'); showForm.value=false; load() } else { ElMessage.error(res?.msg||'更新失败') }
    }
  }catch(e:any){ const resp=e?.response?.data; ElMessage.error(resp?.message||resp?.msg||'保存失败') }
}

async function onToggle(row:any){
  const res = await toggleSite(row.id, row.is_active===1?0:1)
  if(res?.code===200){ ElMessage.success('已更新'); load() } else { ElMessage.error(res?.msg||'操作失败') }
}

async function onDelete(row:any){
  try{ await ElMessageBox.confirm('确认删除该站点？','提示',{ type:'warning' }); const res=await deleteSite(row.id); if(res?.code===200){ ElMessage.success('已删除'); load() } else { ElMessage.error(res?.msg||'删除失败') } }catch(_){}
}

async function onDetail(row:any){ const d=await fetchSiteDetail(row.id); detail.value=d; health.value=null; healthFailed.value=false; showDetail.value=true }

function onConfig(row:any){
  const token = localStorage.getItem('token') || ''
  if (!token) { router.push('/login'); return }
  currentSiteId.value = row.id
  showConfig.value = true
}

async function onInit(row:any){
  try{
    const res = await initSite(row.id)
    if (res?.code === 200) {
      ElMessage.success(res?.msg || '初始化成功')
    } else {
      ElMessage.error(res?.msg || '初始化失败')
    }
  } catch(e:any){
    const resp = e?.response?.data
    ElMessage.error(resp?.message || resp?.msg || '初始化失败')
  }
}

async function checkHealth(silent: boolean = false){
  try{
    if (!detail.value) return
    const base = String(detail.value.base_api_url || '').replace(/\/$/, '')
    const token = String(detail.value.api_token || '')
    if (!base) { if (!silent) ElMessage.error('未配置基础API地址'); healthFailed.value = true; stopHealthTimer(); return }
    if (!token) { if (!silent) ElMessage.error('未配置API令牌'); healthFailed.value = true; stopHealthTimer(); return }
    healthLoading.value = !silent
    const res = await http.get(`${base}/Health/index`, { headers: { 'X-Api-Token': token } })
    const data = res?.data?.data || null
    health.value = data
    healthFailed.value = false
    if (!silent) ElMessage.success('检测完成')
  } catch(e:any){
    healthFailed.value = true
    stopHealthTimer()
    if (!silent) {
      const resp = e?.response?.data
      ElMessage.error(resp?.message || resp?.msg || '检测失败')
    }
  } finally {
    healthLoading.value = false
  }
}

watch(showDetail, (v) => {
  if (v) {
    checkHealth(true)
    if (healthTimer) clearInterval(healthTimer)
    healthTimer = setInterval(() => checkHealth(true), 5000)
  } else {
    if (healthTimer) { clearInterval(healthTimer); healthTimer = null }
  }
})

onUnmounted(() => {
  if (healthTimer) { clearInterval(healthTimer); healthTimer = null }
})

onMounted(load)
</script>

<style scoped>
.wrap{ display:grid; gap:12px }
.toolbar{ display:grid; gap:8px }
.toolbar-grid{ display:grid; grid-template-columns: 1fr auto; gap:12px; align-items:center }
.toolbar-grid .actions{ display:inline-flex; gap:8px; justify-self:end }
.subline{ color: var(--nc-muted); font-size:12px }
.pager{ display:flex; justify-content:flex-end; margin-top:12px }
</style>

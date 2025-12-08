<template>
  <div class="wrap">
    <el-card shadow="never" class="toolbar">
      <div class="toolbar-grid">
        <el-select v-model="siteId" placeholder="选择站点" clearable filterable style="width:220px">
          <el-option v-for="s in siteOptions" :key="s.value" :label="s.label" :value="s.value" />
        </el-select>
        <el-input v-model="kw" placeholder="搜索域名" clearable />
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
        <el-table-column prop="site_id" label="站点ID" width="100" />
        <el-table-column prop="domain" label="域名" min-width="180" />
        <el-table-column label="类型" width="100">
          <template #default="{ row }"><el-tag size="small">{{ row.type }}</el-tag></template>
        </el-table-column>
        <el-table-column prop="priority" label="优先级" width="100" />
        <el-table-column label="启用" width="80">
          <template #default="{ row }"><el-tag size="small" :type="row.is_active===1?'success':'danger'">{{ row.is_active===1?'启用':'停用' }}</el-tag></template>
        </el-table-column>
        <el-table-column prop="remark" label="备注" min-width="160" />
        <el-table-column prop="created_at" label="创建时间" min-width="160" />
        <el-table-column label="操作" width="200" fixed="right">
          <template #default="{ row }">
            <el-button link @click="onDetail(row)">详情</el-button>
            <el-button link type="primary" @click="onEdit(row)">编辑</el-button>
            <el-button link type="danger" @click="onDelete(row)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>
      <div class="pager">
        <el-pagination background layout="prev, pager, next, jumper, sizes, total" :page-size="limit" :current-page="page" :total="total" @current-change="onPage" @size-change="onSize" :page-sizes="[10,20,50]" />
      </div>
    </el-card>

    <el-dialog v-model="showForm" :title="formMode==='add'?'新建域名':'编辑域名'" width="560px">
      <el-form :model="form" label-width="100px">
        <el-form-item label="站点">
          <el-select v-model="form.site_id" placeholder="选择站点">
            <el-option v-for="s in siteOptions" :key="s.value" :label="s.label" :value="s.value" />
          </el-select>
        </el-form-item>
        <el-form-item label="域名"><el-input v-model="form.domain" /></el-form-item>
        <el-form-item label="类型">
          <el-radio-group v-model="form.type">
            <el-radio label="api">api</el-radio>
            <el-radio label="image">image</el-radio>
            <el-radio label="share">share</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="优先级"><el-input v-model="form.priority" /></el-form-item>
        <el-form-item label="启用"><el-switch v-model="form.is_active" :active-value="1" :inactive-value="0" /></el-form-item>
        <el-form-item label="备注"><el-input v-model="form.remark" /></el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="showForm=false">取消</el-button>
        <el-button type="primary" @click="saveForm">保存</el-button>
      </template>
    </el-dialog>

    <el-dialog v-model="showDetail" title="域名详情" width="560px">
      <el-descriptions :column="2" border>
        <el-descriptions-item label="ID">{{ detail?.id }}</el-descriptions-item>
        <el-descriptions-item label="站点ID">{{ detail?.site_id }}</el-descriptions-item>
        <el-descriptions-item label="域名">{{ detail?.domain }}</el-descriptions-item>
        <el-descriptions-item label="类型">{{ detail?.type }}</el-descriptions-item>
        <el-descriptions-item label="优先级">{{ detail?.priority }}</el-descriptions-item>
        <el-descriptions-item label="启用">{{ detail?.is_active }}</el-descriptions-item>
        <el-descriptions-item label="备注">{{ detail?.remark }}</el-descriptions-item>
        <el-descriptions-item label="创建时间">{{ detail?.created_at }}</el-descriptions-item>
        <el-descriptions-item label="更新时间">{{ detail?.updated_at }}</el-descriptions-item>
      </el-descriptions>
    </el-dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { fetchDomainList, fetchDomainDetail, createDomain, updateDomain, deleteDomain } from '../api/domainlist'
import { fetchSiteList } from '../api/sites'

const page = ref(1)
const limit = ref(10)
const total = ref(0)
const rows = ref<any[]>([])
const loading = ref(false)
const kw = ref('')
const showForm = ref(false)
const formMode = ref<'add'|'edit'>('add')
const form = ref<any>({ site_id: 0, domain: '', type: 'api', priority: 0, is_active: 1, remark: '' })
const showDetail = ref(false)
const detail = ref<any>(null)
const siteId = ref<number|undefined>(undefined)
const siteOptions = ref<Array<{label:string; value:number}>>([])

const filtered = computed(() => {
  let list = rows.value
  if (siteId.value) list = list.filter(x => Number(x.site_id) === Number(siteId.value))
  if (!kw.value) return list
  const k = kw.value.toLowerCase()
  return list.filter((x) => (x.domain || '').toLowerCase().includes(k))
})

async function load() {
  loading.value = true
  try {
    const data = await fetchDomainList({ page: page.value, limit: limit.value })
    rows.value = data?.list || []
    total.value = data?.count || 0
  } finally { loading.value = false }
}

async function loadSites(){
  const data = await fetchSiteList({ page: 1, limit: 100 })
  siteOptions.value = (data?.list || []).map((x:any)=>({ label: `${x.name}(${x.code})`, value: Number(x.id) }))
}

function onPage(p:number){ page.value=p; load() }
function onSize(s:number){ limit.value=s; page.value=1; load() }
function reload(){ load() }

function onAdd(){ formMode.value='add'; form.value={ site_id: (siteId.value||0), domain:'', type:'api', priority:0, is_active:1, remark:'' }; showForm.value=true }
function onEdit(row:any){ formMode.value='edit'; form.value={ ...row }; showForm.value=true }

async function saveForm(){
  try{
    if(!form.value.site_id || !form.value.domain){ ElMessage.error('请填写站点与域名'); return }
    if(formMode.value==='add'){
      const res = await createDomain(form.value)
      if(res?.code===200){ ElMessage.success('已创建'); showForm.value=false; load() } else { ElMessage.error(res?.msg||'创建失败') }
    }else{
      const res = await updateDomain(form.value.id, form.value)
      if(res?.code===200){ ElMessage.success('已更新'); showForm.value=false; load() } else { ElMessage.error(res?.msg||'更新失败') }
    }
  }catch(e:any){ const resp=e?.response?.data; ElMessage.error(resp?.message||resp?.msg||'保存失败') }
}

async function onDelete(row:any){
  try{ await ElMessageBox.confirm('确认删除该域名？','提示',{ type:'warning' }); const res=await deleteDomain(row.id); if(res?.code===200){ ElMessage.success('已删除'); load() } else { ElMessage.error(res?.msg||'删除失败') } }catch(_){}
}

async function onDetail(row:any){ const d=await fetchDomainDetail(row.id); detail.value=d; showDetail.value=true }

onMounted(load)
onMounted(loadSites)
</script>

<style scoped>
.wrap{ display:grid; gap:12px }
.toolbar{ display:grid; gap:8px }
.toolbar-grid{ display:grid; grid-template-columns: 220px 1fr auto; gap:12px; align-items:center }
.toolbar-grid .actions{ display:inline-flex; gap:8px; justify-self:end }
.subline{ color: var(--nc-muted); font-size:12px }
.pager{ display:flex; justify-content:flex-end; margin-top:12px }
</style>


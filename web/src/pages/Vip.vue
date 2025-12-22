<template>
  <div class="wrap">
    <el-card shadow="never" class="toolbar">
      <div class="toolbar-grid">
        <el-input v-model="kw" placeholder="搜索名称或描述" clearable />
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
        <el-table-column prop="name" label="套餐名称" min-width="160" />
        <el-table-column prop="descript" label="描述" min-width="160" />
        <el-table-column prop="days" label="天数" width="100" />
        <el-table-column prop="price" label="价格" width="120" />
        <el-table-column prop="old_price" label="原价" width="120" />
        <el-table-column prop="sort" label="排序" width="100" />
        <el-table-column label="状态" width="100">
          <template #default="{ row }">
            <el-tag size="small" :type="row.status===1?'success':'danger'">{{ row.status===1?'正常':'停用' }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="热门" width="100">
          <template #default="{ row }">
            <el-tag size="small" :type="row.is_hot===1?'warning':'info'">{{ row.is_hot===1?'是':'否' }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="sold_num" label="销量" width="100" />
        <el-table-column prop="sold_total" label="售额" width="120" />
        <el-table-column prop="return_total" label="返额" width="120" />
        <el-table-column prop="return_num" label="返次" width="100" />
        <el-table-column prop="created_at" label="创建时间" min-width="160" />
        <el-table-column prop="updated_at" label="更新时间" min-width="160" />
        <el-table-column label="操作" width="240" fixed="right">
          <template #default="{ row }">
            <el-button link @click="onDetail(row)">详情</el-button>
            <el-button link type="primary" @click="onEdit(row)">编辑</el-button>
            <el-button link type="warning" @click="onToggle(row)">{{ row.status===1?'停用':'启用' }}</el-button>
            <el-button link type="danger" @click="onDelete(row)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>
      <div class="pager">
        <el-pagination background layout="prev, pager, next, jumper, sizes, total" :page-size="limit" :current-page="page" :total="total" @current-change="onPage" @size-change="onSize" :page-sizes="[10,20,50]" />
      </div>
    </el-card>

    <el-dialog v-model="showForm" :title="formMode==='add'?'新建VIP套餐':'编辑VIP套餐'" width="720px">
      <el-form :model="form" label-width="120px" class="form">
        <el-form-item label="套餐名称"><el-input v-model="form.name" /></el-form-item>
        <el-form-item label="状态"><el-switch v-model="form.status" :active-value="1" :inactive-value="0" /></el-form-item>
        <el-form-item label="热门"><el-switch v-model="form.is_hot" :active-value="1" :inactive-value="2" /></el-form-item>
        <el-form-item label="天数"><el-input-number v-model="form.days" :min="1" /></el-form-item>
        <el-form-item label="价格"><el-input v-model="form.price" /></el-form-item>
        <el-form-item label="原价"><el-input v-model="form.old_price" /></el-form-item>
        <el-form-item label="排序"><el-input-number v-model="form.sort" :min="0" /></el-form-item>
        <el-form-item label="描述"><el-input v-model="form.descript" type="textarea" /></el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="showForm=false">取消</el-button>
        <el-button type="primary" @click="saveForm">保存</el-button>
      </template>
    </el-dialog>

    <el-dialog v-model="showDetail" title="套餐详情" width="640px">
      <el-descriptions :column="2" border>
        <el-descriptions-item label="ID">{{ detail?.id }}</el-descriptions-item>
        <el-descriptions-item label="套餐名称">{{ detail?.name }}</el-descriptions-item>
        <el-descriptions-item label="描述">{{ detail?.descript }}</el-descriptions-item>
        <el-descriptions-item label="天数">{{ detail?.days }}</el-descriptions-item>
        <el-descriptions-item label="价格">{{ detail?.price }}</el-descriptions-item>
        <el-descriptions-item label="原价">{{ detail?.old_price }}</el-descriptions-item>
        <el-descriptions-item label="排序">{{ detail?.sort }}</el-descriptions-item>
        <el-descriptions-item label="状态">{{ detail?.status===1?'正常':'停用' }}</el-descriptions-item>
        <el-descriptions-item label="热门">{{ detail?.is_hot===1?'是':'否' }}</el-descriptions-item>
        <el-descriptions-item label="销量">{{ detail?.sold_num }}</el-descriptions-item>
        <el-descriptions-item label="售额">{{ detail?.sold_total }}</el-descriptions-item>
        <el-descriptions-item label="返额">{{ detail?.return_total }}</el-descriptions-item>
        <el-descriptions-item label="返次">{{ detail?.return_num }}</el-descriptions-item>
        <el-descriptions-item label="创建时间">{{ detail?.created_at }}</el-descriptions-item>
        <el-descriptions-item label="更新时间">{{ detail?.updated_at }}</el-descriptions-item>
      </el-descriptions>
    </el-dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { fetchVipList, fetchVipDetail, createVip, updateVip, toggleVip, deleteVip } from '../api/vip'

const page = ref(1)
const limit = ref(10)
const total = ref(0)
const rows = ref<any[]>([])
const loading = ref(false)
const kw = ref('')
const showForm = ref(false)
const formMode = ref<'add'|'edit'>('add')
const form = ref<any>({ name: '', status: 1, is_hot: 2, days: 1, price: '0.00', old_price: '', sort: 0, descript: '' })
const showDetail = ref(false)
const detail = ref<any>(null)

const filtered = computed(() => {
  if (!kw.value) return rows.value
  const k = kw.value.toLowerCase()
  return rows.value.filter((x) => (x.name || '').toLowerCase().includes(k) || (x.descript || '').toLowerCase().includes(k))
})

async function load() {
  loading.value = true
  try {
    const data = await fetchVipList({ page: page.value, limit: limit.value })
    rows.value = data?.list || []
    total.value = data?.count || 0
  } catch (e: any) {
    const resp = e?.response?.data
    ElMessage.error(resp?.message || resp?.msg || '加载失败')
    rows.value = []
    total.value = 0
  } finally {
    loading.value = false
  }
}
function onPage(p: number){ page.value = p; load() }
function onSize(s: number){ limit.value = s; page.value = 1; load() }
function reload(){ load() }

function onAdd(){ formMode.value='add'; form.value={ name: '', status: 1, is_hot: 2, days: 1, price: '0.00', old_price: '', sort: 0, descript: '' }; showForm.value=true }
async function onEdit(row:any){
  formMode.value='edit'
  try{
    const d = await fetchVipDetail(row.id)
    form.value = {
      id: d?.id ?? row.id,
      name: d?.name ?? row.name ?? '',
      status: Number(d?.status ?? row.status ?? 1),
      is_hot: Number(d?.is_hot ?? row.is_hot ?? 2),
      days: Number(d?.days ?? row.days ?? 1),
      price: String(d?.price ?? row.price ?? '0.00'),
      old_price: String(d?.old_price ?? row.old_price ?? ''),
      sort: Number(d?.sort ?? row.sort ?? 0),
      descript: d?.descript ?? row.descript ?? '',
    }
  }catch(_){
    form.value = { id: row.id, name: row.name, status: Number(row.status ?? 1), is_hot: Number(row.is_hot ?? 2), days: Number(row.days ?? 1), price: String(row.price ?? '0.00'), old_price: String(row.old_price ?? ''), sort: Number(row.sort ?? 0), descript: row.descript ?? '' }
  }
  showForm.value = true
}

async function saveForm(){
  try{
    if(!form.value.name){ ElMessage.error('请填写套餐名称'); return }
    if(formMode.value==='add'){
      const res = await createVip(form.value)
      if(res?.code===200){ ElMessage.success('已创建'); showForm.value=false; load() } else { ElMessage.error(res?.msg||'创建失败') }
    }else{
      const payload = { ...form.value }; delete payload.id
      const res = await updateVip(form.value.id, payload)
      if(res?.code===200){ ElMessage.success('已更新'); showForm.value=false; load() } else { ElMessage.error(res?.msg||'更新失败') }
    }
  }catch(e:any){ const resp=e?.response?.data; ElMessage.error(resp?.message||resp?.msg||'保存失败') }
}

async function onToggle(row:any){
  const res = await toggleVip(row.id, row.status===1?0:1)
  if(res?.code===200){ ElMessage.success('已更新'); load() } else { ElMessage.error(res?.msg||'操作失败') }
}

async function onDelete(row:any){
  try{ await ElMessageBox.confirm('确认删除该套餐？','提示',{ type:'warning' }); const res=await deleteVip(row.id); if(res?.code===200){ ElMessage.success('已删除'); load() } else { ElMessage.error(res?.msg||'删除失败') } }catch(_){}
}

async function onDetail(row:any){ const d=await fetchVipDetail(row.id); detail.value=d; showDetail.value=true }

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

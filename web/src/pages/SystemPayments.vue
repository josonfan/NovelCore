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
        <el-table-column prop="name" label="渠道名称" min-width="160" />
        <el-table-column label="状态" width="100">
          <template #default="{ row }">
            <el-tag size="small" :type="row.status===1?'success':'danger'">{{ row.status===1?'正常':'停用' }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="USDT" width="100">
          <template #default="{ row }">
            <el-tag size="small" :type="row.is_usdt===1?'success':'info'">{{ row.is_usdt===1?'是':'否' }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="order_quantity" label="下单数" width="100" />
        <el-table-column prop="payment_quantity" label="支付数" width="100" />
        <el-table-column prop="place_order" label="下单额" width="120" />
        <el-table-column prop="payment" label="支付额" width="120" />
        <el-table-column prop="limit_price" label="限额" width="120" />
        <el-table-column prop="cycle_price" label="周期额" width="120" />
        <el-table-column prop="is_default" label="默认" width="90">
          <template #default="{ row }">
            <el-tag size="small" :type="row.is_default===1?'success':'info'">{{ row.is_default===1?'是':'否' }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="is_web" label="Web" width="90">
          <template #default="{ row }">
            <el-tag size="small" :type="row.is_web===1?'success':'info'">{{ row.is_web===1?'是':'否' }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="not_pc" label="PC禁用" width="100">
          <template #default="{ row }">
            <el-tag size="small" :type="row.not_pc===1?'warning':'info'">{{ row.not_pc===1?'是':'否' }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="icon_iden" label="图标" width="120" />
        <el-table-column prop="pay_url" label="支付地址" min-width="200" />
        <el-table-column prop="sup_order_url" label="补单地址" min-width="200" />
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

    <el-dialog v-model="showForm" :title="formMode==='add'?'新建渠道':'编辑渠道'" width="720px">
      <el-form :model="form" label-width="120px" class="form">
        <el-form-item label="渠道名称"><el-input v-model="form.name" /></el-form-item>
        <el-form-item label="状态"><el-switch v-model="form.status" :active-value="1" :inactive-value="0" /></el-form-item>
        <el-form-item label="USDT"><el-switch v-model="form.is_usdt" :active-value="1" :inactive-value="2" /></el-form-item>
        <el-form-item label="默认"><el-switch v-model="form.is_default" :active-value="1" :inactive-value="2" /></el-form-item>
        <el-form-item label="Web"><el-switch v-model="form.is_web" :active-value="1" :inactive-value="2" /></el-form-item>
        <el-form-item label="PC禁用"><el-switch v-model="form.not_pc" :active-value="1" :inactive-value="2" /></el-form-item>
        <el-form-item label="支付地址"><el-input v-model="form.pay_url" /></el-form-item>
        <el-form-item label="补单地址"><el-input v-model="form.sup_order_url" /></el-form-item>
        <el-form-item label="图标标识"><el-input v-model="form.icon_iden" /></el-form-item>
        <el-form-item label="排序"><el-input-number v-model="form.sort" :min="0" /></el-form-item>
        <el-form-item label="限额"><el-input v-model="form.limit_price" /></el-form-item>
        <el-form-item label="周期额"><el-input v-model="form.cycle_price" /></el-form-item>
        <el-form-item label="支付规则"><el-input v-model="form.pay_rules" type="textarea" /></el-form-item>
        <el-form-item label="备注"><el-input v-model="form.remarks" type="textarea" /></el-form-item>
        <el-form-item label="支付渠道ID"><el-input v-model="form.pay_id" /></el-form-item>
        <el-form-item label="SKey"><el-input v-model="form.skey" /></el-form-item>
        <el-form-item label="MD5 Key"><el-input v-model="form.md5_key" /></el-form-item>
        <el-form-item label="银行编码"><el-input v-model="form.pay_bankcode" /></el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="showForm=false">取消</el-button>
        <el-button type="primary" @click="saveForm">保存</el-button>
      </template>
    </el-dialog>

    <el-dialog v-model="showDetail" title="渠道详情" width="640px">
      <el-descriptions :column="2" border>
        <el-descriptions-item label="ID">{{ detail?.id }}</el-descriptions-item>
        <el-descriptions-item label="渠道名称">{{ detail?.name }}</el-descriptions-item>
        <el-descriptions-item label="USDT">{{ detail?.is_usdt===1?'是':'否' }}</el-descriptions-item>
        <el-descriptions-item label="状态">{{ detail?.status===1?'正常':'停用' }}</el-descriptions-item>
        <el-descriptions-item label="下单数">{{ detail?.order_quantity }}</el-descriptions-item>
        <el-descriptions-item label="支付数">{{ detail?.payment_quantity }}</el-descriptions-item>
        <el-descriptions-item label="下单额">{{ detail?.place_order }}</el-descriptions-item>
        <el-descriptions-item label="支付额">{{ detail?.payment }}</el-descriptions-item>
        <el-descriptions-item label="限额">{{ detail?.limit_price }}</el-descriptions-item>
        <el-descriptions-item label="周期额">{{ detail?.cycle_price }}</el-descriptions-item>
        <el-descriptions-item label="默认">{{ detail?.is_default===1?'是':'否' }}</el-descriptions-item>
        <el-descriptions-item label="Web">{{ detail?.is_web===1?'是':'否' }}</el-descriptions-item>
        <el-descriptions-item label="PC禁用">{{ detail?.not_pc===1?'是':'否' }}</el-descriptions-item>
        <el-descriptions-item label="图标">{{ detail?.icon_iden }}</el-descriptions-item>
        <el-descriptions-item label="支付地址">{{ detail?.pay_url }}</el-descriptions-item>
        <el-descriptions-item label="补单地址">{{ detail?.sup_order_url }}</el-descriptions-item>
        <el-descriptions-item label="备注">{{ detail?.remarks }}</el-descriptions-item>
        <el-descriptions-item label="创建时间">{{ detail?.created_at }}</el-descriptions-item>
        <el-descriptions-item label="更新时间">{{ detail?.updated_at }}</el-descriptions-item>
      </el-descriptions>
    </el-dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { fetchPaymentChannelList, fetchPaymentChannelDetail, createPaymentChannel, updatePaymentChannel, togglePaymentChannel, deletePaymentChannel } from '../api/paymentChannels'

const page = ref(1)
const limit = ref(10)
const total = ref(0)
const rows = ref<any[]>([])
const loading = ref(false)
const kw = ref('')
const showForm = ref(false)
const formMode = ref<'add'|'edit'>('add')
const form = ref<any>({ name: '', status: 1, is_usdt: 2, is_default: 2, is_web: 2, not_pc: 2, pay_url: '', sup_order_url: '', icon_iden: '', sort: 0, limit_price: '0.00', cycle_price: '0.00', pay_rules: '', remarks: '', pay_id: '', skey: '', md5_key: '', pay_bankcode: '' })
const showDetail = ref(false)
const detail = ref<any>(null)

const filtered = computed(() => {
  if (!kw.value) return rows.value
  const k = kw.value.toLowerCase()
  return rows.value.filter((x) => (x.name || '').toLowerCase().includes(k) || (x.pay_url || '').toLowerCase().includes(k))
})

async function load() {
  loading.value = true
  try {
    const data = await fetchPaymentChannelList({ page: page.value, limit: limit.value })
    rows.value = data?.list || []
    total.value = data?.count || 0
  } finally {
    loading.value = false
  }
}
function onPage(p: number){ page.value = p; load() }
function onSize(s: number){ limit.value = s; page.value = 1; load() }
function reload(){ load() }

function onAdd(){ formMode.value='add'; form.value={ name: '', status: 1, is_usdt: 2, is_default: 2, is_web: 2, not_pc: 2, pay_url: '', sup_order_url: '', icon_iden: '', sort: 0, limit_price: '0.00', cycle_price: '0.00', pay_rules: '', remarks: '', pay_id: '', skey: '', md5_key: '', pay_bankcode: '' }; showForm.value=true }
async function onEdit(row:any){
  formMode.value='edit'
  try{
    const d = await fetchPaymentChannelDetail(row.id)
    form.value = {
      id: d?.id ?? row.id,
      name: d?.name ?? row.name ?? '',
      status: Number(d?.status ?? row.status ?? 1),
      is_usdt: Number(d?.is_usdt ?? row.is_usdt ?? 2),
      is_default: Number(d?.is_default ?? row.is_default ?? 2),
      is_web: Number(d?.is_web ?? row.is_web ?? 2),
      not_pc: Number(d?.not_pc ?? row.not_pc ?? 2),
      pay_url: d?.pay_url ?? row.pay_url ?? '',
      sup_order_url: d?.sup_order_url ?? row.sup_order_url ?? '',
      icon_iden: d?.icon_iden ?? row.icon_iden ?? '',
      sort: Number(d?.sort ?? row.sort ?? 0),
      limit_price: String(d?.limit_price ?? row.limit_price ?? '0.00'),
      cycle_price: String(d?.cycle_price ?? row.cycle_price ?? '0.00'),
      pay_rules: d?.pay_rules ?? row.pay_rules ?? '',
      remarks: d?.remarks ?? row.remarks ?? '',
      pay_id: d?.pay_id ?? row.pay_id ?? '',
      skey: d?.skey ?? row.skey ?? '',
      md5_key: d?.md5_key ?? row.md5_key ?? '',
      pay_bankcode: d?.pay_bankcode ?? row.pay_bankcode ?? '',
    }
  }catch(_){
    form.value = { id: row.id, name: row.name, status: Number(row.status ?? 1), is_usdt: Number(row.is_usdt ?? 2), is_default: Number(row.is_default ?? 2), is_web: Number(row.is_web ?? 2), not_pc: Number(row.not_pc ?? 2), pay_url: row.pay_url ?? '', sup_order_url: row.sup_order_url ?? '', icon_iden: row.icon_iden ?? '', sort: Number(row.sort ?? 0), limit_price: String(row.limit_price ?? '0.00'), cycle_price: String(row.cycle_price ?? '0.00'), pay_rules: row.pay_rules ?? '', remarks: row.remarks ?? '', pay_id: row.pay_id ?? '', skey: row.skey ?? '', md5_key: row.md5_key ?? '', pay_bankcode: row.pay_bankcode ?? '' }
  }
  showForm.value = true
}

async function saveForm(){
  try{
    if(!form.value.name){ ElMessage.error('请填写渠道名称'); return }
    if(formMode.value==='add'){
      const res = await createPaymentChannel(form.value)
      if(res?.code===200){ ElMessage.success('已创建'); showForm.value=false; load() } else { ElMessage.error(res?.msg||'创建失败') }
    }else{
      const payload = { ...form.value }; delete payload.id
      const res = await updatePaymentChannel(form.value.id, payload)
      if(res?.code===200){ ElMessage.success('已更新'); showForm.value=false; load() } else { ElMessage.error(res?.msg||'更新失败') }
    }
  }catch(e:any){ const resp=e?.response?.data; ElMessage.error(resp?.message||resp?.msg||'保存失败') }
}

async function onToggle(row:any){
  const res = await togglePaymentChannel(row.id, row.status===1?0:1)
  if(res?.code===200){ ElMessage.success('已更新'); load() } else { ElMessage.error(res?.msg||'操作失败') }
}

async function onDelete(row:any){
  try{ await ElMessageBox.confirm('确认删除该渠道？','提示',{ type:'warning' }); const res=await deletePaymentChannel(row.id); if(res?.code===200){ ElMessage.success('已删除'); load() } else { ElMessage.error(res?.msg||'删除失败') } }catch(_){}
}

async function onDetail(row:any){ const d=await fetchPaymentChannelDetail(row.id); detail.value=d; showDetail.value=true }

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

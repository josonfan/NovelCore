<template>
  <div class="wrap">
    <el-card shadow="never" class="toolbar">
      <div class="toolbar-grid">
        <el-input v-model="kw" placeholder="搜索用户名/昵称" clearable />
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
        <el-table-column prop="username" label="用户名" min-width="160" />
        <el-table-column prop="nickname" label="昵称" min-width="140" />
        <el-table-column label="状态" width="80">
          <template #default="{ row }">
            <el-tag size="small" :type="row.status===1?'success':'danger'">{{ row.status===1?'正常':'禁用' }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="last_login_at" label="最后登录" min-width="180" />
        <el-table-column prop="created_at" label="创建时间" min-width="160" />
        <el-table-column label="操作" width="220" fixed="right">
          <template #default="{ row }">
            <el-button link @click="onDetail(row)">详情</el-button>
            <el-button link type="primary" @click="onBindRoles(row)">绑定角色</el-button>
          </template>
        </el-table-column>
      </el-table>
      <div class="pager">
        <el-pagination background layout="prev, pager, next, jumper, sizes, total" :page-size="limit" :current-page="page" :total="total" @current-change="onPage" @size-change="onSize" :page-sizes="[10,20,50]" />
      </div>
    </el-card>

    <el-dialog v-model="showForm" title="新建管理员" width="460px">
      <el-form :model="form" label-width="100px">
        <el-form-item label="用户名"><el-input v-model="form.username" /></el-form-item>
        <el-form-item label="密码"><el-input v-model="form.password" type="password" /></el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="showForm=false">取消</el-button>
        <el-button type="primary" @click="saveForm">保存</el-button>
      </template>
    </el-dialog>

    <el-dialog v-model="showDetail" title="管理员详情" width="560px">
      <el-descriptions :column="2" border>
        <el-descriptions-item label="ID">{{ detail?.id }}</el-descriptions-item>
        <el-descriptions-item label="用户名">{{ detail?.username }}</el-descriptions-item>
        <el-descriptions-item label="昵称">{{ detail?.nickname }}</el-descriptions-item>
        <el-descriptions-item label="状态">{{ detail?.status }}</el-descriptions-item>
        <el-descriptions-item label="最后登录">{{ detail?.last_login_at }}</el-descriptions-item>
        <el-descriptions-item label="创建时间">{{ detail?.created_at }}</el-descriptions-item>
      </el-descriptions>
    </el-dialog>

    <el-dialog v-model="showBind" title="绑定角色" width="520px">
      <el-checkbox-group v-model="bindRoleIds">
        <el-checkbox v-for="r in roleOptions" :key="r.value" :label="r.value">{{ r.label }}</el-checkbox>
      </el-checkbox-group>
      <template #footer>
        <el-button @click="showBind=false">取消</el-button>
        <el-button type="primary" @click="saveBind">保存</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { fetchAdminList, fetchAdminDetail, createAdmin, assignAdminRoles } from '../api/admins'
import { fetchRoleList } from '../api/roles'

const page = ref(1)
const limit = ref(10)
const total = ref(0)
const rows = ref<any[]>([])
const loading = ref(false)
const kw = ref('')
const showForm = ref(false)
const form = ref<any>({ username: '', password: '' })
const showDetail = ref(false)
const detail = ref<any>(null)
const showBind = ref(false)
const bindAdminId = ref<number>(0)
const bindRoleIds = ref<Array<number>>([])
const roleOptions = ref<Array<{label:string; value:number}>>([])

const filtered = computed(() => {
  if (!kw.value) return rows.value
  const k = kw.value.toLowerCase()
  return rows.value.filter((x) => (x.username || '').toLowerCase().includes(k) || (x.nickname || '').toLowerCase().includes(k))
})

async function load() {
  loading.value = true
  try {
    const data = await fetchAdminList({ page: page.value, limit: limit.value })
    rows.value = data?.list || []
    total.value = data?.count || 0
  } finally { loading.value = false }
}
function onPage(p:number){ page.value=p; load() }
function onSize(s:number){ limit.value=s; page.value=1; load() }
function reload(){ load() }

function onAdd(){ form.value={ username:'', password:'' }; showForm.value=true }
async function saveForm(){
  const res = await createAdmin(form.value)
  if (res?.code === 200){ ElMessage.success('已创建'); showForm.value=false; load() } else { ElMessage.error(res?.msg||'创建失败') }
}

async function onDetail(row:any){ const d=await fetchAdminDetail(row.id); detail.value=d; showDetail.value=true }

async function onBindRoles(row:any){
  bindAdminId.value = Number(row.id)
  const data = await fetchRoleList({ page: 1, limit: 100 })
  roleOptions.value = (data?.list || []).map((x:any)=>({ label: x.name, value: Number(x.id) }))
  bindRoleIds.value = []
  showBind.value = true
}
async function saveBind(){
  const res = await assignAdminRoles(bindAdminId.value, bindRoleIds.value)
  if(res?.code===200){ ElMessage.success('已绑定'); showBind.value=false } else { ElMessage.error(res?.msg||'绑定失败') }
}

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


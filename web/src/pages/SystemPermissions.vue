<template>
  <div class="wrap">
    <div class="content-area">
      <el-card shadow="never" class="toolbar">
        <div class="toolbar-grid">
          <div class="cell">
            <el-input v-model="kw" placeholder="按名称/资源搜索" clearable />
          </div>
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
          <el-table-column prop="resource" label="资源" min-width="160" />
          <el-table-column prop="action" label="操作" min-width="120" />
          <el-table-column prop="field" label="字段" min-width="120" />
          <el-table-column prop="created_at" label="创建时间" min-width="160" />
        </el-table>
      </el-card>

      <el-dialog v-model="showForm" :title="'新建权限'" width="520px">
        <el-form :model="form" label-width="100px">
          <el-form-item label="名称"><el-input v-model="form.name" /></el-form-item>
          <el-form-item label="资源"><el-input v-model="form.resource" /></el-form-item>
          <el-form-item label="操作"><el-input v-model="form.action" /></el-form-item>
          <el-form-item label="字段"><el-input v-model="form.field" /></el-form-item>
        </el-form>
        <template #footer>
          <el-button @click="showForm=false">取消</el-button>
          <el-button type="primary" @click="saveForm">保存</el-button>
        </template>
      </el-dialog>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { fetchPermissionList, createPermission } from '../api/permissions'
import { ElMessage } from 'element-plus'

const rows = ref<any[]>([])
const loading = ref(false)
const kw = ref('')
const total = ref(0)
const showForm = ref(false)
const form = ref<any>({ name: '', resource: '', action: '', field: '' })

const filtered = computed(() => {
  if (!kw.value) return rows.value
  const k = kw.value.toLowerCase()
  return rows.value.filter((x) => (x.name || '').toLowerCase().includes(k) || (x.resource || '').toLowerCase().includes(k))
})

async function load() {
  loading.value = true
  try {
    const list = await fetchPermissionList()
    rows.value = list || []
    total.value = rows.value.length
  } finally { loading.value = false }
}

function reload() { load() }
function onAdd() { showForm.value = true }

async function saveForm() {
  try {
    if (!form.value.name || !form.value.resource || !form.value.action) {
      ElMessage.error('请填写名称、资源与操作')
      return
    }
    const res = await createPermission(form.value)
    if (res?.code === 200) {
      ElMessage.success('已创建')
      showForm.value = false
      load()
    } else {
      ElMessage.error(res?.msg || '创建失败')
    }
  } catch (e: any) {
    const resp = e?.response?.data
    ElMessage.error(resp?.message || resp?.msg || '创建失败')
  }
}

onMounted(load)
</script>

<style scoped>
.wrap { display: grid; gap: 12px; }
.content-area { display: grid; gap: 12px; }
.toolbar { display: grid; gap: 8px; }
.toolbar-grid { display: grid; grid-template-columns: 260px 1fr; gap: 12px; align-items: center; }
.toolbar-grid .actions { justify-self: end; display: inline-flex; gap: 8px; }
.subline { color: var(--nc-muted); font-size: 12px; }
.table-card { }
</style>


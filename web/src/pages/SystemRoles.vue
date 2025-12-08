<template>
  <div class="wrap">
    <div class="content-area">
      <el-card shadow="never" class="toolbar">
        <div class="toolbar-grid">
          <div class="cell">
            <el-input v-model="kw" :placeholder="t('common.search_placeholder')" clearable />
          </div>
          <div class="actions">
            <el-button @click="reload" :loading="loading">{{ t('common.refresh') }}</el-button>
            <el-button type="primary" @click="onAdd">新建</el-button>
          </div>
        </div>
        <div class="subline">共 {{ total }} 条</div>
      </el-card>

      <el-card shadow="hover" class="table-card">
        <el-table :data="filtered" v-loading="loading" border size="small" stripe highlight-current-row>
          <el-table-column prop="id" label="ID" width="80" />
          <el-table-column prop="name" label="角色名" min-width="180" />
          <el-table-column prop="description" label="描述" min-width="200" />
          <el-table-column prop="created_at" label="创建时间" min-width="160" />
          <el-table-column label="操作" width="160" fixed="right">
            <template #default="{ row }">
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

      <el-dialog v-model="showForm" :title="formMode==='add'?'新建角色':'编辑角色'" width="520px">
        <el-form :model="form" label-width="100px">
          <el-form-item label="角色名"><el-input v-model="form.name" /></el-form-item>
          <el-form-item label="描述"><el-input v-model="form.description" type="textarea" /></el-form-item>
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
import { useI18n } from 'vue-i18n'
import { ElMessage, ElMessageBox } from 'element-plus'
import { fetchRoleList, createRole } from '../api/roles'

const { t } = useI18n()
const page = ref(1)
const limit = ref(10)
const total = ref(0)
const rows = ref<any[]>([])
const loading = ref(false)
const kw = ref('')
const showForm = ref(false)
const formMode = ref<'add'|'edit'>('add')
const form = ref<any>({ name: '', description: '' })

const filtered = computed(() => {
  if (!kw.value) return rows.value
  const k = kw.value.toLowerCase()
  return rows.value.filter((x) => (x.name || '').toLowerCase().includes(k))
})

async function load() {
  loading.value = true
  try {
    const data = await fetchRoleList({ page: page.value, limit: limit.value, kw: kw.value })
    rows.value = data?.list || []
    total.value = data?.count || 0
  } finally {
    loading.value = false
  }
}

function onPage(p: number) { page.value = p; load() }
function onSize(s: number) { limit.value = s; page.value = 1; load() }
function reload() { load() }

function onAdd() {
  formMode.value = 'add'
  form.value = { name: '', description: '' }
  showForm.value = true
}

function onEdit(row: any) {
  formMode.value = 'edit'
  form.value = { id: row.id, name: row.name, description: row.description }
  showForm.value = true
}

async function saveForm() {
  try {
    if (!form.value.name) { ElMessage.error('请填写角色名'); return }
    const payload = { name: form.value.name, description: form.value.description }
    const res = await createRole(payload)
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
    await ElMessageBox.confirm('暂未开放删除，请到后端处理', '提示', { type: 'warning' })
  } catch (_) {}
}

onMounted(load)
</script>

<style scoped>
.wrap { display: grid; gap: 12px; grid-template-columns: 1fr; }
.content-area { display: grid; gap: 12px; }
.toolbar { display: grid; gap: 8px; }
.toolbar-grid { display: grid; grid-template-columns: 1fr auto; gap: 12px; align-items: center; }
.toolbar-grid .actions { justify-self: end; display: inline-flex; gap: 8px; }
.subline { color: var(--nc-muted); font-size: 12px; }
.table-card { }
.pager { display: flex; justify-content: flex-end; margin-top: 12px; }
:deep(.el-table__header .el-table__cell){ background: #f3f4f6; color: var(--nc-text); font-weight: 600; }
:deep(.el-table__cell){ padding: 10px 12px; }
</style>


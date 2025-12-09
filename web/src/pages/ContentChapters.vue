<template>
  <div class="wrap">
    <el-card shadow="never" class="toolbar">
      <div class="toolbar-grid">
        <div class="cell">
          <el-input v-model="kw" placeholder="搜索标题/小说ID" clearable />
        </div>
        <div class="actions">
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

    <el-dialog v-model="showForm" :title="formMode==='add'?'新建章节':'编辑章节'" width="560px">
      <el-form :model="form" label-width="100px">
        <el-form-item label="小说ID"><el-input v-model="form.novel_id" /></el-form-item>
        <el-form-item label="标题"><el-input v-model="form.title" /></el-form-item>
        <el-form-item label="序号"><el-input-number v-model="form.index" :min="1" /></el-form-item>
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
import { fetchChapterList, createChapter, updateChapter, deleteChapter } from '../api/chapters'

const loading = ref(true)
const rows = ref<any[]>([])
const total = ref(0)
const page = ref(1)
const limit = ref(10)
const kw = ref('')
const showForm = ref(false)
const formMode = ref<'add'|'edit'>('add')
const form = ref<any>({ novel_id: '', title: '', index: 1 })

const filtered = computed(() => {
  if (!kw.value) return rows.value
  const k = kw.value.toLowerCase()
  return rows.value.filter((x) => (x.title || '').toLowerCase().includes(k) || String(x.novel_id || '').includes(k))
})

async function load() {
  loading.value = true
  try {
    const data = await fetchChapterList({ page: page.value, limit: limit.value })
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

function reload(){ load() }

function onAdd(){
  formMode.value = 'add'
  form.value = { novel_id: '', title: '', index: 1 }
  showForm.value = true
}

function onEdit(row: any){
  formMode.value = 'edit'
  form.value = { id: row.id, novel_id: row.novel_id, title: row.title, index: row.index ?? 1 }
  showForm.value = true
}

async function saveForm(){
  try {
    if (!form.value.novel_id || !form.value.title) { ElMessage.error('请填写小说ID与标题'); return }
    const payload = { novel_id: form.value.novel_id, title: form.value.title, index: form.value.index }
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
</script>

<style scoped>
 .wrap { display: grid; gap: 12px }
 .toolbar { display: grid; gap: 8px }
 .toolbar-grid { display: grid; grid-template-columns: 1fr auto; gap: 12px; align-items: center }
 .toolbar-grid .actions { justify-self: end; display: inline-flex; gap: 8px }
 .subline { color: var(--nc-muted); font-size: 12px }
 .pager { display: flex; justify-content: flex-end; padding-top: 12px }
</style>

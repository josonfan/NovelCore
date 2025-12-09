<template>
  <div class="page">
    <el-card>
      <template #header>小说管理</template>
      <el-table :data="rows" v-loading="loading" style="width: 100%">
        <el-table-column prop="id" label="ID" width="100" />
        <el-table-column prop="title" label="标题" />
        <el-table-column prop="author" label="作者" />
        <el-table-column prop="status" label="状态" width="120" />
      </el-table>
      <div class="pager">
        <el-pagination
          background
          layout="prev, pager, next, sizes, total"
          :page-size="limit"
          :current-page="page"
          :total="total"
          @current-change="onPage"
          @size-change="onSize"
        />
      </div>
    </el-card>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { fetchNovelList } from '../api/novels'

const loading = ref(true)
const rows = ref<any[]>([])
const total = ref(0)
const page = ref(1)
const limit = ref(10)

async function load() {
  loading.value = true
  try {
    const data = await fetchNovelList({ page: page.value, limit: limit.value })
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
</script>

<style scoped>
.page { display: grid; gap: 12px }
.pager { display: flex; justify-content: flex-end; padding-top: 12px }
</style>


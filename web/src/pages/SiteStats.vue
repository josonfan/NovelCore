<template>
  <div class="page-container">
    <!-- 工具栏 -->
    <SiteStatsToolbar
      v-model:site-id="filters.siteId"
      v-model:start-date="filters.startDate"
      v-model:end-date="filters.endDate"
      :loading="loading"
      :total="total"
      :sites="sites"
      @refresh="refresh"
      @search="load"
    />

    <!-- 表格 -->
    <SiteStatsTable
      :data="rows"
      :loading="loading"
      :current-page="page"
      :page-size="limit"
      :total="total"
      :has-site="!!filters.siteId"
      :format-amount="formatAmount"
      @detail="handleDetail"
      @page-change="onPageChange"
      @size-change="onSizeChange"
    />

    <!-- 详情弹窗 -->
    <SiteStatsDetailDialog
      v-model="showDetail"
      :detail="detail"
      :format-amount="formatAmount"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import {
  SiteStatsToolbar,
  SiteStatsTable,
  SiteStatsDetailDialog,
} from '../components/site-stats'
import { useSiteStatsList } from '../composables/useSiteStatsList'
import { fetchSiteList } from '../api/sites'
import type { Site } from '../api/sites'

// 站点列表
const sites = ref<Site[]>([])

async function loadSites() {
  try {
    const data = await fetchSiteList({ page: 1, limit: 100 })
    sites.value = data?.list || []
  } catch {
    sites.value = []
  }
}

// 统计列表管理
const {
  loading,
  page,
  limit,
  total,
  rows,
  filters,
  showDetail,
  detail,
  load,
  refresh,
  onPageChange,
  onSizeChange,
  handleDetail,
  formatAmount,
} = useSiteStatsList()

onMounted(() => {
  loadSites()
})
</script>

<style scoped>
.page-container {
  display: flex;
  flex-direction: column;
  gap: 12px;
}
</style>


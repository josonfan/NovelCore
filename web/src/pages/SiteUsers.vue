<template>
  <div class="page-container">
    <!-- 工具栏 -->
    <SiteUserToolbar
      v-model:site-id="filters.siteId"
      v-model:keyword="filters.keyword"
      :loading="loading"
      :total="total"
      :sites="sites"
      @refresh="refresh"
      @search="load"
    />

    <!-- 表格 -->
    <SiteUserTable
      :data="rows"
      :loading="loading"
      :current-page="page"
      :page-size="limit"
      :total="total"
      :has-site="!!filters.siteId"
      :get-status-info="getStatusInfo"
      :format-vip-expire="formatVipExpire"
      @detail="handleDetail"
      @toggle-status="handleToggleStatus"
      @page-change="onPageChange"
      @size-change="onSizeChange"
    />

    <!-- 详情弹窗 -->
    <SiteUserDetailDialog
      v-model="showDetail"
      :detail="detail"
      :get-status-info="getStatusInfo"
      :format-vip-expire="formatVipExpire"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import {
  SiteUserToolbar,
  SiteUserTable,
  SiteUserDetailDialog,
} from '../components/site-users'
import { useSiteUserList } from '../composables/useSiteUserList'
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

// 用户列表管理
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
  handleToggleStatus,
  getStatusInfo,
  formatVipExpire,
} = useSiteUserList()

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

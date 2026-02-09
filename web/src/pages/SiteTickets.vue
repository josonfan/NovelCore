<template>
  <div class="page-container">
    <!-- 工具栏 -->
    <TicketToolbar
      v-model:site-id="filters.siteId"
      v-model:ticket-no="filters.ticketNo"
      v-model:user-id="filters.userId"
      v-model:status="filters.status"
      v-model:type="filters.type"
      :loading="loading"
      :total="total"
      :sites="sites"
      :ticket-types="ticketTypes"
      :ticket-statuses="ticketStatuses"
      @refresh="refresh"
      @search="load"
    />

    <!-- 表格 -->
    <TicketTable
      :data="rows"
      :loading="loading"
      :current-page="page"
      :page-size="limit"
      :total="total"
      :has-site="!!filters.siteId"
      :get-status-info="getStatusInfo"
      :get-type-name="getTypeName"
      :get-priority-info="getPriorityInfo"
      @detail="handleDetail"
      @process="handleDetail"
      @page-change="onPageChange"
      @size-change="onSizeChange"
    />

    <!-- 工单处理面板 -->
    <TicketProcessPanel
      v-model="showDetail"
      :detail="detail"
      :replies="replies"
      :get-status-info="getStatusInfo"
      @replied="onReplied"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import {
  TicketToolbar,
  TicketTable,
  TicketProcessPanel,
} from '../components/tickets'
import { useTicketList } from '../composables/useTicketList'
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

// 工单列表管理
const {
  loading,
  page,
  limit,
  total,
  rows,
  filters,
  showDetail,
  detail,
  replies,
  ticketTypes,
  ticketStatuses,
  loadOptions,
  load,
  refresh,
  onPageChange,
  onSizeChange,
  handleDetail,
  onReplied,
  getStatusInfo,
  getTypeName,
  getPriorityInfo,
} = useTicketList()

onMounted(() => {
  loadSites()
  loadOptions()
})
</script>

<style scoped>
.page-container {
  display: flex;
  flex-direction: column;
  gap: 12px;
}
</style>

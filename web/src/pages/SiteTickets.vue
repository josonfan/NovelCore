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
      @process="handleProcess"
      @page-change="onPageChange"
      @size-change="onSizeChange"
    />

    <!-- 详情弹窗 -->
    <TicketDetailDialog
      v-model="showDetail"
      :detail="detail"
      :get-status-info="getStatusInfo"
      :get-type-name="getTypeName"
      :get-priority-info="getPriorityInfo"
      @process="handleProcessFromDetail"
    />

    <!-- 处理工单弹窗 -->
    <TicketProcessDialog
      v-model="showProcess"
      :form="processForm"
      :ticket-statuses="ticketStatuses"
      :submitting="processing"
      @submit="onSubmitProcess"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import {
  TicketToolbar,
  TicketTable,
  TicketDetailDialog,
  TicketProcessDialog,
} from '../components/tickets'
import { useTicketList } from '../composables/useTicketList'
import { fetchSiteList } from '../api/sites'
import type { Site } from '../api/sites'
import type { Ticket } from '../api/tickets'

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
  showProcess,
  processForm,
  ticketTypes,
  ticketStatuses,
  loadOptions,
  load,
  refresh,
  onPageChange,
  onSizeChange,
  handleDetail,
  handleProcess,
  submitProcess,
  getStatusInfo,
  getTypeName,
  getPriorityInfo,
} = useTicketList()

// 处理中状态
const processing = ref(false)

// 从详情弹窗发起处理
function handleProcessFromDetail(row: Ticket) {
  showDetail.value = false
  handleProcess(row)
}

// 提交处理
async function onSubmitProcess() {
  processing.value = true
  try {
    await submitProcess()
  } finally {
    processing.value = false
  }
}

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

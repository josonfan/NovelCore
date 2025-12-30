<template>
  <div class="page-container">
    <!-- 工具栏 -->
    <FeedbackToolbar
      v-model:site-id="filters.siteId"
      v-model:feedback-no="filters.feedbackNo"
      v-model:user-id="filters.userId"
      v-model:status="filters.status"
      v-model:type="filters.type"
      :loading="loading"
      :total="total"
      :sites="sites"
      :feedback-types="feedbackTypes"
      :feedback-statuses="feedbackStatuses"
      @refresh="refresh"
      @search="load"
    />

    <!-- 表格 -->
    <FeedbackTable
      :data="rows"
      :loading="loading"
      :current-page="page"
      :page-size="limit"
      :total="total"
      :has-site="!!filters.siteId"
      :get-status-info="getStatusInfo"
      :get-type-name="getTypeName"
      @detail="handleDetail"
      @process="handleProcess"
      @page-change="onPageChange"
      @size-change="onSizeChange"
    />

    <!-- 详情弹窗 -->
    <FeedbackDetailDialog
      v-model="showDetail"
      :detail="detail"
      :get-status-info="getStatusInfo"
      :get-type-name="getTypeName"
      @process="handleProcessFromDetail"
    />

    <!-- 处理建议弹窗 -->
    <FeedbackProcessDialog
      v-model="showProcess"
      :form="processForm"
      :feedback-statuses="feedbackStatuses"
      :submitting="processing"
      @submit="onSubmitProcess"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import {
  FeedbackToolbar,
  FeedbackTable,
  FeedbackDetailDialog,
  FeedbackProcessDialog,
} from '../components/feedbacks'
import { useFeedbackList } from '../composables/useFeedbackList'
import { fetchSiteList } from '../api/sites'
import type { Site } from '../api/sites'
import type { Feedback } from '../api/feedbacks'

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

// 建议列表管理
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
  feedbackTypes,
  feedbackStatuses,
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
} = useFeedbackList()

// 处理中状态
const processing = ref(false)

// 从详情弹窗发起处理
function handleProcessFromDetail(row: Feedback) {
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

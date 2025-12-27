<template>
  <div class="page-container">
    <!-- 工具栏 -->
    <CommentToolbar
      v-model:site-id="filters.siteId"
      v-model:novel-id="filters.novelId"
      :loading="loading"
      :total="total"
      :sites="sites"
      @refresh="refresh"
    />

    <!-- 表格 -->
    <CommentTable
      :data="rows"
      :loading="loading"
      :current-page="page"
      :page-size="limit"
      :total="total"
      :has-site="!!filters.siteId"
      :get-status-info="getStatusInfo"
      @approve="handleApprove"
      @reject="handleReject"
      @detail="handleDetail"
      @delete="handleDelete"
      @page-change="onPageChange"
      @size-change="onSizeChange"
    />

    <!-- 详情弹窗 -->
    <CommentDetailDialog
      v-model="showDetail"
      :detail="detail"
      :get-status-info="getStatusInfo"
      :get-review-source-text="getReviewSourceText"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import {
  CommentToolbar,
  CommentTable,
  CommentDetailDialog,
} from '../components/comments'
import { useCommentList } from '../composables/useCommentList'
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

// 评论列表管理
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
  handleApprove,
  handleReject,
  handleDelete,
  getStatusInfo,
  getReviewSourceText,
} = useCommentList()

onMounted(() => {
  loadSites()
  // 如果有默认站点，自动加载
  if (filters.value.siteId) {
    load()
  }
})
</script>

<style scoped>
.page-container {
  display: flex;
  flex-direction: column;
  gap: 12px;
}
</style>

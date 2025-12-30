<template>
  <el-dialog
    :model-value="modelValue"
    title="工单详情"
    width="700px"
    @update:model-value="$emit('update:modelValue', $event)"
  >
    <div
      v-if="detail"
      class="ticket-header"
    >
      <div class="ticket-header-main">
        <el-tag
          size="small"
          :type="getStatusInfo(detail.status).type"
        >
          {{ getStatusInfo(detail.status).text }}
        </el-tag>
        <span class="ticket-no">{{ detail.ticket_no }}</span>
      </div>
      <div class="ticket-title">
        {{ detail.title }}
      </div>
    </div>

    <el-descriptions
      :column="cols"
      border
      class="detail-desc"
    >
      <el-descriptions-item label="工单ID">
        {{ detail?.id }}
      </el-descriptions-item>
      <el-descriptions-item label="站点ID">
        {{ detail?.site_id }}
      </el-descriptions-item>
      <el-descriptions-item label="用户ID">
        {{ detail?.user_id }}
      </el-descriptions-item>
      <el-descriptions-item label="工单类型">
        <el-tag
          size="small"
          type="info"
        >
          {{ getTypeName(detail?.type || '') }}
        </el-tag>
      </el-descriptions-item>
      <el-descriptions-item label="优先级">
        <el-tag
          size="small"
          :type="getPriorityInfo(detail?.priority || 0).type"
        >
          {{ getPriorityInfo(detail?.priority || 0).text }}
        </el-tag>
      </el-descriptions-item>
      <el-descriptions-item label="联系方式">
        {{ detail?.contact || '-' }}
      </el-descriptions-item>
      <el-descriptions-item
        label="问题描述"
        :span="2"
      >
        <div class="description-content">
          {{ detail?.description || '-' }}
        </div>
      </el-descriptions-item>
      <el-descriptions-item label="创建时间">
        {{ detail?.created_at }}
      </el-descriptions-item>
      <el-descriptions-item label="更新时间">
        {{ detail?.updated_at }}
      </el-descriptions-item>
    </el-descriptions>

    <!-- 附件 -->
    <div
      v-if="detail?.attachments && detail.attachments.length > 0"
      class="attachments-section"
    >
      <div class="section-title">
        附件 ({{ detail.attachments.length }})
      </div>
      <div class="attachments-list">
        <div
          v-for="attachment in detail.attachments"
          :key="attachment.id"
          class="attachment-item"
        >
          <el-image
            v-if="attachment.file_type === 'image'"
            :src="attachment.file_url"
            :preview-src-list="[attachment.file_url]"
            fit="cover"
            class="attachment-image"
          />
          <a
            v-else
            :href="attachment.file_url"
            target="_blank"
            class="attachment-file"
          >
            {{ attachment.file_url.split('/').pop() }}
          </a>
        </div>
      </div>
    </div>

    <!-- 回复信息 -->
    <div
      v-if="detail?.reply_content"
      class="reply-section"
    >
      <div class="section-title">
        处理回复
      </div>
      <el-alert
        type="success"
        :closable="false"
        show-icon
      >
        <template #title>
          <div class="reply-time">
            回复时间：{{ detail.reply_at }}
          </div>
        </template>
        <div class="reply-content">
          {{ detail.reply_content }}
        </div>
      </el-alert>
    </div>

    <template #footer>
      <el-button @click="$emit('update:modelValue', false)">
        关闭
      </el-button>
      <el-button
        v-if="detail && detail.status !== 2 && detail.status !== 3 && detail.status !== 4"
        type="primary"
        @click="$emit('process', detail)"
      >
        处理工单
      </el-button>
    </template>
  </el-dialog>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import type { Ticket } from '../../api/tickets'

defineProps<{
  modelValue: boolean
  detail: Ticket | null
  getStatusInfo: (status: number) => { text: string; type: string }
  getTypeName: (type: string) => string
  getPriorityInfo: (priority: number) => { text: string; type: string }
}>()

defineEmits<{
  'update:modelValue': [value: boolean]
  'process': [row: Ticket]
}>()

const cols = ref(2)

function updateCols() {
  cols.value = window.innerWidth < 700 ? 1 : 2
}

onMounted(() => {
  updateCols()
  window.addEventListener('resize', updateCols)
})

onUnmounted(() => {
  window.removeEventListener('resize', updateCols)
})
</script>

<style scoped>
.ticket-header {
  margin-bottom: 20px;
  padding-bottom: 16px;
  border-bottom: 1px solid var(--nc-border, #e5e7eb);
}

.ticket-header-main {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 8px;
}

.ticket-no {
  font-family: 'Monaco', 'Menlo', monospace;
  font-size: 13px;
  color: var(--nc-muted);
}

.ticket-title {
  font-size: 18px;
  font-weight: 600;
}

.detail-desc {
  margin-top: 8px;
}

.description-content {
  white-space: pre-wrap;
  word-break: break-word;
  line-height: 1.6;
}

.section-title {
  font-weight: 600;
  margin: 20px 0 12px;
  padding-bottom: 8px;
  border-bottom: 1px solid var(--nc-border, #e5e7eb);
}

.attachments-list {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
}

.attachment-item {
  border: 1px solid var(--nc-border, #e5e7eb);
  border-radius: 6px;
  overflow: hidden;
}

.attachment-image {
  width: 120px;
  height: 120px;
  cursor: pointer;
}

.attachment-file {
  display: block;
  padding: 12px 16px;
  color: var(--el-color-primary);
  text-decoration: none;
}

.attachment-file:hover {
  background: var(--el-color-primary-light-9);
}

.reply-section {
  margin-top: 16px;
}

.reply-time {
  font-size: 13px;
  color: var(--nc-muted);
}

.reply-content {
  margin-top: 8px;
  white-space: pre-wrap;
  word-break: break-word;
  line-height: 1.6;
}
</style>

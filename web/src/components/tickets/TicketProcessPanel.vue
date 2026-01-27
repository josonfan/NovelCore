<template>
  <el-dialog
    :model-value="modelValue"
    title="工单详情"
    width="700px"
    @update:model-value="$emit('update:modelValue', $event)"
  >
    <!-- 头部信息区 -->
    <div v-if="detail" class="ticket-header">
      <div class="header-row">
        <span class="header-label">工单编号：</span>
        <span class="header-value ticket-no">{{ detail.ticket_no }}</span>
        <span class="header-label">创建时间：</span>
        <span class="header-value">{{ detail.created_at }}</span>
        <span class="header-label">工单状态：</span>
        <el-tag size="small" :type="getStatusInfo(detail.status).type">
          {{ getStatusInfo(detail.status).text }}
        </el-tag>
      </div>
      <div class="header-row">
        <span class="header-label">用户账号：</span>
        <el-link type="primary" @click="$emit('viewUser', detail.user_id)">
          {{ detail.user_id }}
        </el-link>
      </div>
    </div>

    <!-- 进度详情时间线 -->
    <div class="progress-section">
      <div class="section-title">进度详情</div>
      <div class="timeline-container">
        <el-timeline>
          <!-- 当前状态提示 -->
          <el-timeline-item
            v-if="detail"
            :type="getStatusInfo(detail.status).type"
            :hollow="true"
          >
            <div class="timeline-title">
              {{ getStatusHint(detail.status) }}
            </div>
            <div class="timeline-hint">
              {{ getStatusDescription(detail.status) }}
            </div>
          </el-timeline-item>

          <!-- 回复记录（倒序显示） -->
          <el-timeline-item
            v-for="reply in sortedReplies"
            :key="reply.id"
            :type="reply.user_type === 2 ? 'primary' : 'info'"
          >
            <div class="timeline-title">
              <span class="reply-role">{{ reply.user_type_text }}</span>
              <span class="reply-time">{{ reply.created_at }}</span>
            </div>
            <div class="timeline-content">
              {{ reply.content }}
            </div>
            <div
              v-if="reply.attachments && reply.attachments.length > 0"
              class="timeline-attachments"
            >
              <el-image
                v-for="(url, idx) in reply.attachments"
                :key="idx"
                :src="url"
                :preview-src-list="reply.attachments"
                :initial-index="idx"
                fit="cover"
                class="attachment-thumb"
              />
            </div>
          </el-timeline-item>

          <!-- 工单创建信息 -->
          <el-timeline-item v-if="detail" type="success">
            <div class="timeline-title">
              <span class="reply-role">用户创建工单</span>
              <span class="reply-time">{{ detail.created_at }}</span>
            </div>
            <div class="timeline-content">
              <div class="ticket-title">
                {{ detail.title }}
              </div>
              <!-- <div class="ticket-desc">
                {{ detail.description }}
              </div> -->
            </div>
            <div
              v-if="detail.attachments && detail.attachments.length > 0"
              class="timeline-attachments"
            >
              <el-image
                v-for="attachment in detail.attachments"
                :key="attachment.id"
                :src="attachment.file_url"
                :preview-src-list="detailImageUrls"
                fit="cover"
                class="attachment-thumb"
              />
            </div>
          </el-timeline-item>
        </el-timeline>
      </div>
    </div>

    <!-- 回复输入区 -->
    <div v-if="detail && !isTicketClosed" class="reply-section">
      <div class="reply-input-row">
        <el-input
          v-model="replyForm.content"
          type="textarea"
          :rows="3"
          placeholder="请输入回复..."
          maxlength="500"
          show-word-limit
          class="reply-input"
        />
        <div class="reply-upload">
          <ImageUploader
            v-model="replyImages"
            multiple
            :limit="5"
            upload-text=""
          />
        </div>
      </div>
      <div class="reply-footer">
        <div class="status-select">
          <span class="status-label">工单状态</span>
          <el-select
            v-model="replyForm.status"
            placeholder="选择状态"
            style="width: 120px"
          >
            <el-option
              v-for="item in statusOptions"
              :key="item.code"
              :label="item.name"
              :value="item.code"
            />
          </el-select>
        </div>
        <el-button type="primary" :loading="submitting" @click="handleSubmit">
          确定
        </el-button>
      </div>
    </div>

    <template #footer>
      <el-button @click="$emit('update:modelValue', false)"> 关闭 </el-button>
    </template>
  </el-dialog>
</template>

<script setup lang="ts">
import { ref, computed, watch } from "vue";
import { ElMessage } from "element-plus";
import ImageUploader from "../ImageUploader.vue";
import type { ImageItem } from "../ImageUploader.vue";
import type { Ticket, TicketReply } from "../../api/tickets";
import { REPLY_STATUS_OPTIONS, replyTicket } from "../../api/tickets";

const props = defineProps<{
  modelValue: boolean;
  detail: Ticket | null;
  replies: TicketReply[];
  getStatusInfo: (status: number) => { text: string; type: string };
}>();

const emit = defineEmits<{
  "update:modelValue": [value: boolean];
  viewUser: [userId: string];
  replied: [];
}>();

// 回复表单
const replyForm = ref({
  content: "",
  status: 1, // 默认处理中
});
const replyImages = ref<ImageItem[]>([]);
const submitting = ref(false);

// 状态选项
const statusOptions = REPLY_STATUS_OPTIONS;

// 倒序回复列表（最新在上）
const sortedReplies = computed(() => {
  return [...props.replies].reverse();
});

// 工单是否已关闭
const isTicketClosed = computed(() => {
  const status = props.detail?.status;
  return status === 2 || status === 3 || status === 4;
});

// 工单附件图片URL列表
const detailImageUrls = computed(() => {
  return props.detail?.attachments?.map((a) => a.file_url) || [];
});

// 状态提示
function getStatusHint(status: number): string {
  const hints: Record<number, string> = {
    0: "待处理",
    1: "处理中",
    2: "已解决",
    3: "已关闭",
    4: "已拒绝",
  };
  return hints[status] || "未知状态";
}

function getStatusDescription(status: number): string {
  const descriptions: Record<number, string> = {
    0: "工单待平台处理",
    1: "工单正在处理中，请耐心等待",
    2: "问题已解决，如有疑问可点击重新打开",
    3: "工单已关闭",
    4: "工单已被拒绝",
  };
  return descriptions[status] || "";
}

// 提交回复
async function handleSubmit() {
  if (!replyForm.value.content.trim()) {
    ElMessage.warning("请输入回复内容");
    return;
  }

  if (!props.detail) return;

  submitting.value = true;
  try {
    const res = await replyTicket({
      id: props.detail.id,
      content: replyForm.value.content,
      attachments: replyImages.value.map((img) => img.url),
      status: replyForm.value.status,
    });

    if (res?.code === 200) {
      ElMessage.success("回复成功");
      replyForm.value.content = "";
      replyImages.value = [];
      emit("replied");
    } else {
      ElMessage.error(res?.msg || "回复失败");
    }
  } catch {
    ElMessage.error("回复失败");
  } finally {
    submitting.value = false;
  }
}

// 监听弹窗打开，重置表单
watch(
  () => props.modelValue,
  (val) => {
    if (val && props.detail) {
      replyForm.value = {
        content: "",
        status: props.detail.status === 0 ? 1 : props.detail.status,
      };
      replyImages.value = [];
    }
  },
);
</script>

<style scoped>
.ticket-header {
  margin-bottom: 20px;
  padding-bottom: 16px;
  border-bottom: 1px solid var(--nc-border, #e5e7eb);
}

.header-row {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 8px;
  margin-bottom: 8px;
}

.header-row:last-child {
  margin-bottom: 0;
}

.header-label {
  color: var(--nc-muted, #6b7280);
  font-size: 13px;
}

.header-value {
  font-size: 13px;
  margin-right: 16px;
}

.ticket-no {
  font-family: "Monaco", "Menlo", monospace;
}

.section-title {
  font-weight: 600;
  margin-bottom: 12px;
  padding-bottom: 8px;
  border-bottom: 1px solid var(--nc-border, #e5e7eb);
}

.timeline-container {
  max-height: 400px;
  overflow-y: auto;
  padding-right: 8px;
}

.timeline-title {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 4px;
}

.reply-role {
  font-weight: 600;
  color: var(--el-text-color-primary);
}

.reply-time {
  font-size: 12px;
  color: var(--nc-muted, #6b7280);
}

.timeline-hint {
  font-size: 13px;
  color: var(--nc-muted, #6b7280);
}

.timeline-content {
  white-space: pre-wrap;
  word-break: break-word;
  line-height: 1.6;
  color: var(--el-text-color-regular);
}

.ticket-title {
  font-weight: 600;
  margin-bottom: 4px;
}

.ticket-desc {
  color: var(--el-text-color-regular);
}

.timeline-attachments {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 8px;
}

.attachment-thumb {
  width: 80px;
  height: 80px;
  border-radius: 4px;
  cursor: pointer;
  border: 1px solid var(--nc-border, #e5e7eb);
}

.reply-section {
  margin-top: 20px;
  padding-top: 16px;
  border-top: 1px solid var(--nc-border, #e5e7eb);
}

.reply-input-row {
  display: flex;
  gap: 12px;
}

.reply-input {
  flex: 1;
}

.reply-upload {
  flex-shrink: 0;
}

.reply-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 12px;
}

.status-select {
  display: flex;
  align-items: center;
  gap: 8px;
}

.status-label {
  font-size: 13px;
  color: var(--nc-muted, #6b7280);
}
</style>

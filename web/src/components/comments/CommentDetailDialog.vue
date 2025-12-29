<template>
  <el-dialog
    :model-value="modelValue"
    title="评论详情"
    width="600px"
    @update:model-value="$emit('update:modelValue', $event)"
  >
    <el-descriptions
      :column="cols"
      border
    >
      <el-descriptions-item label="评论ID">
        {{ detail?.id }}
      </el-descriptions-item>
      <el-descriptions-item label="站点ID">
        {{ detail?.site_id }}
      </el-descriptions-item>
      <el-descriptions-item label="小说ID">
        {{ detail?.novel_id }}
      </el-descriptions-item>
      <el-descriptions-item label="章节ID">
        {{ detail?.chapter_id || '-' }}
      </el-descriptions-item>
      <el-descriptions-item label="用户ID">
        {{ detail?.user_id }}
      </el-descriptions-item>
      <el-descriptions-item label="父评论ID">
        {{ detail?.parent_id || '-' }}
      </el-descriptions-item>
      <el-descriptions-item
        label="评论内容"
        :span="2"
      >
        <div class="content-box">
          {{ detail?.content }}
        </div>
      </el-descriptions-item>
      <el-descriptions-item label="R18内容">
        <el-tag
          size="small"
          :type="detail?.is_r18 === 1 ? 'danger' : 'info'"
        >
          {{ detail?.is_r18 === 1 ? '是' : '否' }}
        </el-tag>
      </el-descriptions-item>
      <el-descriptions-item label="状态">
        <el-tag
          size="small"
          :type="getStatusInfo(detail?.status || 0).type"
        >
          {{ getStatusInfo(detail?.status || 0).text }}
        </el-tag>
      </el-descriptions-item>
      <el-descriptions-item label="审核来源">
        {{ getReviewSourceText(detail?.review_source || 0) }}
      </el-descriptions-item>
      <el-descriptions-item label="点赞数">
        {{ detail?.like_count }}
      </el-descriptions-item>
      <el-descriptions-item label="创建时间">
        {{ detail?.created_at }}
      </el-descriptions-item>
      <el-descriptions-item label="更新时间">
        {{ detail?.updated_at }}
      </el-descriptions-item>
      <el-descriptions-item
        label="最后同步"
        :span="2"
      >
        {{ detail?.last_synced_at }}
      </el-descriptions-item>
    </el-descriptions>

    <template #footer>
      <el-button @click="$emit('update:modelValue', false)">
        关闭
      </el-button>
    </template>
  </el-dialog>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import type { SiteComment } from '../../composables/useCommentList'
import type { GetStatusInfoFn, GetReviewSourceTextFn } from '../../api/comments'

defineProps<{
  modelValue: boolean
  detail: SiteComment | null
  getStatusInfo: GetStatusInfoFn
  getReviewSourceText: GetReviewSourceTextFn
}>()

defineEmits<{
  'update:modelValue': [value: boolean]
}>()

const cols = ref(2)

function updateCols() {
  cols.value = window.innerWidth < 600 ? 1 : 2
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
.content-box {
  word-break: break-all;
  line-height: 1.6;
  max-height: 200px;
  overflow-y: auto;
}
</style>

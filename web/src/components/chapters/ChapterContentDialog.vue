<template>
  <el-dialog
    :model-value="visible"
    title="章节详情"
    width="800px"
    @update:model-value="$emit('update:visible', $event)"
  >
    <div class="detail-container">
      <!-- 基本信息 -->
      <el-descriptions
        title="基本信息"
        :column="2"
        border
      >
        <el-descriptions-item label="小说ID">
          {{ form.novel_id }}
        </el-descriptions-item>
        <el-descriptions-item label="排序">
          {{ form.sort_order }}
        </el-descriptions-item>
        <el-descriptions-item label="标题">
          {{ form.title }}
        </el-descriptions-item>
        <el-descriptions-item label="字数">
          {{ form.word_count }}
        </el-descriptions-item>
      </el-descriptions>

      <!-- 付费设置 -->
      <el-descriptions
        title="付费设置"
        :column="3"
        border
        class="section"
      >
        <el-descriptions-item label="免费章节">
          <el-tag :type="form.is_free === '1' ? 'success' : 'info'">
            {{ form.is_free === '1' ? '是' : '否' }}
          </el-tag>
        </el-descriptions-item>
        <el-descriptions-item label="VIP专享">
          <el-tag :type="form.is_vip === '1' ? 'warning' : 'info'">
            {{ form.is_vip === '1' ? '是' : '否' }}
          </el-tag>
        </el-descriptions-item>
        <el-descriptions-item label="单章价格">
          {{ form.price || '0' }}
        </el-descriptions-item>
      </el-descriptions>

      <!-- SEO设置 -->
      <el-descriptions
        title="SEO设置"
        :column="1"
        border
        class="section"
      >
        <el-descriptions-item label="SEO标题">
          {{ form.seo_title || '-' }}
        </el-descriptions-item>
        <el-descriptions-item label="SEO关键词">
          {{ form.seo_keywords || '-' }}
        </el-descriptions-item>
        <el-descriptions-item label="SEO描述">
          {{ form.seo_description || '-' }}
        </el-descriptions-item>
      </el-descriptions>

      <!-- 内容摘要 -->
      <div class="section">
        <div class="section-title">
          内容摘要
        </div>
        <div class="content-box">
          {{ form.content_short || '暂无摘要' }}
        </div>
      </div>

      <!-- 章节内容 -->
      <div class="section">
        <div class="section-title">
          章节内容
        </div>
        <div
          class="content-box content-html"
          v-html="form.content || '暂无内容'"
        />
      </div>
    </div>

    <template #footer>
      <el-button
        type="primary"
        @click="$emit('update:visible', false)"
      >
        关闭
      </el-button>
    </template>
  </el-dialog>
</template>

<script setup lang="ts">
import type { ChapterContentForm } from '../../composables/useChapterContent'

defineProps<{
  visible: boolean
  form: ChapterContentForm
}>()

defineEmits<{
  'update:visible': [value: boolean]
}>()
</script>

<style scoped>
.detail-container {
  max-height: 60vh;
  overflow-y: auto;
}

.section {
  margin-top: 20px;
}

.section-title {
  font-weight: 600;
  font-size: 14px;
  color: var(--el-text-color-primary);
  margin-bottom: 12px;
  padding-left: 8px;
  border-left: 3px solid var(--el-color-primary);
}

.content-box {
  background: var(--el-fill-color-light);
  border: 1px solid var(--el-border-color-lighter);
  border-radius: 4px;
  padding: 12px 16px;
  min-height: 60px;
  line-height: 1.6;
  color: var(--el-text-color-regular);
  white-space: pre-wrap;
  word-break: break-word;
}

.content-html {
  max-height: 300px;
  overflow-y: auto;
}

.content-html :deep(p) {
  margin: 0 0 8px;
}

.content-html :deep(img) {
  max-width: 100%;
}
</style>

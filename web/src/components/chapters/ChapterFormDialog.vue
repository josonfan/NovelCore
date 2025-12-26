<template>
  <el-dialog
    :model-value="visible"
    :title="mode === 'add' ? '新建章节' : '编辑章节'"
    width="720px"
    @update:model-value="$emit('update:visible', $event)"
  >
    <el-form
      :model="form"
      label-width="100px"
    >
      <el-tabs v-model="localActiveTab">
        <el-tab-pane
          label="基本信息"
          name="basic"
        >
          <el-form-item
            v-if="!hideNovelId"
            label="小说ID"
          >
            <el-input
              :model-value="form.novel_id"
              @update:model-value="updateForm('novel_id', $event)"
            />
          </el-form-item>
          <el-form-item label="标题">
            <el-input
              :model-value="form.title"
              @update:model-value="updateForm('title', $event)"
            />
          </el-form-item>
          <el-form-item label="排序">
            <el-input-number
              :model-value="Number(form.sort_order)"
              :min="1"
              @update:model-value="updateForm('sort_order', String($event))"
            />
          </el-form-item>
          <el-form-item label="字数">
            <el-input-number
              :model-value="Number(form.word_count)"
              :min="0"
              @update:model-value="updateForm('word_count', String($event))"
            />
          </el-form-item>
        </el-tab-pane>
        <el-tab-pane
          label="内容"
          name="content"
        >
          <el-form-item label="章节内容">
            <RichTextEditor
              :model-value="form.content"
              placeholder="请输入章节内容"
              @update:model-value="updateForm('content', $event)"
            />
          </el-form-item>
          <el-form-item label="内容摘要">
            <el-input
              type="textarea"
              :rows="4"
              :model-value="form.content_short"
              placeholder="章节前几百字摘要（试看用）"
              @update:model-value="updateForm('content_short', $event)"
            />
          </el-form-item>
        </el-tab-pane>
        <el-tab-pane
          label="付费设置"
          name="payment"
        >
          <el-form-item label="免费章节">
            <el-radio-group
              :model-value="form.is_free"
              @update:model-value="updateForm('is_free', $event)"
            >
              <el-radio value="1">
                是
              </el-radio>
              <el-radio value="0">
                否
              </el-radio>
            </el-radio-group>
          </el-form-item>
          <el-form-item label="VIP专享">
            <el-radio-group
              :model-value="form.is_vip"
              @update:model-value="updateForm('is_vip', $event)"
            >
              <el-radio value="1">
                是
              </el-radio>
              <el-radio value="0">
                否
              </el-radio>
            </el-radio-group>
          </el-form-item>
          <el-form-item label="单章价格">
            <el-input-number
              :model-value="Number(form.price)"
              :min="0"
              @update:model-value="updateForm('price', String($event))"
            />
            <span style="margin-left: 8px; color: var(--nc-muted)">虚拟币或分，0为免费</span>
          </el-form-item>
        </el-tab-pane>
        <el-tab-pane
          label="SEO设置"
          name="seo"
        >
          <el-form-item label="SEO标题">
            <el-input
              :model-value="form.seo_title"
              placeholder="SEO 页面标题"
              @update:model-value="updateForm('seo_title', $event)"
            />
          </el-form-item>
          <el-form-item label="SEO关键词">
            <el-input
              :model-value="form.seo_keywords"
              placeholder="SEO 页面关键词"
              @update:model-value="updateForm('seo_keywords', $event)"
            />
          </el-form-item>
          <el-form-item label="SEO描述">
            <el-input
              type="textarea"
              :rows="3"
              :model-value="form.seo_description"
              placeholder="SEO 页面描述"
              @update:model-value="updateForm('seo_description', $event)"
            />
          </el-form-item>
        </el-tab-pane>
      </el-tabs>
    </el-form>
    <template #footer>
      <el-button @click="$emit('update:visible', false)">
        取消
      </el-button>
      <el-button
        type="primary"
        @click="$emit('save')"
      >
        保存
      </el-button>
    </template>
  </el-dialog>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import RichTextEditor from '../RichTextEditor.vue'
import type { ChapterFormData } from '../../composables/useChapterForm'

const props = defineProps<{
  visible: boolean
  mode: 'add' | 'edit'
  form: ChapterFormData
  activeTab: string
  hideNovelId?: boolean
}>()

const emit = defineEmits<{
  'update:visible': [value: boolean]
  'update:form': [value: ChapterFormData]
  'update:activeTab': [value: string]
  'save': []
}>()

const localActiveTab = computed({
  get: () => props.activeTab,
  set: (v) => emit('update:activeTab', v),
})

function updateForm<K extends keyof ChapterFormData>(key: K, value: ChapterFormData[K]) {
  emit('update:form', { ...props.form, [key]: value })
}
</script>

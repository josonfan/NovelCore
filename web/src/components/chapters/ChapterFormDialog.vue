<template>
  <el-dialog
    :model-value="visible"
    :title="mode === 'add' ? '新建章节' : '编辑章节'"
    width="560px"
    @update:model-value="$emit('update:visible', $event)"
  >
    <el-form :model="form" label-width="100px">
      <el-tabs v-model="localActiveTab">
        <el-tab-pane label="基本信息" name="basic">
          <el-form-item v-if="!hideNovelId" label="小说ID">
            <el-input :model-value="form.novel_id" @update:model-value="updateForm('novel_id', $event)" />
          </el-form-item>
          <el-form-item label="标题">
            <el-input :model-value="form.title" @update:model-value="updateForm('title', $event)" />
          </el-form-item>
          <el-form-item label="序号">
            <el-input-number
              :model-value="form.index"
              :min="1"
              @update:model-value="updateForm('index', $event)"
            />
          </el-form-item>
        </el-tab-pane>
        <el-tab-pane label="内容" name="content">
          <el-form-item label="内容">
            <RichTextEditor
              :model-value="form.content"
              placeholder="请输入章节内容"
              @update:model-value="updateForm('content', $event)"
            />
          </el-form-item>
        </el-tab-pane>
      </el-tabs>
    </el-form>
    <template #footer>
      <el-button @click="$emit('update:visible', false)">取消</el-button>
      <el-button type="primary" @click="$emit('save')">保存</el-button>
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

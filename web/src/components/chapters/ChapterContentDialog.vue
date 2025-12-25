<template>
  <el-dialog
    :model-value="visible"
    title="章节内容"
    width="800px"
    @update:model-value="$emit('update:visible', $event)"
  >
    <el-form :model="form" label-width="100px">
      <el-form-item label="标题">
        <el-input :model-value="form.title" @update:model-value="updateForm('title', $event)" />
      </el-form-item>
      <el-form-item label="内容">
        <RichTextEditor
          :model-value="form.content"
          placeholder="请输入章节内容"
          @update:model-value="updateForm('content', $event)"
        />
      </el-form-item>
    </el-form>
    <template #footer>
      <el-button @click="$emit('update:visible', false)">关闭</el-button>
      <el-button type="primary" @click="$emit('save')">保存内容</el-button>
    </template>
  </el-dialog>
</template>

<script setup lang="ts">
import RichTextEditor from '../RichTextEditor.vue'
import type { ChapterContentForm } from '../../composables/useChapterContent'

const props = defineProps<{
  visible: boolean
  form: ChapterContentForm
}>()

const emit = defineEmits<{
  'update:visible': [value: boolean]
  'update:form': [value: ChapterContentForm]
  'save': []
}>()

function updateForm<K extends keyof ChapterContentForm>(key: K, value: ChapterContentForm[K]) {
  emit('update:form', { ...props.form, [key]: value })
}
</script>

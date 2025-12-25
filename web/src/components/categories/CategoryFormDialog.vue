<template>
  <el-dialog
    :model-value="visible"
    :title="mode === 'add' ? '新建分类' : '编辑分类'"
    width="520px"
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
          <el-form-item label="名称">
            <el-input
              :model-value="form.name"
              @update:model-value="updateForm('name', $event)"
            />
          </el-form-item>
          <el-form-item label="Slug">
            <el-input
              :model-value="form.slug"
              @update:model-value="updateForm('slug', $event)"
            />
          </el-form-item>
          <el-form-item label="启用">
            <el-switch
              :model-value="form.is_active"
              :active-value="1"
              :inactive-value="0"
              @update:model-value="updateForm('is_active', $event)"
            />
          </el-form-item>
        </el-tab-pane>
        <el-tab-pane
          label="SEO"
          name="seo"
        >
          <el-form-item label="SEO标题">
            <el-input
              :model-value="form.seo_title"
              @update:model-value="updateForm('seo_title', $event)"
            />
          </el-form-item>
          <el-form-item label="SEO关键字">
            <el-input
              :model-value="form.seo_keywords"
              @update:model-value="updateForm('seo_keywords', $event)"
            />
          </el-form-item>
          <el-form-item label="SEO描述">
            <el-input
              :model-value="form.seo_description"
              type="textarea"
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
import type { CategoryFormData } from '../../composables/useCategoryForm'

const props = defineProps<{
  visible: boolean
  mode: 'add' | 'edit'
  form: CategoryFormData
  activeTab: string
}>()

const emit = defineEmits<{
  'update:visible': [value: boolean]
  'update:form': [value: CategoryFormData]
  'update:activeTab': [value: string]
  'save': []
}>()

const localActiveTab = computed({
  get: () => props.activeTab,
  set: (v) => emit('update:activeTab', v),
})

function updateForm<K extends keyof CategoryFormData>(key: K, value: CategoryFormData[K]) {
  emit('update:form', { ...props.form, [key]: value })
}
</script>

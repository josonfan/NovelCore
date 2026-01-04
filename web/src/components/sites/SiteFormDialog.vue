<template>
  <el-dialog
    :model-value="visible"
    :title="mode === 'add' ? '新建站点' : '编辑站点'"
    width="560px"
    @update:model-value="$emit('update:visible', $event)"
  >
    <el-form
      :model="formData"
      label-width="100px"
    >
      <el-form-item label="名称">
        <el-input v-model="formData.name" />
      </el-form-item>
      <el-form-item label="编码">
        <el-input
          v-model="formData.code"
          :disabled="mode === 'edit'"
        />
      </el-form-item>
      <el-form-item label="基础API">
        <el-input v-model="formData.base_api_url" />
      </el-form-item>
      <el-form-item label="主域名">
        <el-input v-model="formData.primary_domain" />
      </el-form-item>
      <el-form-item
        v-if="mode === 'add'"
        label="API令牌"
      >
        <el-input v-model="formData.api_token" />
      </el-form-item>
      <el-form-item label="启用">
        <el-switch
          v-model="formData.is_active"
          :active-value="1"
          :inactive-value="0"
        />
      </el-form-item>
      <el-form-item label="备注">
        <el-input v-model="formData.remark" />
      </el-form-item>
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
import type { SiteFormData } from '../../composables/useSiteForm'

defineProps<{
  visible: boolean
  mode: 'add' | 'edit'
}>()

const formData = defineModel<SiteFormData>('form', { required: true })

defineEmits<{
  'update:visible': [value: boolean]
  'save': []
}>()
</script>

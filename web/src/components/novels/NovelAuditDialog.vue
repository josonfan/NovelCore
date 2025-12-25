<template>
  <el-dialog
    :model-value="visible"
    @update:model-value="$emit('update:visible', $event)"
    title="小说审核"
    width="520px"
  >
    <el-form :model="audit" label-width="100px">
      <div style="margin-bottom: 8px">待审核：{{ audit.ids.length }} 本</div>
      <el-form-item label="审核结果">
        <el-radio-group v-model="audit.status">
          <el-radio :label="1">已通过</el-radio>
          <el-radio :label="2">已拒绝</el-radio>
          <el-radio :label="3">已下线</el-radio>
        </el-radio-group>
      </el-form-item>
      <el-form-item label="备注">
        <el-input
          v-model="audit.reason"
          type="textarea"
          :autosize="{ minRows: 3, maxRows: 6 }"
        />
      </el-form-item>
    </el-form>
    <template #footer>
      <el-button @click="$emit('update:visible', false)">取消</el-button>
      <el-button type="primary" @click="$emit('submit')">提交审核</el-button>
    </template>
  </el-dialog>
</template>

<script setup lang="ts">
import type { AuditData } from '../../composables/useNovelAudit'

defineProps<{
  visible: boolean
  audit: AuditData
}>()

defineEmits<{
  (e: 'update:visible', value: boolean): void
  (e: 'submit'): void
}>()
</script>

<template>
  <el-dialog
    :model-value="modelValue"
    title="处理建议"
    width="500px"
    @update:model-value="$emit('update:modelValue', $event)"
  >
    <el-form
      ref="formRef"
      :model="form"
      :rules="rules"
      label-width="80px"
    >
      <el-form-item
        label="处理状态"
        prop="status"
      >
        <el-select
          v-model="form.status"
          placeholder="请选择处理状态"
          class="full-width"
        >
          <el-option
            v-for="item in availableStatuses"
            :key="item.code"
            :label="item.name"
            :value="item.code"
          />
        </el-select>
      </el-form-item>

      <el-form-item
        label="回复内容"
        prop="reply_content"
      >
        <el-input
          v-model="form.reply_content"
          type="textarea"
          :rows="5"
          placeholder="请输入回复内容"
          maxlength="500"
          show-word-limit
        />
      </el-form-item>
    </el-form>

    <template #footer>
      <el-button @click="$emit('update:modelValue', false)">
        取消
      </el-button>
      <el-button
        type="primary"
        :loading="submitting"
        @click="handleSubmit"
      >
        提交
      </el-button>
    </template>
  </el-dialog>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import type { FormInstance, FormRules } from 'element-plus'
import type { FeedbackStatus } from '../../api/feedbacks'

const props = defineProps<{
  modelValue: boolean
  form: {
    id: string
    status: number
    reply_content: string
  }
  feedbackStatuses: FeedbackStatus[]
  submitting?: boolean
}>()

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
  'update:form': [value: typeof props.form]
  'submit': []
}>()

const formRef = ref<FormInstance>()

// 可选的状态（排除待处理）
const availableStatuses = computed(() => {
  return props.feedbackStatuses.filter(s => s.code !== 0)
})

const rules: FormRules = {
  status: [
    { required: true, message: '请选择处理状态', trigger: 'change' }
  ],
  reply_content: [
    { required: true, message: '请输入回复内容', trigger: 'blur' }
  ]
}

async function handleSubmit() {
  if (!formRef.value) return
  
  await formRef.value.validate((valid) => {
    if (valid) {
      emit('submit')
    }
  })
}
</script>

<style scoped>
.full-width {
  width: 100%;
}
</style>

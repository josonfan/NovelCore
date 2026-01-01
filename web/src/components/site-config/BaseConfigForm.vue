<template>
  <el-form
    ref="formRef"
    :model="config"
    :rules="rules"
    label-width="140px"
    class="config-form"
  >
    <el-form-item
      label="隐私政策"
      prop="config_data.privacy_policy"
    >
      <RichTextEditor
        v-model="config.config_data.privacy_policy"
        placeholder="请输入隐私政策内容"
        :height="320"
      />
    </el-form-item>
    <el-form-item
      label="相关协议"
      prop="config_data.payment_agreement"
    >
      <RichTextEditor
        v-model="config.config_data.payment_agreement"
        placeholder="请输入相关协议内容"
        :height="320"
      />
    </el-form-item>
    <el-form-item
      label="18+ 内容"
      prop="config_data.18+_content"
    >
      <el-input
        v-model="config.config_data['18+_content']"
        type="textarea"
        :rows="6"
        placeholder="请输入 18+ 内容"
      />
    </el-form-item>
    <div class="form-actions">
      <el-button
        type="primary"
        :loading="saving"
        @click="handleSave"
      >
        保存
      </el-button>
    </div>
  </el-form>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useSiteConfig } from '../../composables/useSiteConfig'
import { fetchBaseConfigDetail, saveBaseConfig } from '../../api/baseConfig'
import RichTextEditor from '../RichTextEditor.vue'
import type { FormInstance, FormRules } from 'element-plus'

type BaseConfig = {
  config_name: 'base_config'
  config_data: {
    privacy_policy: string
    payment_agreement: string
    '18+_content': string
  }
}

const props = defineProps<{
  siteId: number
}>()

const { config, saving, load, save } = useSiteConfig<BaseConfig>(props.siteId, {
  fetchFn: fetchBaseConfigDetail,
  saveFn: saveBaseConfig,
  defaultValue: () => ({
    config_name: 'base_config',
    config_data: {
      privacy_policy: '',
      payment_agreement: '',
      '18+_content': '',
    },
  }),
})

const formRef = ref<FormInstance>()
function stripHtml(value: string) {
  return (value || '').replace(/<[^>]*>/g, '').replace(/&nbsp;/g, ' ').trim()
}

const rules: FormRules = {
  'config_data.privacy_policy': [
    {
      validator: (_, value, callback) => {
        if (!stripHtml(value || '')) callback(new Error('请输入隐私政策内容'))
        else callback()
      },
      trigger: 'change',
    },
  ],
  'config_data.payment_agreement': [
    {
      validator: (_, value, callback) => {
        if (!stripHtml(value || '')) callback(new Error('请输入相关协议内容'))
        else callback()
      },
      trigger: 'change',
    },
  ],
  'config_data.18+_content': [{ required: true, message: '请输入 18+ 内容', trigger: 'blur' }],
}

onMounted(load)

async function handleSave() {
  const valid = await formRef.value?.validate()
  if (!valid) return
  await save()
  await load()
}

defineExpose({ load })
</script>

<style scoped>
.config-form {
  max-width: 860px;
  margin-top: 8px;
}

.form-actions {
  margin-top: 16px;
}
</style>

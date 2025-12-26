<template>
  <el-form
    :model="config"
    label-width="140px"
    class="config-form"
  >
    <el-form-item label="启用">
      <el-switch
        v-model="config.enabled"
        :active-value="1"
        :inactive-value="0"
      />
    </el-form-item>
    <el-form-item label="需要人工审核">
      <el-switch
        v-model="config.require_approval"
        :active-value="1"
        :inactive-value="0"
      />
    </el-form-item>
    <el-form-item label="最大长度">
      <el-input-number
        v-model="config.max_length"
        :min="1"
      />
    </el-form-item>
    <el-form-item label="每分钟限制">
      <el-input-number
        v-model="config.max_per_minute"
        :min="1"
      />
    </el-form-item>
    <el-form-item label="违禁词列表">
      <el-input
        v-model="forbiddenWordsStr"
        type="textarea"
        :rows="4"
        placeholder="请输入违禁词，支持汉字、字母、数字、下划线及破折号。多个词可用逗号、换行或空格分隔。"
        @input="onForbiddenWordsInput"
      />
    </el-form-item>
    <div class="form-actions">
      <el-button
        type="primary"
        :loading="saving"
        @click="onSave"
      >
        保存
      </el-button>
    </div>
  </el-form>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { fetchCommentReviewConfigBySite, saveCommentReviewConfig } from '../../api/siteConfigs'

interface CommentReviewConfig {
  enabled: number
  require_approval: number
  max_length: number
  max_per_minute: number
  forbidden_words_json: string[]
}

const props = defineProps<{
  siteId: number
}>()

const config = ref<CommentReviewConfig>({
  enabled: 1,
  require_approval: 1,
  max_length: 500,
  max_per_minute: 5,
  forbidden_words_json: [],
})

const forbiddenWordsStr = ref('')
const saving = ref(false)

function onForbiddenWordsInput(val: string) {
  const arr = val.split(/[,，\n\s]+/).map((s) => s.trim()).filter((s) => s)
  config.value.forbidden_words_json = arr
}

async function load() {
  try {
    const data = await fetchCommentReviewConfigBySite(props.siteId)
    if (data) {
      config.value = {
        enabled: data.enabled ?? 1,
        require_approval: data.require_approval ?? 1,
        max_length: data.max_length ?? 500,
        max_per_minute: data.max_per_minute ?? 5,
        forbidden_words_json: data.forbidden_words_json || [],
      }
      forbiddenWordsStr.value = (data.forbidden_words_json || []).join(',')
    }
  } catch {
    // ignore
  }
}

async function onSave() {
  try {
    saving.value = true
    const res = await saveCommentReviewConfig({
      site_id: props.siteId,
      ...config.value,
    })
    if (res?.code === 200) {
      ElMessage.success('保存成功')
    } else {
      ElMessage.error(res?.msg || '保存失败')
    }
  } catch (e: unknown) {
    const resp = (e as { response?: { data?: { message?: string; msg?: string } } })?.response?.data
    ElMessage.error(resp?.message || resp?.msg || '保存失败')
  } finally {
    saving.value = false
  }
}

onMounted(load)

defineExpose({ load })
</script>

<style scoped>
.config-form {
  max-width: 640px;
  margin-top: 8px;
}

.form-actions {
  margin-top: 16px;
}
</style>

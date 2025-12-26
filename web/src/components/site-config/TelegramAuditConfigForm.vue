<template>
  <el-form
    :model="config"
    label-width="140px"
    class="config-form"
  >
    <el-form-item label="Bot Token">
      <el-input
        v-model="config.bot_token"
        type="password"
        show-password
      />
    </el-form-item>
    <el-form-item label="Chat ID">
      <el-input v-model="config.chat_id" />
    </el-form-item>
    <el-form-item label="启用">
      <el-switch
        v-model="config.is_active"
        :active-value="1"
        :inactive-value="0"
      />
    </el-form-item>
    <div class="form-actions">
      <el-button
        type="primary"
        :loading="saving"
        @click="save"
      >
        保存
      </el-button>
    </div>
  </el-form>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useSiteConfig } from '../../composables/useSiteConfig'
import { fetchTelegramAuditConfigBySite, saveTelegramAuditConfig } from '../../api/siteConfigs'

interface TelegramAuditConfig {
  bot_token: string
  chat_id: string
  is_active: number
}

const props = defineProps<{
  siteId: number
}>()

const { config, saving, load, save } = useSiteConfig<TelegramAuditConfig>(props.siteId, {
  fetchFn: fetchTelegramAuditConfigBySite,
  saveFn: saveTelegramAuditConfig,
  defaultValue: () => ({
    bot_token: '',
    chat_id: '',
    is_active: 1,
  }),
})

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

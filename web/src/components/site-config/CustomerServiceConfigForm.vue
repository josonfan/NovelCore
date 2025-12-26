<template>
  <el-form
    :model="config"
    label-width="140px"
    class="config-form"
  >
    <el-form-item label="服务商">
      <el-input v-model="config.provider" />
    </el-form-item>
    <el-form-item label="基础地址">
      <el-input v-model="config.base_url" />
    </el-form-item>
    <el-form-item label="访问密钥">
      <el-input
        v-model="config.api_key"
        type="password"
        show-password
      />
    </el-form-item>
    <el-form-item label="回调地址">
      <el-input v-model="config.webhook_url" />
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
import { fetchCustomerServiceConfigBySite, saveCustomerServiceConfig } from '../../api/siteConfigs'

interface CustomerServiceConfig {
  provider: string
  base_url: string
  api_key: string
  webhook_url: string
  is_active: number
}

const props = defineProps<{
  siteId: number
}>()

const { config, saving, load, save } = useSiteConfig<CustomerServiceConfig>(props.siteId, {
  fetchFn: fetchCustomerServiceConfigBySite,
  saveFn: saveCustomerServiceConfig,
  defaultValue: () => ({
    provider: '',
    base_url: '',
    api_key: '',
    webhook_url: '',
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

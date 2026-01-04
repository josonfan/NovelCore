<template>
  <el-form
    :model="config"
    label-width="140px"
    class="config-form"
  >
    <el-form-item label="客服邮箱">
      <el-input v-model="config.support_email" />
    </el-form-item>
    <el-form-item label="客服链接">
      <el-input v-model="config.support_url" />
    </el-form-item>
    <el-form-item label="AppID">
      <el-input v-model="config.appid" />
    </el-form-item>
    <el-form-item label="备注">
      <el-input v-model="config.remark" />
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
  id?: string
  site_id?: string
  support_email: string
  support_url: string
  appid: string
  is_active: number | string
  remark: string
  updated_at?: string
}

const props = defineProps<{
  siteId: number
}>()

const { config, saving, load, save } = useSiteConfig<CustomerServiceConfig>(props.siteId, {
  fetchFn: fetchCustomerServiceConfigBySite,
  saveFn: saveCustomerServiceConfig,
  defaultValue: () => ({
    support_email: '',
    support_url: '',
    appid: '',
    is_active: 1,
    remark: '',
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

<template>
  <el-form
    :model="config"
    label-width="140px"
    class="config-form"
  >
    <el-form-item label="服务商">
      <el-input v-model="config.provider" />
    </el-form-item>
    <el-form-item label="区域">
      <el-input v-model="config.region" />
    </el-form-item>
    <el-form-item label="访问密钥">
      <el-input v-model="config.access_key" />
    </el-form-item>
    <el-form-item label="密钥">
      <el-input
        v-model="config.secret_key"
        type="password"
        show-password
      />
    </el-form-item>
    <el-form-item label="发件邮箱">
      <el-input v-model="config.from_address" />
    </el-form-item>
    <el-form-item label="发件人名称">
      <el-input v-model="config.from_name" />
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
import { fetchEmailConfigBySite, saveEmailConfig } from '../../api/siteConfigs'

interface EmailConfig {
  provider: string
  region: string
  access_key: string
  secret_key: string
  from_address: string
  from_name: string
  is_active: number
}

const props = defineProps<{
  siteId: number
}>()

const { config, saving, load, save } = useSiteConfig<EmailConfig>(props.siteId, {
  fetchFn: fetchEmailConfigBySite,
  saveFn: saveEmailConfig,
  defaultValue: () => ({
    provider: '',
    region: '',
    access_key: '',
    secret_key: '',
    from_address: '',
    from_name: '',
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

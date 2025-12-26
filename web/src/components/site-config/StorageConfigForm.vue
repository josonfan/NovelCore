<template>
  <el-form
    :model="config"
    label-width="140px"
    class="config-form"
  >
    <el-form-item label="存储服务">
      <el-input
        v-model="config.provider"
        placeholder="如：b2、s3、oss"
      />
    </el-form-item>
    <el-form-item label="访问密钥ID">
      <el-input v-model="config.access_key_id" />
    </el-form-item>
    <el-form-item label="密钥">
      <el-input
        v-model="config.secret_key"
        type="password"
        show-password
      />
    </el-form-item>
    <el-form-item label="存储桶名称">
      <el-input v-model="config.bucket_name" />
    </el-form-item>
    <el-form-item label="存储区域">
      <el-input v-model="config.bucket_region" />
    </el-form-item>
    <el-form-item label="接口地址">
      <el-input
        v-model="config.endpoint"
        placeholder="如：https://s3.us-east-005.backblazeb2.com"
      />
    </el-form-item>
    <el-form-item label="CDN 基础地址">
      <el-input
        v-model="config.base_url"
        placeholder="如：https://cdn.example.com"
      />
    </el-form-item>
    <el-form-item label="允许后缀">
      <el-input
        v-model="config.allowed_suffix"
        placeholder="如：jpg,png,webp,mp4"
      />
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
import { fetchStorageConfigBySite, saveStorageConfig } from '../../api/storageConfig'

interface StorageConfig {
  provider: string
  access_key_id: string
  secret_key: string
  bucket_name: string
  bucket_region: string
  endpoint: string
  base_url: string
  allowed_suffix: string
  is_active: number
}

const props = defineProps<{
  siteId: number
}>()

const { config, saving, load, save } = useSiteConfig<StorageConfig>(props.siteId, {
  fetchFn: fetchStorageConfigBySite,
  saveFn: saveStorageConfig,
  defaultValue: () => ({
    provider: '',
    access_key_id: '',
    secret_key: '',
    bucket_name: '',
    bucket_region: '',
    endpoint: '',
    base_url: '',
    allowed_suffix: '',
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

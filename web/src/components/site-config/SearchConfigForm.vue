<template>
  <el-form
    :model="config"
    label-width="140px"
    class="config-form"
  >
    <el-form-item label="搜索引擎类型">
      <el-select
        v-model="config.provider"
        placeholder="请选择"
      >
        <el-option
          label="Elasticsearch"
          value="elasticsearch"
        />
        <el-option
          label="其他"
          value="other"
        />
      </el-select>
    </el-form-item>
    <el-form-item label="搜索服务地址">
      <el-input
        v-model="config.host"
        placeholder="如：http://127.0.0.1:9200"
      />
    </el-form-item>
    <el-form-item label="索引名前缀">
      <el-input
        v-model="config.index_prefix"
        placeholder="如：bl_"
      />
    </el-form-item>
    <el-form-item label="访问用户名">
      <el-input
        v-model="config.username"
        placeholder="Basic Auth 用户名"
      />
    </el-form-item>
    <el-form-item label="访问密码">
      <el-input
        v-model="config.password"
        type="password"
        show-password
        placeholder="Basic Auth 密码"
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
import { fetchSearchConfigBySite, saveSearchConfig } from '../../api/siteConfigs'

interface SearchConfig {
  provider: string
  host: string
  index_prefix: string
  username: string
  password: string
  is_active: number
}

const props = defineProps<{
  siteId: number
}>()

const { config, saving, load, save } = useSiteConfig<SearchConfig>(props.siteId, {
  fetchFn: fetchSearchConfigBySite,
  saveFn: saveSearchConfig,
  defaultValue: () => ({
    provider: 'elasticsearch',
    host: '',
    index_prefix: '',
    username: '',
    password: '',
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

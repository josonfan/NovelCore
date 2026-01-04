<template>
  <el-dialog
    :model-value="visible"
    title="站点详情"
    width="800px"
    @update:model-value="$emit('update:visible', $event)"
  >
    <div class="health-section">
      <el-button
        :loading="healthLoading"
        @click="$emit('check-health')"
      >
        检测状态
      </el-button>
      <template v-if="health">
        <el-tag :type="health.db ? 'success' : 'danger'">
          DB：{{ health.db ? '正常' : '异常' }}
        </el-tag>
        <el-tag :type="health.cache ? 'success' : 'danger'">
          Cache：{{ health.cache ? '正常' : '异常' }}
        </el-tag>
        <span class="health-info">
          语言：{{ health.lang }}，时间：{{ formatTime(health.ts) }}
        </span>
      </template>
      <el-tag
        v-if="healthFailed"
        type="danger"
      >
        检测失败
      </el-tag>
    </div>

    <div
      v-if="health && health.queue"
      class="queue-section"
    >
      <div class="queue-title">
        队列状态
      </div>
      <div class="queue-tags">
        <el-tag
          v-for="(val, key) in health.queue"
          :key="key"
          :type="Number(val) === 0 ? 'success' : 'warning'"
        >
          {{ key }}：{{ val }}
        </el-tag>
      </div>
    </div>

    <el-descriptions
      :column="2"
      border
    >
      <el-descriptions-item label="ID">
        {{ detail?.id }}
      </el-descriptions-item>
      <el-descriptions-item label="名称">
        {{ detail?.name }}
      </el-descriptions-item>
      <el-descriptions-item label="编码">
        {{ detail?.code }}
      </el-descriptions-item>
      <el-descriptions-item label="基础API">
        {{ detail?.base_api_url }}
      </el-descriptions-item>
      <el-descriptions-item label="主域名">
        {{ detail?.primary_domain }}
      </el-descriptions-item>
      <el-descriptions-item label="启用">
        {{ detail?.is_active }}
      </el-descriptions-item>
      <el-descriptions-item label="备注">
        {{ detail?.remark }}
      </el-descriptions-item>
      <el-descriptions-item label="创建时间">
        {{ detail?.created_at }}
      </el-descriptions-item>
      <el-descriptions-item label="更新时间">
        {{ detail?.updated_at }}
      </el-descriptions-item>
    </el-descriptions>
  </el-dialog>
</template>

<script setup lang="ts">
import type { Site } from '../../api/sites'
import type { HealthData } from '../../composables/useSiteHealth'

defineProps<{
  visible: boolean
  detail: Site | null
  health: HealthData | null
  healthLoading: boolean
  healthFailed: boolean
  formatTime: (ts: unknown) => string
}>()

defineEmits<{
  'update:visible': [value: boolean]
  'check-health': []
}>()
</script>

<style scoped>
.health-section {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 8px;
}

.health-info {
  color: var(--nc-muted);
  font-size: 12px;
}

.queue-section {
  display: grid;
  gap: 8px;
  margin-bottom: 8px;
}

.queue-title {
  font-weight: 600;
}

.queue-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}
</style>

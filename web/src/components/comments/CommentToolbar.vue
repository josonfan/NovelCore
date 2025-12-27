<template>
  <el-card
    shadow="never"
    class="toolbar"
  >
    <template #header>
      <div class="toolbar-header">
        <span class="title">用户评论管理</span>
        <el-tag
          type="info"
          size="small"
        >
          共 {{ total }} 条
        </el-tag>
      </div>
    </template>

    <div class="toolbar-content">
      <div class="filters">
        <el-select
          :model-value="siteId"
          placeholder="请选择站点"
          clearable
          filterable
          class="filter-select"
          @update:model-value="$emit('update:siteId', $event)"
        >
          <el-option
            v-for="site in sites"
            :key="site.id"
            :label="site.name"
            :value="String(site.id)"
          />
        </el-select>

        <el-input
          :model-value="novelId"
          placeholder="小说ID"
          clearable
          :prefix-icon="Search"
          class="novel-input"
          @update:model-value="$emit('update:novelId', $event)"
        />
      </div>

      <div class="actions">
        <el-button
          :icon="Refresh"
          :loading="loading"
          @click="$emit('refresh')"
        >
          刷新
        </el-button>
      </div>
    </div>
  </el-card>
</template>

<script setup lang="ts">
import { Search, Refresh } from '@element-plus/icons-vue'

defineProps<{
  siteId: string
  novelId: string
  loading: boolean
  total: number
  sites: Array<{ id: number | string; name: string }>
}>()

defineEmits<{
  'update:siteId': [value: string]
  'update:novelId': [value: string]
  'refresh': []
}>()
</script>

<style scoped>
.toolbar-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.title {
  font-weight: 600;
  font-size: 16px;
}

.toolbar-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  flex-wrap: wrap;
}

.filters {
  display: flex;
  gap: 12px;
  flex: 1;
}

.filter-select {
  width: 200px;
}

.novel-input {
  width: 160px;
}

.actions {
  display: flex;
  gap: 8px;
  flex-shrink: 0;
}
</style>

<template>
  <el-card
    shadow="never"
    class="toolbar"
  >
    <template #header>
      <div class="toolbar-header">
        <span class="title">工单管理</span>
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

        <el-select
          :model-value="status"
          placeholder="工单状态"
          clearable
          class="filter-select-sm"
          @update:model-value="$emit('update:status', $event)"
        >
          <el-option
            v-for="item in ticketStatuses"
            :key="item.code"
            :label="item.name"
            :value="String(item.code)"
          />
        </el-select>

        <el-select
          :model-value="type"
          placeholder="工单类型"
          clearable
          class="filter-select-sm"
          @update:model-value="$emit('update:type', $event)"
        >
          <el-option
            v-for="item in ticketTypes"
            :key="item.code"
            :label="item.name"
            :value="item.code"
          />
        </el-select>

        <el-input
          :model-value="ticketNo"
          placeholder="工单号"
          clearable
          class="search-input"
          @update:model-value="$emit('update:ticketNo', $event)"
          @keyup.enter="$emit('search')"
        />

        <el-input
          :model-value="userId"
          placeholder="用户ID"
          clearable
          class="search-input-sm"
          @update:model-value="$emit('update:userId', $event)"
          @keyup.enter="$emit('search')"
        />
      </div>

      <div class="actions">
        <el-button
          type="primary"
          :icon="Search"
          :loading="loading"
          @click="$emit('search')"
        >
          搜索
        </el-button>
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
import type { TicketType, TicketStatus } from '../../api/tickets'

defineProps<{
  siteId: string
  ticketNo: string
  userId: string
  status: string
  type: string
  loading: boolean
  total: number
  sites: Array<{ id: number | string; name: string }>
  ticketTypes: TicketType[]
  ticketStatuses: TicketStatus[]
}>()

defineEmits<{
  'update:siteId': [value: string]
  'update:ticketNo': [value: string]
  'update:userId': [value: string]
  'update:status': [value: string]
  'update:type': [value: string]
  'refresh': []
  'search': []
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
  flex-wrap: wrap;
}

.filter-select {
  width: 180px;
}

.filter-select-sm {
  width: 120px;
}

.search-input {
  width: 200px;
}

.search-input-sm {
  width: 120px;
}

.actions {
  display: flex;
  gap: 8px;
  flex-shrink: 0;
}
</style>

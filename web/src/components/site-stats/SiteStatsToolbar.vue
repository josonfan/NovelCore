<template>
  <el-card
    shadow="never"
    class="toolbar"
  >
    <template #header>
      <div class="toolbar-header">
        <span class="title">站点统计</span>
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

        <el-date-picker
          :model-value="dateRange"
          type="daterange"
          range-separator="至"
          start-placeholder="开始日期"
          end-placeholder="结束日期"
          value-format="YYYY-MM-DD"
          class="date-picker"
          @update:model-value="handleDateChange"
        />
      </div>

      <div class="actions">
        <el-button
          type="primary"
          :icon="Search"
          :loading="loading"
          @click="$emit('search')"
        >
          查询
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
import { computed } from 'vue'
import { Search, Refresh } from '@element-plus/icons-vue'

const props = defineProps<{
  siteId: string
  startDate: string
  endDate: string
  loading: boolean
  total: number
  sites: Array<{ id: number | string; name: string }>
}>()

const emit = defineEmits<{
  'update:siteId': [value: string]
  'update:startDate': [value: string]
  'update:endDate': [value: string]
  'refresh': []
  'search': []
}>()

// 日期范围
const dateRange = computed(() => {
  if (props.startDate && props.endDate) {
    return [props.startDate, props.endDate]
  }
  return null
})

// 处理日期变化
function handleDateChange(val: [string, string] | null) {
  if (val) {
    emit('update:startDate', val[0])
    emit('update:endDate', val[1])
  } else {
    emit('update:startDate', '')
    emit('update:endDate', '')
  }
}
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

.date-picker {
  width: 280px;
}

.actions {
  display: flex;
  gap: 8px;
  flex-shrink: 0;
}
</style>



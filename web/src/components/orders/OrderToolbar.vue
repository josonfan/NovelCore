<template>
  <el-card
    shadow="never"
    class="toolbar"
  >
    <template #header>
      <div class="toolbar-header">
        <span class="title">订单管理</span>
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
        <el-input
          v-model="localFilters.order_no"
          placeholder="订单号"
          clearable
          :prefix-icon="Document"
          @change="onFilterChange"
        />
        <el-input
          v-model="localFilters.user_id"
          placeholder="用户ID"
          clearable
          :prefix-icon="User"
          @change="onFilterChange"
        />
        <el-select
          v-model="localFilters.status"
          placeholder="订单状态"
          clearable
          @change="onFilterChange"
        >
          <el-option
            label="待处理"
            value="pending"
          />
          <el-option
            label="处理中"
            value="processing"
          />
          <el-option
            label="已支付"
            value="paid"
          />
          <el-option
            label="已取消"
            value="cancelled"
          />
          <el-option
            label="已退款"
            value="refunded"
          />
        </el-select>
        <el-input
          v-model="localFilters.site_id"
          placeholder="站点ID"
          clearable
          :prefix-icon="OfficeBuilding"
          @change="onFilterChange"
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
import { reactive, watch } from 'vue'
import { Document, User, OfficeBuilding, Refresh } from '@element-plus/icons-vue'
import type { OrderFilters } from '../../composables/useOrderList'

const props = defineProps<{
  filters: OrderFilters
  loading: boolean
  total: number
}>()

const emit = defineEmits<{
  'update:filters': [value: OrderFilters]
  'refresh': []
}>()

const localFilters = reactive<OrderFilters>({ ...props.filters })

watch(
  () => props.filters,
  (newVal) => {
    Object.assign(localFilters, newVal)
  },
  { deep: true }
)

function onFilterChange() {
  emit('update:filters', { ...localFilters })
}
</script>

<style scoped>
.toolbar-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.toolbar-header .title {
  font-weight: 600;
  font-size: 16px;
}

.toolbar-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
}

.filters {
  display: grid;
  grid-template-columns: repeat(4, minmax(140px, 200px));
  gap: 12px;
  flex: 1;
}

.actions {
  display: flex;
  gap: 8px;
  flex-shrink: 0;
}

@media (max-width: 1200px) {
  .filters {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .toolbar-content {
    flex-direction: column;
    align-items: stretch;
  }

  .filters {
    grid-template-columns: 1fr;
  }

  .actions {
    justify-content: flex-end;
  }
}
</style>

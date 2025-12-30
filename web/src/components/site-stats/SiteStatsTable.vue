<template>
  <el-card
    shadow="hover"
    class="table-card"
  >
    <el-empty
      v-if="!hasSite"
      description="请先选择站点"
    />

    <template v-else>
      <el-table
        v-loading="loading"
        :data="data"
        border
        size="small"
        stripe
        highlight-current-row
        show-summary
        :summary-method="getSummaries"
      >
        <el-table-column
          prop="stat_date"
          label="统计日期"
          width="120"
          align="center"
        >
          <template #default="{ row }">
            <el-text type="primary">
              {{ row.stat_date }}
            </el-text>
          </template>
        </el-table-column>

        <el-table-column
          prop="user_count"
          label="新增用户"
          width="120"
          align="center"
        >
          <template #default="{ row }">
            <span class="stat-number user-count">
              {{ row.user_count }}
            </span>
          </template>
        </el-table-column>

        <el-table-column
          prop="read_count"
          label="阅读次数"
          width="120"
          align="center"
        >
          <template #default="{ row }">
            <span class="stat-number read-count">
              {{ row.read_count }}
            </span>
          </template>
        </el-table-column>

        <el-table-column
          prop="order_count"
          label="订单数"
          width="120"
          align="center"
        >
          <template #default="{ row }">
            <span class="stat-number order-count">
              {{ row.order_count }}
            </span>
          </template>
        </el-table-column>

        <el-table-column
          prop="order_amount"
          label="订单金额"
          width="140"
          align="right"
        >
          <template #default="{ row }">
            <span class="stat-amount">
              {{ formatAmount(row.order_amount) }}
            </span>
          </template>
        </el-table-column>

        <el-table-column
          label="最后同步"
          min-width="160"
        >
          <template #default="{ row }">
            <el-text
              type="info"
              size="small"
            >
              {{ row.last_synced_at }}
            </el-text>
          </template>
        </el-table-column>

        <el-table-column
          label="操作"
          width="100"
          fixed="right"
          align="center"
        >
          <template #default="{ row }">
            <el-button
              link
              size="small"
              @click="$emit('detail', row)"
            >
              详情
            </el-button>
          </template>
        </el-table-column>
      </el-table>

      <div class="pager">
        <el-pagination
          background
          layout="prev, pager, next, jumper, sizes, total"
          :page-size="pageSize"
          :current-page="currentPage"
          :total="total"
          :page-sizes="[10, 20, 50, 100]"
          @current-change="$emit('page-change', $event)"
          @size-change="$emit('size-change', $event)"
        />
      </div>
    </template>
  </el-card>
</template>

<script setup lang="ts">
import type { SiteStat } from '../../api/siteStats'
import type { TableColumnCtx } from 'element-plus'

const props = defineProps<{
  data: SiteStat[]
  loading: boolean
  currentPage: number
  pageSize: number
  total: number
  hasSite: boolean
  formatAmount: (amount: string | number) => string
}>()

defineEmits<{
  'detail': [row: SiteStat]
  'page-change': [page: number]
  'size-change': [size: number]
}>()

// 合计行
interface SummaryMethodProps<T = SiteStat> {
  columns: TableColumnCtx<T>[]
  data: T[]
}

function getSummaries(param: SummaryMethodProps) {
  const { columns, data } = param
  const sums: string[] = []
  
  columns.forEach((column, index) => {
    if (index === 0) {
      sums[index] = '合计'
      return
    }
    
    const prop = column.property
    if (['user_count', 'read_count', 'order_count'].includes(prop)) {
      const sum = data.reduce((prev, curr) => {
        const value = Number(curr[prop as keyof SiteStat])
        return prev + (isNaN(value) ? 0 : value)
      }, 0)
      sums[index] = String(sum)
    } else if (prop === 'order_amount') {
      const sum = data.reduce((prev, curr) => {
        const value = parseFloat(curr.order_amount)
        return prev + (isNaN(value) ? 0 : value)
      }, 0)
      sums[index] = props.formatAmount(sum)
    } else {
      sums[index] = ''
    }
  })
  
  return sums
}
</script>

<style scoped>
.table-card {
  margin-top: 12px;
}

.stat-number {
  font-weight: 600;
  font-size: 14px;
}

.user-count {
  color: var(--el-color-primary);
}

.read-count {
  color: var(--el-color-success);
}

.order-count {
  color: var(--el-color-warning);
}

.stat-amount {
  font-weight: 600;
  font-size: 14px;
  color: var(--el-color-danger);
}

.pager {
  display: flex;
  justify-content: flex-end;
  margin-top: 12px;
}

:deep(.el-table__header .el-table__cell) {
  background: #f3f4f6;
  color: var(--nc-text);
  font-weight: 600;
}

:deep(.el-table__cell) {
  padding: 10px 12px;
  vertical-align: middle;
}

:deep(.el-table__row:hover) {
  background: rgba(64, 158, 255, 0.06);
}

:deep(.el-table__footer .el-table__cell) {
  background: #fafafa;
  font-weight: 600;
}
</style>

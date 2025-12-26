<template>
  <el-card
    shadow="hover"
    class="table-card"
  >
    <el-table
      v-loading="loading"
      :data="rows"
      border
      size="small"
      stripe
    >
      <el-table-column
        prop="id"
        label="ID"
        width="80"
      />
      <el-table-column
        prop="site_id"
        label="站点ID"
        width="90"
      />
      <el-table-column
        prop="order_id"
        label="订单ID"
        width="100"
      />
      <el-table-column
        prop="order_no"
        label="订单号"
        min-width="180"
      />
      <el-table-column
        prop="user_id"
        label="用户ID"
        width="100"
      />
      <el-table-column
        prop="order_type"
        label="类型"
        width="100"
      />
      <el-table-column
        prop="good_id"
        label="商品ID"
        width="100"
      />
      <el-table-column
        label="商品信息"
        min-width="220"
      >
        <template #default="{ row }">
          <div class="good-info">
            <div>名称：{{ row.good_info?.name || '-' }}</div>
            <div>
              天数：{{ row.good_info?.days ?? '-' }}
              金额：{{ row.good_info?.amount || '-' }}
            </div>
            <div class="desc">
              描述：{{ row.good_info?.descript || '-' }}
            </div>
          </div>
        </template>
      </el-table-column>
      <el-table-column
        prop="amount"
        label="金额"
        width="120"
      />
      <el-table-column
        prop="pay_channel_id"
        label="渠道ID"
        width="110"
      />
      <el-table-column
        label="状态"
        width="110"
      >
        <template #default="{ row }">
          <el-tag
            size="small"
            :type="getStatusType(row.status)"
          >
            {{ getStatusText(row.status) }}
          </el-tag>
        </template>
      </el-table-column>
      <el-table-column
        label="扩展"
        min-width="200"
      >
        <template #default="{ row }">
          <div class="extra-info">
            <div>客户端：{{ row.extra?.client || '-' }}</div>
            <div>
              来源：{{ row.extra?.created_by || '-' }}
              天数：{{ row.extra?.days ?? '-' }}
            </div>
          </div>
        </template>
      </el-table-column>
      <el-table-column
        prop="created_at"
        label="创建时间"
        min-width="160"
      />
      <el-table-column
        prop="updated_at"
        label="更新时间"
        min-width="160"
      />
      <el-table-column
        prop="paid_at"
        label="支付时间"
        min-width="160"
      />
      <el-table-column
        prop="last_synced_at"
        label="最后同步"
        min-width="160"
      />
      <el-table-column
        label="操作"
        width="100"
        fixed="right"
      >
        <template #default="{ row }">
          <el-button
            type="primary"
            link
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
        :page-sizes="[10, 20, 50]"
        @current-change="$emit('page-change', $event)"
        @size-change="$emit('size-change', $event)"
      />
    </div>
  </el-card>
</template>

<script setup lang="ts">
import { getStatusText, getStatusType, type SiteOrderItem } from '../../composables/useOrderList'

defineProps<{
  loading: boolean
  rows: SiteOrderItem[]
  total: number
  currentPage: number
  pageSize: number
}>()

defineEmits<{
  'detail': [row: SiteOrderItem]
  'page-change': [page: number]
  'size-change': [size: number]
}>()
</script>

<style scoped>
.pager {
  display: flex;
  justify-content: flex-end;
  margin-top: 12px;
}

.good-info,
.extra-info {
  font-size: 12px;
  line-height: 1.5;
}

.good-info .desc {
  color: var(--nc-muted);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
</style>

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
        prop="name"
        label="渠道名称"
        min-width="160"
      />
      <el-table-column
        label="状态"
        width="100"
      >
        <template #default="{ row }">
          <el-tag
            size="small"
            :type="row.status === 1 ? 'success' : 'danger'"
          >
            {{ row.status === 1 ? '正常' : '停用' }}
          </el-tag>
        </template>
      </el-table-column>
      <el-table-column
        label="USDT"
        width="100"
      >
        <template #default="{ row }">
          <el-tag
            size="small"
            :type="row.is_usdt === 1 ? 'success' : 'info'"
          >
            {{ row.is_usdt === 1 ? '是' : '否' }}
          </el-tag>
        </template>
      </el-table-column>
      <el-table-column
        prop="order_quantity"
        label="下单数"
        width="100"
      />
      <el-table-column
        prop="payment_quantity"
        label="支付数"
        width="100"
      />
      <el-table-column
        prop="place_order"
        label="下单额"
        width="120"
      />
      <el-table-column
        prop="payment"
        label="支付额"
        width="120"
      />
      <el-table-column
        prop="limit_price"
        label="限额"
        width="120"
      />
      <el-table-column
        prop="cycle_price"
        label="周期额"
        width="120"
      />
      <el-table-column
        label="默认"
        width="90"
      >
        <template #default="{ row }">
          <el-tag
            size="small"
            :type="row.is_default === 1 ? 'success' : 'info'"
          >
            {{ row.is_default === 1 ? '是' : '否' }}
          </el-tag>
        </template>
      </el-table-column>
      <el-table-column
        label="Web"
        width="90"
      >
        <template #default="{ row }">
          <el-tag
            size="small"
            :type="row.is_web === 1 ? 'success' : 'info'"
          >
            {{ row.is_web === 1 ? '是' : '否' }}
          </el-tag>
        </template>
      </el-table-column>
      <el-table-column
        label="PC禁用"
        width="100"
      >
        <template #default="{ row }">
          <el-tag
            size="small"
            :type="row.not_pc === 1 ? 'warning' : 'info'"
          >
            {{ row.not_pc === 1 ? '是' : '否' }}
          </el-tag>
        </template>
      </el-table-column>
      <el-table-column
        prop="icon_iden"
        label="图标"
        width="120"
      />
      <el-table-column
        prop="pay_url"
        label="支付地址"
        min-width="200"
      />
      <el-table-column
        prop="sup_order_url"
        label="补单地址"
        min-width="200"
      />
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
        label="操作"
        width="240"
        fixed="right"
      >
        <template #default="{ row }">
          <el-button
            link
            @click="$emit('detail', row)"
          >
            详情
          </el-button>
          <el-button
            link
            type="primary"
            @click="$emit('edit', row)"
          >
            编辑
          </el-button>
          <el-button
            link
            type="warning"
            @click="$emit('toggle', row)"
          >
            {{ row.status === 1 ? '停用' : '启用' }}
          </el-button>
          <el-button
            link
            type="danger"
            @click="$emit('delete', row)"
          >
            删除
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
import type { PaymentChannel } from '../../composables/usePaymentChannelList'

defineProps<{
  loading: boolean
  rows: PaymentChannel[]
  total: number
  currentPage: number
  pageSize: number
}>()

defineEmits<{
  'detail': [row: PaymentChannel]
  'edit': [row: PaymentChannel]
  'toggle': [row: PaymentChannel]
  'delete': [row: PaymentChannel]
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
</style>

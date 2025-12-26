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
        label="套餐名称"
        min-width="160"
      />
      <el-table-column
        prop="descript"
        label="描述"
        min-width="160"
      />
      <el-table-column
        prop="days"
        label="天数"
        width="100"
      />
      <el-table-column
        prop="price"
        label="价格"
        width="120"
      />
      <el-table-column
        prop="old_price"
        label="原价"
        width="120"
      />
      <el-table-column
        prop="sort"
        label="排序"
        width="100"
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
        label="热门"
        width="100"
      >
        <template #default="{ row }">
          <el-tag
            size="small"
            :type="row.is_hot === 1 ? 'warning' : 'info'"
          >
            {{ row.is_hot === 1 ? '是' : '否' }}
          </el-tag>
        </template>
      </el-table-column>
      <el-table-column
        prop="sold_num"
        label="销量"
        width="100"
      />
      <el-table-column
        prop="sold_total"
        label="售额"
        width="120"
      />
      <el-table-column
        prop="return_total"
        label="返额"
        width="120"
      />
      <el-table-column
        prop="return_num"
        label="返次"
        width="100"
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
import type { VipItem } from '../../composables/useVipList'

defineProps<{
  loading: boolean
  rows: VipItem[]
  total: number
  currentPage: number
  pageSize: number
}>()

defineEmits<{
  'detail': [row: VipItem]
  'edit': [row: VipItem]
  'toggle': [row: VipItem]
  'delete': [row: VipItem]
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

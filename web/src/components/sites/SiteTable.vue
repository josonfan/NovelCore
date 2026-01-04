<template>
  <el-card
    shadow="hover"
    class="table-card"
  >
    <el-table
      v-loading="loading"
      :data="data"
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
        label="名称"
        min-width="160"
      />
      <el-table-column
        prop="code"
        label="编码"
        min-width="140"
      />
      <el-table-column
        prop="base_api_url"
        label="基础API"
        min-width="200"
      />
      <el-table-column
        prop="primary_domain"
        label="主域名"
        min-width="160"
      />
      <el-table-column
        label="启用"
        width="80"
      >
        <template #default="{ row }">
          <el-tag
            size="small"
            :type="row.is_active === 1 ? 'success' : 'danger'"
          >
            {{ row.is_active === 1 ? '启用' : '停用' }}
          </el-tag>
        </template>
      </el-table-column>
      <el-table-column
        prop="remark"
        label="备注"
        min-width="160"
      />
      <el-table-column
        prop="created_at"
        label="创建时间"
        min-width="160"
      />
      <el-table-column
        label="操作"
        width="400"
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
            {{ row.is_active === 1 ? '停用' : '启用' }}
          </el-button>
          <el-button
            link
            type="success"
            @click="$emit('config', row)"
          >
            配置
          </el-button>
          <el-button
            link
            type="success"
            @click="$emit('init', row)"
          >
            初始化数据
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
import type { Site } from '../../api/sites'

defineProps<{
  data: Site[]
  loading: boolean
  total: number
  currentPage: number
  pageSize: number
}>()

defineEmits<{
  'detail': [row: Site]
  'edit': [row: Site]
  'toggle': [row: Site]
  'config': [row: Site]
  'init': [row: Site]
  'delete': [row: Site]
  'page-change': [page: number]
  'size-change': [size: number]
}>()
</script>

<style scoped>
.pager {
  display: flex;
  justify-content: flex-end;
  padding-top: 12px;
}
</style>

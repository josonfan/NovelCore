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
      highlight-current-row
    >
      <el-table-column
        prop="id"
        label="ID"
        width="80"
      />
      <el-table-column
        prop="name"
        label="名称"
        min-width="180"
      />
      <el-table-column
        label="Slug"
        min-width="160"
      >
        <template #default="{ row }">
          {{ row.slug || row.code || '' }}
        </template>
      </el-table-column>
      <el-table-column
        label="状态"
        width="120"
      >
        <template #default="{ row }">
          <el-tag
            :type="Number(row.is_active) === 1 ? 'success' : 'danger'"
            size="small"
          >
            {{ Number(row.is_active) === 1 ? '启用' : '禁用' }}
          </el-tag>
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
        label="操作"
        width="260"
        fixed="right"
      >
        <template #default="{ row }">
          <template
            v-for="btn in actionButtonsRow"
            :key="btn.id"
          >
            <el-button
              :type="resolveBtnType(btn)"
              link
              @click="$emit('action-row', btn, row)"
            >
              <el-icon
                v-if="resolveIcon(btn.icon)"
                :size="16"
                style="margin-right: 6px"
              >
                <component :is="resolveIcon(btn.icon)" />
              </el-icon>
              {{ btn.name }}
            </el-button>
          </template>
          <el-button
            type="primary"
            link
            @click="$emit('edit', row)"
          >
            编辑
          </el-button>
          <el-button
            type="danger"
            link
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
import type { Category } from '../../api/categories'
import type { ActionButton } from '../../composables/useActionButtons'
import { useActionButtons } from '../../composables/useActionButtons'

defineProps<{
  data: Category[]
  loading: boolean
  total: number
  currentPage: number
  pageSize: number
  actionButtonsRow: ActionButton[]
}>()

defineEmits<{
  'action-row': [btn: ActionButton, row: Category]
  'edit': [row: Category]
  'delete': [row: Category]
  'page-change': [page: number]
  'size-change': [size: number]
}>()

const { resolveIcon, resolveBtnType } = useActionButtons()
</script>

<style scoped>
.pager {
  display: flex;
  justify-content: flex-end;
  padding-top: 12px;
}
</style>

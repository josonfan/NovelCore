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
      row-key="id"
      :tree-props="{ children: 'children', hasChildren: 'hasChildren' }"
      default-expand-all
      highlight-current-row
    >
      <el-table-column
        prop="id"
        label="ID"
        width="80"
      />

      <el-table-column
        label="名称"
        min-width="200"
      >
        <template #default="{ row }">
          <span class="name-cell">
            <el-icon
              v-if="menuIcon(row.icon)"
              :size="16"
              class="menu-icon"
            >
              <component :is="menuIcon(row.icon)" />
            </el-icon>
            <span>{{ row.name }}</span>
          </span>
        </template>
      </el-table-column>

      <el-table-column
        prop="code"
        label="编码"
        min-width="140"
      />

      <el-table-column
        label="路由"
        min-width="160"
      >
        <template #default="{ row }">
          <el-text
            type="info"
            size="small"
          >
            {{ formatPath(row.path) }}
          </el-text>
        </template>
      </el-table-column>

      <el-table-column
        label="类型"
        width="90"
        align="center"
      >
        <template #default="{ row }">
          <el-tag
            size="small"
            :type="row.type === 'menu' ? 'success' : 'info'"
          >
            {{ row.type === 'menu' ? '菜单' : '按钮' }}
          </el-tag>
        </template>
      </el-table-column>

      <el-table-column
        label="显示"
        width="80"
        align="center"
      >
        <template #default="{ row }">
          <el-tag
            size="small"
            :type="row.visible === 1 ? '' : 'warning'"
          >
            {{ row.visible === 1 ? '显示' : '隐藏' }}
          </el-tag>
        </template>
      </el-table-column>

      <el-table-column
        label="启用"
        width="80"
        align="center"
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
        prop="sort_order"
        label="排序"
        width="70"
        align="center"
      />

      <el-table-column
        label="操作"
        width="280"
        fixed="right"
      >
        <template #default="{ row }">
          <div class="action-buttons">
            <el-button
              type="primary"
              link
              size="small"
              @click="$emit('add-child', row)"
            >
              添加子级
            </el-button>
            <el-button
              type="primary"
              link
              size="small"
              @click="$emit('edit', row)"
            >
              编辑
            </el-button>
            <el-button
              type="danger"
              link
              size="small"
              @click="$emit('delete', row)"
            >
              删除
            </el-button>
            <el-dropdown trigger="click">
              <el-button
                link
                size="small"
              >
                更多
                <el-icon class="el-icon--right">
                  <ArrowDown />
                </el-icon>
              </el-button>
              <template #dropdown>
                <el-dropdown-menu>
                  <el-dropdown-item @click="$emit('detail', row)">
                    查看详情
                  </el-dropdown-item>
                  <el-dropdown-item @click="$emit('bind', row)">
                    绑定权限
                  </el-dropdown-item>
                </el-dropdown-menu>
              </template>
            </el-dropdown>
          </div>
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
  </el-card>
</template>

<script setup lang="ts">
import { ArrowDown } from '@element-plus/icons-vue'
import type { MenuNode } from '../../api/menus'
import { resolveMenuIcon } from '../../composables/useMenuForm'
import { pathOf } from '../../router/routes'

defineProps<{
  data: MenuNode[]
  loading: boolean
  currentPage: number
  pageSize: number
  total: number
}>()

defineEmits<{
  'edit': [row: MenuNode]
  'delete': [row: MenuNode]
  'detail': [row: MenuNode]
  'bind': [row: MenuNode]
  'add-child': [row: MenuNode]
  'page-change': [page: number]
  'size-change': [size: number]
}>()

function menuIcon(icon?: string) {
  return resolveMenuIcon(icon)
}

function formatPath(path?: string) {
  return path ? pathOf(path) : '-'
}
</script>

<style scoped>
.table-card {
  margin-top: 12px;
}

.name-cell {
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.menu-icon {
  color: var(--nc-primary);
}

.action-buttons {
  display: flex;
  align-items: center;
  gap: 4px;
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
</style>

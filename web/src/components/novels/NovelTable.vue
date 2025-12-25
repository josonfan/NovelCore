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
      @selection-change="handleSelect"
    >
      <el-table-column
        type="selection"
        width="48"
      />
      <el-table-column
        prop="id"
        label="ID"
        width="80"
      />
      <el-table-column
        label="封面"
        width="80"
      >
        <template #default="{ row }">
          <el-image
            v-if="row.cover"
            :src="row.cover"
            :preview-src-list="[row.cover]"
            :preview-teleported="true"
            fit="cover"
            class="cover-img"
          />
          <div
            v-else
            class="cover-placeholder"
          >
            <el-icon><Picture /></el-icon>
          </div>
        </template>
      </el-table-column>
      <el-table-column
        label="标题"
        min-width="220"
      >
        <template #default="{ row }">
          <el-tooltip
            :content="row.title"
            placement="top"
            :disabled="!row.title || row.title.length < 20"
          >
            <span class="title-cell">{{ row.title }}</span>
          </el-tooltip>
        </template>
      </el-table-column>
      <!-- <el-table-column
        prop="author"
        label="作者"
        min-width="160"
      /> -->
      <el-table-column
        label="标签"
        min-width="220"
      >
        <template #default="{ row }">
          <template
            v-for="t in row.tags || []"
            :key="t.id"
          >
            <el-tag
              size="small"
              :type="tagTypeFor(t.type)"
              style="margin-right: 4px; margin-bottom: 4px"
            >
              {{ t.name }}
            </el-tag>
          </template>
        </template>
      </el-table-column>
      <el-table-column
        label="连载状态"
        width="120"
      >
        <template #default="{ row }">
          <el-tag
            :type="row.status === 1 ? 'success' : 'warning'"
            size="small"
          >
            {{ row.status === 1 ? '已完结' : '连载中' }}
          </el-tag>
        </template>
      </el-table-column>
      <el-table-column
        label="审核状态"
        width="120"
      >
        <template #default="{ row }">
          <el-tag
            :type="auditTagType(row.audit_status)"
            size="small"
          >
            {{ auditLabel(row.audit_status) }}
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
        width="340"
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
            type="warning"
            link
            @click="$emit('audit', row)"
          >
            审核
          </el-button>
          <el-button
            type="success"
            link
            @click="$emit('chapters', row)"
          >
            章节列表
          </el-button>
          <el-button
            link
            @click="$emit('tags', row)"
          >
            设置标签
          </el-button>
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
import { Picture } from '@element-plus/icons-vue'
import type { Novel } from '../../api/novels'
import type { ActionButton } from '../../composables/useActionButtons'
import { useActionButtons } from '../../composables/useActionButtons'

defineProps<{
  data: Novel[]
  loading: boolean
  total: number
  currentPage: number
  pageSize: number
  actionButtonsRow: ActionButton[]
}>()

const emit = defineEmits<{
  'selection-change': [rows: Novel[]]
  'action-row': [btn: ActionButton, row: Novel]
  'audit': [row: Novel]
  'chapters': [row: Novel]
  'tags': [row: Novel]
  'edit': [row: Novel]
  'delete': [row: Novel]
  'page-change': [page: number]
  'size-change': [size: number]
}>()

const { resolveIcon, resolveBtnType } = useActionButtons()

function handleSelect(rows: Novel[]) {
  emit('selection-change', rows)
}

function tagTypeFor(type?: string) {
  const m: Record<string, string> = {
    theme: 'success',
    plot: 'warning',
    role: 'info',
    r18: 'danger',
    status: 'warning',
    other: '',
  }
  const v = (type || '').toLowerCase()
  return m[v] || ''
}

function auditLabel(s: unknown) {
  const v = Number(s ?? 0)
  if (v === 1) return '已通过'
  if (v === 2) return '已拒绝'
  if (v === 3) return '已下线'
  return '待审'
}

function auditTagType(s: unknown) {
  const v = Number(s ?? 0)
  if (v === 1) return 'success'
  if (v === 2) return 'danger'
  if (v === 3) return 'info'
  return 'warning'
}
</script>

<style scoped>
.pager {
  display: flex;
  justify-content: flex-end;
  padding-top: 12px;
}

.cover-img {
  width: 48px;
  height: 64px;
  border-radius: 4px;
  object-fit: cover;
}

.cover-placeholder {
  width: 48px;
  height: 64px;
  border-radius: 4px;
  background: var(--el-fill-color-light);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--el-text-color-placeholder);
  font-size: 20px;
}

.title-cell {
  display: block;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  max-width: 200px;
}
</style>

<style>
/* 修复图片预览被表格固定列遮挡的问题 */
.el-image-viewer__wrapper {
  z-index: 3000 !important;
}
</style>

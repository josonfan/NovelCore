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
      >
        <el-table-column
          prop="feedback_no"
          label="反馈编号"
          width="200"
        >
          <template #default="{ row }">
            <el-text
              type="primary"
              class="feedback-no"
            >
              {{ row.feedback_no }}
            </el-text>
          </template>
        </el-table-column>

        <el-table-column
          label="类型"
          width="110"
          align="center"
        >
          <template #default="{ row }">
            <el-tag
              size="small"
              type="info"
            >
              {{ getTypeName(row.type) }}
            </el-tag>
          </template>
        </el-table-column>

        <el-table-column
          prop="content"
          label="反馈内容"
          min-width="200"
        >
          <template #default="{ row }">
            <el-text
              class="feedback-content"
              :title="row.content"
            >
              {{ row.content }}
            </el-text>
          </template>
        </el-table-column>

        <el-table-column
          prop="user_id"
          label="用户ID"
          width="100"
          align="center"
        />

        <el-table-column
          prop="contact"
          label="联系方式"
          width="140"
        >
          <template #default="{ row }">
            {{ row.contact || '-' }}
          </template>
        </el-table-column>

        <el-table-column
          label="图片"
          width="70"
          align="center"
        >
          <template #default="{ row }">
            <el-tag
              v-if="row.image_count > 0"
              size="small"
              type="warning"
            >
              {{ row.image_count }}张
            </el-tag>
            <span
              v-else
              class="text-muted"
            >-</span>
          </template>
        </el-table-column>

        <el-table-column
          label="状态"
          width="90"
          align="center"
        >
          <template #default="{ row }">
            <el-tag
              size="small"
              :type="getStatusInfo(row.status).type"
            >
              {{ getStatusInfo(row.status).text }}
            </el-tag>
          </template>
        </el-table-column>

        <el-table-column
          label="提交时间"
          width="160"
        >
          <template #default="{ row }">
            <el-text
              type="info"
              size="small"
            >
              {{ row.created_at }}
            </el-text>
          </template>
        </el-table-column>

        <el-table-column
          label="操作"
          width="140"
          fixed="right"
        >
          <template #default="{ row }">
            <el-button
              link
              size="small"
              @click="$emit('detail', row)"
            >
              详情
            </el-button>
            <el-button
              v-if="row.status !== 2 && row.status !== 3 && row.status !== 4"
              type="primary"
              link
              size="small"
              @click="$emit('process', row)"
            >
              处理
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
import type { Feedback } from '../../api/feedbacks'

defineProps<{
  data: Feedback[]
  loading: boolean
  currentPage: number
  pageSize: number
  total: number
  hasSite: boolean
  getStatusInfo: (status: number) => { text: string; type: string }
  getTypeName: (type: string) => string
}>()

defineEmits<{
  'detail': [row: Feedback]
  'process': [row: Feedback]
  'page-change': [page: number]
  'size-change': [size: number]
}>()
</script>

<style scoped>
.table-card {
  margin-top: 12px;
}

.feedback-no {
  font-family: 'Monaco', 'Menlo', monospace;
  font-size: 12px;
}

.feedback-content {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
}

.text-muted {
  color: var(--nc-muted);
  font-size: 12px;
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

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
          prop="id"
          label="ID"
          width="80"
        />

        <el-table-column
          prop="novel_id"
          label="小说ID"
          width="90"
        />

        <el-table-column
          prop="chapter_id"
          label="章节ID"
          width="90"
        >
          <template #default="{ row }">
            {{ row.chapter_id || '-' }}
          </template>
        </el-table-column>

        <el-table-column
          prop="user_id"
          label="用户ID"
          width="90"
        />

        <el-table-column
          label="评论内容"
          min-width="240"
        >
          <template #default="{ row }">
            <div class="content-cell">
              <el-tag
                v-if="row.is_r18 === 1"
                type="danger"
                size="small"
                class="r18-tag"
              >
                R18
              </el-tag>
              <span class="content-text">{{ row.content }}</span>
            </div>
          </template>
        </el-table-column>

        <el-table-column
          label="状态"
          width="100"
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
          prop="like_count"
          label="点赞"
          width="70"
          align="center"
        />

        <el-table-column
          label="创建时间"
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
          width="200"
          fixed="right"
        >
          <template #default="{ row }">
            <div class="action-buttons">
              <el-button
                v-if="row.status !== 1"
                type="success"
                link
                size="small"
                @click="$emit('approve', row)"
              >
                通过
              </el-button>
              <el-button
                v-if="row.status !== 2"
                type="warning"
                link
                size="small"
                @click="$emit('reject', row)"
              >
                拒绝
              </el-button>
              <el-button
                link
                size="small"
                @click="$emit('detail', row)"
              >
                详情
              </el-button>
              <el-button
                type="danger"
                link
                size="small"
                @click="$emit('delete', row)"
              >
                删除
              </el-button>
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
    </template>
  </el-card>
</template>

<script setup lang="ts">
import type { SiteComment } from '../../composables/useCommentList'
import type { GetStatusInfoFn } from '../../api/comments'

defineProps<{
  data: SiteComment[]
  loading: boolean
  currentPage: number
  pageSize: number
  total: number
  hasSite: boolean
  getStatusInfo: GetStatusInfoFn
}>()

defineEmits<{
  'approve': [row: SiteComment]
  'reject': [row: SiteComment]
  'detail': [row: SiteComment]
  'delete': [row: SiteComment]
  'page-change': [page: number]
  'size-change': [size: number]
}>()
</script>

<style scoped>
.table-card {
  margin-top: 12px;
}

.content-cell {
  display: flex;
  align-items: flex-start;
  gap: 6px;
}

.r18-tag {
  flex-shrink: 0;
}

.content-text {
  word-break: break-all;
  line-height: 1.5;
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

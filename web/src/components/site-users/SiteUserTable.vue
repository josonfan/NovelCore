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
        <!-- <el-table-column
          prop="id"
          label="ID"
          width="80"
        /> -->

        <el-table-column
          prop="user_id"
          label="用户ID"
          width="100"
        />

        <el-table-column
          label="用户信息"
          min-width="200"
        >
          <template #default="{ row }">
            <div class="user-info">
              <el-avatar
                :size="32"
                :src="row.avatar"
              >
                {{ row.nickname?.charAt(0) || row.username?.charAt(0) || 'U' }}
              </el-avatar>
              <div class="user-text">
                <div class="nickname">
                  {{ row.nickname || '-' }}
                </div>
                <div class="username">
                  @{{ row.username }}
                </div>
              </div>
            </div>
          </template>
        </el-table-column>

        <el-table-column
          prop="email"
          label="邮箱"
          min-width="180"
        >
          <template #default="{ row }">
            {{ row.email || '-' }}
          </template>
        </el-table-column>

        <el-table-column
          prop="device"
          label="设备"
          width="80"
          align="center"
        >
          <template #default="{ row }">
            <el-tag
              size="small"
              type="info"
            >
              {{ row.device || '-' }}
            </el-tag>
          </template>
        </el-table-column>

        <el-table-column
          label="状态"
          width="80"
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
          label="VIP到期"
          width="110"
          align="center"
        >
          <template #default="{ row }">
            <el-tag
              v-if="row.vip_expire && row.vip_expire > 0"
              size="small"
              type="warning"
            >
              {{ formatVipExpire(row.vip_expire) }}
            </el-tag>
            <span
              v-else
              class="text-muted"
            >非VIP</span>
          </template>
        </el-table-column>

        <el-table-column
          label="注册时间"
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
              :type="row.status === 1 ? 'danger' : 'success'"
              link
              size="small"
              @click="$emit('toggle-status', row)"
            >
              {{ row.status === 1 ? '禁用' : '启用' }}
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
import type { SiteUser } from '../../composables/useSiteUserList'
import type { GetUserStatusInfoFn, FormatVipExpireFn } from '../../api/siteUsers'

defineProps<{
  data: SiteUser[]
  loading: boolean
  currentPage: number
  pageSize: number
  total: number
  hasSite: boolean
  getStatusInfo: GetUserStatusInfoFn
  formatVipExpire: FormatVipExpireFn
}>()

defineEmits<{
  'detail': [row: SiteUser]
  'toggle-status': [row: SiteUser]
  'page-change': [page: number]
  'size-change': [size: number]
}>()
</script>

<style scoped>
.table-card {
  margin-top: 12px;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 10px;
}

.user-text {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.nickname {
  font-weight: 500;
}

.username {
  font-size: 12px;
  color: var(--nc-muted);
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

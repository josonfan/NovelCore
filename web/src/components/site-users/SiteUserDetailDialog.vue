<template>
  <el-dialog
    :model-value="modelValue"
    title="用户详情"
    width="600px"
    @update:model-value="$emit('update:modelValue', $event)"
  >
    <div
      v-if="detail"
      class="user-header"
    >
      <el-avatar
        :size="64"
        :src="detail.avatar"
      >
        {{ detail.nickname?.charAt(0) || detail.username?.charAt(0) || 'U' }}
      </el-avatar>
      <div class="user-header-info">
        <div class="nickname">
          {{ detail.nickname || detail.username }}
        </div>
        <div class="username">
          @{{ detail.username }}
        </div>
      </div>
    </div>

    <el-descriptions
      :column="cols"
      border
      class="detail-desc"
    >
      <el-descriptions-item label="用户ID">
        {{ detail?.id }}
      </el-descriptions-item>
      <el-descriptions-item label="站点用户ID">
        {{ detail?.user_id }}
      </el-descriptions-item>
      <el-descriptions-item label="站点ID">
        {{ detail?.site_id }}
      </el-descriptions-item>
      <el-descriptions-item label="邮箱">
        {{ detail?.email || '-' }}
      </el-descriptions-item>
      <el-descriptions-item label="设备">
        <el-tag
          size="small"
          type="info"
        >
          {{ detail?.device || '-' }}
        </el-tag>
      </el-descriptions-item>
      <el-descriptions-item label="状态">
        <el-tag
          size="small"
          :type="getStatusInfo(detail?.status || 0).type"
        >
          {{ getStatusInfo(detail?.status || 0).text }}
        </el-tag>
      </el-descriptions-item>
      <el-descriptions-item label="客户端版本">
        {{ detail?.client_version || '-' }}
      </el-descriptions-item>
      <el-descriptions-item label="VIP到期">
        <el-tag
          v-if="detail?.vip_expire && detail.vip_expire > 0"
          size="small"
          type="warning"
        >
          {{ formatVipExpire(detail.vip_expire) }}
        </el-tag>
        <span v-else>非VIP</span>
      </el-descriptions-item>
      <el-descriptions-item label="注册时间">
        {{ detail?.created_at }}
      </el-descriptions-item>
      <el-descriptions-item label="更新时间">
        {{ detail?.updated_at }}
      </el-descriptions-item>
      <el-descriptions-item
        label="最后同步"
        :span="2"
      >
        {{ detail?.last_synced_at }}
      </el-descriptions-item>
    </el-descriptions>

    <template #footer>
      <el-button @click="$emit('update:modelValue', false)">
        关闭
      </el-button>
    </template>
  </el-dialog>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import type { SiteUser } from '../../composables/useSiteUserList'
import type { GetUserStatusInfoFn, FormatVipExpireFn } from '../../api/siteUsers'

defineProps<{
  modelValue: boolean
  detail: SiteUser | null
  getStatusInfo: GetUserStatusInfoFn
  formatVipExpire: FormatVipExpireFn
}>()

defineEmits<{
  'update:modelValue': [value: boolean]
}>()

const cols = ref(2)

function updateCols() {
  cols.value = window.innerWidth < 600 ? 1 : 2
}

onMounted(() => {
  updateCols()
  window.addEventListener('resize', updateCols)
})

onUnmounted(() => {
  window.removeEventListener('resize', updateCols)
})
</script>

<style scoped>
.user-header {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 20px;
  padding-bottom: 16px;
  border-bottom: 1px solid var(--nc-border, #e5e7eb);
}

.user-header-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.nickname {
  font-size: 18px;
  font-weight: 600;
}

.username {
  font-size: 14px;
  color: var(--nc-muted);
}

.detail-desc {
  margin-top: 8px;
}
</style>

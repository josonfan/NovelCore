<template>
  <el-dialog
    :model-value="modelValue"
    title="统计详情"
    width="600px"
    @update:model-value="$emit('update:modelValue', $event)"
  >
    <div
      v-if="detail"
      class="stats-header"
    >
      <div class="stat-date">
        <el-icon :size="20">
          <Calendar />
        </el-icon>
        <span>{{ detail.stat_date }}</span>
      </div>
    </div>

    <!-- 核心指标卡片 -->
    <div class="stats-cards">
      <div class="stat-card user">
        <div class="stat-label">新增用户</div>
        <div class="stat-value">{{ detail?.user_count || 0 }}</div>
      </div>
      <div class="stat-card read">
        <div class="stat-label">阅读次数</div>
        <div class="stat-value">{{ detail?.read_count || 0 }}</div>
      </div>
      <div class="stat-card order">
        <div class="stat-label">订单数</div>
        <div class="stat-value">{{ detail?.order_count || 0 }}</div>
      </div>
      <div class="stat-card amount">
        <div class="stat-label">订单金额</div>
        <div class="stat-value">{{ formatAmount(detail?.order_amount || '0.00') }}</div>
      </div>
    </div>

    <el-descriptions
      :column="cols"
      border
      class="detail-desc"
    >
      <el-descriptions-item label="统计ID">
        {{ detail?.id }}
      </el-descriptions-item>
      <el-descriptions-item label="站点ID">
        {{ detail?.site_id }}
      </el-descriptions-item>
      <el-descriptions-item label="统计日期">
        {{ detail?.stat_date }}
      </el-descriptions-item>
      <el-descriptions-item label="Stats ID">
        {{ detail?.stats_id }}
      </el-descriptions-item>
      <el-descriptions-item label="创建时间">
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
import { Calendar } from '@element-plus/icons-vue'
import type { SiteStat } from '../../api/siteStats'

defineProps<{
  modelValue: boolean
  detail: SiteStat | null
  formatAmount: (amount: string | number) => string
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
.stats-header {
  margin-bottom: 20px;
  padding-bottom: 16px;
  border-bottom: 1px solid var(--nc-border, #e5e7eb);
}

.stat-date {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 18px;
  font-weight: 600;
  color: var(--el-color-primary);
}

.stats-cards {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
  margin-bottom: 20px;
}

@media (max-width: 600px) {
  .stats-cards {
    grid-template-columns: repeat(2, 1fr);
  }
}

.stat-card {
  padding: 16px;
  border-radius: 8px;
  text-align: center;
}

.stat-card.user {
  background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
}

.stat-card.read {
  background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
}

.stat-card.order {
  background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
}

.stat-card.amount {
  background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
}

.stat-label {
  font-size: 13px;
  color: #666;
  margin-bottom: 8px;
}

.stat-value {
  font-size: 24px;
  font-weight: 700;
}

.stat-card.user .stat-value {
  color: #0284c7;
}

.stat-card.read .stat-value {
  color: #16a34a;
}

.stat-card.order .stat-value {
  color: #d97706;
}

.stat-card.amount .stat-value {
  color: #dc2626;
}

.detail-desc {
  margin-top: 8px;
}
</style>

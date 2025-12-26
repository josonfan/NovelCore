<template>
  <el-dialog
    :model-value="visible"
    title="套餐详情"
    width="640px"
    @update:model-value="$emit('update:visible', $event)"
  >
    <el-descriptions
      v-if="detail"
      :column="2"
      border
    >
      <el-descriptions-item label="ID">
        {{ detail.id }}
      </el-descriptions-item>
      <el-descriptions-item label="套餐名称">
        {{ detail.name }}
      </el-descriptions-item>
      <el-descriptions-item
        label="描述"
        :span="2"
      >
        {{ detail.descript || '-' }}
      </el-descriptions-item>
      <el-descriptions-item label="天数">
        {{ detail.days }}
      </el-descriptions-item>
      <el-descriptions-item label="价格">
        <span class="price">{{ detail.price }}</span>
      </el-descriptions-item>
      <el-descriptions-item label="原价">
        <span class="old-price">{{ detail.old_price || '-' }}</span>
      </el-descriptions-item>
      <el-descriptions-item label="排序">
        {{ detail.sort }}
      </el-descriptions-item>
      <el-descriptions-item label="状态">
        <el-tag
          size="small"
          :type="detail.status === 1 ? 'success' : 'danger'"
        >
          {{ detail.status === 1 ? '正常' : '停用' }}
        </el-tag>
      </el-descriptions-item>
      <el-descriptions-item label="热门">
        <el-tag
          size="small"
          :type="detail.is_hot === 1 ? 'warning' : 'info'"
        >
          {{ detail.is_hot === 1 ? '是' : '否' }}
        </el-tag>
      </el-descriptions-item>
      <el-descriptions-item label="销量">
        {{ detail.sold_num ?? 0 }}
      </el-descriptions-item>
      <el-descriptions-item label="售额">
        {{ detail.sold_total || '0.00' }}
      </el-descriptions-item>
      <el-descriptions-item label="返额">
        {{ detail.return_total || '0.00' }}
      </el-descriptions-item>
      <el-descriptions-item label="返次">
        {{ detail.return_num ?? 0 }}
      </el-descriptions-item>
      <el-descriptions-item label="创建时间">
        {{ detail.created_at || '-' }}
      </el-descriptions-item>
      <el-descriptions-item label="更新时间">
        {{ detail.updated_at || '-' }}
      </el-descriptions-item>
    </el-descriptions>
    <template #footer>
      <el-button
        type="primary"
        @click="$emit('update:visible', false)"
      >
        关闭
      </el-button>
    </template>
  </el-dialog>
</template>

<script setup lang="ts">
import type { VipItem } from '../../composables/useVipList'

defineProps<{
  visible: boolean
  detail: VipItem | null
}>()

defineEmits<{
  'update:visible': [value: boolean]
}>()
</script>

<style scoped>
.price {
  color: var(--el-color-danger);
  font-weight: 600;
}

.old-price {
  color: var(--nc-muted);
  text-decoration: line-through;
}
</style>

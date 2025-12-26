<template>
  <el-card
    shadow="never"
    class="toolbar"
  >
    <template #header>
      <div class="toolbar-header">
        <span class="title">支付渠道管理</span>
        <el-tag
          type="info"
          size="small"
        >
          共 {{ total }} 条
        </el-tag>
      </div>
    </template>
    <div class="toolbar-content">
      <div class="search-area">
        <el-input
          v-model="searchValue"
          placeholder="搜索渠道名称或地址"
          clearable
          :prefix-icon="Search"
          class="search-input"
        />
      </div>
      <div class="actions">
        <el-button
          :icon="Refresh"
          :loading="loading"
          @click="$emit('refresh')"
        >
          刷新
        </el-button>
        <el-button
          type="primary"
          :icon="Plus"
          @click="$emit('add')"
        >
          新建渠道
        </el-button>
      </div>
    </div>
  </el-card>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Search, Refresh, Plus } from '@element-plus/icons-vue'

const props = defineProps<{
  modelValue: string
  loading: boolean
  total: number
}>()

const emit = defineEmits<{
  'update:modelValue': [value: string]
  'refresh': []
  'add': []
}>()

const searchValue = computed({
  get: () => props.modelValue,
  set: (v) => emit('update:modelValue', v),
})
</script>

<style scoped>
.toolbar-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.toolbar-header .title {
  font-weight: 600;
  font-size: 16px;
}

.toolbar-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
}

.search-area {
  flex: 1;
  max-width: 320px;
}

.search-input {
  width: 100%;
}

.actions {
  display: flex;
  gap: 8px;
  flex-shrink: 0;
}
</style>

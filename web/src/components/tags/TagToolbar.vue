<template>
  <el-card
    shadow="never"
    class="toolbar"
  >
    <template #header>
      <div class="toolbar-header">
        <span class="title">标签管理</span>
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
          placeholder="搜索标签名称"
          clearable
          :prefix-icon="Search"
          class="search-input"
        />
      </div>
      <div class="actions">
        <template
          v-for="btn in actionButtons"
          :key="btn.id"
        >
          <el-button
            :type="resolveBtnType(btn)"
            @click="$emit('action', btn)"
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
          新建标签
        </el-button>
      </div>
    </div>
  </el-card>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Search, Refresh, Plus } from '@element-plus/icons-vue'
import type { ActionButton } from '../../composables/useActionButtons'
import { useActionButtons } from '../../composables/useActionButtons'

const props = defineProps<{
  modelValue: string
  loading: boolean
  total: number
  actionButtons: ActionButton[]
}>()

const emit = defineEmits<{
  'update:modelValue': [value: string]
  'action': [btn: ActionButton]
  'refresh': []
  'add': []
}>()

const { resolveIcon, resolveBtnType } = useActionButtons()

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
  flex-wrap: wrap;
  justify-content: flex-end;
}
</style>

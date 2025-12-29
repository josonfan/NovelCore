<template>
  <el-card
    shadow="never"
    class="toolbar"
  >
    <template #header>
      <div class="toolbar-header">
        <div class="title-group">
          <span class="title">菜单管理</span>
          <el-tag
            type="warning"
            size="small"
          >
            {{ currentParentLabel }}
          </el-tag>
        </div>
        <el-tag
          type="info"
          size="small"
        >
          共 {{ total }} 条
        </el-tag>
      </div>
    </template>

    <div class="toolbar-content">
      <div class="filters">
        <ParentFilterSelect
          :model-value="parentId"
          :data="treeSelectData"
          :props="treeProps"
          placeholder="选择上级菜单"
          class="filter-select"
          @update:model-value="$emit('update:parentId', $event)"
        />
        <el-input
          :model-value="keyword"
          placeholder="搜索名称/编码"
          clearable
          :prefix-icon="Search"
          class="search-input"
          @update:model-value="$emit('update:keyword', $event)"
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
          新建菜单
        </el-button>
      </div>
    </div>
  </el-card>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Search, Refresh, Plus } from '@element-plus/icons-vue'
import ParentFilterSelect from '../ParentFilterSelect.vue'
import type { MenuNode } from '../../api/menus'
import { buildTreeSelectData } from '../../composables/useMenuManage'

const props = defineProps<{
  parentId: string | number
  keyword: string
  loading: boolean
  total: number
  currentParentLabel: string
  options: MenuNode[]
}>()

defineEmits<{
  'update:parentId': [value: string | number]
  'update:keyword': [value: string]
  'refresh': []
  'add': []
}>()

const treeSelectData = computed(() => buildTreeSelectData(props.options))

const treeProps = {
  value: 'id',
  label: 'label',
  children: 'children',
}
</script>

<style scoped>
.toolbar-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.title-group {
  display: flex;
  align-items: center;
  gap: 8px;
}

.title {
  font-weight: 600;
  font-size: 16px;
}

.toolbar-content {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  flex-wrap: wrap;
}

.filters {
  display: flex;
  gap: 12px;
  flex: 1;
}

.filter-select {
  width: 200px;
}

.search-input {
  width: 200px;
}

.actions {
  display: flex;
  gap: 8px;
  flex-shrink: 0;
}
</style>

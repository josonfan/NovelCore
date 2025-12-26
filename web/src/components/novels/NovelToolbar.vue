<template>
  <el-card
    shadow="never"
    class="toolbar"
  >
    <template #header>
      <div class="toolbar-header">
        <span class="title">小说管理</span>
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
          placeholder="搜索标题/作者"
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
          新建小说
        </el-button>
      </div>
    </div>
    <div class="filter-row">
      <el-select
        v-model="localFilters.category_id"
        placeholder="全部分类"
        clearable
        @change="onFilterChange"
      >
        <el-option
          v-for="cat in categories"
          :key="cat.id"
          :label="cat.name"
          :value="String(cat.id)"
        />
      </el-select>
      <el-select
        v-model="localFilters.status"
        placeholder="完结状态"
        clearable
        @change="onFilterChange"
      >
        <el-option
          label="未完结"
          value="0"
        />
        <el-option
          label="已完结"
          value="1"
        />
      </el-select>
      <el-select
        v-model="localFilters.is_vip"
        placeholder="VIP"
        clearable
        @change="onFilterChange"
      >
        <el-option
          label="否"
          value="0"
        />
        <el-option
          label="是"
          value="1"
        />
      </el-select>
      <el-select
        v-model="localFilters.is_r18"
        placeholder="R18"
        clearable
        @change="onFilterChange"
      >
        <el-option
          label="否"
          value="0"
        />
        <el-option
          label="是"
          value="1"
        />
      </el-select>
    </div>
  </el-card>
</template>

<script setup lang="ts">
import { computed, reactive, watch } from 'vue'
import { Search, Refresh, Plus } from '@element-plus/icons-vue'
import type { ActionButton } from '../../composables/useActionButtons'
import { useActionButtons } from '../../composables/useActionButtons'
import type { NovelFilters } from '../../composables/useNovelList'

const props = defineProps<{
  modelValue: string
  loading: boolean
  total: number
  actionButtons: ActionButton[]
  filters: NovelFilters
  categories: Array<{ id: number | string; name: string }>
}>()

const emit = defineEmits<{
  'update:modelValue': [value: string]
  'update:filters': [value: NovelFilters]
  'action': [btn: ActionButton]
  'refresh': []
  'add': []
  'filter-change': []
}>()

const { resolveIcon, resolveBtnType } = useActionButtons()

const searchValue = computed({
  get: () => props.modelValue,
  set: (v) => emit('update:modelValue', v),
})

// 本地筛选状态
const localFilters = reactive<NovelFilters>({
  category_id: '',
  status: '',
  is_vip: '',
  is_r18: '',
})

// 同步外部 filters 到本地
watch(
  () => props.filters,
  (val) => {
    if (val) {
      localFilters.category_id = val.category_id
      localFilters.status = val.status
      localFilters.is_vip = val.is_vip
      localFilters.is_r18 = val.is_r18
    }
  },
  { immediate: true, deep: true }
)

function onFilterChange() {
  emit('update:filters', { ...localFilters })
  emit('filter-change')
}
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
  margin-bottom: 12px;
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

.filter-row {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
}

.filter-row .el-select {
  width: 140px;
}
</style>

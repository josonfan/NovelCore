<template>
  <el-card shadow="never" class="toolbar">
    <div class="toolbar-grid">
      <div class="cell">
        <el-input v-model="searchValue" placeholder="搜索标题/作者" clearable />
      </div>
      <div class="actions">
        <template v-for="btn in actionButtons" :key="btn.id">
          <el-button @click="$emit('action', btn)" :type="resolveBtnType(btn)">
            <el-icon v-if="resolveIcon(btn.icon)" :size="16" style="margin-right: 6px">
              <component :is="resolveIcon(btn.icon)" />
            </el-icon>
            {{ btn.name }}
          </el-button>
        </template>
        <el-button @click="$emit('refresh')" :loading="loading">刷新</el-button>
        <el-button type="primary" @click="$emit('add')">新建</el-button>
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
        <el-option label="未完结" value="0" />
        <el-option label="已完结" value="1" />
      </el-select>
      <el-select
        v-model="localFilters.is_vip"
        placeholder="VIP"
        clearable
        @change="onFilterChange"
      >
        <el-option label="否" value="0" />
        <el-option label="是" value="1" />
      </el-select>
      <el-select
        v-model="localFilters.is_r18"
        placeholder="R18"
        clearable
        @change="onFilterChange"
      >
        <el-option label="否" value="0" />
        <el-option label="是" value="1" />
      </el-select>
    </div>
    <div class="subline">共 {{ total }} 条</div>
  </el-card>
</template>

<script setup lang="ts">
import { computed, reactive, watch } from 'vue'
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
  (e: 'update:modelValue', value: string): void
  (e: 'update:filters', value: NovelFilters): void
  (e: 'action', btn: ActionButton): void
  (e: 'refresh'): void
  (e: 'add'): void
  (e: 'filter-change'): void
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
.toolbar {
  display: grid;
  gap: 8px;
}

.toolbar-grid {
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 12px;
  align-items: center;
}

.toolbar-grid .actions {
  justify-self: end;
  display: inline-flex;
  gap: 8px;
}

.filter-row {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
}

.filter-row .el-select {
  width: 140px;
}

.subline {
  color: var(--nc-muted);
  font-size: 12px;
}
</style>

<template>
  <el-card shadow="never" class="toolbar">
    <template #header>
      章节管理<span v-if="novelTitle">（{{ novelTitle }}）</span>
    </template>
    <div class="toolbar-grid">
      <div class="cell">
        <el-input
          v-model="searchValue"
          placeholder="搜索标题/小说ID"
          clearable
        />
      </div>
      <div class="actions">
        <template v-for="btn in actionButtons" :key="btn.id">
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
        <el-button :loading="loading" @click="$emit('refresh')">
          刷新
        </el-button>
        <el-button type="primary" @click="$emit('add')">
          新建
        </el-button>
      </div>
    </div>
    <div class="subline">共 {{ total }} 条</div>
  </el-card>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { ActionButton } from '../../composables/useActionButtons'
import { useActionButtons } from '../../composables/useActionButtons'

const props = defineProps<{
  modelValue: string
  loading: boolean
  total: number
  novelTitle: string
  actionButtons: ActionButton[]
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void
  (e: 'action', btn: ActionButton): void
  (e: 'refresh'): void
  (e: 'add'): void
}>()

const { resolveIcon, resolveBtnType } = useActionButtons()

const searchValue = computed({
  get: () => props.modelValue,
  set: (v) => emit('update:modelValue', v),
})
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

.subline {
  color: var(--nc-muted);
  font-size: 12px;
}
</style>

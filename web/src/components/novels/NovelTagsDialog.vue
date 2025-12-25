<template>
  <el-dialog
    :model-value="visible"
    @update:model-value="$emit('update:visible', $event)"
    title="设置标签"
    width="640px"
  >
    <div v-loading="loading" style="display: grid; gap: 12px">
      <div
        v-for="g in tagGroups"
        :key="g.type"
        style="border: 1px solid var(--nc-border); border-radius: 8px; padding: 8px"
      >
        <div style="font-weight: 600; margin-bottom: 6px">{{ g.label }}</div>
        <el-checkbox-group :model-value="selectedTags[g.type] || []" @update:model-value="updateSelection(g.type, $event)">
          <el-checkbox v-for="c in g.children" :key="c.id" :label="c.id">
            {{ c.name }}
          </el-checkbox>
        </el-checkbox-group>
      </div>
    </div>
    <template #footer>
      <el-button @click="$emit('update:visible', false)">取消</el-button>
      <el-button type="primary" @click="$emit('submit')">保存标签</el-button>
    </template>
  </el-dialog>
</template>

<script setup lang="ts">
import type { TagGroup } from '../../composables/useNovelTags'

defineProps<{
  visible: boolean
  loading: boolean
  tagGroups: TagGroup[]
  selectedTags: Record<string, Array<number | string>>
}>()

const emit = defineEmits<{
  (e: 'update:visible', value: boolean): void
  (e: 'update:selectedTags', type: string, ids: Array<number | string>): void
  (e: 'submit'): void
}>()

function updateSelection(type: string, ids: Array<number | string>) {
  emit('update:selectedTags', type, ids)
}
</script>

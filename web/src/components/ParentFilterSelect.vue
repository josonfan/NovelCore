<template>
  <el-tree-select
    v-model="inner"
    :data="data"
    :props="props"
    :placeholder="placeholder || '选择上级菜单'"
    filterable
    clearable
    check-strictly
    automatic-dropdown
    :default-expand-all="true"
    :render-after-expand="false"
    style="width: 100%"
  />
</template>

<script setup lang="ts">
import { computed } from 'vue'

const emit = defineEmits<{ (e: 'update:modelValue', v: string | number): void }>()
const p = defineProps<{ modelValue: string | number; data: any[]; props: Record<string, any>; placeholder?: string }>()

const inner = computed({
  get: () => p.modelValue,
  set: (v) => emit('update:modelValue', v as any),
})
</script>

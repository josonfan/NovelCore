<template>
  <el-dialog
    :model-value="modelValue"
    title="绑定权限"
    width="600px"
    @update:model-value="$emit('update:modelValue', $event)"
  >
    <div
      v-if="permOptions.length === 0"
      class="empty-tip"
    >
      暂无可绑定的权限
    </div>
    <el-checkbox-group
      v-else
      v-model="localPermIds"
      class="perm-list"
    >
      <el-checkbox
        v-for="p in permOptions"
        :key="p.value"
        :value="p.value"
        class="perm-item"
      >
        {{ p.label }}
      </el-checkbox>
    </el-checkbox-group>

    <template #footer>
      <el-button @click="$emit('update:modelValue', false)">
        取消
      </el-button>
      <el-button
        type="primary"
        :disabled="permOptions.length === 0"
        @click="handleSave"
      >
        保存
      </el-button>
    </template>
  </el-dialog>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'

const props = defineProps<{
  modelValue: boolean
  permOptions: Array<{ label: string; value: number }>
  permIds: number[]
}>()

const emit = defineEmits<{
  'update:modelValue': [value: boolean]
  'update:permIds': [value: number[]]
  'save': []
}>()

const localPermIds = ref<number[]>([])

// 同步外部值
watch(
  () => props.permIds,
  (val) => {
    localPermIds.value = [...val]
  },
  { immediate: true }
)

function handleSave() {
  emit('update:permIds', localPermIds.value)
  emit('save')
}
</script>

<style scoped>
.empty-tip {
  text-align: center;
  color: var(--nc-muted);
  padding: 24px;
}

.perm-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
  max-height: 400px;
  overflow-y: auto;
}

.perm-item {
  margin: 0;
}
</style>

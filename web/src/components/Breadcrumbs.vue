<template>
  <el-breadcrumb separator="/" class="crumbs">
    <el-breadcrumb-item v-for="(c, i) in items" :key="i">{{ c }}</el-breadcrumb-item>
  </el-breadcrumb>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
const route = useRoute()
const items = computed(() => {
  const segs: string[] = []
  const m = route.meta as any
  if (m?.title) segs.push(m.title)
  return segs
})
</script>

<style scoped>
.crumbs { font-weight: 500; }
</style>

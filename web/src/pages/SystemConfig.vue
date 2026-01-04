<template>
  <SystemSiteConfig :site-id="siteId" />
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import SystemSiteConfig from './SystemSiteConfig.vue'

const route = useRoute()

const siteId = computed(() => {
  const id = route.params.id
  return id ? Number(id) : 0
})
console.log(siteId.value)
</script>

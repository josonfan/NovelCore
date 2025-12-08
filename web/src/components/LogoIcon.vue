<template>
  <svg :width="size" :height="size" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
    <rect x="3" y="3" width="18" height="18" rx="4" fill="none" stroke="currentColor" stroke-width="1.6" />
    <path d="M7 8.5c2.2 0 3 .9 5 1.4V18H7c-1.7 0-3-1.3-3-3V9.5c0-.6.4-1 1-1z" fill="currentColor" />
    <path d="M17 8.5c-2.2 0-3 .9-5 1.4V18h5c1.7 0 3-1.3 3-3V9.5c0-.6-.4-1-1-1z" fill="currentColor" />
    <circle cx="12" cy="12" r="2.2" fill="#fff" stroke="currentColor" stroke-width="1.6" />
  </svg>
</template>

<script setup lang="ts">
defineProps<{ size?: number }>()
</script>


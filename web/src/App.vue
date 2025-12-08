<template>
  <el-config-provider :locale="locale">
    <router-view />
  </el-config-provider>
</template>

<script setup lang="ts">
import zhCn from 'element-plus/es/locale/lang/zh-cn'
const locale = zhCn
</script>

<style>
html, body, #app {
  height: 100%;
}
body {
  margin: 0;
  background: #f5f7fa;
  color: #111827;
  font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, 'Fira Sans', 'Droid Sans', 'Helvetica Neue', Arial, 'Noto Sans', sans-serif;
}
</style>

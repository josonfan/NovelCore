<template>
  <el-config-provider>
    <router-view />
  </el-config-provider>
</template>

<script setup lang="ts"></script>

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

<template>
  <div class="dashboard">
    <el-card class="hero" shadow="hover">
      <h2>欢迎使用 NovelCore 后台</h2>
      <p>请从左侧菜单开始使用。</p>
    </el-card>
  </div>
</template>

<script setup lang="ts"></script>

<style scoped>
.dashboard {
  display: grid;
  gap: 16px;
}
.hero {
  background: linear-gradient(135deg, rgba(30,58,138,0.35), rgba(67,56,202,0.25));
}
</style>

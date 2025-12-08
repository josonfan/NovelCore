<template>
  <div class="dashboard">
    <el-card class="hero" shadow="hover">
      <h2>{{ t('dashboard.welcome') }}</h2>
      <p class="muted">{{ t('dashboard.hint') }}</p>
    </el-card>
    <el-row :gutter="12" class="stats">
      <el-col :span="6">
        <el-card shadow="hover" class="stat"><div class="stat-title">今日新增</div><div class="stat-value">12</div></el-card>
      </el-col>
      <el-col :span="6">
        <el-card shadow="hover" class="stat"><div class="stat-title">待审核</div><div class="stat-value">5</div></el-card>
      </el-col>
      <el-col :span="6">
        <el-card shadow="hover" class="stat"><div class="stat-title">已发布</div><div class="stat-value">132</div></el-card>
      </el-col>
      <el-col :span="6">
        <el-card shadow="hover" class="stat"><div class="stat-title">用户数</div><div class="stat-value">48</div></el-card>
      </el-col>
    </el-row>
    <el-row :gutter="12" class="quick">
      <el-col :span="12">
        <el-card shadow="hover"><el-button type="primary">新建小说</el-button> <el-button>导入数据</el-button></el-card>
      </el-col>
      <el-col :span="12">
        <el-card shadow="hover"><el-button>审核队列</el-button> <el-button>用户管理</el-button></el-card>
      </el-col>
    </el-row>
  </div>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n'
const { t } = useI18n()
</script>

<style scoped>
.dashboard {
  display: grid;
  gap: 16px;
}
.hero { background: linear-gradient(135deg, rgba(59,130,246,0.14), rgba(99,102,241,0.12)); }
.muted { color: var(--nc-muted); }
.stats .stat { text-align: center; }
.stat-title { color: var(--nc-muted); font-size: 12px; }
.stat-value { font-size: 20px; font-weight: 600; }
</style>

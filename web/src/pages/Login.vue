<template>
  <div class="login-wrap">
    <div class="panel">
      <div class="brand">{{ t('common.system_name') }}</div>
      <el-form :model="form" @submit.prevent="onSubmit" class="form">
        <el-form-item>
          <el-input v-model="form.username" :placeholder="t('login.username')" />
        </el-form-item>
        <el-form-item>
          <el-input v-model="form.password" type="password" :placeholder="t('login.password')" />
        </el-form-item>
        <el-button type="primary" size="large" class="submit" @click="onSubmit">{{ t('login.submit') }}</el-button>
      </el-form>
    </div>
  </div>
  <div class="bg-gradient" />
  <div class="bg-blur" />
</template>

<script setup lang="ts">
import { reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../store'
import { http } from '../api/http'
import { ElMessage } from 'element-plus'
import { useI18n } from 'vue-i18n'

const form = reactive({ username: '', password: '' })
const router = useRouter()
const auth = useAuthStore()
const { t } = useI18n()

async function onSubmit() {
  try {
    const { data } = await http.post('Login/login', form)
    if (data?.code === 200) {
      auth.setToken(data.token)
      ElMessage.success(data?.msg || '登录成功')
      router.push('/')
    } else {
      ElMessage.error(data?.message || data?.msg || '登录失败')
    }
  } catch (e: any) {
    const resp = e?.response?.data
    ElMessage.error(resp?.message || resp?.msg || '登录失败')
  }
}
</script>

<style scoped>
.login-wrap {
  height: 100vh;
  display: grid;
  place-items: center;
}
.panel {
  width: 380px;
  padding: 24px;
  border-radius: 16px;
  background: #ffffff;
  border: 1px solid #e5e7eb;
  box-shadow: 0 10px 25px rgba(0,0,0,.06);
}
.brand {
  text-align: center;
  font-size: 18px;
  font-weight: 700;
  margin-bottom: 16px;
}
.form {
  display: grid;
  gap: 12px;
}
.submit {
  width: 100%;
}
.bg-gradient {
  position: fixed;
  inset: 0;
  background: radial-gradient(600px 400px at 20% 10%, rgba(59,130,246,.12), transparent), radial-gradient(600px 400px at 80% 50%, rgba(99,102,241,.10), transparent);
  z-index: -2;
}
.bg-blur {
  position: fixed;
  inset: 0;
  backdrop-filter: blur(24px);
  z-index: -1;
}
</style>

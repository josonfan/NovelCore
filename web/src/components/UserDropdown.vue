<template>
  <div class="user-wrap">
    <el-dropdown trigger="click" @command="onCommand">
      <span class="el-dropdown-link">
        <el-avatar :size="28" class="avatar">{{ initials }}</el-avatar>
        <span class="nickname">{{ displayName }}</span>
      </span>
      <template #dropdown>
        <el-dropdown-menu>
          <el-dropdown-item command="profile">个人信息</el-dropdown-item>
          <el-dropdown-item command="edit">编辑信息</el-dropdown-item>
          <el-dropdown-item command="password">修改密码</el-dropdown-item>
          <el-dropdown-item divided command="logout">退出</el-dropdown-item>
        </el-dropdown-menu>
      </template>
    </el-dropdown>

    <el-dialog v-model="showProfile" title="个人信息" width="420px">
      <el-descriptions :column="1" border>
        <el-descriptions-item label="用户名">{{ profile?.username }}</el-descriptions-item>
        <el-descriptions-item label="昵称">{{ profile?.nickname || '-' }}</el-descriptions-item>
        <el-descriptions-item label="最后登录">{{ profile?.last_login_at }}</el-descriptions-item>
        <el-descriptions-item label="创建时间">{{ profile?.created_at }}</el-descriptions-item>
      </el-descriptions>
    </el-dialog>

    <el-dialog v-model="showEdit" title="编辑信息" width="420px">
      <el-form :model="editForm" @submit.prevent>
        <el-form-item label="昵称">
          <el-input v-model="editForm.nickname" />
        </el-form-item>
        <el-button type="primary" @click="saveEdit">保存</el-button>
      </el-form>
    </el-dialog>

    <el-dialog v-model="showPwd" title="修改密码" width="420px">
      <el-form :model="pwdForm" @submit.prevent>
        <el-form-item label="旧密码">
          <el-input v-model="pwdForm.old_password" type="password" />
        </el-form-item>
        <el-form-item label="新密码">
          <el-input v-model="pwdForm.new_password" type="password" />
        </el-form-item>
        <el-form-item label="确认密码">
          <el-input v-model="pwdForm.confirm_password" type="password" />
        </el-form-item>
        <el-button type="primary" @click="savePwd">保存</el-button>
      </el-form>
    </el-dialog>
  </div>
  </template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'
import { useAuthStore } from '../store'
import { useUserStore } from '../store/user'
import { changePassword, logoutApi } from '../api/auth'

const router = useRouter()
const auth = useAuthStore()
const user = useUserStore()
const profile = computed(() => user.profile)
const displayName = computed(() => profile.value?.nickname || profile.value?.username || '用户')
const initials = computed(() => (profile.value?.nickname || profile.value?.username || '用').slice(0, 1))

const showProfile = ref(false)
const showEdit = ref(false)
const showPwd = ref(false)
const editForm = ref({ nickname: '' })
const pwdForm = ref({ old_password: '', new_password: '', confirm_password: '' })

onMounted(async () => {
  try {
    await user.load()
    editForm.value.nickname = user.profile?.nickname || ''
  } catch (_) {}
})

function onCommand(cmd: string) {
  if (cmd === 'profile') showProfile.value = true
  if (cmd === 'edit') showEdit.value = true
  if (cmd === 'password') showPwd.value = true
  if (cmd === 'logout') doLogout()
}

async function saveEdit() {
  await user.updateNickname(editForm.value.nickname)
  ElMessage.success('已保存')
  showEdit.value = false
}

async function savePwd() {
  if (!pwdForm.value.old_password || !pwdForm.value.new_password || !pwdForm.value.confirm_password) {
    ElMessage.error('请填写完整')
    return
  }
  if (pwdForm.value.new_password !== pwdForm.value.confirm_password) {
    ElMessage.error('两次密码不一致')
    return
  }
  try {
    const d = await changePassword({ password: pwdForm.value.old_password, new_password: pwdForm.value.new_password, confirm_password: pwdForm.value.confirm_password })
    if (d?.code === 200) {
      ElMessage.success(d?.msg || '密码已修改，请重新登录')
      await doLogout()
    } else {
      ElMessage.error(d?.msg || '修改失败')
    }
  } catch (e: any) {
    const resp = e?.response?.data
    ElMessage.error(resp?.message || resp?.msg || '修改失败')
  }
}

async function doLogout() {
  try { await logoutApi() } catch (_) {}
  auth.clear()
  user.clear()
  router.push('/login')
}
</script>

<style scoped>
.user-wrap {
  display: flex;
  align-items: center;
}
.el-dropdown-link {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
}
.avatar {
  background: #3b82f6;
  color: white;
}
.nickname {
  font-weight: 500;
}
</style>

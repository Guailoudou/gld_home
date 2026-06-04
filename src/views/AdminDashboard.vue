<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import HIcon from '@/components/HIcon.vue'
import type { AdminModule } from '@/models/admin/types'

const modules: AdminModule[] = [
  {
    name: '下载管理',
    path: '/admin/downloads',
    icon: 'download',
    description: '管理下载资源，包括添加、编辑、删除下载项',
    color: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'
  },
  {
    name: '下载包管理',
    path: '/admin/packages',
    icon: 'package',
    description: '创建和管理下载资源包，生成随机下载码',
    color: 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)'
  },
  {
    name: '留言管理',
    path: '/admin/messages',
    icon: 'chat',
    description: '审核和管理用户留言，控制展示状态',
    color: 'linear-gradient(135deg, #11998e 0%, #38ef7d 100%)'
  },
  {
    name: '链接管理',
    path: '/admin/links',
    icon: 'link',
    description: '管理链接导航内容，包括分组和链接的增删改查',
    color: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'
  },
  {
    name: '作品管理',
    path: '/admin/portfolio',
    icon: 'atom',
    description: '管理作品展示内容，包括作品的增删改查和排序',
    color: 'linear-gradient(135deg, #fa709a 0%, #fee140 100%)'
  }
]

// 登录验证
const adminPassword = ref('')
const isAuthenticated = ref(false)
const showPasswordModal = ref(false)
const passwordInput = ref('')
const passwordError = ref('')
const passwordHash = ref('')
const loading = ref(false)
const actionMessage = ref('')
const actionType = ref<'success' | 'error' | ''>('')

// 计算密码哈希
async function computePasswordHash(password: string): Promise<string> {
  const encoder = new TextEncoder()
  const data = encoder.encode(password)
  const hashBuffer = await crypto.subtle.digest('SHA-256', data)
  const hashArray = Array.from(new Uint8Array(hashBuffer))
  return hashArray.map(b => b.toString(16).padStart(2, '0')).join('')
}

// 验证密码
async function authenticate() {
  if (!passwordInput.value) {
    showActionMessage('请输入密码', 'error')
    return
  }

  passwordError.value = ''
  loading.value = true
  const hash = await computePasswordHash(passwordInput.value)
  
  try {
    // 使用downloads API验证密码
    const response = await fetch('/api/downloads.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'get_all',
        password_hash: hash
      })
    })
    
    const result = await response.json()
    
    if (result.success) {
      isAuthenticated.value = true
      adminPassword.value = passwordInput.value
      localStorage.setItem('admin_password', adminPassword.value)
      localStorage.setItem('admin_password_hash', hash)
      showPasswordModal.value = false
      showActionMessage('验证成功', 'success')
    } else if (result.error === '密码错误') {
      passwordError.value = '密码错误'
      localStorage.removeItem('admin_password')
      localStorage.removeItem('admin_password_hash')
    } else {
      passwordError.value = '验证失败'
    }
  } catch (error) {
    passwordError.value = '验证失败，请重试'
  } finally {
    loading.value = false
  }
}

// 退出登录
function logout() {
  localStorage.removeItem('admin_password')
  localStorage.removeItem('admin_password_hash')
  adminPassword.value = ''
  isAuthenticated.value = false
  showPasswordModal.value = true
  passwordInput.value = ''
  showActionMessage('已退出登录', 'success')
}

// 显示操作消息
function showActionMessage(msg: string, type: 'success' | 'error') {
  actionMessage.value = msg
  actionType.value = type
  setTimeout(() => {
    actionMessage.value = ''
    actionType.value = ''
  }, 3000)
}

onMounted(async () => {
  const savedPassword = localStorage.getItem('admin_password')
  const savedHash = localStorage.getItem('admin_password_hash')
  if (savedPassword && savedHash) {
    adminPassword.value = savedPassword
    passwordHash.value = savedHash
    isAuthenticated.value = true
    showPasswordModal.value = false
  } else {
    showPasswordModal.value = true
  }
})
</script>

<template>
  <div class="admin-dashboard">
    <div class="content-wrapper">
      <!-- 页面头部 -->
      <div class="page-header">
        <div class="header-content">
          <h1 class="page-title"><HIcon name="settings" :size="40" class="title-icon" /> 管理后台</h1>
          <p class="page-description">欢迎访问管理后台，请选择要管理的模块</p>
        </div>
        <div class="header-actions">
          <button v-if="isAuthenticated" @click="logout" class="btn-logout">
            <HIcon name="close" :size="16" />
            <span>退出登录</span>
          </button>
          <RouterLink to="/" class="home-link">
            <HIcon name="home" :size="20" class="home-icon" />
            <span>返回首页</span>
          </RouterLink>
        </div>
      </div>

      <!-- 密码验证 -->
      <div v-if="showPasswordModal" class="auth-card">
        <div class="auth-form">
          <h2 class="auth-title">需要管理密码</h2>
          <input
            v-model="passwordInput"
            type="password"
            placeholder="请输入管理密码"
            class="auth-input"
            @keyup.enter="authenticate"
          />
          <button class="auth-btn" @click="authenticate" :disabled="loading">
            {{ loading ? '验证中...' : '验证' }}
          </button>
          <p v-if="passwordError" class="auth-error">{{ passwordError }}</p>
        </div>
      </div>

      <!-- 管理模块卡片 -->
      <template v-else>
        <div class="modules-grid">
          <RouterLink 
            v-for="module in modules" 
            :key="module.path"
            :to="module.path"
            class="module-card"
            :style="{ background: module.color }"
          >
            <HIcon :name="module.icon as any" :size="64" class="card-icon" />
            <h2 class="card-title">{{ module.name }}</h2>
            <p class="card-description">{{ module.description }}</p>
            <HIcon name="arrow" :size="32" class="card-arrow" />
          </RouterLink>
        </div>

        <!-- 快速提示 -->
        <div class="tips-section">
          <h3 class="tips-title"><HIcon name="lightbulb" :size="24" class="tip-icon" /> 使用提示</h3>
          <div class="tips-list">
            <div class="tip-item">
              <HIcon name="visibility" :size="24" class="tip-icon" />
              <span>首次访问管理页面需要输入管理密码进行验证</span>
            </div>
            <div class="tip-item">
              <HIcon name="download" :size="24" class="tip-icon" />
              <span>下载管理：可以添加、编辑、删除下载资源，设置是否默认展示</span>
            </div>
            <div class="tip-item">
              <HIcon name="package" :size="24" class="tip-icon" />
              <span>下载包管理：选择多个资源创建下载包，生成 5 位随机下载码</span>
            </div>
            <div class="tip-item">
              <HIcon name="link" :size="24" class="tip-icon" />
              <span>下载码访问格式：/download/下载码（如 /download/abi3r）</span>
            </div>
            <div class="tip-item">
              <HIcon name="chat" :size="24" class="tip-icon" />
              <span>留言管理：审核用户留言，控制展示状态，支持批量操作</span>
            </div>
            <div class="tip-item">
              <HIcon name="link" :size="24" class="tip-icon" />
              <span>链接管理：管理链接导航的分组和链接，支持启用/禁用控制</span>
            </div>
            <div class="tip-item">
              <HIcon name="atom" :size="24" class="tip-icon" />
              <span>作品管理：管理作品展示的项目，支持拖拽排序、启用/禁用控制</span>
            </div>
          </div>
        </div>
      </template>

      <!-- 操作反馈 -->
      <div v-if="actionMessage" class="action-toast" :class="actionType">
        {{ actionMessage }}
      </div>
    </div>
  </div>
</template>

<style src="@/styles/AdminDashboard.scss" scoped lang="scss"></style>

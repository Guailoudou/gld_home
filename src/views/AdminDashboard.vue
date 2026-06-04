<script setup lang="ts">
import { RouterLink } from 'vue-router'
import HIcon from '@/components/HIcon.vue'
import { useAdminAuth } from '@/composables/admin/useAdminAuth'
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

const { authState, actionMessage, actionType, authenticate, logout } = useAdminAuth()
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
          <button v-if="authState.isAuthenticated" @click="logout" class="btn-logout">
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
      <div v-if="authState.showPasswordModal" class="auth-card">
        <div class="auth-form">
          <h2 class="auth-title">需要管理密码</h2>
          <input
            v-model="authState.passwordInput"
            type="password"
            placeholder="请输入管理密码"
            class="auth-input"
            @keyup.enter="authenticate"
          />
          <button class="auth-btn" @click="authenticate" :disabled="authState.loading">
            {{ authState.loading ? '验证中...' : '验证' }}
          </button>
          <p v-if="authState.passwordError" class="auth-error">{{ authState.passwordError }}</p>
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

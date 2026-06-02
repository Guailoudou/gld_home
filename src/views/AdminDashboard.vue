<script setup lang="ts">
import { RouterLink } from 'vue-router'
import HIcon from '@/components/HIcon.vue'

interface AdminModule {
  name: string
  path: string
  icon: string
  description: string
  color: string
}

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
  }
]
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
        <RouterLink to="/" class="home-link">
          <HIcon name="home" :size="20" class="home-icon" />
          <span>返回首页</span>
        </RouterLink>
      </div>

      <!-- 管理模块卡片 -->
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
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss">
.admin-dashboard {
  min-height: 100vh;
  padding: 20px;
  background: rgba(255, 255, 255, 0.2);
}

.content-wrapper {
  max-width: 1200px;
  margin: 0 auto;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 40px;
  padding: 24px;
  background: rgba(255, 255, 255, 0.35);
  backdrop-filter: blur(20px);
  border-radius: 20px;
  border: 1px solid rgba(255, 255, 255, 0.4);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);

  .header-content {
    .page-title {
      font-size: 2.5rem;
      font-weight: 800;
      color: rgba(30, 30, 30, 0.95);
      margin: 0 0 8px 0;
      letter-spacing: -0.5px;
      display: flex;
      align-items: center;
      gap: 12px;

      .title-icon {
        width: 40px;
        height: 40px;
        color: rgba(30, 30, 30, 0.9);
      }
    }

    .page-description {
      font-size: 1.1rem;
      color: rgba(30, 30, 30, 0.7);
      margin: 0;
    }
  }

  .home-link {
    padding: 12px 24px;
    background: rgba(255, 255, 255, 0.5);
    backdrop-filter: blur(20px);
    border: 2px solid rgba(255, 255, 255, 0.6);
    border-radius: 14px;
    color: rgba(30, 30, 30, 0.9);
    text-decoration: none;
    font-weight: 700;
    font-size: 1rem;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;

    .home-icon {
      width: 20px;
      height: 20px;
      color: rgba(30, 30, 30, 0.9);
    }

    &:hover {
      background: rgba(255, 255, 255, 0.7);
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }
  }
}

.modules-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 24px;
  margin-bottom: 40px;
}

.module-card {
  position: relative;
  padding: 32px;
  border-radius: 20px;
  text-decoration: none;
  color: white;
  transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
  overflow: hidden;
  cursor: pointer;
  min-height: 220px;
  display: flex;
  flex-direction: column;

  &::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
  }

  &:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);

    &::before {
      opacity: 1;
    }

    .card-arrow {
      transform: translateX(8px);
    }
  }

  .card-icon {
    width: 64px;
    height: 64px;
    margin-bottom: 16px;
    filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.2));
    color: white;
  }

  .card-title {
    font-size: 1.8rem;
    font-weight: 800;
    margin: 0 0 12px 0;
    letter-spacing: -0.5px;
    text-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
  }

  .card-description {
    font-size: 1rem;
    line-height: 1.6;
    opacity: 0.95;
    margin: 0;
    flex: 1;
    display: flex;
    align-items: flex-end;
    text-shadow: 0 1px 4px rgba(0, 0, 0, 0.15);
  }

  .card-arrow {
    position: absolute;
    bottom: 32px;
    right: 32px;
    font-size: 2rem;
    font-weight: 700;
    opacity: 0.8;
    transition: transform 0.3s ease;
  }
}

.tips-section {
  padding: 28px;
  background: rgba(255, 255, 255, 0.35);
  backdrop-filter: blur(20px);
  border-radius: 20px;
  border: 1px solid rgba(255, 255, 255, 0.4);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);

  .tips-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: rgba(30, 30, 30, 0.95);
    margin: 0 0 20px 0;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .tips-list {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 16px;
  }
}

.tip-item {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 16px;
  background: rgba(255, 255, 255, 0.5);
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.4);
  transition: all 0.3s ease;

  &:hover {
    background: rgba(255, 255, 255, 0.7);
    transform: translateX(4px);
  }

  .tip-icon {
    font-size: 1.5rem;
    flex-shrink: 0;
  }

  span:last-child {
    font-size: 0.95rem;
    color: rgba(30, 30, 30, 0.85);
    line-height: 1.5;
  }
}

@media (max-width: 768px) {
  .admin-dashboard {
    padding: 16px;
  }

  .page-header {
    flex-direction: column;
    gap: 16px;
    align-items: flex-start;

    .header-content {
      .page-title {
        font-size: 2rem;
      }
    }

    .home-link {
      align-self: flex-end;
    }
  }

  .modules-grid {
    grid-template-columns: 1fr;
    gap: 16px;
  }

  .module-card {
    min-height: 200px;
    padding: 24px;

    .card-icon {
      font-size: 3rem;
    }

    .card-title {
      font-size: 1.5rem;
    }
  }

  .tips-section {
    padding: 20px;

    .tips-list {
      grid-template-columns: 1fr;
    }
  }
}
</style>

<script setup lang="ts">
import { RouterLink } from 'vue-router'
import { siteConfig } from '@/config/site.config'

const { personalInfo, navCards } = siteConfig.home
</script>

<template>
  <div class="home-container">
    <div class="content-wrapper">
      <!-- 个人信息卡片 -->
      <div class="profile-card">
        <div class="avatar-container">
          <img :src="personalInfo.avatar" :alt="personalInfo.name" class="avatar" />
        </div>
        <h1 class="profile-name">{{ personalInfo.name }}</h1>
        <p class="profile-title">{{ personalInfo.title }}</p>
        <p class="profile-description">{{ personalInfo.description }}</p>
        
        <!-- 社交链接 -->
        <div class="social-links">
          <a 
            v-for="(social, index) in personalInfo.socialLinks" 
            :key="index"
            :href="social.url"
            target="_blank"
            class="social-link"
          >
            <span class="social-icon">{{ social.icon }}</span>
            <span class="social-name">{{ social.name }}</span>
          </a>
        </div>
      </div>

      <!-- 导航卡片 -->
      <div class="nav-cards">
        <!-- 动态导航卡片（来自 navigation 配置） -->
        <RouterLink 
          v-for="(link, index) in siteConfig.navigation" 
          :key="index"
          :to="link.url"
          class="nav-card"
        >
          <div class="nav-card-icon">
            <span v-if="index === 0">🔗</span>
            <span v-else-if="index === 1">💾</span>
            <span v-else-if="index === 2">🔧</span>
            <span v-else>💬</span>
          </div>
          <h3 class="nav-card-title">{{ link.name }}</h3>
          <p class="nav-card-description">前往 {{ link.name }} 页面</p>
        </RouterLink>

        <!-- 额外的导航卡片（来自 navCards 配置） -->
        <RouterLink 
          v-for="(card, index) in navCards" 
          :key="index"
          :to="card.url"
          class="nav-card"
          :class="{ featured: card.featured }"
        >
          <div class="nav-card-icon">
            <span>{{ card.icon }}</span>
          </div>
          <h3 class="nav-card-title">{{ card.title }}</h3>
          <p class="nav-card-description">{{ card.description }}</p>
        </RouterLink>
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss">
.home-container {
  min-height: 100vh;
  padding: 40px 20px;
  display: flex;
  justify-content: center;
  align-items: flex-start;
}

.content-wrapper {
  width: 100%;
  max-width: 1000px;
  display: flex;
  flex-direction: column;
  gap: 40px;
}

.profile-card {
  background: rgba(255, 255, 255, 0.25);
  backdrop-filter: blur(15px);
  border-radius: 20px;
  padding: 50px 30px;
  border: 1px solid rgba(255, 255, 255, 0.3);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
  text-align: center;
  transition: all 0.3s ease;

  &:hover {
    background: rgba(255, 255, 255, 0.35);
    transform: translateY(-4px);
    box-shadow: 0 16px 48px rgba(0, 0, 0, 0.15);
  }

  .avatar-container {
    margin-bottom: 25px;

    .avatar {
      width: 150px;
      height: 150px;
      border-radius: 50%;
      object-fit: cover;
      border: 4px solid rgba(255, 255, 255, 0.5);
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
      transition: all 0.3s ease;
    }

    &:hover .avatar {
      transform: scale(1.05);
      border-color: rgba(255, 255, 255, 0.8);
    }
  }

  .profile-name {
    font-size: 2.5rem;
    font-weight: 700;
    color: rgba(30, 30, 30, 0.95);
    margin: 0 0 10px 0;
    text-shadow: 0 2px 10px rgba(255, 255, 255, 0.3);
  }

  .profile-title {
    font-size: 1.2rem;
    color: rgba(30, 30, 30, 0.85);
    margin: 0 0 20px 0;
    font-weight: 500;
  }

  .profile-description {
    font-size: 1rem;
    color: rgba(30, 30, 30, 0.8);
    line-height: 1.6;
    max-width: 600px;
    margin: 0 auto 30px;
  }

  .social-links {
    display: flex;
    justify-content: center;
    gap: 15px;
    flex-wrap: wrap;

    .social-link {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 10px 20px;
      background: rgba(255, 255, 255, 0.6);
      border-radius: 25px;
      text-decoration: none;
      color: rgba(30, 30, 30, 0.9);
      font-weight: 500;
      transition: all 0.3s ease;

      &:hover {
        background: rgba(255, 255, 255, 0.8);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      }

      .social-icon {
        font-size: 1.2rem;
      }
    }
  }

  @media (max-width: 768px) {
    padding: 35px 20px;

    .profile-name {
      font-size: 2rem;
    }

    .avatar {
      width: 120px !important;
      height: 120px !important;
    }
  }
}

.nav-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 20px;

  @media (max-width: 768px) {
    grid-template-columns: repeat(2, 1fr);
  }

  @media (max-width: 480px) {
    grid-template-columns: 1fr;
  }
}

.nav-card {
  background: rgba(255, 255, 255, 0.25);
  backdrop-filter: blur(15px);
  border-radius: 16px;
  padding: 30px 25px;
  border: 1px solid rgba(255, 255, 255, 0.3);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
  text-decoration: none;
  text-align: center;
  transition: all 0.3s ease;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 15px;

  &:hover {
    background: rgba(255, 255, 255, 0.35);
    transform: translateY(-6px) scale(1.03);
    box-shadow: 0 16px 48px rgba(0, 0, 0, 0.2);
  }

  &.featured {
    background: rgba(255, 255, 255, 0.3);
    border-color: rgba(255, 255, 255, 0.5);

    &:hover {
      background: rgba(255, 255, 255, 0.45);
    }
  }

  .nav-card-icon {
    font-size: 3rem;
    margin-bottom: 5px;
  }

  .nav-card-title {
    font-size: 1.3rem;
    font-weight: 600;
    color: rgba(30, 30, 30, 0.95);
    margin: 0;
  }

  .nav-card-description {
    font-size: 0.9rem;
    color: rgba(30, 30, 30, 0.8);
    margin: 0;
  }
}
</style>

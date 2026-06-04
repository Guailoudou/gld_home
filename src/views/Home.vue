<script setup lang="ts">
import { RouterLink } from 'vue-router'
import { siteConfig } from '@/config/site.config'
import HIcon from '@/components/HIcon.vue'

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
            <HIcon :name="social.icon as any" :size="20" class="social-icon" />
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
            <HIcon v-if="index === 0" name="link" :size="48" />
            <HIcon v-else-if="index === 1" name="download" :size="48" />
            <HIcon v-else-if="index === 2" name="settings" :size="48" />
            <HIcon v-else name="chat" :size="48" />
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
            <HIcon :name="card.icon as any" :size="48" />
          </div>
          <h3 class="nav-card-title">{{ card.title }}</h3>
          <p class="nav-card-description">{{ card.description }}</p>
        </RouterLink>
      </div>
    </div>
  </div>
</template>

<style src="@/styles/Home.scss" scoped lang="scss"></style>

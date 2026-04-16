<script setup lang="ts">
import { siteConfig } from '@/config/site.config'
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'

const showSection = ref<Record<number, boolean>>({})

onMounted(() => {
  siteConfig.sections.forEach((_, index) => {
    showSection.value[index] = true
  })
})

const navigateTo = (url: string) => {
  if (url.startsWith('http')) {
    window.open(url, '_blank')
  } else {
    window.location.href = url
  }
}
</script>

<template>
  <div class="links-container">
    <div class="content-wrapper">
      <!-- 页面标题 -->
      <div class="page-header">
        <h1 class="page-title">链接导航</h1>
        <p class="page-description">快速访问常用链接和工具</p>
      </div>

      <!-- 各个板块 -->
      <div class="sections-container">
        <div 
          v-for="(section, index) in siteConfig.sections" 
          :key="index"
          class="section-card"
          :class="{ 'visible': showSection[index] }"
        >
          <h2 class="section-title">
            <span v-if="section.icon" class="section-icon">{{ section.icon }}</span>
            {{ section.title }}
          </h2>
          <div class="links-grid">
            <button 
              v-for="(link, linkIndex) in section.links" 
              :key="linkIndex"
              class="link-button"
              @click="navigateTo(link.url)"
            >
              {{ link.name }}
            </button>
          </div>
        </div>
      </div>

      <!-- 作品展示入口 -->
      <div class="portfolio-entry" v-if="siteConfig.portfolio">
        <RouterLink to="/portfolio" class="portfolio-link">
          <div class="portfolio-icon">⚛</div>
          <h2>{{ siteConfig.portfolio.title }}</h2>
          <p>{{ siteConfig.portfolio.description }}</p>
          <span class="view-more">查看更多 →</span>
        </RouterLink>
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss">
.links-container {
  min-height: 100vh;
  padding: 40px 20px;
  display: flex;
  justify-content: center;
  align-items: flex-start;
}

.content-wrapper {
  width: 100%;
  max-width: 900px;
  display: flex;
  flex-direction: column;
  gap: 30px;
}

.page-header {
  text-align: center;
  margin-bottom: 20px;

  .page-title {
    font-size: 2.5rem;
    font-weight: 600;
    color: rgba(30, 30, 30, 0.95);
    margin: 0 0 10px 0;
    text-shadow: 0 2px 10px rgba(255, 255, 255, 0.3);

    @media (max-width: 768px) {
      font-size: 2rem;
    }
  }

  .page-description {
    font-size: 1.1rem;
    color: rgba(30, 30, 30, 0.85);
    margin: 0;
  }
}

.sections-container {
  display: flex;
  flex-direction: column;
  gap: 25px;
}

.section-card {
  background: rgba(255, 255, 255, 0.25);
  backdrop-filter: blur(15px);
  border-radius: 16px;
  padding: 30px;
  border: 1px solid rgba(255, 255, 255, 0.3);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
  opacity: 0;
  transform: translateY(20px);

  &.visible {
    opacity: 1;
    transform: translateY(0);
  }

  &:hover {
    background: rgba(255, 255, 255, 0.35);
    transform: translateY(-2px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
  }

  @media (max-width: 768px) {
    padding: 20px;
  }
}

.section-title {
  font-size: 1.8rem;
  font-weight: 600;
  color: rgba(30, 30, 30, 0.95);
  text-align: center;
  margin: 0 0 25px 0;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;

  .section-icon {
    font-size: 1.5rem;
  }

  @media (max-width: 768px) {
    font-size: 1.4rem;
  }
}

.links-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  justify-content: center;
}

.link-button {
  background: rgba(255, 255, 255, 0.5);
  border: none;
  border-radius: 8px;
  padding: 12px 24px;
  font-size: 1rem;
  color: #333;
  cursor: pointer;
  transition: all 0.3s ease;
  font-weight: 500;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);

  &:hover {
    background: rgba(255, 255, 255, 0.8);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  }

  &:active {
    transform: translateY(0);
  }

  @media (max-width: 768px) {
    padding: 10px 18px;
    font-size: 0.9rem;
  }

  @media (max-width: 480px) {
    padding: 8px 14px;
    font-size: 0.85rem;
    flex: 1 1 calc(50% - 12px);
  }
}

.portfolio-entry {
  margin-top: 20px;
  text-align: center;

  .portfolio-link {
    display: inline-block;
    background: rgba(255, 255, 255, 0.25);
    backdrop-filter: blur(15px);
    border-radius: 16px;
    padding: 40px;
    border: 1px solid rgba(255, 255, 255, 0.3);
    text-decoration: none;
    transition: all 0.3s ease;
    cursor: pointer;

    &:hover {
      background: rgba(255, 255, 255, 0.35);
      transform: translateY(-4px);
      box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
    }

    .portfolio-icon {
      font-size: 3rem;
      margin-bottom: 15px;
    }

    h2 {
      font-size: 1.8rem;
      color: rgba(30, 30, 30, 0.95);
      margin: 0 0 10px 0;
    }

    p {
      color: rgba(30, 30, 30, 0.85);
      font-size: 1rem;
      margin: 0 0 20px 0;
    }

    .view-more {
      color: rgba(30, 30, 30, 0.9);
      font-weight: 600;
      font-size: 1.1rem;
    }
  }
}
</style>

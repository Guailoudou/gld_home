<script setup lang="ts">
import { siteConfig } from '@/config/site.config'

const homeworkSections = siteConfig.sections.find(s => s.title === 'html 上机作业')

const navigateTo = (url: string) => {
  if (url.startsWith('http')) {
    window.open(url, '_blank')
  } else {
    window.location.href = url
  }
}
</script>

<template>
  <div class="homework-container">
    <div class="content-wrapper">
      <div class="page-header">
        <h1 class="page-title">HTML 上机作业</h1>
        <p class="page-description">课程作业和练习作品展示</p>
      </div>

      <div class="homework-grid">
        <div 
          v-for="(link, index) in homeworkSections?.links" 
          :key="index"
          class="homework-card"
          @click="navigateTo(link.url)"
        >
          <div class="card-number">
            {{ String(index + 1).padStart(2, '0') }}
          </div>
          <div class="card-content">
            <h3 class="card-title">{{ link.name }}</h3>
            <p class="card-description" v-if="link.description">
              {{ link.description }}
            </p>
            <span class="view-btn">点击查看 →</span>
          </div>
        </div>
      </div>

      <div v-if="!homeworkSections || homeworkSections.links.length === 0" class="empty-state">
        <div class="empty-icon">📝</div>
        <p>暂无作业展示</p>
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss">
.homework-container {
  min-height: 100vh;
  padding: 40px 20px;
  display: flex;
  justify-content: center;
  align-items: flex-start;
}

.content-wrapper {
  width: 100%;
  max-width: 1000px;
}

.page-header {
  text-align: center;
  margin-bottom: 50px;

  .page-title {
    font-size: 2.5rem;
    font-weight: 600;
    color: rgba(30, 30, 30, 0.95);
    margin: 0 0 15px 0;
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

.homework-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 25px;

  @media (max-width: 768px) {
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 20px;
  }

  @media (max-width: 480px) {
    grid-template-columns: 1fr;
  }
}

.homework-card {
  background: rgba(255, 255, 255, 0.25);
  backdrop-filter: blur(15px);
  border-radius: 16px;
  padding: 25px;
  border: 1px solid rgba(255, 255, 255, 0.3);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  gap: 20px;
  align-items: center;

  &:hover {
    background: rgba(255, 255, 255, 0.35);
    transform: translateY(-4px) scale(1.02);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
  }

  .card-number {
    font-size: 2rem;
    font-weight: 700;
    color: rgba(255, 255, 255, 0.3);
    min-width: 60px;
    text-align: center;
  }

  .card-content {
    flex: 1;

    .card-title {
      font-size: 1.2rem;
      font-weight: 600;
      color: rgba(30, 30, 30, 0.95);
      margin: 0 0 8px 0;
    }

    .card-description {
      font-size: 0.9rem;
      color: rgba(30, 30, 30, 0.8);
      margin: 0 0 10px 0;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    .view-btn {
      font-size: 0.9rem;
      color: rgba(30, 30, 30, 0.85);
      font-weight: 600;
    }
  }
}

.empty-state {
  text-align: center;
  padding: 60px 20px;

  .empty-icon {
    font-size: 4rem;
    margin-bottom: 20px;
  }

  p {
    font-size: 1.2rem;
    color: rgba(30, 30, 30, 0.8);
  }
}
</style>

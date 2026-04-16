<script setup lang="ts">
import { siteConfig } from '@/config/site.config'

const portfolio = siteConfig.portfolio

const openLink = (url: string | undefined) => {
  if (url) {
    window.open(url, '_blank')
  }
}
</script>

<template>
  <div class="portfolio-container">
    <div class="content-wrapper">
      <!-- 页面标题 -->
      <div class="page-header">
        <h1 class="page-title">{{ portfolio?.title }}</h1>
        <p class="page-description">{{ portfolio?.description }}</p>
      </div>

      <!-- 作品网格 -->
      <div class="portfolio-grid">
        <div 
          v-for="(item, index) in portfolio?.items" 
          :key="index"
          class="portfolio-card"
        >
          <!-- 作品图片 -->
          <div class="card-image" v-if="item.image">
            <img :src="item.image" :alt="item.title" />
            <div class="image-overlay">
              <div class="overlay-buttons">
                <button 
                  v-if="item.link" 
                  class="overlay-btn" 
                  @click="openLink(item.link)"
                  title="查看演示"
                >
                  🔗
                </button>
                <button 
                  v-if="item.github" 
                  class="overlay-btn" 
                  @click="openLink(item.github)"
                  title="查看代码"
                >
                  
                </button>
              </div>
            </div>
          </div>

          <!-- 作品信息 -->
          <div class="card-content">
            <h3 class="card-title">{{ item.title }}</h3>
            <p class="card-description">{{ item.description }}</p>
            <div class="card-tags">
              <span 
                v-for="(tag, tagIndex) in item.tags" 
                :key="tagIndex"
                class="tag"
              >
                {{ tag }}
              </span>
            </div>
            <div class="card-actions">
              <button 
                v-if="item.link" 
                class="action-btn primary"
                @click="openLink(item.link)"
              >
                查看演示
              </button>
              <button 
                v-if="item.github" 
                class="action-btn secondary"
                @click="openLink(item.github)"
              >
                GitHub
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- 空状态 -->
      <div v-if="!portfolio?.items || portfolio.items.length === 0" class="empty-state">
        <div class="empty-icon">📭</div>
        <p>暂无作品展示</p>
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss">
.portfolio-container {
  min-height: 100vh;
  padding: 40px 20px;
  display: flex;
  justify-content: center;
  align-items: flex-start;
}

.content-wrapper {
  width: 100%;
  max-width: 1200px;
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

.portfolio-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 30px;

  @media (max-width: 768px) {
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
  }

  @media (max-width: 480px) {
    grid-template-columns: 1fr;
  }
}

.portfolio-card {
  background: rgba(255, 255, 255, 0.25);
  backdrop-filter: blur(15px);
  border-radius: 16px;
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, 0.3);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;

  &:hover {
    transform: translateY(-8px);
    box-shadow: 0 16px 48px rgba(0, 0, 0, 0.2);
    background: rgba(255, 255, 255, 0.35);
  }

  .card-image {
    position: relative;
    width: 100%;
    height: 220px;
    overflow: hidden;

    img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.3s ease;
    }

    &:hover img {
      transform: scale(1.1);
    }

    .image-overlay {
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.6);
      display: flex;
      align-items: center;
      justify-content: center;
      opacity: 0;
      transition: opacity 0.3s ease;

      .overlay-buttons {
        display: flex;
        gap: 15px;

        .overlay-btn {
          width: 50px;
          height: 50px;
          border-radius: 50%;
          border: none;
          background: rgba(255, 255, 255, 0.9);
          font-size: 1.5rem;
          cursor: pointer;
          transition: all 0.3s ease;

          &:hover {
            transform: scale(1.2);
            background: rgba(255, 255, 255, 1);
          }
        }
      }
    }

    &:hover .image-overlay {
      opacity: 1;
    }
  }

  .card-content {
    padding: 25px;

    .card-title {
      font-size: 1.4rem;
      font-weight: 600;
      color: rgba(30, 30, 30, 0.95);
      margin: 0 0 12px 0;
    }

    .card-description {
      font-size: 0.95rem;
      color: rgba(30, 30, 30, 0.85);
      line-height: 1.6;
      margin: 0 0 15px 0;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    .card-tags {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      margin-bottom: 20px;

      .tag {
        background: rgba(255, 255, 255, 0.5);
        color: rgba(30, 30, 30, 0.9);
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
      }
    }

    .card-actions {
      display: flex;
      gap: 10px;

      .action-btn {
        flex: 1;
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        font-size: 0.95rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;

        &.primary {
          background: rgba(255, 255, 255, 0.9);
          color: #333;

          &:hover {
            background: rgba(255, 255, 255, 1);
            transform: translateY(-2px);
          }
        }

        &.secondary {
          background: rgba(255, 255, 255, 0.4);
          color: rgba(30, 30, 30, 0.9);
          border: 1px solid rgba(255, 255, 255, 0.6);

          &:hover {
            background: rgba(255, 255, 255, 0.6);
            transform: translateY(-2px);
          }
        }
      }
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

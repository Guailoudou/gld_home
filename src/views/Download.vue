<script setup lang="ts">
import { siteConfig } from '@/config/site.config'

const { title, description, categories } = siteConfig.download

const downloadFile = (url: string) => {
  window.open(url, '_blank')
}
</script>

<template>
  <div class="download-container">
    <div class="content-wrapper">
      <div class="page-header">
        <h1 class="page-title">{{ title }}</h1>
        <p class="page-description">{{ description }}</p>
      </div>

      <div class="download-grid">
        <div 
          v-for="(category, index) in categories" 
          :key="index"
          class="download-card"
        >
          <div class="card-header">
            <span class="card-icon">{{ category.icon }}</span>
            <div>
              <h2 class="card-title">{{ category.name }}</h2>
              <p class="card-subtitle">{{ category.description }}</p>
            </div>
          </div>

          <div class="download-list">
            <div 
              v-for="(item, itemIndex) in category.items" 
              :key="itemIndex"
              class="download-item"
            >
              <div class="item-info">
                <span class="item-name">{{ item.name }}</span>
                <span class="item-size">{{ item.size }}</span>
              </div>
              <button class="download-btn" @click="downloadFile(item.url)">
                下载
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss">
.download-container {
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

.download-grid {
  display: flex;
  flex-direction: column;
  gap: 25px;
}

.download-card {
  background: rgba(255, 255, 255, 0.25);
  backdrop-filter: blur(15px);
  border-radius: 16px;
  padding: 30px;
  border: 1px solid rgba(255, 255, 255, 0.3);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;

  &:hover {
    background: rgba(255, 255, 255, 0.35);
    transform: translateY(-2px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
  }

  .card-header {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 25px;
    padding-bottom: 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);

    .card-icon {
      font-size: 2.5rem;
    }

    .card-title {
      font-size: 1.5rem;
      font-weight: 600;
      color: rgba(30, 30, 30, 0.95);
      margin: 0 0 5px 0;
    }

    .card-subtitle {
      font-size: 0.95rem;
      color: rgba(30, 30, 30, 0.8);
      margin: 0;
    }
  }

  .download-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
  }

  .download-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    background: rgba(255, 255, 255, 0.3);
    border-radius: 10px;
    transition: all 0.3s ease;

    &:hover {
      background: rgba(255, 255, 255, 0.45);
      transform: translateX(5px);
    }

    .item-info {
      display: flex;
      flex-direction: column;
      gap: 5px;

      .item-name {
        font-size: 1rem;
        font-weight: 500;
        color: rgba(30, 30, 30, 0.9);
      }

      .item-size {
        font-size: 0.85rem;
        color: rgba(30, 30, 30, 0.7);
      }
    }

    .download-btn {
      background: rgba(255, 255, 255, 0.9);
      color: #333;
      border: none;
      border-radius: 8px;
      padding: 8px 24px;
      font-size: 0.95rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;

      &:hover {
        background: rgba(255, 255, 255, 1);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      }
    }
  }
}
</style>

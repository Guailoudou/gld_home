<script setup lang="ts">
import { siteConfig } from '@/config/site.config'

const { title, description, items: tools } = siteConfig.tools

const navigateToTool = (url: string) => {
  window.location.href = url
}
</script>

<template>
  <div class="tools-container">
    <div class="content-wrapper">
      <div class="page-header">
        <h1 class="page-title">{{ title }}</h1>
        <p class="page-description">{{ description }}</p>
      </div>

      <div class="tools-grid">
        <div 
          v-for="(tool, index) in tools" 
          :key="index"
          class="tool-card"
          :style="{ '--tool-color': tool.color }"
          @click="navigateToTool(tool.url)"
        >
          <div class="tool-icon" :style="{ backgroundColor: tool.color }">
            {{ tool.icon }}
          </div>
          <h3 class="tool-name">{{ tool.name }}</h3>
          <p class="tool-description">{{ tool.description }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss">
.tools-container {
  min-height: 100vh;
  padding: 40px 20px;
  display: flex;
  justify-content: center;
  align-items: flex-start;
}

.content-wrapper {
  width: 100%;
  max-width: 1100px;
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

.tools-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 25px;

  @media (max-width: 768px) {
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 20px;
  }

  @media (max-width: 480px) {
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
  }
}

.tool-card {
  background: rgba(255, 255, 255, 0.25);
  backdrop-filter: blur(15px);
  border-radius: 16px;
  padding: 30px;
  border: 1px solid rgba(255, 255, 255, 0.3);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
  cursor: pointer;
  transition: all 0.3s ease;
  text-align: center;

  &:hover {
    background: rgba(255, 255, 255, 0.35);
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 16px 48px rgba(0, 0, 0, 0.2);
  }

  .tool-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    margin: 0 auto 20px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
    transition: all 0.3s ease;
  }

  &:hover .tool-icon {
    transform: scale(1.1) rotate(5deg);
  }

  .tool-name {
    font-size: 1.3rem;
    font-weight: 600;
    color: rgba(30, 30, 30, 0.95);
    margin: 0 0 10px 0;
  }

  .tool-description {
    font-size: 0.95rem;
    color: rgba(30, 30, 30, 0.85);
    margin: 0;
  }
}
</style>

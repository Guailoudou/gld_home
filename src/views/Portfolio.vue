<script setup lang="ts">
import { ref, onMounted } from 'vue'
import HIcon from '@/components/HIcon.vue'
import type { PortfolioItem, PortfolioConfig } from '@/models/portfolio/types'

const config = ref<PortfolioConfig>({
  title: '作品展示',
  description: '这里展示我的个人项目和作品'
})

const items = ref<PortfolioItem[]>([])
const loading = ref(true)

const openLink = (url: string | undefined) => {
  if (url) {
    window.open(url, '_blank')
  }
}

async function fetchData() {
  loading.value = true
  try {
    const response = await fetch('/api/portfolios.php')
    const result = await response.json()

    if (result.success) {
      config.value = result.data.config || config.value
      items.value = result.data.items || []
    }
  } catch (error) {
    console.error('获取作品数据失败:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchData()
})
</script>

<template>
  <div class="portfolio-container">
    <div class="content-wrapper">
      <!-- 加载状态 -->
      <div v-if="loading" class="loading-state">
        <HIcon name="refresh" :size="40" class="loading-spinner" />
        <p>加载中...</p>
      </div>

      <template v-else>
        <!-- 页面标题 -->
        <div class="page-header">
          <h1 class="page-title">{{ config.title }}</h1>
          <p class="page-description">{{ config.description }}</p>
        </div>

        <!-- 作品网格 -->
        <div class="portfolio-grid">
          <div
            v-for="item in items"
            :key="item.id"
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
                    <HIcon name="link" :size="24" />
                  </button>
                  <button
                    v-if="item.github"
                    class="overlay-btn"
                    @click="openLink(item.github)"
                    title="查看代码"
                  >
                    <HIcon name="code" :size="24" />
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
        <div v-if="items.length === 0" class="empty-state">
          <HIcon name="empty" :size="64" class="empty-icon" />
          <p>暂无作品展示</p>
        </div>
      </template>
    </div>
  </div>
</template>

<style src="@/styles/Portfolio.scss" scoped lang="scss"></style>

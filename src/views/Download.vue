<script setup lang="ts">
import { useDownload } from '@/composables/download/useDownload'
import HIcon from '@/components/HIcon.vue'

interface Props {
  packageCode?: string
}

const props = defineProps<Props>()

const {
  mergedDownloads,
  pageTitle,
  pageDescription,
  downloadFile,
  loading
} = useDownload({ packageCode: props.packageCode })
</script>

<template>
  <div class="download-container">
    <div class="content-wrapper">
      <div class="page-header">
        <h1 class="page-title">{{ pageTitle }}</h1>
        <p class="page-description">{{ pageDescription }}</p>
      </div>

      <div class="download-grid">
        <div 
          v-for="(category, index) in mergedDownloads" 
          :key="index"
          class="download-card"
        >
          <div class="card-header">
            <HIcon :name="category.icon as any" :size="40" class="card-icon" />
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
              :class="{ featured: item.is_featured === '1' }"
            >
              <div class="item-info">
                <div class="item-name-row">
                  <span v-if="item.is_featured === '1'" class="featured-badge">
                    <HIcon name="star" :size="14" class="star-icon" />
                    推荐
                  </span>
                  <span class="item-name">{{ item.name }}</span>
                </div>
                <span class="item-size">{{ item.size }}</span>
              </div>
              <button class="download-btn" @click="downloadFile(item.url)">
                下载
              </button>
            </div>
          </div>
        </div>

        <!-- 加载状态 -->
        <div v-if="loading" class="loading-state">
          <HIcon name="refresh" :size="48" class="loading-spinner" />
          <p>加载中...</p>
        </div>
      </div>
    </div>
  </div>
</template>

<style src="@/styles/Download.scss" scoped lang="scss"></style>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import HIcon from '@/components/HIcon.vue'
import type { SectionItem } from '@/models/link/types'

const API_BASE = '/api/links.php'
const sections = ref<SectionItem[]>([])
const loading = ref(true)
const showSection = ref<Record<string, boolean>>({})

onMounted(async () => {
  try {
    const response = await fetch(API_BASE)
    const result = await response.json()

    if (result.success) {
      sections.value = result.data
      result.data.forEach((section: SectionItem) => {
        showSection.value[section.id] = true
      })
    }
  } catch (error) {
    console.error('获取链接数据失败:', error)
  } finally {
    loading.value = false
  }
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

      <!-- 加载状态 -->
      <div v-if="loading" class="loading-state">
        <HIcon name="refresh" :size="40" class="loading-spinner" />
        <p>加载中...</p>
      </div>

      <!-- 空状态 -->
      <div v-else-if="sections.length === 0" class="empty-state">
        <HIcon name="link" :size="64" />
        <p>暂无链接数据</p>
      </div>

      <!-- 各个板块 -->
      <div v-else class="sections-container">
        <div 
          v-for="section in sections" 
          :key="section.id"
          class="section-card"
          :class="{ 'visible': showSection[section.id] }"
        >
          <h2 class="section-title">
            <HIcon v-if="section.icon" :name="section.icon as any" :size="28" class="section-icon" />
            {{ section.title }}
          </h2>
          <div class="links-grid">
            <button 
              v-for="link in section.links" 
              :key="link.id"
              class="link-button"
              @click="navigateTo(link.url)"
            >
              {{ link.name }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style src="@/styles/Links.scss" scoped lang="scss"></style>

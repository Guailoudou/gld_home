<script setup lang="ts">
import { siteConfig } from '@/config/site.config'
import HIcon from '@/components/HIcon.vue'

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
            <HIcon :name="tool.icon as any" :size="40" class="tool-icon-svg" />
          </div>
          <h3 class="tool-name">{{ tool.name }}</h3>
          <p class="tool-description">{{ tool.description }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<style src="@/styles/Tools.scss" scoped lang="scss"></style>

<script setup lang="ts">
import { RouterLink } from 'vue-router'
import { onMounted } from 'vue'

// 设置 404 页面的 meta 信息，告诉搜索引擎这是错误页面
onMounted(() => {
  const metaRobots = document.createElement('meta')
  metaRobots.name = 'robots'
  metaRobots.content = 'noindex, nofollow'
  document.head.appendChild(metaRobots)

  // 设置页面标题
  document.title = '404 - 页面未找到 | GLD 小站'
})
</script>

<template>
  <div class="not-found-container">
    <div class="content-wrapper">
      <!-- 404 大字 -->
      <div class="error-code">
        <span class="num-4 first">4</span>
        <span class="num-0">0</span>
        <span class="num-4 second">4</span>
      </div>

      <!-- 错误信息 -->
      <div class="error-message">
        <h1 class="error-title">页面走丢啦~</h1>
        <p class="error-description">
          抱歉，您访问的页面不存在
        </p>
      </div>

      <!-- 返回首页按钮 -->
      <RouterLink to="/" class="home-button">
        <span class="button-icon">🏠</span>
        <span class="button-text">返回首页</span>
      </RouterLink>

      <!-- 装饰元素 -->
      <div class="decoration">
        <div class="floating-shapes">
          <div class="shape shape-1"></div>
          <div class="shape shape-2"></div>
          <div class="shape shape-3"></div>
        </div>
      </div>
    </div>
  </div>
</template>

<style src="@/styles/NotFound.scss" scoped lang="scss"></style>

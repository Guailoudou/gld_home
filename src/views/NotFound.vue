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

<style scoped lang="scss">
.not-found-container {
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 20px;
  position: relative;
  overflow: hidden;
}

.content-wrapper {
  text-align: center;
  position: relative;
  z-index: 10;
}

.error-code {
  display: flex;
  justify-content: center;
  gap: 10px;
  margin-bottom: 30px;
  font-size: 8rem;
  font-weight: 700;
  position: relative;

  .num-4,
  .num-0 {
    color: rgba(255, 255, 255, 0.95);
    text-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
    animation: float 3s ease-in-out infinite;
  }

  .first {
    animation-delay: 0s;
  }

  .num-0 {
    animation-delay: 0.3s;
    color: rgba(255, 255, 255, 0.85);
  }

  .second {
    animation-delay: 0.6s;
  }

  @media (max-width: 768px) {
    font-size: 5rem;
    gap: 5px;
  }
}

@keyframes float {
  0%,
  100% {
    transform: translateY(0);
  }

  50% {
    transform: translateY(-20px);
  }
}

.error-message {
  margin-bottom: 40px;

  .error-title {
    font-size: 2.5rem;
    font-weight: 600;
    color: rgba(30, 30, 30, 0.95);
    margin: 0 0 15px 0;
    text-shadow: 0 2px 10px rgba(255, 255, 255, 0.3);

    @media (max-width: 768px) {
      font-size: 2rem;
    }
  }

  .error-description {
    font-size: 1.1rem;
    color: rgba(30, 30, 30, 0.85);
    margin: 0;

    @media (max-width: 768px) {
      font-size: 1rem;
    }
  }
}

.home-button {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  background: rgba(255, 255, 255, 0.35);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.4);
  border-radius: 16px;
  padding: 16px 32px;
  text-decoration: none;
  color: rgba(30, 30, 30, 0.95);
  font-weight: 600;
  font-size: 1.1rem;
  transition: all 0.3s ease;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);

  &:hover {
    background: rgba(255, 255, 255, 0.55);
    transform: translateY(-4px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.2);
  }

  .button-icon {
    font-size: 1.5rem;
  }

  @media (max-width: 768px) {
    padding: 14px 24px;
    font-size: 1rem;
  }
}

.decoration {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  pointer-events: none;
  overflow: hidden;
  z-index: -1;
}

.floating-shapes {
  position: relative;
  width: 100%;
  height: 100%;
}

.shape {
  position: absolute;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  animation: floatShape 20s infinite ease-in-out;

  &.shape-1 {
    width: 100px;
    height: 100px;
    top: 10%;
    left: 10%;
    animation-delay: 0s;
  }

  &.shape-2 {
    width: 150px;
    height: 150px;
    top: 60%;
    right: 10%;
    animation-delay: 5s;
  }

  &.shape-3 {
    width: 80px;
    height: 80px;
    bottom: 20%;
    left: 20%;
    animation-delay: 10s;
  }
}

@keyframes floatShape {
  0%,
  100% {
    transform: translate(0, 0) rotate(0deg);
  }

  25% {
    transform: translate(20px, -30px) rotate(90deg);
  }

  50% {
    transform: translate(-20px, 20px) rotate(180deg);
  }

  75% {
    transform: translate(30px, 10px) rotate(270deg);
  }
}
</style>

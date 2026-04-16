<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { siteConfig } from '@/config/site.config'

const route = useRoute()
const isMenuOpen = ref(false)
const scrollY = ref(0)

const footerText = computed(() => {
  return siteConfig.footer.copyright.replace('{{ siteName }}', siteConfig.siteName)
})

const handleScroll = () => {
  scrollY.value = window.scrollY
}

const toggleMenu = () => {
  isMenuOpen.value = !isMenuOpen.value
}

const closeMenu = () => {
  isMenuOpen.value = false
}

onMounted(() => {
  window.addEventListener('scroll', handleScroll)
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
})
</script>

<template>
  <div class="layout-container">
    <!-- 导航栏 -->
    <nav 
      class="navbar"
      :class="{ 'scrolled': scrollY > 50 }"
    >
      <div class="nav-content">
        <RouterLink to="/" class="nav-logo" @click="closeMenu">
          <span class="logo-icon"></span>
          <span class="logo-text">{{ siteConfig.siteName }}</span>
        </RouterLink>

        <!-- 桌面端导航 -->
        <div class="nav-links">
          <RouterLink 
            to="/"
            class="nav-link"
            :class="{ 'active': route.path === '/' }"
            @click="closeMenu"
          >
            首页
          </RouterLink>
          <RouterLink 
            v-for="(link, index) in siteConfig.navigation" 
            :key="index"
            :to="link.url"
            class="nav-link"
            :class="{ 'active': route.path === link.url }"
            @click="closeMenu"
          >
            {{ link.name }}
          </RouterLink>
          <RouterLink 
            to="/portfolio"
            class="nav-link"
            :class="{ 'active': route.path === '/portfolio' }"
            @click="closeMenu"
          >
            作品展示
          </RouterLink>
        </div>

        <!-- 移动端菜单按钮 -->
        <button class="menu-toggle" @click="toggleMenu">
          <span></span>
          <span></span>
          <span></span>
        </button>
      </div>

      <!-- 移动端导航菜单 -->
      <transition name="slide">
        <div v-if="isMenuOpen" class="mobile-menu">
          <RouterLink 
            to="/"
            class="mobile-nav-link"
            :class="{ 'active': route.path === '/' }"
            @click="closeMenu"
          >
            首页
          </RouterLink>
          <RouterLink 
            v-for="(link, index) in siteConfig.navigation" 
            :key="index"
            :to="link.url"
            class="mobile-nav-link"
            :class="{ 'active': route.path === link.url }"
            @click="closeMenu"
          >
            {{ link.name }}
          </RouterLink>
          <RouterLink 
            to="/portfolio"
            class="mobile-nav-link"
            :class="{ 'active': route.path === '/portfolio' }"
            @click="closeMenu"
          >
            作品展示
          </RouterLink>
        </div>
      </transition>
    </nav>

    <!-- 主要内容 -->
    <main class="main-content">
      <router-view />
    </main>

    <!-- 页脚 -->
    <footer class="footer">
      <div class="footer-content">
        <p>&copy; {{ new Date().getFullYear() }} {{ footerText }}</p>
        <p class="footer-links">
          <a 
            v-for="(link, index) in siteConfig.footer.links" 
            :key="index"
            :href="link.url"
            target="_blank"
          >
            {{ link.name }}
          </a>
        </p>
      </div>
    </footer>
  </div>
</template>

<style scoped lang="scss">
.layout-container {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.navbar {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1000;
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(20px);
  border-bottom: 1px solid rgba(255, 255, 255, 0.2);
  transition: all 0.3s ease;

  &.scrolled {
    background: rgba(255, 255, 255, 0.25);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
  }

  .nav-content {
    max-width: 1200px;
    margin: 0 auto;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .nav-logo {
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
    color: rgba(30, 30, 30, 0.95);
    font-weight: 600;
    font-size: 1.2rem;
    transition: all 0.3s ease;

    &:hover {
      transform: scale(1.05);
    }

    .logo-icon {
      font-size: 1.5rem;
    }
  }

  .nav-links {
    display: flex;
    gap: 8px;

    .nav-link {
      padding: 10px 18px;
      text-decoration: none;
      color: rgba(30, 30, 30, 0.85);
      font-weight: 500;
      border-radius: 8px;
      transition: all 0.3s ease;
      font-size: 0.95rem;

      &:hover {
        background: rgba(255, 255, 255, 0.3);
        color: rgba(30, 30, 30, 1);
      }

      &.active {
        background: rgba(255, 255, 255, 0.4);
        color: rgba(30, 30, 30, 1);
      }
    }
  }

  .menu-toggle {
    display: none;
    flex-direction: column;
    gap: 5px;
    background: none;
    border: none;
    cursor: pointer;
    padding: 5px;

    span {
      width: 25px;
      height: 3px;
      background: rgba(30, 30, 30, 0.9);
      border-radius: 2px;
      transition: all 0.3s ease;
    }

    &:hover span {
      background: rgba(30, 30, 30, 1);
    }
  }

  .mobile-menu {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: rgba(255, 255, 255, 0.25);
    backdrop-filter: blur(20px);
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    border-bottom: 1px solid rgba(30, 30, 30, 0.2);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
  }

  .mobile-nav-link {
    padding: 15px 20px;
    text-decoration: none;
    color: rgba(30, 30, 30, 0.9);
    font-weight: 500;
    border-radius: 8px;
    transition: all 0.3s ease;
    text-align: center;

    &:hover {
      background: rgba(255, 255, 255, 0.3);
    }

    &.active {
      background: rgba(255, 255, 255, 0.4);
    }
  }

  @media (max-width: 768px) {
    .nav-links {
      display: none;
    }

    .menu-toggle {
      display: flex;
    }
  }
}

.slide-enter-active,
.slide-leave-active {
  transition: all 0.3s ease;
}

.slide-enter-from {
  opacity: 0;
  transform: translateY(-10px);
}

.slide-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

.main-content {
  flex: 1;
  padding-top: 80px;
}

.footer {
  background: rgba(0, 0, 0, 0.3);
  backdrop-filter: blur(10px);
  border-top: 1px solid rgba(255, 255, 255, 0.15);
  padding: 25px 20px;
  margin-top: auto;

  .footer-content {
    max-width: 1200px;
    margin: 0 auto;
    text-align: center;

    p {
      margin: 0;
      font-size: 0.9rem;
      color: rgba(255, 255, 255, 0.85);
      text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
    }

    .footer-links {
      margin-top: 10px;

      a {
        color: rgba(255, 255, 255, 0.9);
        text-decoration: none;
        transition: color 0.3s ease;

        &:hover {
          color: rgba(255, 255, 255, 1);
          text-shadow: 0 0 8px rgba(255, 255, 255, 0.3);
        }
      }

      .separator {
        margin: 0 10px;
        color: rgba(255, 255, 255, 0.6);
      }
    }
  }
}
</style>

import { createRouter, createWebHistory } from 'vue-router'
import Home from '../views/Home.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: Home,
      meta: { title: '首页' }
    },
    {
      path: '/portfolio',
      name: 'portfolio',
      component: () => import('../views/Portfolio.vue'),
      meta: { title: '作品展示' }
    },
    {
      path: '/links',
      name: 'links',
      component: () => import('../views/Links.vue'),
      meta: { title: '链接' }
    },
    {
      path: '/download',
      name: 'download',
      component: () => import('../views/Download.vue'),
      meta: { title: '下载' }
    },
    {
      path: '/tools',
      name: 'tools',
      component: () => import('../views/Tools.vue'),
      meta: { title: '小工具' }
    },
    {
      path: '/guestbook',
      name: 'guestbook',
      component: () => import('../views/Guestbook.vue'),
      meta: { title: '留言' }
    }
  ],
  scrollBehavior(_to, _from, savedPosition) {
    if (savedPosition) {
      return savedPosition
    } else {
      return { top: 0 }
    }
  }
})

export default router

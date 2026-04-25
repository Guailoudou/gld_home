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
      path: '/download/:id',
      name: 'download-detail',
      component: () => import('../views/Download.vue'),
      meta: { title: '下载详情' }
    },
    // 下载码路由（必须在 /download/:id 之后）
    {
      path: '/download/:code',
      name: 'download-package',
      component: () => import('../views/Download.vue'),
      meta: { title: '下载包' },
      props: (route) => ({ packageCode: route.params.code })
    },
    {
      path: '/admin',
      name: 'admin',
      component: () => import('../views/AdminDashboard.vue'),
      meta: { title: '管理后台' }
    },
    {
      path: '/admin/packages',
      name: 'admin-packages',
      component: () => import('../views/DownloadPackages.vue'),
      meta: { title: '下载包管理' }
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
    },
    {
      path: '/admin/downloads',
      name: 'admin-downloads',
      component: () => import('../views/AdminDownloads.vue'),
      meta: { title: '下载管理' }
    },
    {
      path: '/:pathMatch(.*)*',
      name: 'not-found',
      component: () => import('../views/NotFound.vue'),
      meta: { title: '页面未找到' }
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

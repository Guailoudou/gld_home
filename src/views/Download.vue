<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import { siteConfig } from '@/config/site.config'
import HIcon from '@/components/HIcon.vue'
import type { ApiDownloadItem } from '@/models/download/types'

interface Props {
  packageCode?: string
}

const props = defineProps<Props>()
const route = useRoute()
const API_BASE = '/api/downloads.php'

// 从配置文件获取分类信息
const { title, description, categories } = siteConfig.download

// API 获取的数据
const apiDownloads = ref<ApiDownloadItem[]>([])
const loading = ref(false)

// 下载包信息
const packageInfo = ref<{
  name?: string
  description?: string
  code?: string
} | null>(null)

// 合并后的下载列表（API 数据 + 配置数据）
const mergedDownloads = computed(() => {
  if (apiDownloads.value.length === 0) {
    return categories
  }

  // 如果是下载包访问，只返回一个分类
  if (packageInfo.value) {
    return [{
      name: packageInfo.value.name || '下载包',
      description: packageInfo.value.description || '',
      icon: 'package',
      items: apiDownloads.value.map(item => ({
        name: item.name,
        size: item.size,
        url: item.url,
        is_featured: item.is_featured,
        priority: item.priority || 0
      }))
    }]
  }

  // 如果有 API 数据，使用 API 数据替换第一个分类的内容
  return categories.map((category, index) => {
    if (index === 0) {
      // 第一个分类使用 API 数据
      return {
        ...category,
        items: apiDownloads.value.map(item => ({
          name: item.name,
          size: item.size,
          url: item.url,
          is_featured: item.is_featured,
          priority: item.priority || 0
        }))
      }
    }
    return category
  })
})

// 页面标题和描述
const pageTitle = computed(() => {
  if (packageInfo.value) {
    return packageInfo.value.name || '下载包'
  }
  return title
})

const pageDescription = computed(() => {
  if (packageInfo.value) {
    return packageInfo.value.description || '包含精选下载资源'
  }
  return description
})

const downloadFile = (url: string) => {
  window.open(url, '_blank')
}

// 按优先级排序
const sortByPriority = (items: ApiDownloadItem[]) => {
  return items.sort((a, b) => {
    const priorityA = a.priority || 0
    const priorityB = b.priority || 0
    if (priorityA !== priorityB) {
      return priorityA - priorityB
    }
    return 0
  })
}

// 从 API 获取数据
const fetchDownloads = async () => {
  // 检查是否有路由参数 id（支持 /download/:id 格式）
  const routeId = route.params.id as string
  
  // 检查是否有查询参数 id（支持 /download?id=xxx 格式，向后兼容）
  const queryId = route.query.id as string
  
  // 检查是否有查询参数 package_code（下载码）
  const queryPackageCode = route.query.package_code as string
  
  // 使用 props 中的 packageCode 或查询参数
  const packageCode = props.packageCode || queryPackageCode
  
  // 优先使用路由参数，其次使用查询参数
  const downloadIds = routeId || queryId
  
  if (packageCode) {
    // 下载码访问
    loading.value = true
    try {
      const response = await fetch(`${API_BASE}?package_code=${packageCode}`)
      const result = await response.json()
      
      if (result.success && result.data) {
        // 下载包数据
        apiDownloads.value = sortByPriority(result.data.downloads)
        packageInfo.value = result.data.package
      }
    } catch (error) {
      console.error('获取下载包数据失败:', error)
    } finally {
      loading.value = false
    }
  } else if (downloadIds) {
    // 获取指定 ID 的数据（支持多个 ID，用逗号分隔）
    loading.value = true
    try {
      const response = await fetch(`${API_BASE}?id=${downloadIds}`)
      const result = await response.json()
      
      if (result.success && result.data) {
        // 检查是否是下载包返回（包含 package 和 downloads）
        if (result.data.package && result.data.downloads) {
          packageInfo.value = result.data.package
          apiDownloads.value = sortByPriority(result.data.downloads)
        } else {
          // 普通下载项，可能是数组或单个对象
          const items = Array.isArray(result.data) ? result.data : [result.data]
          apiDownloads.value = sortByPriority(items)
        }
      }
    } catch (error) {
      console.error('获取下载数据失败:', error)
    } finally {
      loading.value = false
    }
  } else {
    // 获取默认展示的数据
    loading.value = true
    try {
      const response = await fetch(API_BASE)
      const result = await response.json()
      
      if (result.success) {
        apiDownloads.value = sortByPriority(result.data)
      }
    } catch (error) {
      console.error('获取下载列表失败:', error)
    } finally {
      loading.value = false
    }
  }
}

onMounted(() => {
  fetchDownloads()
})
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

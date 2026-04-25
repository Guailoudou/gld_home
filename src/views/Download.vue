<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import { siteConfig } from '@/config/site.config'

interface ApiDownloadItem {
  id: string
  name: string
  size: string
  url: string
  is_default: string
}

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
      icon: '📦',
      items: apiDownloads.value.map(item => ({
        name: item.name,
        size: item.size,
        url: item.url
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
          url: item.url
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
        apiDownloads.value = result.data.downloads
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
          apiDownloads.value = result.data.downloads
        } else {
          // 普通下载项，可能是数组或单个对象
          apiDownloads.value = Array.isArray(result.data) ? result.data : [result.data]
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
        apiDownloads.value = result.data
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
            <span class="card-icon">{{ category.icon }}</span>
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
            >
              <div class="item-info">
                <span class="item-name">{{ item.name }}</span>
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
          <div class="loading-spinner">⏳</div>
          <p>加载中...</p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss">
.download-container {
  min-height: 100vh;
  padding: 40px 20px;
  display: flex;
  justify-content: center;
  align-items: flex-start;
}

.content-wrapper {
  width: 100%;
  max-width: 1000px;
}

.page-header {
  text-align: center;
  margin-bottom: 50px;

  .page-title {
    font-size: 2.5rem;
    font-weight: 600;
    color: rgba(30, 30, 30, 0.95);
    margin: 0 0 15px 0;
    text-shadow: 0 2px 10px rgba(255, 255, 255, 0.3);

    @media (max-width: 768px) {
      font-size: 2rem;
    }
  }

  .page-description {
    font-size: 1.1rem;
    color: rgba(30, 30, 30, 0.85);
    margin: 0;
  }
}

.download-grid {
  display: flex;
  flex-direction: column;
  gap: 25px;
}

.download-card {
  background: rgba(255, 255, 255, 0.25);
  backdrop-filter: blur(15px);
  border-radius: 16px;
  padding: 30px;
  border: 1px solid rgba(255, 255, 255, 0.3);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;

  &:hover {
    background: rgba(255, 255, 255, 0.35);
    transform: translateY(-2px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
  }

  .card-header {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 25px;
    padding-bottom: 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);

    .card-icon {
      font-size: 2.5rem;
    }

    .card-title {
      font-size: 1.5rem;
      font-weight: 600;
      color: rgba(30, 30, 30, 0.95);
      margin: 0 0 5px 0;
    }

    .card-subtitle {
      font-size: 0.95rem;
      color: rgba(30, 30, 30, 0.8);
      margin: 0;
    }
  }

  .download-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
  }

  .download-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    background: rgba(255, 255, 255, 0.3);
    border-radius: 10px;
    transition: all 0.3s ease;

    &:hover {
      background: rgba(255, 255, 255, 0.45);
      transform: translateX(5px);
    }

    .item-info {
      display: flex;
      flex-direction: column;
      gap: 5px;

      .item-name {
        font-size: 1rem;
        font-weight: 500;
        color: rgba(30, 30, 30, 0.9);
      }

      .item-size {
        font-size: 0.85rem;
        color: rgba(30, 30, 30, 0.7);
      }
    }

    .download-btn {
      background: rgba(255, 255, 255, 0.9);
      color: #333;
      border: none;
      border-radius: 8px;
      padding: 8px 24px;
      font-size: 0.95rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;

      &:hover {
        background: rgba(255, 255, 255, 1);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      }
    }
  }
}

.loading-state {
  text-align: center;
  padding: 40px 20px;

  .loading-spinner {
    font-size: 3rem;
    margin-bottom: 16px;
    animation: spin 1s linear infinite;
  }

  p {
    color: rgba(30, 30, 30, 0.7);
    font-size: 1.1rem;
  }
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>

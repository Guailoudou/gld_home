<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import type { DownloadItem, DownloadFormData } from '@/models/download/types'

const API_BASE = '/api/downloads.php'

const downloads = ref<DownloadItem[]>([])
const loading = ref(false)
const showMessage = ref(false)
const messageText = ref('')
const messageType = ref<'success' | 'error'>('success')

// 表单数据
const formData = ref<DownloadFormData>({
  id: '',
  name: '',
  size: '',
  url: '',
  is_default: false,
  is_featured: false,
  priority: 0,
  password: ''
})

const isEditing = ref(false)
const showForm = ref(false)

// 显示消息提示
const showToast = (text: string, type: 'success' | 'error' = 'success') => {
  messageText.value = text
  messageType.value = type
  showMessage.value = true
  setTimeout(() => {
    showMessage.value = false
  }, 3000)
}

// 复制文本到剪贴板
const copyToClipboard = async (text: string, label: string) => {
  try {
    await navigator.clipboard.writeText(text)
    showToast(`${label}已复制`, 'success')
  } catch (error) {
    console.error('复制失败:', error)
    showToast('复制失败', 'error')
  }
}

// 获取密码哈希
const getPasswordHash = (): string => {
  return localStorage.getItem('admin_password_hash') || ''
}

// 获取下载列表
const fetchDownloads = async () => {
  loading.value = true
  try {
    const passwordHash = getPasswordHash()
    const response = await fetch(API_BASE, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        action: 'get_all',
        password_hash: passwordHash
      })
    })
    const result = await response.json()
    
    if (result.success) {
      downloads.value = result.data
    } else {
      // 密码错误
      if (result.error === '密码错误') {
        showToast('密码错误，请重新登录', 'error')
      }
    }
  } catch (error) {
    console.error('获取下载列表失败:', error)
    showToast('网络错误', 'error')
  } finally {
    loading.value = false
  }
}

// 打开新增表单
const openAddForm = () => {
  formData.value = {
    id: '',
    name: '',
    size: '',
    url: '',
    is_default: false,
    is_featured: false,
    priority: 0,
    password: ''
  }
  isEditing.value = false
  showForm.value = true
}

// 打开编辑表单
const openEditForm = (item: DownloadItem) => {
  formData.value = {
    id: item.id,
    name: item.name,
    size: item.size,
    url: item.url,
    is_default: item.is_default === '1',
    is_featured: item.is_featured === '1',
    priority: item.priority || 0,
    password: ''
  }
  isEditing.value = true
  showForm.value = true
}

// 保存数据（新增或编辑）
const saveDownload = async () => {
  // 验证必填字段
  if (!formData.value.name || !formData.value.size || !formData.value.url) {
    showToast('请填写所有必填字段', 'error')
    return
  }

  loading.value = true
  try {
    const passwordHash = getPasswordHash()
    const action = isEditing.value ? 'update' : 'create'
    const requestData = {
      action,
      password_hash: passwordHash,
      ...formData.value
    }
    const response = await fetch(API_BASE, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(requestData)
    })
    
    const result = await response.json()
    
    if (result.success) {
      showToast(isEditing.value ? '更新成功' : '创建成功', 'success')
      showForm.value = false
      fetchDownloads()
    } else {
      if (result.error === '密码错误') {
        showToast('密码错误，请重新登录', 'error')
      } else {
        showToast(result.error || '操作失败', 'error')
      }
    }
  } catch (error) {
    console.error('保存失败:', error)
    showToast('网络错误', 'error')
  } finally {
    loading.value = false
  }
}

// 删除下载项
const deleteDownload = async (id: string, name: string) => {
  if (!confirm(`确定要删除"${name}"吗？`)) {
    return
  }

  loading.value = true
  try {
    const passwordHash = getPasswordHash()
    const response = await fetch(API_BASE, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ 
        action: 'delete',
        password_hash: passwordHash,
        id 
      })
    })
    
    const result = await response.json()
    
    if (result.success) {
      showToast('删除成功', 'success')
      fetchDownloads()
    } else {
      if (result.error === '密码错误') {
        showToast('密码错误，请重新登录', 'error')
      } else {
        showToast(result.error || '删除失败', 'error')
      }
    }
  } catch (error) {
    console.error('删除失败:', error)
    showToast('网络错误', 'error')
  } finally {
    loading.value = false
  }
}

// 取消表单
const cancelForm = () => {
  showForm.value = false
}

// 页面加载时获取数据
onMounted(() => {
  fetchDownloads()
})
</script>

<template>
  <div class="admin-container">
    <div class="content-wrapper">
      <!-- 页面头部 -->
      <div class="page-header">
        <div class="header-left">
          <h1 class="page-title">下载管理</h1>
          <p class="page-description">管理下载中心的资源信息</p>
        </div>
        <div class="header-right">
          <RouterLink to="/download" class="back-link">
            <span>← 返回下载页</span>
          </RouterLink>
        </div>
      </div>

      <!-- 消息提示 -->
      <transition name="fade">
        <div v-if="showMessage" :class="['toast', messageType]">
          {{ messageText }}
        </div>
      </transition>

      <!-- 操作栏 -->
      <div class="action-bar">
        <button @click="openAddForm" class="btn btn-primary" :disabled="loading">
          <span class="btn-icon">➕</span>
          <span>添加下载项</span>
        </button>
        <button @click="fetchDownloads" class="btn btn-secondary" :disabled="loading">
          <span class="btn-icon">🔄</span>
          <span>刷新</span>
        </button>
      </div>

      <!-- 数据表格 -->
      <div class="table-container">
        <table v-if="downloads.length > 0" class="data-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>名称</th>
              <th>大小/来源</th>
              <th>原始链接</th>
              <th>访问链接</th>
              <th>默认展示</th>
              <th>强调显示</th>
              <th>优先级</th>
              <th>操作</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in downloads" :key="item.id">
              <td>
                <div class="id-cell">
                  <code class="id-text">{{ item.id }}</code>
                  <button @click="copyToClipboard(item.id, 'ID')" class="btn-copy" title="复制 ID">
                    📋
                  </button>
                </div>
              </td>
              <td>{{ item.name }}</td>
              <td>{{ item.size }}</td>
              <td>
                <div class="url-cell">
                  <a :href="item.url" target="_blank" class="link-url">
                    {{ item.url }}
                  </a>
                  <button @click="copyToClipboard(item.url, '原始链接')" class="btn-copy" title="复制原始链接">
                    🔗
                  </button>
                </div>
              </td>
              <td>
                <div class="url-cell">
                  <span class="access-url">/download/{{ item.id }}</span>
                  <button @click="copyToClipboard(`/download/${item.id}`, '访问链接')" class="btn-copy" title="复制访问链接">
                    📋
                  </button>
                </div>
              </td>
              <td>
                <span :class="['badge', item.is_default === '1' ? 'badge-success' : 'badge-secondary']">
                  {{ item.is_default === '1' ? '是' : '否' }}
                </span>
              </td>
              <td>
                <span :class="['badge', item.is_featured === '1' ? 'badge-warning' : 'badge-secondary']">
                  {{ item.is_featured === '1' ? '是' : '否' }}
                </span>
              </td>
              <td>
                <span class="priority-badge">{{ item.priority || 0 }}</span>
              </td>
              <td>
                <div class="action-buttons">
                  <button @click="openEditForm(item)" class="btn-icon-only" title="编辑">
                    ✏️
                  </button>
                  <button @click="deleteDownload(item.id, item.name)" class="btn-icon-only" title="删除">
                    🗑️
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <div v-else-if="!loading" class="empty-state">
          <div class="empty-icon">📭</div>
          <p class="empty-text">暂无下载项</p>
          <button @click="openAddForm" class="btn btn-primary">
            <span class="btn-icon">➕</span>
            <span>添加第一个下载项</span>
          </button>
        </div>

        <div v-if="loading" class="loading-state">
          <div class="loading-spinner">⏳</div>
          <p>加载中...</p>
        </div>
      </div>

      <!-- 编辑/新增表单弹窗 -->
      <transition name="fade">
        <div v-if="showForm" class="modal-overlay" @click="cancelForm">
          <div class="modal" @click.stop>
            <div class="modal-header">
              <h2>{{ isEditing ? '编辑下载项' : '添加下载项' }}</h2>
              <button @click="cancelForm" class="close-btn">✕</button>
            </div>
            
            <div class="modal-body">
              <div class="form-group">
                <label for="name">名称 *</label>
                <input
                  id="name"
                  v-model="formData.name"
                  type="text"
                  placeholder="请输入名称"
                  class="form-input"
                />
              </div>

              <div class="form-group">
                <label for="size">大小/来源 *</label>
                <input
                  id="size"
                  v-model="formData.size"
                  type="text"
                  placeholder="例如：10MB 或 百度网盘"
                  class="form-input"
                />
              </div>

              <div class="form-group">
                <label for="url">下载链接 *</label>
                <input
                  id="url"
                  v-model="formData.url"
                  type="url"
                  placeholder="请输入完整的 URL"
                  class="form-input"
                />
              </div>

              <div class="form-group form-checkbox">
                <label class="checkbox-label">
                  <input
                    v-model="formData.is_default"
                    type="checkbox"
                    class="form-checkbox-input"
                  />
                  <span>默认展示（在首页显示）</span>
                </label>
              </div>

              <div class="form-group form-checkbox">
                <label class="checkbox-label">
                  <input
                    v-model="formData.is_featured"
                    type="checkbox"
                    class="form-checkbox-input"
                  />
                  <span>强调显示（高亮展示）</span>
                </label>
              </div>

              <div class="form-group">
                <label for="priority">优先级</label>
                <input
                  id="priority"
                  v-model.number="formData.priority"
                  type="number"
                  placeholder="数字越小越靠前，默认为 0"
                  class="form-input"
                />
              </div>
            </div>

            <div class="modal-footer">
              <button @click="cancelForm" class="btn btn-secondary">取消</button>
              <button @click="saveDownload" class="btn btn-primary" :disabled="loading">
                {{ loading ? '保存中...' : '保存' }}
              </button>
            </div>
          </div>
        </div>
      </transition>
    </div>
  </div>
</template>

<style src="@/styles/AdminDownloads.scss" scoped lang="scss"></style>

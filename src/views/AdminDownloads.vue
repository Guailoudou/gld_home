<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'

interface DownloadItem {
  id: string
  name: string
  size: string
  url: string
  is_default: string
}

const API_BASE = '/api/downloads.php'

// 密码管理
const adminPassword = ref('')
const isAuthenticated = ref(false)
const showPasswordModal = ref(false)

// 计算密码的 SHA256 哈希值
const computePasswordHash = async (password: string): Promise<string> => {
  const encoder = new TextEncoder()
  const data = encoder.encode(password)
  const hashBuffer = await crypto.subtle.digest('SHA-256', data)
  const hashArray = Array.from(new Uint8Array(hashBuffer))
  return hashArray.map(b => b.toString(16).padStart(2, '0')).join('')
}

// 从本地存储加载密码
onMounted(() => {
  const savedPassword = localStorage.getItem('admin_password')
  if (savedPassword) {
    adminPassword.value = savedPassword
    isAuthenticated.value = true
  } else {
    showPasswordModal.value = true
  }
  fetchDownloads()
})

// 验证密码
const verifyPassword = async () => {
  if (!adminPassword.value) {
    showToast('请输入密码', 'error')
    return
  }
  
  loading.value = true
  try {
    // 计算密码哈希
    const passwordHash = await computePasswordHash(adminPassword.value)
    
    // 通过 POST 请求验证密码并获取所有数据
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
      isAuthenticated.value = true
      localStorage.setItem('admin_password', adminPassword.value)
      showPasswordModal.value = false
      downloads.value = result.data
      showToast('验证成功', 'success')
    } else if (result.error === '密码错误') {
      showToast('密码错误', 'error')
      localStorage.removeItem('admin_password')
    } else {
      downloads.value = result.data
      showPasswordModal.value = false
    }
  } catch (error) {
    console.error('验证失败:', error)
    showToast('网络错误', 'error')
  } finally {
    loading.value = false
  }
}

// 退出登录
const logout = () => {
  localStorage.removeItem('admin_password')
  adminPassword.value = ''
  isAuthenticated.value = false
  showPasswordModal.value = true
  showToast('已退出登录', 'success')
}

const downloads = ref<DownloadItem[]>([])
const loading = ref(false)
const showMessage = ref(false)
const messageText = ref('')
const messageType = ref<'success' | 'error'>('success')

// 表单数据
const formData = ref({
  id: '',
  name: '',
  size: '',
  url: '',
  is_default: false,
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

// 获取下载列表
const fetchDownloads = async () => {
  loading.value = true
  try {
    // 如果没有密码，只获取默认数据
    if (!adminPassword.value) {
      const response = await fetch(API_BASE)
      const result = await response.json()
      
      if (result.success) {
        downloads.value = result.data
      }
      return
    }
    
    // 有密码时，使用 POST 请求获取所有数据
    const passwordHash = await computePasswordHash(adminPassword.value)
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
        logout()
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
    const passwordHash = await computePasswordHash(adminPassword.value)
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
        logout()
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
    const passwordHash = await computePasswordHash(adminPassword.value)
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
        logout()
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
          <button v-if="isAuthenticated" @click="logout" class="btn btn-logout">
            <span class="btn-icon">🔓</span>
            <span>退出登录</span>
          </button>
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

      <!-- 密码输入弹窗 -->
      <transition name="fade">
        <div v-if="showPasswordModal" class="modal-overlay">
          <div class="modal modal-small" @click.stop>
            <div class="modal-header">
              <h2>🔐 管理员验证</h2>
            </div>
            
            <div class="modal-body">
              <p class="password-description">请输入管理密码以进行验证</p>
              <div class="form-group">
                <label for="admin-password">管理密码</label>
                <input
                  id="admin-password"
                  v-model="adminPassword"
                  type="password"
                  placeholder="请输入管理密码"
                  class="form-input"
                  @keyup.enter="verifyPassword"
                />
              </div>
            </div>

            <div class="modal-footer">
              <button @click="verifyPassword" class="btn btn-primary" :disabled="loading">
                {{ loading ? '验证中...' : '验证并进入' }}
              </button>
            </div>
          </div>
        </div>
      </transition>

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

<style scoped lang="scss">
.admin-container {
  min-height: 100vh;
  padding: 20px;
}

.content-wrapper {
  max-width: 1200px;
  margin: 0 auto;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
  padding: 20px;
  background: rgba(255, 255, 255, 0.35);
  backdrop-filter: blur(20px);
  border-radius: 16px;
  border: 1px solid rgba(255, 255, 255, 0.4);

  .header-left {
    .page-title {
      font-size: 2rem;
      font-weight: 700;
      color: rgba(30, 30, 30, 0.95);
      margin: 0 0 8px 0;
    }

    .page-description {
      font-size: 1rem;
      color: rgba(30, 30, 30, 0.75);
      margin: 0;
    }
  }

  .header-right {
    display: flex;
    gap: 12px;
    align-items: center;
  }

  .back-link {
    padding: 10px 20px;
    background: rgba(255, 255, 255, 0.35);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.4);
    border-radius: 12px;
    color: rgba(30, 30, 30, 0.9);
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;

    &:hover {
      background: rgba(255, 255, 255, 0.55);
      transform: translateY(-2px);
    }
  }
}

.btn-logout {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  background: rgba(239, 68, 68, 0.9);
  border: none;
  border-radius: 12px;
  color: white;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;

  &:hover {
    background: rgba(239, 68, 68, 1);
    transform: translateY(-2px);
  }
}

// 消息提示
.toast {
  position: fixed;
  top: 20px;
  left: 50%;
  transform: translateX(-50%);
  padding: 12px 24px;
  border-radius: 12px;
  color: white;
  font-weight: 600;
  z-index: 10000;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);

  &.success {
    background: #10b981;
  }

  &.error {
    background: #ef4444;
  }
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

// 操作栏
.action-bar {
  display: flex;
  gap: 12px;
  margin-bottom: 20px;
}

.btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 24px;
  border: none;
  border-radius: 12px;
  font-weight: 600;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.3s ease;

  &:disabled {
    opacity: 0.6;
    cursor: not-allowed;
  }

  &.btn-primary {
    background: rgba(59, 130, 246, 0.9);
    color: white;

    &:hover:not(:disabled) {
      background: rgba(59, 130, 246, 1);
      transform: translateY(-2px);
    }
  }

  &.btn-secondary {
    background: rgba(255, 255, 255, 0.35);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.4);
    color: rgba(30, 30, 30, 0.9);

    &:hover:not(:disabled) {
      background: rgba(255, 255, 255, 0.55);
    }
  }
}

.btn-icon {
  font-size: 1.2rem;
}

// ID 和链接单元格
.id-cell,
.url-cell {
  display: flex;
  align-items: center;
  gap: 8px;
}

.id-text {
  font-family: 'Courier New', monospace;
  font-size: 0.85rem;
  padding: 4px 8px;
  background: rgba(0, 0, 0, 0.05);
  border-radius: 4px;
  color: rgba(30, 30, 30, 0.8);
}

.access-url {
  font-family: 'Courier New', monospace;
  font-size: 0.85rem;
  padding: 4px 8px;
  background: rgba(59, 130, 246, 0.1);
  border-radius: 4px;
  color: rgba(59, 130, 246, 0.9);
}

.btn-copy {
  width: 28px;
  height: 28px;
  border: none;
  background: rgba(255, 255, 255, 0.5);
  border-radius: 6px;
  cursor: pointer;
  font-size: 1rem;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;

  &:hover {
    background: rgba(255, 255, 255, 0.8);
    transform: scale(1.1);
  }
}

// 表格
.table-container {
  background: rgba(255, 255, 255, 0.35);
  backdrop-filter: blur(20px);
  border-radius: 16px;
  border: 1px solid rgba(255, 255, 255, 0.4);
  overflow: hidden;
}

.data-table {
  width: 100%;
  border-collapse: collapse;

  thead {
    background: rgba(255, 255, 255, 0.5);
    
    th {
      padding: 16px;
      text-align: left;
      font-weight: 600;
      color: rgba(30, 30, 30, 0.9);
      border-bottom: 2px solid rgba(255, 255, 255, 0.4);
    }
  }

  tbody {
    tr {
      border-bottom: 1px solid rgba(255, 255, 255, 0.3);
      transition: background 0.3s ease;

      &:hover {
        background: rgba(255, 255, 255, 0.3);
      }

      td {
        padding: 16px;
        color: rgba(30, 30, 30, 0.85);

        .link-url {
          color: rgba(59, 130, 246, 0.9);
          text-decoration: none;
          word-break: break-all;

          &:hover {
            text-decoration: underline;
          }
        }
      }
    }
  }
}

.badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 0.875rem;
  font-weight: 600;

  &.badge-success {
    background: rgba(16, 185, 129, 0.2);
    color: #059669;
  }

  &.badge-secondary {
    background: rgba(107, 114, 128, 0.2);
    color: #6b7280;
  }
}

.action-buttons {
  display: flex;
  gap: 8px;
}

.btn-icon-only {
  width: 36px;
  height: 36px;
  border: none;
  background: rgba(255, 255, 255, 0.35);
  border-radius: 8px;
  cursor: pointer;
  font-size: 1.2rem;
  transition: all 0.3s ease;

  &:hover {
    background: rgba(255, 255, 255, 0.55);
    transform: scale(1.1);
  }
}

// 空状态
.empty-state {
  text-align: center;
  padding: 60px 20px;

  .empty-icon {
    font-size: 4rem;
    margin-bottom: 16px;
  }

  .empty-text {
    font-size: 1.1rem;
    color: rgba(30, 30, 30, 0.7);
    margin-bottom: 24px;
  }
}

// 加载状态
.loading-state {
  text-align: center;
  padding: 60px 20px;

  .loading-spinner {
    font-size: 3rem;
    margin-bottom: 16px;
    animation: spin 1s linear infinite;
  }

  p {
    color: rgba(30, 30, 30, 0.7);
  }
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

// 弹窗
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  padding: 20px;
}

.modal {
  background: white;
  border-radius: 16px;
  max-width: 500px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  
  &.modal-small {
    max-width: 400px;
  }
}

.password-description {
  margin-bottom: 20px;
  color: rgba(30, 30, 30, 0.7);
  font-size: 0.95rem;
  text-align: center;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid rgba(0, 0, 0, 0.1);

  h2 {
    margin: 0;
    font-size: 1.5rem;
    color: rgba(30, 30, 30, 0.9);
  }

  .close-btn {
    width: 32px;
    height: 32px;
    border: none;
    background: transparent;
    border-radius: 8px;
    cursor: pointer;
    font-size: 1.5rem;
    color: rgba(30, 30, 30, 0.6);
    transition: all 0.3s ease;

    &:hover {
      background: rgba(0, 0, 0, 0.05);
      color: rgba(30, 30, 30, 0.9);
    }
  }
}

.modal-body {
  padding: 20px;
}

.form-group {
  margin-bottom: 20px;

  label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: rgba(30, 30, 30, 0.9);
  }

  .form-input {
    width: 100%;
    padding: 12px;
    border: 1px solid rgba(0, 0, 0, 0.2);
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.3s ease;

    &:focus {
      outline: none;
      border-color: rgba(59, 130, 246, 0.8);
      box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
  }

  &.form-checkbox {
    .checkbox-label {
      display: flex;
      align-items: center;
      gap: 8px;
      cursor: pointer;

      .form-checkbox-input {
        width: 18px;
        height: 18px;
        cursor: pointer;
      }

      span {
        font-weight: 500;
        color: rgba(30, 30, 30, 0.85);
      }
    }
  }
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding: 20px;
  border-top: 1px solid rgba(0, 0, 0, 0.1);
}

@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
    gap: 16px;
    align-items: flex-start;

    .header-right {
      align-self: flex-end;
    }
  }

  .data-table {
    thead {
      display: none;
    }

    tbody {
      tr {
        display: block;
        margin-bottom: 16px;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.3);

        td {
          display: block;
          padding: 12px;
          border-bottom: 1px solid rgba(255, 255, 255, 0.2);

          &:last-child {
            border-bottom: none;
          }

          &:before {
            display: block;
            font-weight: 600;
            margin-bottom: 4px;
            color: rgba(30, 30, 30, 0.7);
          }

          &:nth-child(1):before { content: 'ID'; }
          &:nth-child(2):before { content: '名称'; }
          &:nth-child(3):before { content: '大小/来源'; }
          &:nth-child(4):before { content: '原始链接'; }
          &:nth-child(5):before { content: '访问链接'; }
          &:nth-child(6):before { content: '默认展示'; }
          &:nth-child(7):before { content: '操作'; }
        }
      }
    }
  }

  .action-bar {
    flex-direction: column;

    .btn {
      width: 100%;
      justify-content: center;
    }
  }
}
</style>

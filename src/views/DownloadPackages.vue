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

interface DownloadPackage {
  id: string
  code: string
  name: string
  description: string
  download_ids: string[]
  is_active: string
}

const API_BASE = '/api/downloads.php'

// 密码管理
const adminPassword = ref('')
const isAuthenticated = ref(false)

// 下载列表
const downloads = ref<DownloadItem[]>([])
const selectedDownloads = ref<Set<string>>(new Set())

// 下载包列表
const packages = ref<DownloadPackage[]>([])
const loading = ref(false)
const showMessage = ref(false)
const messageText = ref('')
const messageType = ref<'success' | 'error'>('success')

// 表单数据
const formData = ref({
  id: '',
  code: '',
  name: '',
  description: '',
  download_ids: [] as string[],
  is_active: false
})

const isEditing = ref(false)
const showForm = ref(false)
const showPackageModal = ref(false)

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
    fetchAll()
  } else {
    showPackageModal.value = true
  }
})

// 显示消息提示
const showToast = (text: string, type: 'success' | 'error' = 'success') => {
  messageText.value = text
  messageType.value = type
  showMessage.value = true
  setTimeout(() => {
    showMessage.value = false
  }, 3000)
}

// 验证密码
const verifyPassword = async () => {
  if (!adminPassword.value) {
    showToast('请输入密码', 'error')
    return
  }
  
  loading.value = true
  try {
    const passwordHash = await computePasswordHash(adminPassword.value)
    const response = await fetch(API_BASE, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'get_all', password_hash: passwordHash })
    })
    const result = await response.json()
    
    if (result.success) {
      isAuthenticated.value = true
      localStorage.setItem('admin_password', adminPassword.value)
      showPackageModal.value = false
      downloads.value = result.data
      fetchPackages()
      showToast('验证成功', 'success')
    } else if (result.error === '密码错误') {
      showToast('密码错误', 'error')
      localStorage.removeItem('admin_password')
    }
  } catch (error) {
    console.error('验证失败:', error)
    showToast('网络错误', 'error')
  } finally {
    loading.value = false
  }
}

// 获取所有数据
const fetchAll = async () => {
  loading.value = true
  try {
    const passwordHash = await computePasswordHash(adminPassword.value)
    const response = await fetch(API_BASE, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'get_all', password_hash: passwordHash })
    })
    const result = await response.json()
    
    if (result.success) {
      downloads.value = result.data
      fetchPackages()
    }
  } catch (error) {
    console.error('获取数据失败:', error)
    showToast('网络错误', 'error')
  } finally {
    loading.value = false
  }
}

// 获取下载包列表
const fetchPackages = async () => {
  try {
    const passwordHash = await computePasswordHash(adminPassword.value)
    const response = await fetch(API_BASE, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'get_packages', password_hash: passwordHash })
    })
    const result = await response.json()
    
    if (result.success) {
      // 解析每个下载包的 download_ids
      packages.value = result.data.map((pkg: any) => {
        let downloadIds: string[] = []
        if (typeof pkg.download_ids === 'string') {
          try {
            downloadIds = JSON.parse(pkg.download_ids)
          } catch (e) {
            downloadIds = []
          }
        } else if (Array.isArray(pkg.download_ids)) {
          downloadIds = pkg.download_ids
        }
        return {
          ...pkg,
          download_ids: downloadIds
        }
      })
    }
  } catch (error) {
    console.error('获取下载包失败:', error)
  }
}

// 切换选择下载项
const toggleDownloadSelection = (id: string) => {
  if (selectedDownloads.value.has(id)) {
    selectedDownloads.value.delete(id)
  } else {
    selectedDownloads.value.add(id)
  }
}

// 打开创建下载包表单
const openCreatePackage = () => {
  if (selectedDownloads.value.size === 0) {
    showToast('请至少选择一个下载项', 'error')
    return
  }
  
  formData.value = {
    id: '',
    code: '',
    name: '',
    description: '',
    download_ids: Array.from(selectedDownloads.value),
    is_active: true
  }
  isEditing.value = false
  showForm.value = true
}

// 打开编辑下载包表单
const openEditPackage = (pkg: DownloadPackage) => {
  // 解析 download_ids，如果是字符串则解析 JSON
  let downloadIds: string[] = []
  if (typeof pkg.download_ids === 'string') {
    try {
      downloadIds = JSON.parse(pkg.download_ids)
    } catch (e) {
      downloadIds = pkg.download_ids as any
    }
  } else {
    downloadIds = pkg.download_ids
  }
  
  formData.value = {
    id: pkg.id,
    code: pkg.code,
    name: pkg.name,
    description: pkg.description,
    download_ids: downloadIds,
    is_active: pkg.is_active === '1'
  }
  isEditing.value = true
  showForm.value = true
}

// 保存下载包
const savePackage = async () => {
  if (!formData.value.name || formData.value.download_ids.length === 0) {
    showToast('请填写名称并选择至少一个下载项', 'error')
    return
  }

  loading.value = true
  try {
    const passwordHash = await computePasswordHash(adminPassword.value)
    const requestData = {
      action: isEditing.value ? 'update_package' : 'create_package',
      ...formData.value,
      is_active: formData.value.is_active,
      password_hash: passwordHash
    }
    
    const response = await fetch(API_BASE, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(requestData)
    })
    
    const result = await response.json()
    
    if (result.success) {
      showToast(isEditing.value ? '更新成功' : '创建成功', 'success')
      showForm.value = false
      fetchPackages()
    } else {
      showToast(result.error || '操作失败', 'error')
    }
  } catch (error) {
    console.error('保存失败:', error)
    showToast('网络错误', 'error')
  } finally {
    loading.value = false
  }
}

// 删除下载包
const deletePackage = async (id: string, name: string) => {
  if (!confirm(`确定要删除"${name}"吗？`)) {
    return
  }

  loading.value = true
  try {
    const passwordHash = await computePasswordHash(adminPassword.value)
    const response = await fetch(API_BASE, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'delete_package', id, password_hash: passwordHash })
    })
    
    const result = await response.json()
    
    if (result.success) {
      showToast('删除成功', 'success')
      fetchPackages()
    } else {
      showToast(result.error || '删除失败', 'error')
    }
  } catch (error) {
    console.error('删除失败:', error)
    showToast('网络错误', 'error')
  } finally {
    loading.value = false
  }
}

// 复制下载码
const copyDownloadCode = async (code: string) => {
  try {
    await navigator.clipboard.writeText(code)
    showToast('下载码已复制', 'success')
  } catch (error) {
    console.error('复制失败:', error)
    showToast('复制失败', 'error')
  }
}

// 取消表单
const cancelForm = () => {
  showForm.value = false
}
</script>

<template>
  <div class="package-container">
    <div class="content-wrapper">
      <!-- 页面头部 -->
      <div class="page-header">
        <div class="header-left">
          <h1 class="page-title">下载包管理</h1>
          <p class="page-description">创建和管理下载资源包</p>
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

      <!-- 下载项选择 -->
      <div class="section">
        <h2 class="section-title">1️⃣ 选择下载项</h2>
        <div class="download-grid">
          <div 
            v-for="item in downloads" 
            :key="item.id"
            :class="['download-card', { selected: selectedDownloads.has(item.id) }]"
            @click="toggleDownloadSelection(item.id)"
          >
            <div class="card-checkbox">
              <span v-if="selectedDownloads.has(item.id)" class="checkbox-checked">✅</span>
              <span v-else class="checkbox-unchecked">⬜</span>
            </div>
            <div class="card-info">
              <div class="card-name">{{ item.name }}</div>
              <div class="card-size">{{ item.size }}</div>
            </div>
          </div>
          <div v-if="downloads.length === 0" class="empty-tip">
            <p>暂无下载项</p>
          </div>
        </div>
        <div class="selected-info">
          已选择 <strong>{{ selectedDownloads.size }}</strong> 个下载项
        </div>
        <button 
          @click="openCreatePackage" 
          class="btn btn-primary"
          :disabled="selectedDownloads.size === 0"
        >
          <span class="btn-icon">➕</span>
          <span>创建下载包</span>
        </button>
      </div>

      <!-- 下载包列表 -->
      <div class="section">
        <h2 class="section-title">2️⃣ 下载包列表</h2>
        <div class="packages-table">
          <table v-if="packages.length > 0">
            <thead>
              <tr>
                <th>下载码</th>
                <th>名称</th>
                <th>描述</th>
                <th>包含资源</th>
                <th>状态</th>
                <th>操作</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="pkg in packages" :key="pkg.id">
                <td>
                  <div class="code-cell">
                    <code class="code-text">{{ pkg.code }}</code>
                    <button @click="copyDownloadCode(pkg.code)" class="btn-copy" title="复制下载码">
                      📋
                    </button>
                  </div>
                </td>
                <td>{{ pkg.name }}</td>
                <td>{{ pkg.description || '-' }}</td>
                <td>{{ pkg.download_ids.length }} 个资源</td>
                <td>
                  <span :class="['badge', pkg.is_active === '1' ? 'badge-success' : 'badge-secondary']">
                    {{ pkg.is_active === '1' ? '启用' : '禁用' }}
                  </span>
                </td>
                <td>
                  <div class="action-buttons">
                    <button @click="openEditPackage(pkg)" class="btn-icon-only" title="编辑">
                      ✏️
                    </button>
                    <button @click="deletePackage(pkg.id, pkg.name)" class="btn-icon-only" title="删除">
                      🗑️
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
          <div v-else class="empty-state">
            <p>暂无下载包</p>
          </div>
        </div>
      </div>

      <!-- 编辑/创建表单弹窗 -->
      <transition name="fade">
        <div v-if="showForm" class="modal-overlay" @click="cancelForm">
          <div class="modal" @click.stop>
            <div class="modal-header">
              <h2>{{ isEditing ? '编辑下载包' : '创建下载包' }}</h2>
              <button @click="cancelForm" class="close-btn">✕</button>
            </div>
            
            <div class="modal-body">
              <div class="form-group">
                <label for="name">名称 *</label>
                <input
                  id="name"
                  v-model="formData.name"
                  type="text"
                  placeholder="请输入下载包名称"
                  class="form-input"
                />
              </div>

              <div class="form-group">
                <label for="description">描述</label>
                <textarea
                  id="description"
                  v-model="formData.description"
                  placeholder="请输入下载包描述（可选）"
                  class="form-input form-textarea"
                  rows="3"
                ></textarea>
              </div>

              <div class="form-group">
                <label>包含的下载项</label>
                <div class="selected-downloads">
                  <div 
                    v-for="id in formData.download_ids" 
                    :key="id"
                    class="selected-item"
                  >
                    {{ downloads.find(d => d.id === id)?.name || id }}
                  </div>
                </div>
              </div>

              <div class="form-group form-checkbox">
                <label class="checkbox-label">
                  <input
                    v-model="formData.is_active"
                    type="checkbox"
                    class="form-checkbox-input"
                  />
                  <span>启用此下载包</span>
                </label>
              </div>
            </div>

            <div class="modal-footer">
              <button @click="cancelForm" class="btn btn-secondary">取消</button>
              <button @click="savePackage" class="btn btn-primary" :disabled="loading">
                {{ loading ? '保存中...' : '保存' }}
              </button>
            </div>
          </div>
        </div>
      </transition>

      <!-- 密码弹窗 -->
      <transition name="fade">
        <div v-if="showPackageModal" class="modal-overlay">
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
    </div>
  </div>
</template>

<style scoped lang="scss">
.package-container {
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
}

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

.section {
  margin-bottom: 40px;
  padding: 20px;
  background: rgba(255, 255, 255, 0.35);
  backdrop-filter: blur(20px);
  border-radius: 16px;
  border: 1px solid rgba(255, 255, 255, 0.4);

  .section-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: rgba(30, 30, 30, 0.95);
    margin: 0 0 20px 0;
  }
}

.download-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 16px;
  margin-bottom: 20px;
}

.download-card {
  padding: 16px;
  background: rgba(255, 255, 255, 0.5);
  border-radius: 12px;
  border: 2px solid transparent;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 12px;

  &:hover {
    background: rgba(255, 255, 255, 0.7);
    transform: translateY(-2px);
  }

  &.selected {
    border-color: rgba(59, 130, 246, 0.8);
    background: rgba(59, 130, 246, 0.1);
  }

  .card-checkbox {
    font-size: 1.5rem;
  }

  .card-info {
    flex: 1;
    min-width: 0;

    .card-name {
      font-weight: 600;
      color: rgba(30, 30, 30, 0.9);
      margin-bottom: 4px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .card-size {
      font-size: 0.875rem;
      color: rgba(30, 30, 30, 0.6);
    }
  }
}

.empty-tip {
  text-align: center;
  padding: 40px;
  color: rgba(30, 30, 30, 0.6);
}

.selected-info {
  margin-bottom: 20px;
  color: rgba(30, 30, 30, 0.8);
  font-size: 1rem;

  strong {
    color: rgba(59, 130, 246, 0.9);
    font-size: 1.2rem;
  }
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

.packages-table {
  overflow-x: auto;

  table {
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
        }
      }
    }
  }
}

.code-cell {
  display: flex;
  align-items: center;
  gap: 8px;
}

.code-text {
  font-family: 'Courier New', monospace;
  font-size: 1rem;
  padding: 4px 8px;
  background: rgba(59, 130, 246, 0.1);
  border-radius: 4px;
  color: rgba(59, 130, 246, 0.9);
  font-weight: 600;
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

.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: rgba(30, 30, 30, 0.6);
}

.selected-downloads {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  padding: 12px;
  background: rgba(0, 0, 0, 0.03);
  border-radius: 8px;
  min-height: 50px;
}

.selected-item {
  padding: 6px 12px;
  background: rgba(59, 130, 246, 0.1);
  border-radius: 6px;
  color: rgba(59, 130, 246, 0.9);
  font-size: 0.875rem;
  font-weight: 500;
}

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

.password-description {
  margin-bottom: 20px;
  color: rgba(30, 30, 30, 0.7);
  font-size: 0.95rem;
  text-align: center;
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

    &.form-textarea {
      resize: vertical;
      min-height: 80px;
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

  .download-grid {
    grid-template-columns: 1fr;
  }
}
</style>

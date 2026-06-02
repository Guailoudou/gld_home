<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import HIcon from '@/components/HIcon.vue'

interface Message {
  id: number
  nickname: string
  email: string
  content: string
  ip_address: string | null
  is_displayed: number
  created_at: string
}

interface Pagination {
  page: number
  limit: number
  total: number
  totalPages: number
}

const API_BASE = '/api/messages.php'
const messages = ref<Message[]>([])
const loading = ref(false)
const passwordHash = ref<string>('')
const isAuthenticated = ref(false)

// 分页
const pagination = ref<Pagination>({
  page: 1,
  limit: 20,
  total: 0,
  totalPages: 0
})

// 搜索和过滤
const searchNickname = ref('')
const filterDisplayed = ref<number | null>(null)
const filterDateFrom = ref('')
const filterDateTo = ref('')

// 批量操作
const selectedMessages = ref<Set<number>>(new Set())

// 密码输入
const showPasswordInput = ref(true)
const passwordInput = ref('')
const passwordError = ref('')

// 操作反馈
const actionMessage = ref('')
const actionType = ref<'success' | 'error' | ''>('')

// 计算属性
const displayedCount = computed(() => messages.value.filter(m => m.is_displayed === 1).length)
const pendingCount = computed(() => messages.value.filter(m => m.is_displayed === 0).length)

// 计算密码哈希
async function computePasswordHash(password: string): Promise<string> {
  const encoder = new TextEncoder()
  const data = encoder.encode(password)
  const hashBuffer = await crypto.subtle.digest('SHA-256', data)
  const hashArray = Array.from(new Uint8Array(hashBuffer))
  return hashArray.map(b => b.toString(16).padStart(2, '0')).join('')
}

// 验证密码
async function authenticate() {
  passwordError.value = ''
  const hash = await computePasswordHash(passwordInput.value)
  passwordHash.value = hash
  
  try {
    const response = await fetch(API_BASE, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'get_all',
        password_hash: hash,
        page: 1,
        limit: pagination.value.limit
      })
    })
    
    const result = await response.json()
    
    if (result.success) {
      isAuthenticated.value = true
      showPasswordInput.value = false
      messages.value = result.data.messages
      pagination.value = result.data.pagination
    } else {
      passwordError.value = '密码错误'
    }
  } catch (error) {
    passwordError.value = '验证失败，请重试'
  }
}

// 获取留言列表
async function fetchMessages(page = 1) {
  loading.value = true
  try {
    const response = await fetch(API_BASE, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'get_all',
        password_hash: passwordHash.value,
        page,
        limit: pagination.value.limit,
        search_nickname: searchNickname.value || undefined,
        filter_displayed: filterDisplayed.value,
        filter_date_from: filterDateFrom.value || undefined,
        filter_date_to: filterDateTo.value || undefined
      })
    })
    
    const result = await response.json()
    
    if (result.success) {
      messages.value = result.data.messages
      pagination.value = result.data.pagination
    }
  } catch (error) {
    console.error('获取留言列表失败:', error)
  } finally {
    loading.value = false
  }
}

// 切换展示状态
async function toggleDisplay(id: number) {
  try {
    const response = await fetch(API_BASE, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'toggle_display',
        id,
        password_hash: passwordHash.value
      })
    })
    
    const result = await response.json()
    
    if (result.success) {
      showActionMessage('状态已更新', 'success')
      fetchMessages(pagination.value.page)
    } else {
      showActionMessage('更新失败', 'error')
    }
  } catch (error) {
    showActionMessage('操作失败', 'error')
  }
}

// 删除留言
async function deleteMessage(id: number) {
  if (!confirm('确定要删除这条留言吗？')) {
    return
  }
  
  try {
    const response = await fetch(API_BASE, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'delete',
        id,
        password_hash: passwordHash.value
      })
    })
    
    const result = await response.json()
    
    if (result.success) {
      showActionMessage('留言已删除', 'success')
      fetchMessages(pagination.value.page)
    } else {
      showActionMessage('删除失败', 'error')
    }
  } catch (error) {
    showActionMessage('操作失败', 'error')
  }
}

// 批量操作
async function batchToggle(display: number) {
  if (selectedMessages.value.size === 0) {
    showActionMessage('请先选择留言', 'error')
    return
  }
  
  try {
    const response = await fetch(API_BASE, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'batch_toggle',
        ids: Array.from(selectedMessages.value),
        display,
        password_hash: passwordHash.value
      })
    })
    
    const result = await response.json()
    
    if (result.success) {
      showActionMessage(`已更新 ${result.affected} 条留言`, 'success')
      selectedMessages.value.clear()
      fetchMessages(pagination.value.page)
    }
  } catch (error) {
    showActionMessage('操作失败', 'error')
  }
}

async function batchDelete() {
  if (selectedMessages.value.size === 0) {
    showActionMessage('请先选择留言', 'error')
    return
  }
  
  if (!confirm(`确定要删除选中的 ${selectedMessages.value.size} 条留言吗？`)) {
    return
  }
  
  try {
    const response = await fetch(API_BASE, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'batch_delete',
        ids: Array.from(selectedMessages.value),
        password_hash: passwordHash.value
      })
    })
    
    const result = await response.json()
    
    if (result.success) {
      showActionMessage(`已删除 ${result.affected} 条留言`, 'success')
      selectedMessages.value.clear()
      fetchMessages(pagination.value.page)
    }
  } catch (error) {
    showActionMessage('操作失败', 'error')
  }
}

// 选择/取消选择
function toggleSelect(id: number) {
  if (selectedMessages.value.has(id)) {
    selectedMessages.value.delete(id)
  } else {
    selectedMessages.value.add(id)
  }
}

// 显示操作消息
function showActionMessage(msg: string, type: 'success' | 'error') {
  actionMessage.value = msg
  actionType.value = type
  setTimeout(() => {
    actionMessage.value = ''
    actionType.value = ''
  }, 3000)
}

// 搜索
function handleSearch() {
  fetchMessages(1)
}

// 重置筛选
function resetFilters() {
  searchNickname.value = ''
  filterDisplayed.value = null
  filterDateFrom.value = ''
  filterDateTo.value = ''
  fetchMessages(1)
}

// 格式化时间
function formatTime(time: string) {
  const date = new Date(time)
  return date.toLocaleString('zh-CN', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit'
  })
}

onMounted(() => {
  // 检查本地是否有密码
  const savedHash = localStorage.getItem('admin_password_hash')
  if (savedHash) {
    passwordHash.value = savedHash
    isAuthenticated.value = true
    showPasswordInput.value = false
    fetchMessages(1)
  }
})
</script>

<template>
  <div class="admin-messages">
    <div class="content-wrapper">
      <!-- 页面头部 -->
      <div class="page-header">
        <h1 class="page-title">
          <HIcon name="chat" :size="32" class="title-icon" />
          留言管理
        </h1>
        <p class="page-description">管理用户留言，审核并展示优质内容</p>
      </div>

      <!-- 密码验证 -->
      <div v-if="showPasswordInput" class="auth-card">
        <div class="auth-form">
          <h2 class="auth-title">需要管理密码</h2>
          <input
            v-model="passwordInput"
            type="password"
            placeholder="请输入管理密码"
            class="auth-input"
            @keyup.enter="authenticate"
          />
          <button class="auth-btn" @click="authenticate">验证</button>
          <p v-if="passwordError" class="auth-error">{{ passwordError }}</p>
        </div>
      </div>

      <!-- 管理界面 -->
      <template v-else>
        <!-- 统计信息 -->
        <div class="stats-row">
          <div class="stat-card">
            <span class="stat-value">{{ pagination.total }}</span>
            <span class="stat-label">总留言数</span>
          </div>
          <div class="stat-card success">
            <span class="stat-value">{{ displayedCount }}</span>
            <span class="stat-label">已展示</span>
          </div>
          <div class="stat-card warning">
            <span class="stat-value">{{ pendingCount }}</span>
            <span class="stat-label">待审核</span>
          </div>
          <div class="stat-card info">
            <span class="stat-value">{{ selectedMessages.size }}</span>
            <span class="stat-label">已选择</span>
          </div>
        </div>

        <!-- 搜索和筛选 -->
        <div class="filter-card">
          <div class="filter-row">
            <input
              v-model="searchNickname"
              type="text"
              placeholder="搜索昵称..."
              class="filter-input"
            />
            <select v-model="filterDisplayed" class="filter-select">
              <option :value="null">全部状态</option>
              <option :value="1">已展示</option>
              <option :value="0">未展示</option>
            </select>
            <input v-model="filterDateFrom" type="date" class="filter-input" />
            <span class="filter-separator">至</span>
            <input v-model="filterDateTo" type="date" class="filter-input" />
            <button class="filter-btn" @click="handleSearch">搜索</button>
            <button class="filter-btn reset" @click="resetFilters">重置</button>
          </div>
        </div>

        <!-- 批量操作 -->
        <div v-if="selectedMessages.size > 0" class="batch-actions">
          <button class="batch-btn approve" @click="batchToggle(1)">
            <HIcon name="check" :size="16" />
            批量展示
          </button>
          <button class="batch-btn hide" @click="batchToggle(0)">
            <HIcon name="close" :size="16" />
            批量隐藏
          </button>
          <button class="batch-btn delete" @click="batchDelete">
            <HIcon name="delete" :size="16" />
            批量删除
          </button>
        </div>

        <!-- 留言列表 -->
        <div class="messages-container">
          <div v-if="loading" class="loading-state">
            <HIcon name="refresh" :size="40" class="loading-spinner" />
            <p>加载中...</p>
          </div>

          <div v-else-if="messages.length === 0" class="empty-state">
            <HIcon name="empty" :size="64" />
            <p>暂无留言</p>
          </div>

          <div v-else class="message-list">
            <div
              v-for="msg in messages"
              :key="msg.id"
              class="message-card"
              :class="{
                selected: selectedMessages.has(msg.id),
                displayed: msg.is_displayed === 1
              }"
            >
              <div class="message-header">
                <label class="checkbox-label">
                  <input
                    type="checkbox"
                    :checked="selectedMessages.has(msg.id)"
                    @change="toggleSelect(msg.id)"
                  />
                  <span class="checkmark"></span>
                </label>
                <div class="message-info">
                  <span class="message-nickname">{{ msg.nickname }}</span>
                  <span class="message-email">{{ msg.email }}</span>
                </div>
                <div class="message-meta">
                  <span class="message-time">{{ formatTime(msg.created_at) }}</span>
                  <span class="message-ip">{{ msg.ip_address }}</span>
                  <span
                    class="message-status"
                    :class="{ active: msg.is_displayed === 1 }"
                  >
                    {{ msg.is_displayed === 1 ? '已展示' : '未展示' }}
                  </span>
                </div>
              </div>
              <div class="message-content">{{ msg.content }}</div>
              <div class="message-actions">
                <button
                  class="action-btn toggle"
                  @click="toggleDisplay(msg.id)"
                >
                  {{ msg.is_displayed === 1 ? '隐藏' : '展示' }}
                </button>
                <button class="action-btn delete" @click="deleteMessage(msg.id)">
                  删除
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- 分页 -->
        <div v-if="pagination.totalPages > 1" class="pagination">
          <button
            class="page-btn"
            :disabled="pagination.page === 1"
            @click="fetchMessages(pagination.page - 1)"
          >
            上一页
          </button>
          <span class="page-info">
            第 {{ pagination.page }} / {{ pagination.totalPages }} 页
          </span>
          <button
            class="page-btn"
            :disabled="pagination.page === pagination.totalPages"
            @click="fetchMessages(pagination.page + 1)"
          >
            下一页
          </button>
        </div>
      </template>

      <!-- 操作反馈 -->
      <div v-if="actionMessage" class="action-toast" :class="actionType">
        {{ actionMessage }}
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss">
.admin-messages {
  min-height: 100vh;
  padding: 40px 20px;
}

.content-wrapper {
  max-width: 1200px;
  margin: 0 auto;
}

.page-header {
  margin-bottom: 30px;

  .page-title {
    font-size: 2rem;
    font-weight: 700;
    color: rgba(30, 30, 30, 0.95);
    margin: 0 0 8px 0;
    display: flex;
    align-items: center;
    gap: 12px;

    .title-icon {
      color: rgba(30, 30, 30, 0.9);
    }
  }

  .page-description {
    font-size: 1rem;
    color: rgba(30, 30, 30, 0.7);
    margin: 0;
  }
}

.auth-card {
  background: rgba(255, 255, 255, 0.3);
  backdrop-filter: blur(15px);
  border-radius: 16px;
  padding: 40px;
  border: 1px solid rgba(255, 255, 255, 0.4);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);

  .auth-form {
    max-width: 400px;
    margin: 0 auto;
    text-align: center;
  }

  .auth-title {
    font-size: 1.5rem;
    color: rgba(30, 30, 30, 0.95);
    margin: 0 0 20px 0;
  }

  .auth-input {
    width: 100%;
    padding: 12px 16px;
    background: rgba(255, 255, 255, 0.6);
    border: 2px solid rgba(255, 255, 255, 0.5);
    border-radius: 8px;
    font-size: 1rem;
    margin-bottom: 15px;
    box-sizing: border-box;

    &:focus {
      outline: none;
      border-color: rgba(102, 126, 234, 0.6);
      background: rgba(255, 255, 255, 0.8);
    }
  }

  .auth-btn {
    width: 100%;
    padding: 12px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;

    &:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }
  }

  .auth-error {
    color: #e74c3c;
    margin: 10px 0 0 0;
    font-size: 0.9rem;
  }
}

.stats-row {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 15px;
  margin-bottom: 25px;

  @media (max-width: 768px) {
    grid-template-columns: repeat(2, 1fr);
  }
}

.stat-card {
  background: rgba(255, 255, 255, 0.3);
  backdrop-filter: blur(15px);
  border-radius: 12px;
  padding: 20px;
  text-align: center;
  border: 1px solid rgba(255, 255, 255, 0.4);

  .stat-value {
    display: block;
    font-size: 2rem;
    font-weight: 700;
    color: rgba(30, 30, 30, 0.95);
  }

  .stat-label {
    display: block;
    font-size: 0.9rem;
    color: rgba(30, 30, 30, 0.7);
    margin-top: 5px;
  }
}

.filter-card {
  background: rgba(255, 255, 255, 0.3);
  backdrop-filter: blur(15px);
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 20px;
  border: 1px solid rgba(255, 255, 255, 0.4);

  .filter-row {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;

    @media (max-width: 768px) {
      flex-direction: column;
      align-items: stretch;
    }
  }

  .filter-input,
  .filter-select {
    padding: 10px 14px;
    background: rgba(255, 255, 255, 0.6);
    border: 1px solid rgba(255, 255, 255, 0.4);
    border-radius: 8px;
    font-size: 0.95rem;
    flex: 1;
    min-width: 120px;

    &:focus {
      outline: none;
      border-color: rgba(102, 126, 234, 0.6);
    }
  }

  .filter-separator {
    color: rgba(30, 30, 30, 0.6);
    font-size: 0.9rem;
  }

  .filter-btn {
    padding: 10px 20px;
    background: rgba(102, 126, 234, 0.9);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;

    &:hover {
      background: rgba(102, 126, 234, 1);
    }

    &.reset {
      background: rgba(255, 255, 255, 0.6);
      color: rgba(30, 30, 30, 0.8);

      &:hover {
        background: rgba(255, 255, 255, 0.9);
      }
    }
  }
}

.batch-actions {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
  padding: 15px;
  background: rgba(255, 255, 255, 0.3);
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.4);

  .batch-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 10px 18px;
    border: none;
    border-radius: 8px;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;

    &.approve {
      background: rgba(46, 204, 113, 0.9);
      color: white;

      &:hover {
        background: rgba(46, 204, 113, 1);
      }
    }

    &.hide {
      background: rgba(241, 196, 15, 0.9);
      color: white;

      &:hover {
        background: rgba(241, 196, 15, 1);
      }
    }

    &.delete {
      background: rgba(231, 76, 60, 0.9);
      color: white;

      &:hover {
        background: rgba(231, 76, 60, 1);
      }
    }
  }
}

.messages-container {
  margin-bottom: 30px;
}

.loading-state,
.empty-state {
  text-align: center;
  padding: 60px 20px;

  .loading-spinner {
    animation: spin 1s linear infinite;
    color: rgba(30, 30, 30, 0.6);
  }

  p {
    color: rgba(30, 30, 30, 0.7);
    font-size: 1.1rem;
    margin-top: 15px;
  }
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.message-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.message-card {
  background: rgba(255, 255, 255, 0.3);
  backdrop-filter: blur(15px);
  border-radius: 12px;
  padding: 20px;
  border: 2px solid rgba(255, 255, 255, 0.4);
  transition: all 0.3s ease;

  &.selected {
    border-color: rgba(102, 126, 234, 0.6);
    background: rgba(102, 126, 234, 0.1);
  }

  &.displayed {
    border-left: 4px solid rgba(46, 204, 113, 0.8);
  }

  .message-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 12px;

    @media (max-width: 768px) {
      flex-wrap: wrap;
    }
  }

  .checkbox-label {
    cursor: pointer;
    position: relative;

    input {
      position: absolute;
      opacity: 0;
      cursor: pointer;
    }

    .checkmark {
      width: 20px;
      height: 20px;
      background: rgba(255, 255, 255, 0.6);
      border: 2px solid rgba(255, 255, 255, 0.8);
      border-radius: 4px;
      display: inline-block;
      transition: all 0.3s ease;
    }

    input:checked ~ .checkmark {
      background: rgba(102, 126, 234, 0.9);
      border-color: rgba(102, 126, 234, 0.9);
    }
  }

  .message-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 4px;
  }

  .message-nickname {
    font-size: 1.1rem;
    font-weight: 600;
    color: rgba(30, 30, 30, 0.95);
  }

  .message-email {
    font-size: 0.85rem;
    color: rgba(30, 30, 30, 0.6);
  }

  .message-meta {
    display: flex;
    gap: 15px;
    align-items: center;
    font-size: 0.85rem;
    color: rgba(30, 30, 30, 0.7);

    @media (max-width: 768px) {
      width: 100%;
      justify-content: space-between;
    }
  }

  .message-status {
    padding: 4px 10px;
    background: rgba(241, 196, 15, 0.3);
    border-radius: 12px;
    color: rgba(30, 30, 30, 0.8);
    font-size: 0.8rem;
    font-weight: 600;

    &.active {
      background: rgba(46, 204, 113, 0.3);
      color: rgba(39, 174, 96, 0.9);
    }
  }

  .message-content {
    padding: 12px 15px;
    background: rgba(255, 255, 255, 0.4);
    border-radius: 8px;
    margin-bottom: 12px;
    color: rgba(30, 30, 30, 0.9);
    line-height: 1.6;
    white-space: pre-wrap;
    word-break: break-word;
  }

  .message-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;

    .action-btn {
      padding: 8px 16px;
      border: none;
      border-radius: 6px;
      font-size: 0.85rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;

      &.toggle {
        background: rgba(102, 126, 234, 0.8);
        color: white;

        &:hover {
          background: rgba(102, 126, 234, 1);
        }
      }

      &.delete {
        background: rgba(231, 76, 60, 0.8);
        color: white;

        &:hover {
          background: rgba(231, 76, 60, 1);
        }
      }
    }
  }
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 20px;
  padding: 20px;
  background: rgba(255, 255, 255, 0.3);
  border-radius: 12px;

  .page-btn {
    padding: 10px 20px;
    background: rgba(255, 255, 255, 0.6);
    border: 1px solid rgba(255, 255, 255, 0.4);
    border-radius: 8px;
    font-size: 0.95rem;
    cursor: pointer;
    transition: all 0.3s ease;

    &:hover:not(:disabled) {
      background: rgba(255, 255, 255, 0.9);
    }

    &:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }
  }

  .page-info {
    color: rgba(30, 30, 30, 0.8);
    font-size: 0.95rem;
  }
}

.action-toast {
  position: fixed;
  bottom: 30px;
  left: 50%;
  transform: translateX(-50%);
  padding: 12px 24px;
  border-radius: 8px;
  font-size: 0.95rem;
  font-weight: 600;
  z-index: 1000;
  animation: slideUp 0.3s ease;

  &.success {
    background: rgba(46, 204, 113, 0.9);
    color: white;
  }

  &.error {
    background: rgba(231, 76, 60, 0.9);
    color: white;
  }
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateX(-50%) translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateX(-50%) translateY(0);
  }
}

@media (max-width: 768px) {
  .admin-messages {
    padding: 20px 15px;
  }

  .stats-row {
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
  }

  .message-card {
    padding: 15px;
  }
}
</style>

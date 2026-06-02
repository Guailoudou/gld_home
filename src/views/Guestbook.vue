<script setup lang="ts">
import { ref, onMounted } from 'vue'
import HIcon from '@/components/HIcon.vue'

interface Message {
  id: number
  nickname: string
  email: string
  content: string
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
const submitting = ref(false)

// 分页
const pagination = ref<Pagination>({
  page: 1,
  limit: 10,
  total: 0,
  totalPages: 0
})

// 表单数据
const formData = ref({
  nickname: '',
  email: '',
  content: ''
})

const formErrors = ref<Record<string, string>>({})
const submitSuccess = ref(false)
const submitError = ref('')
const showForm = ref(false)

// 验证表单
function validateForm(): boolean {
  formErrors.value = {}
  
  if (!formData.value.nickname.trim()) {
    formErrors.value.nickname = '请填写昵称'
  } else if (formData.value.nickname.length > 50) {
    formErrors.value.nickname = '昵称不能超过 50 个字符'
  }
  
  // 邮箱可选，但填写后需验证格式
  if (formData.value.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.value.email)) {
    formErrors.value.email = '邮箱格式不正确'
  }
  
  if (!formData.value.content.trim()) {
    formErrors.value.content = '请填写留言内容'
  } else if (formData.value.content.length < 5) {
    formErrors.value.content = '留言内容至少需要 5 个字符'
  } else if (formData.value.content.length > 2000) {
    formErrors.value.content = '留言内容不能超过 2000 个字符'
  }
  
  return Object.keys(formErrors.value).length === 0
}

// 提交留言
async function submitMessage() {
  if (!validateForm()) {
    return
  }
  
  submitting.value = true
  submitSuccess.value = false
  submitError.value = ''
  
  try {
    const response = await fetch(API_BASE, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'submit',
        nickname: formData.value.nickname,
        email: formData.value.email,
        content: formData.value.content
      })
    })
    
    const result = await response.json()
    
    if (result.success) {
      submitSuccess.value = true
      formData.value = {
        nickname: '',
        email: '',
        content: ''
      }
      // 3 秒后清除成功提示
      setTimeout(() => {
        submitSuccess.value = false
      }, 5000)
    } else {
      submitError.value = result.error || '提交失败，请重试'
    }
  } catch (error) {
    submitError.value = '网络错误，请重试'
  } finally {
    submitting.value = false
  }
}

// 获取留言列表
async function fetchMessages(page = 1) {
  loading.value = true
  try {
    const response = await fetch(`${API_BASE}?page=${page}&limit=${pagination.value.limit}`)
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

// 格式化时间
function formatTime(time: string) {
  const date = new Date(time)
  const now = new Date()
  const diff = now.getTime() - date.getTime()
  
  // 小于 1 小时
  if (diff < 3600000) {
    const minutes = Math.floor(diff / 60000)
    return minutes < 1 ? '刚刚' : `${minutes} 分钟前`
  }
  
  // 小于 24 小时
  if (diff < 86400000) {
    const hours = Math.floor(diff / 3600000)
    return `${hours} 小时前`
  }
  
  // 超过 24 小时
  return date.toLocaleString('zh-CN', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// 获取昵称首字母头像颜色
function getAvatarColor(nickname: string) {
  const colors = [
    '#667eea', '#764ba2', '#f093fb', '#f5576c',
    '#11998e', '#38ef7d', '#4facfe', '#00f2fe',
    '#fa709a', '#fee140', '#a8edea', '#fed6e3'
  ]
  const index = nickname.charCodeAt(0) % colors.length
  return colors[index]
}

onMounted(() => {
  fetchMessages(1)
})
</script>

<template>
  <div class="guestbook-container">
    <div class="content-wrapper">
      <!-- 页面头部 -->
      <div class="page-header">
        <h1 class="page-title">
          <HIcon name="chat" :size="40" class="title-icon" />
          留言板
        </h1>
        <p class="page-description">留下你的想法和建议</p>
      </div>

      <!-- 发表留言按钮 -->
      <div class="form-toggle-wrapper">
        <button class="toggle-form-btn" @click="showForm = !showForm">
          <HIcon :name="showForm ? 'close' : 'edit'" :size="20" class="btn-icon" />
          {{ showForm ? '收起留言表单' : '发表留言' }}
        </button>
      </div>

      <!-- 提交表单 -->
      <transition name="slide-fade">
        <div v-show="showForm" class="message-form">
          <h2 class="form-title">
            <HIcon name="edit" :size="24" class="form-icon" />
            发表留言
          </h2>
        
        <div v-if="submitSuccess" class="success-alert">
          <HIcon name="check" :size="20" />
          <span>留言提交成功，请等待管理员审核</span>
        </div>
        
        <div v-if="submitError" class="error-alert">
          <HIcon name="warning" :size="20" />
          <span>{{ submitError }}</span>
        </div>
        
        <div class="form-group">
          <label for="nickname">昵称 *</label>
          <input
            id="nickname"
            v-model="formData.nickname"
            type="text"
            placeholder="请输入昵称"
            class="form-input"
            :class="{ error: formErrors.nickname }"
          />
          <span v-if="formErrors.nickname" class="error-message">{{ formErrors.nickname }}</span>
        </div>

        <div class="form-group">
          <label for="email">邮箱 <span class="optional-label">（可选）</span></label>
          <input
            id="email"
            v-model="formData.email"
            type="email"
            placeholder="请输入邮箱（选填，仅用于验证，不会公开）"
            class="form-input"
            :class="{ error: formErrors.email }"
          />
          <span v-if="formErrors.email" class="error-message">{{ formErrors.email }}</span>
        </div>

        <div class="form-group">
          <label for="content">留言内容 *</label>
          <textarea
            id="content"
            v-model="formData.content"
            rows="5"
            placeholder="写下你的留言..."
            class="form-textarea"
            :class="{ error: formErrors.content }"
          ></textarea>
          <div class="content-footer">
            <span v-if="formErrors.content" class="error-message">{{ formErrors.content }}</span>
            <span class="char-count">{{ formData.content.length }}/2000</span>
          </div>
        </div>

        <button class="submit-btn" :disabled="submitting" @click="submitMessage">
          <HIcon v-if="submitting" name="refresh" :size="18" class="submit-spinner" />
          {{ submitting ? '提交中...' : '提交留言' }}
        </button>
      </div>
      </transition>

      <!-- 留言列表 -->
      <div class="messages-list">
        <h2 class="list-title">
          <HIcon name="star" :size="24" class="list-icon" />
          最新留言
          <span class="message-count">({{ pagination.total }} 条)</span>
        </h2>
        
        <div v-if="loading" class="loading-state">
          <HIcon name="refresh" :size="40" class="loading-spinner" />
          <p>加载中...</p>
        </div>

        <div v-else-if="messages.length === 0" class="empty-state">
          <HIcon name="empty" :size="64" />
          <p>暂无留言，来做第一个留言的人吧！</p>
        </div>

        <div v-else class="message-cards">
          <div 
            v-for="msg in messages" 
            :key="msg.id"
            class="message-card"
          >
            <div class="card-header">
              <div class="author-avatar" :style="{ backgroundColor: getAvatarColor(msg.nickname) }">
                {{ msg.nickname.charAt(0).toUpperCase() }}
              </div>
              <div class="author-info">
                <div class="author-name">{{ msg.nickname }}</div>
                <div class="post-time">{{ formatTime(msg.created_at) }}</div>
              </div>
            </div>
            <div class="card-content">{{ msg.content }}</div>
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
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss">
.guestbook-container {
  min-height: 100vh;
  padding: 40px 20px;
}

.content-wrapper {
  max-width: 900px;
  margin: 0 auto;
}

.page-header {
  text-align: center;
  margin-bottom: 40px;

  .page-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: rgba(30, 30, 30, 0.95);
    margin: 0 0 15px 0;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;

    .title-icon {
      color: rgba(30, 30, 30, 0.9);
    }

    @media (max-width: 768px) {
      font-size: 2rem;
    }
  }

  .page-description {
    font-size: 1.1rem;
    color: rgba(30, 30, 30, 0.7);
    margin: 0;
  }
}

.form-toggle-wrapper {
  display: flex;
  justify-content: center;
  margin-bottom: 30px;

  .toggle-form-btn {
    padding: 14px 28px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);

    &:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 28px rgba(102, 126, 234, 0.4);
    }

    &:active {
      transform: translateY(0);
    }

    .btn-icon {
      color: white;
    }
  }
}

.message-form {
  background: rgba(255, 255, 255, 0.3);
  backdrop-filter: blur(15px);
  border-radius: 16px;
  padding: 30px;
  border: 1px solid rgba(255, 255, 255, 0.4);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
  margin-bottom: 40px;

  .form-title {
    font-size: 1.4rem;
    font-weight: 600;
    color: rgba(30, 30, 30, 0.95);
    margin: 0 0 20px 0;
    display: flex;
    align-items: center;
    gap: 10px;

    .form-icon {
      color: rgba(30, 30, 30, 0.8);
    }
  }

  .success-alert,
  .error-alert {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-size: 0.95rem;
  }

  .success-alert {
    background: rgba(46, 204, 113, 0.2);
    color: rgba(39, 174, 96, 0.9);
    border: 1px solid rgba(46, 204, 113, 0.3);
  }

  .error-alert {
    background: rgba(231, 76, 60, 0.2);
    color: rgba(231, 76, 60, 0.9);
    border: 1px solid rgba(231, 76, 60, 0.3);
  }

  .form-group {
    margin-bottom: 20px;

    label {
      display: block;
      font-size: 0.95rem;
      font-weight: 600;
      color: rgba(30, 30, 30, 0.9);
      margin-bottom: 8px;

      .optional-label {
        font-weight: normal;
        color: rgba(30, 30, 30, 0.5);
        font-size: 0.85rem;
      }
    }

    .form-input,
    .form-textarea {
      width: 100%;
      padding: 12px 16px;
      background: rgba(255, 255, 255, 0.6);
      border: 2px solid rgba(255, 255, 255, 0.5);
      border-radius: 8px;
      font-size: 1rem;
      color: #333;
      transition: all 0.3s ease;
      box-sizing: border-box;

      &:focus {
        outline: none;
        background: rgba(255, 255, 255, 0.9);
        border-color: rgba(102, 126, 234, 0.6);
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
      }

      &::placeholder {
        color: rgba(0, 0, 0, 0.4);
      }

      &.error {
        border-color: rgba(231, 76, 60, 0.6);
      }
    }

    .form-textarea {
      resize: vertical;
      min-height: 120px;
      font-family: inherit;
    }

    .content-footer {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-top: 6px;
    }

    .error-message {
      color: rgba(231, 76, 60, 0.9);
      font-size: 0.85rem;
      margin-top: 6px;
    }

    .char-count {
      font-size: 0.85rem;
      color: rgba(30, 30, 30, 0.5);
    }
  }

  .submit-btn {
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;

    &:hover:not(:disabled) {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
    }

    &:disabled {
      opacity: 0.7;
      cursor: not-allowed;
    }

    .submit-spinner {
      animation: spin 1s linear infinite;
    }
  }
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.slide-fade-enter-active {
  transition: all 0.3s ease-out;
}

.slide-fade-leave-active {
  transition: all 0.2s ease-in;
}

.slide-fade-enter-from {
  opacity: 0;
  transform: translateY(-20px);
}

.slide-fade-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

.messages-list {
  .list-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: rgba(30, 30, 30, 0.95);
    margin: 0 0 25px 0;
    padding-bottom: 15px;
    border-bottom: 2px solid rgba(255, 255, 255, 0.3);
    display: flex;
    align-items: center;
    gap: 10px;

    .list-icon {
      color: rgba(30, 30, 30, 0.8);
    }

    .message-count {
      font-size: 1rem;
      color: rgba(30, 30, 30, 0.6);
      font-weight: normal;
    }
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
}

.message-cards {
  display: flex;
  flex-direction: column;
  gap: 20px;
  margin-bottom: 30px;
}

.message-card {
  background: rgba(255, 255, 255, 0.3);
  backdrop-filter: blur(15px);
  border-radius: 12px;
  padding: 20px;
  border: 1px solid rgba(255, 255, 255, 0.4);
  transition: all 0.3s ease;

  &:hover {
    background: rgba(255, 255, 255, 0.4);
    transform: translateX(5px);
  }

  .card-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 15px;
  }

  .author-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    font-weight: 700;
    color: white;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  }

  .author-info {
    flex: 1;
  }

  .author-name {
    font-size: 1rem;
    font-weight: 600;
    color: rgba(30, 30, 30, 0.95);
  }

  .post-time {
    font-size: 0.85rem;
    color: rgba(30, 30, 30, 0.6);
    margin-top: 4px;
  }

  .card-content {
    padding: 12px 16px;
    background: rgba(255, 255, 255, 0.4);
    border-radius: 8px;
    color: rgba(30, 30, 30, 0.9);
    line-height: 1.6;
    white-space: pre-wrap;
    word-break: break-word;
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
      transform: translateY(-2px);
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

@media (max-width: 768px) {
  .guestbook-container {
    padding: 30px 15px;
  }

  .page-header .page-title {
    font-size: 2rem;
  }

  .message-form {
    padding: 20px;
  }

  .message-card {
    padding: 15px;

    .author-avatar {
      width: 40px;
      height: 40px;
      font-size: 1.1rem;
    }
  }
}
</style>

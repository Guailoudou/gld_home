<script setup lang="ts">
import { ref, onMounted } from 'vue'
import HIcon from '@/components/HIcon.vue'
import type { Message, Pagination, MessageFormData } from '@/models/message/types'

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
const formData = ref<MessageFormData>({
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
  } else if (formData.value.content.length > 500) {
    formErrors.value.content = '留言内容不能超过 500 个字符'
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
            placeholder="请输入邮箱（选填，如果你希望我联系你请务必填写，不会公开）"
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
            <span class="char-count">{{ formData.content.length }}/500</span>
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

<style src="@/styles/Guestbook.scss" scoped lang="scss"></style>

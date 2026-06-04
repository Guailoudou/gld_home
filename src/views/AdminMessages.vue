<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { RouterLink } from 'vue-router'
import HIcon from '@/components/HIcon.vue'
import type { Message, Pagination } from '@/models/message/types'

const API_BASE = '/api/messages.php'
const messages = ref<Message[]>([])
const loading = ref(false)

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

// 操作反馈
const actionMessage = ref('')
const actionType = ref<'success' | 'error' | ''>('')

// 计算属性
const displayedCount = computed(() => messages.value.filter(m => m.is_displayed === 1).length)
const pendingCount = computed(() => messages.value.filter(m => m.is_displayed === 0).length)

// 获取密码哈希
const getPasswordHash = (): string => {
  return localStorage.getItem('admin_password_hash') || ''
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
        password_hash: getPasswordHash(),
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
        password_hash: getPasswordHash()
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
        password_hash: getPasswordHash()
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
        password_hash: getPasswordHash()
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
        password_hash: getPasswordHash()
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
  fetchMessages(1)
})
</script>

<template>
  <div class="admin-messages">
    <div class="content-wrapper">
      <!-- 页面头部 -->
      <div class="page-header">
        <div class="header-content">
          <h1 class="page-title">
            <HIcon name="chat" :size="32" class="title-icon" />
            留言管理
          </h1>
          <p class="page-description">管理用户留言，审核并控制展示状态</p>
        </div>
        <div class="header-actions">
          <RouterLink to="/guestbook" class="back-link">
            <span>← 返回留言页</span>
          </RouterLink>
        </div>
      </div>

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

      <!-- 操作反馈 -->
      <div v-if="actionMessage" class="action-toast" :class="actionType">
        {{ actionMessage }}
      </div>
    </div>
  </div>
</template>

<style src="@/styles/AdminMessages.scss" scoped lang="scss"></style>

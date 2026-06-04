<script setup lang="ts">
import { onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import HIcon from '@/components/HIcon.vue'
import { useMessageCrud } from '@/composables/message/useMessageCrud'

const {
  messages,
  loading,
  pagination,
  searchNickname,
  filterDisplayed,
  filterDateFrom,
  filterDateTo,
  selectedMessages,
  actionMessage,
  actionType,
  displayedCount,
  pendingCount,
  fetchMessages,
  toggleDisplay,
  deleteMessage,
  batchToggle,
  batchDelete,
  toggleSelect
} = useMessageCrud()

function handleSearch() {
  fetchMessages(1)
}

function resetFilters() {
  searchNickname.value = ''
  filterDisplayed.value = null
  filterDateFrom.value = ''
  filterDateTo.value = ''
  fetchMessages(1)
}

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

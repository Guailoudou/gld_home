<script setup lang="ts">
import { onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { useDownloadCrud } from '@/composables/download/useDownloadCrud'
import { useToast } from '@/composables/common/useToast'
import { useClipboard } from '@/composables/common/useClipboard'

const {
  downloads,
  loading,
  formData,
  isEditing,
  showForm,
  draggedIndex,
  fetchDownloads,
  openAddForm,
  openEditForm,
  saveDownload,
  deleteDownload,
  cancelForm,
  onDragStart,
  onDragOver,
  onDragEnd
} = useDownloadCrud()

const { showMessage, messageText, messageType, showToast } = useToast()

const { copyToClipboard: clipboardCopy } = useClipboard()

const copyToClipboard = async (text: string, label: string) => {
  const success = await clipboardCopy(text, label)
  if (success) {
    showToast(`${label}已复制`, 'success')
  } else {
    showToast('复制失败', 'error')
  }
}

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

      <!-- 拖拽排序提示 -->
      <div v-if="downloads.length > 0" class="sort-hint">
        <span class="sort-hint-icon">🔀</span>
        <span>拖拽下载项调整展示顺序</span>
      </div>

      <!-- 数据列表（卡片模式支持拖拽） -->
      <div class="downloads-container">
        <div
          v-if="downloads.length > 0"
          v-for="(item, index) in downloads"
          :key="item.id"
          class="download-card"
          :class="{ dragging: draggedIndex === index }"
          draggable="true"
          @dragstart="onDragStart(index)"
          @dragover.prevent="onDragOver($event, index)"
          @dragend="onDragEnd"
        >
          <!-- 拖拽手柄 -->
          <div class="drag-handle">
            <span class="drag-icon">⋮⋮</span>
          </div>

          <!-- 序号 -->
          <div class="card-index">{{ index + 1 }}</div>

          <!-- 下载信息 -->
          <div class="card-info">
            <div class="card-name">{{ item.name }}</div>
            <div class="card-size-label">{{ item.size }}</div>
            <div class="card-urls">
              <span class="url-tag">原始: <a :href="item.url" target="_blank" class="url-link">{{ item.url }}</a></span>
              <span class="url-tag">访问: /download/{{ item.id }}</span>
            </div>
            <div class="card-badges">
              <span :class="['badge', item.is_default === '1' ? 'badge-success' : 'badge-secondary']">
                默认: {{ item.is_default === '1' ? '是' : '否' }}
              </span>
              <span :class="['badge', item.is_featured === '1' ? 'badge-warning' : 'badge-secondary']">
                推荐: {{ item.is_featured === '1' ? '是' : '否' }}
              </span>
              <span class="priority-badge">优先级: {{ item.priority || 0 }}</span>
            </div>
          </div>

          <!-- 操作按钮 -->
          <div class="card-actions">
            <button @click="copyToClipboard(item.id, 'ID')" class="btn-copy" title="复制 ID">📋</button>
            <button @click="copyToClipboard(item.url, '原始链接')" class="btn-copy" title="复制原始链接">🔗</button>
            <button @click="openEditForm(item)" class="btn-icon-only" title="编辑">✏️</button>
            <button @click="deleteDownload(item.id, item.name)" class="btn-icon-only" title="删除">🗑️</button>
          </div>
        </div>

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

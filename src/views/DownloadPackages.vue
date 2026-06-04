<script setup lang="ts">
import { RouterLink } from 'vue-router'
import { useDownloadPackage } from '@/composables/download/useDownloadPackage'

const {
  downloads,
  selectedDownloads,
  packages,
  loading,
  showMessage,
  messageText,
  messageType,
  formData,
  isEditing,
  showForm,
  toggleDownloadSelection,
  openCreatePackage,
  openEditPackage,
  savePackage,
  deletePackage,
  copyDownloadCode,
  cancelForm
} = useDownloadPackage()
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
            :class="['download-card', { selected: selectedDownloads.has(item.id), featured: item.is_featured === '1' }]"
            @click="toggleDownloadSelection(item.id)"
          >
            <div class="card-checkbox">
              <span v-if="selectedDownloads.has(item.id)" class="checkbox-checked">✅</span>
              <span v-else class="checkbox-unchecked">⬜</span>
            </div>
            <div class="card-info">
              <div class="card-name-row">
                <span v-if="item.is_featured === '1'" class="featured-badge">
                  ⭐ 推荐
                </span>
                <span class="card-name">{{ item.name }}</span>
              </div>
              <div class="card-size">{{ item.size }}</div>
              <div v-if="item.priority !== undefined && item.priority !== 0" class="card-priority">
                优先级: {{ item.priority }}
              </div>
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
                <td>
                <div class="resources-cell">
                  <span class="resources-count">{{ pkg.download_ids.length }} 个资源</span>
                  <div class="resources-preview">
                    <span v-for="id in pkg.download_ids" :key="id" class="resource-tag">
                      {{ downloads.find(d => d.id === id)?.name || id }}
                      <span v-if="downloads.find(d => d.id === id)?.is_featured === '1'" class="resource-badge">⭐</span>
                    </span>
                  </div>
                </div>
              </td>
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
                    :class="{ 'selected-featured': downloads.find(d => d.id === id)?.is_featured === '1' }"
                  >
                    <span v-if="downloads.find(d => d.id === id)?.is_featured === '1'" class="item-badge">⭐</span>
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

    </div>
  </div>
</template>

<style src="@/styles/DownloadPackages.scss" scoped lang="scss"></style>

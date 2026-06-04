<script setup lang="ts">
import { RouterLink } from 'vue-router'
import HIcon from '@/components/HIcon.vue'
import { usePortfolioAdmin } from '@/composables/portfolio/usePortfolioAdmin'

const {
  items,
  loading,
  actionMessage,
  actionType,
  showConfigModal,
  configForm,
  showItemModal,
  editingItem,
  itemForm,
  draggedIndex,
  openConfigEdit,
  saveConfig,
  openCreateItem,
  openEditItem,
  saveItem,
  deleteItem,
  toggleItem,
  onDragStart,
  onDragOver,
  onDragEnd
} = usePortfolioAdmin()
</script>

<template>
  <div class="admin-portfolio">
    <div class="content-wrapper">
      <!-- 页面头部 -->
      <div class="page-header">
        <div class="header-content">
          <h1 class="page-title">
            <HIcon name="atom" :size="32" class="title-icon" />
            作品管理
          </h1>
          <p class="page-description">管理作品展示内容，支持拖拽排序</p>
        </div>
        <div class="header-actions">
          <RouterLink to="/portfolio" class="back-link">
            <span>← 返回作品页</span>
          </RouterLink>
          <button class="config-btn" @click="openConfigEdit">
            <HIcon name="settings" :size="16" />
            页面配置
          </button>
          <button class="add-btn" @click="openCreateItem">
            <HIcon name="add" :size="18" />
            添加作品
          </button>
        </div>
      </div>

      <!-- 加载状态 -->
      <div v-if="loading" class="loading-state">
        <HIcon name="refresh" :size="40" class="loading-spinner" />
        <p>加载中...</p>
      </div>

      <!-- 空状态 -->
      <div v-else-if="items.length === 0" class="empty-state">
        <HIcon name="empty" :size="64" />
        <p>暂无作品数据</p>
        <button class="empty-btn" @click="openCreateItem">添加第一个作品</button>
      </div>

      <!-- 作品列表 -->
      <div v-else class="items-container">
        <div class="sort-hint">
          <HIcon name="drag" :size="16" />
          <span>拖拽作品卡片调整展示顺序</span>
        </div>

        <div
          v-for="(item, index) in items"
          :key="item.id"
          class="item-card"
          :class="{ disabled: item.is_active === '0', dragging: draggedIndex === index }"
          draggable="true"
          @dragstart="onDragStart(index)"
          @dragover.prevent="onDragOver($event, index)"
          @dragend="onDragEnd"
        >
          <!-- 拖拽手柄 -->
          <div class="drag-handle">
            <HIcon name="drag" :size="20" />
          </div>

          <!-- 序号 -->
          <div class="item-index">{{ index + 1 }}</div>

          <!-- 作品缩略图 -->
          <div class="item-thumb">
            <img v-if="item.image" :src="item.image" :alt="item.title" />
            <div v-else class="no-image">
              <HIcon name="image" :size="32" />
            </div>
          </div>

          <!-- 作品信息 -->
          <div class="item-info">
            <div class="item-title-row">
              <h3 class="item-title">{{ item.title }}</h3>
              <span class="item-badge" :class="{ active: item.is_active === '1' }">
                {{ item.is_active === '1' ? '已启用' : '已禁用' }}
              </span>
            </div>
            <p class="item-desc">{{ item.description }}</p>
            <div class="item-meta">
              <div class="item-tags">
                <span v-for="(tag, tagIndex) in item.tags" :key="tagIndex" class="tag">
                  {{ tag }}
                </span>
              </div>
              <div class="item-links">
                <a v-if="item.link" :href="item.link" target="_blank" class="link-tag demo">
                  演示
                </a>
                <a v-if="item.github" :href="item.github" target="_blank" class="link-tag github">
                  GitHub
                </a>
              </div>
            </div>
          </div>

          <!-- 操作按钮 -->
          <div class="item-actions">
            <button class="action-btn toggle" @click.stop="toggleItem(item.id)">
              {{ item.is_active === '1' ? '禁用' : '启用' }}
            </button>
            <button class="action-btn edit" @click.stop="openEditItem(item)">
              编辑
            </button>
            <button class="action-btn delete" @click.stop="deleteItem(item.id)">
              删除
            </button>
          </div>
        </div>
      </div>

      <!-- 页面配置弹窗 -->
      <div v-if="showConfigModal" class="modal-overlay" @click.self="showConfigModal = false">
        <div class="modal">
          <h3 class="modal-title">页面配置</h3>
          <div class="modal-form">
            <div class="form-group">
              <label>页面标题</label>
              <input v-model="configForm.title" type="text" placeholder="例如：作品展示" />
            </div>
            <div class="form-group">
              <label>页面描述</label>
              <textarea v-model="configForm.description" rows="3" placeholder="页面描述文字"></textarea>
            </div>
          </div>
          <div class="modal-actions">
            <button class="modal-btn cancel" @click="showConfigModal = false">取消</button>
            <button class="modal-btn confirm" @click="saveConfig">保存</button>
          </div>
        </div>
      </div>

      <!-- 作品编辑弹窗 -->
      <div v-if="showItemModal" class="modal-overlay" @click.self="showItemModal = false">
        <div class="modal modal-lg">
          <h3 class="modal-title">{{ editingItem ? '编辑作品' : '添加作品' }}</h3>
          <div class="modal-form">
            <div class="form-row">
              <div class="form-group">
                <label>作品标题 *</label>
                <input v-model="itemForm.title" type="text" placeholder="例如：OPL联机工具" />
              </div>
              <div class="form-group">
                <label>排序权重</label>
                <input v-model.number="itemForm.sort_order" type="number" />
              </div>
            </div>
            <div class="form-group">
              <label>作品描述 *</label>
              <textarea v-model="itemForm.description" rows="3" placeholder="作品简介"></textarea>
            </div>
            <div class="form-group">
              <label>作品图片 URL</label>
              <input v-model="itemForm.image" type="text" placeholder="https://example.com/image.jpg" />
            </div>
            <div class="form-group">
              <label>标签（用逗号分隔）</label>
              <input v-model="itemForm.tags" type="text" placeholder="例如：WPF, C#, Golang" />
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>演示链接</label>
                <input v-model="itemForm.link" type="text" placeholder="https://example.com" />
              </div>
              <div class="form-group">
                <label>GitHub 链接</label>
                <input v-model="itemForm.github" type="text" placeholder="https://github.com/..." />
              </div>
            </div>
            <div class="form-group checkbox">
              <label>
                <input v-model="itemForm.is_active" type="checkbox" />
                <span>启用该作品</span>
              </label>
            </div>
          </div>
          <div class="modal-actions">
            <button class="modal-btn cancel" @click="showItemModal = false">取消</button>
            <button class="modal-btn confirm" @click="saveItem">保存</button>
          </div>
        </div>
      </div>

      <!-- 操作反馈 -->
      <div v-if="actionMessage" class="action-toast" :class="actionType">
        {{ actionMessage }}
      </div>
    </div>
  </div>
</template>

<style src="@/styles/PortfolioAdmin.scss" scoped lang="scss"></style>

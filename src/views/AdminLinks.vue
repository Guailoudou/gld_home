<script setup lang="ts">
import { RouterLink } from 'vue-router'
import HIcon from '@/components/HIcon.vue'
import { useLinkCrud } from '@/composables/link/useLinkCrud'

const {
  sections,
  loading,
  actionMessage,
  actionType,
  showSectionModal,
  showLinkModal,
  editingSection,
  editingLink,
  sectionForm,
  linkForm,
  openCreateSection,
  openEditSection,
  saveSection,
  deleteSection,
  toggleSection,
  openCreateLink,
  openEditLink,
  saveLink,
  deleteLink,
  toggleLink
} = useLinkCrud()
</script>

<template>
  <div class="admin-links">
    <div class="content-wrapper">
      <!-- 页面头部 -->
      <div class="page-header">
        <div class="header-content">
          <h1 class="page-title">
            <HIcon name="link" :size="32" class="title-icon" />
            链接管理
          </h1>
          <p class="page-description">管理链接导航内容，包括分组和链接</p>
        </div>
        <div class="header-actions">
          <RouterLink to="/links" class="back-link">
            <span>← 返回链接页</span>
          </RouterLink>
          <button class="add-section-btn" @click="openCreateSection">
            <HIcon name="add" :size="18" />
            添加分组
          </button>
        </div>
      </div>

      <!-- 加载状态 -->
        <div v-if="loading" class="loading-state">
          <HIcon name="refresh" :size="40" class="loading-spinner" />
          <p>加载中...</p>
        </div>

        <!-- 空状态 -->
        <div v-else-if="sections.length === 0" class="empty-state">
          <HIcon name="empty" :size="64" />
          <p>暂无链接数据</p>
          <button class="empty-btn" @click="openCreateSection">添加第一个分组</button>
        </div>

        <!-- 分组列表 -->
        <div v-else class="sections-container">
          <div
            v-for="section in sections"
            :key="section.id"
            class="section-card"
            :class="{ disabled: section.is_active === '0' }"
          >
            <!-- 分组头部 -->
            <div class="section-header">
              <div class="section-title-row">
                <HIcon v-if="section.icon" :name="section.icon as any" :size="24" class="section-icon" />
                <h2 class="section-title">{{ section.title }}</h2>
                <span class="section-badge" :class="{ active: section.is_active === '1' }">
                  {{ section.is_active === '1' ? '已启用' : '已禁用' }}
                </span>
              </div>
              <div class="section-actions">
                <button class="action-btn small" @click="toggleSection(section.id)">
                  {{ section.is_active === '1' ? '禁用' : '启用' }}
                </button>
                <button class="action-btn small edit" @click="openEditSection(section)">
                  编辑
                </button>
                <button class="action-btn small add" @click="openCreateLink(section.id)">
                  添加链接
                </button>
                <button class="action-btn small delete" @click="deleteSection(section.id)">
                  删除
                </button>
              </div>
            </div>

            <!-- 链接列表 -->
            <div class="links-list">
              <div
                v-for="link in section.links"
                :key="link.id"
                class="link-item"
                :class="{ disabled: link.is_active === '0' }"
              >
                <div class="link-info">
                  <span class="link-name">{{ link.name }}</span>
                  <span class="link-url">{{ link.url }}</span>
                  <span v-if="link.description" class="link-desc">{{ link.description }}</span>
                </div>
                <div class="link-actions">
                  <span class="link-status" :class="{ active: link.is_active === '1' }">
                    {{ link.is_active === '1' ? '已启用' : '已禁用' }}
                  </span>
                  <button class="action-btn icon" @click="toggleLink(link.id)">
                    <HIcon name="visibility" :size="16" />
                  </button>
                  <button class="action-btn icon edit" @click="openEditLink(link)">
                    <HIcon name="edit" :size="16" />
                  </button>
                  <button class="action-btn icon delete" @click="deleteLink(link.id)">
                    <HIcon name="delete" :size="16" />
                  </button>
                </div>
              </div>

              <div v-if="section.links.length === 0" class="empty-links">
                <p>暂无链接，点击下方按钮添加</p>
              </div>
            </div>

            <button class="add-link-btn" @click="openCreateLink(section.id)">
              <HIcon name="add" :size="16" />
              添加链接
            </button>
          </div>
        </div>

      <!-- 分组编辑弹窗 -->
      <div v-if="showSectionModal" class="modal-overlay" @click.self="showSectionModal = false">
        <div class="modal">
          <h3 class="modal-title">{{ editingSection ? '编辑分组' : '添加分组' }}</h3>
          <div class="modal-form">
            <div class="form-group">
              <label>分组名称</label>
              <input v-model="sectionForm.name" type="text" placeholder="例如：常用工具" />
            </div>
            <div class="form-group">
              <label>图标名称</label>
              <input v-model="sectionForm.icon" type="text" placeholder="例如：link、settings" />
            </div>
            <div class="form-group">
              <label>排序权重</label>
              <input v-model.number="sectionForm.sort_order" type="number" />
            </div>
            <div class="form-group checkbox">
              <label>
                <input v-model="sectionForm.is_active" type="checkbox" />
                <span>启用该分组</span>
              </label>
            </div>
          </div>
          <div class="modal-actions">
            <button class="modal-btn cancel" @click="showSectionModal = false">取消</button>
            <button class="modal-btn confirm" @click="saveSection">保存</button>
          </div>
        </div>
      </div>

      <!-- 链接编辑弹窗 -->
      <div v-if="showLinkModal" class="modal-overlay" @click.self="showLinkModal = false">
        <div class="modal">
          <h3 class="modal-title">{{ editingLink ? '编辑链接' : '添加链接' }}</h3>
          <div class="modal-form">
            <div class="form-group">
              <label>链接名称</label>
              <input v-model="linkForm.name" type="text" placeholder="例如：GitHub" />
            </div>
            <div class="form-group">
              <label>链接地址</label>
              <input v-model="linkForm.url" type="text" placeholder="例如：https://github.com" />
            </div>
            <div class="form-group">
              <label>描述</label>
              <input v-model="linkForm.description" type="text" placeholder="可选" />
            </div>
            <div class="form-group">
              <label>排序权重</label>
              <input v-model.number="linkForm.sort_order" type="number" />
            </div>
            <div class="form-group checkbox">
              <label>
                <input v-model="linkForm.is_active" type="checkbox" />
                <span>启用该链接</span>
              </label>
            </div>
          </div>
          <div class="modal-actions">
            <button class="modal-btn cancel" @click="showLinkModal = false">取消</button>
            <button class="modal-btn confirm" @click="saveLink">保存</button>
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

<style src="@/styles/AdminLinks.scss" scoped lang="scss"></style>

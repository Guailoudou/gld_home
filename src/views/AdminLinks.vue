<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import HIcon from '@/components/HIcon.vue'

interface LinkItem {
  id: string
  section_id: string
  name: string
  url: string
  description: string
  is_active: string
  sort_order: number
  created_at: string
}

interface SectionItem {
  id: string
  title: string
  icon: string
  is_active: string
  sort_order: number
  links: LinkItem[]
}

const API_BASE = '/api/links.php'
const sections = ref<SectionItem[]>([])
const loading = ref(false)

const actionMessage = ref('')
const actionType = ref<'success' | 'error' | ''>('')

const showSectionModal = ref(false)
const showLinkModal = ref(false)
const editingSection = ref<SectionItem | null>(null)
const editingLink = ref<LinkItem | null>(null)
const linkSectionId = ref('')

const sectionForm = ref({
  name: '',
  icon: '',
  is_active: true,
  sort_order: 0
})

const linkForm = ref({
  name: '',
  url: '',
  description: '',
  is_active: true,
  sort_order: 0
})

// 获取密码哈希
const getPasswordHash = (): string => {
  return localStorage.getItem('admin_password_hash') || ''
}

async function fetchSections() {
  loading.value = true
  try {
    const response = await fetch(API_BASE, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'get_all',
        password_hash: getPasswordHash()
      })
    })

    const result = await response.json()

    if (result.success) {
      sections.value = result.data
    }
  } catch (error) {
    console.error('获取链接列表失败:', error)
  } finally {
    loading.value = false
  }
}

function openCreateSection() {
  editingSection.value = null
  sectionForm.value = {
    name: '',
    icon: '',
    is_active: true,
    sort_order: 0
  }
  showSectionModal.value = true
}

function openEditSection(section: SectionItem) {
  editingSection.value = section
  sectionForm.value = {
    name: section.title,
    icon: section.icon,
    is_active: section.is_active === '1',
    sort_order: section.sort_order
  }
  showSectionModal.value = true
}

async function saveSection() {
  if (!sectionForm.value.name) {
    showActionMessage('请填写分组名称', 'error')
    return
  }

  try {
    const action = editingSection.value ? 'update_section' : 'create_section'
    const payload: Record<string, any> = {
      action,
      name: sectionForm.value.name,
      icon: sectionForm.value.icon,
      is_active: sectionForm.value.is_active,
      sort_order: sectionForm.value.sort_order,
      password_hash: getPasswordHash()
    }

    if (editingSection.value) {
      payload.id = editingSection.value.id
    }

    const response = await fetch(API_BASE, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    })

    const result = await response.json()

    if (result.success) {
      showActionMessage(editingSection.value ? '分组更新成功' : '分组创建成功', 'success')
      showSectionModal.value = false
      fetchSections()
    } else {
      showActionMessage(result.error || '操作失败', 'error')
    }
  } catch (error) {
    showActionMessage('操作失败', 'error')
  }
}

async function deleteSection(id: string) {
  if (!confirm('确定要删除该分组及其所有链接吗？')) {
    return
  }

  try {
    const response = await fetch(API_BASE, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'delete_section',
        id,
        password_hash: getPasswordHash()
      })
    })

    const result = await response.json()

    if (result.success) {
      showActionMessage('分组已删除', 'success')
      fetchSections()
    } else {
      showActionMessage('删除失败', 'error')
    }
  } catch (error) {
    showActionMessage('操作失败', 'error')
  }
}

async function toggleSection(id: string) {
  try {
    const response = await fetch(API_BASE, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'toggle_section',
        id,
        password_hash: getPasswordHash()
      })
    })

    const result = await response.json()

    if (result.success) {
      showActionMessage('状态已更新', 'success')
      fetchSections()
    } else {
      showActionMessage('更新失败', 'error')
    }
  } catch (error) {
    showActionMessage('操作失败', 'error')
  }
}

function openCreateLink(sectionId: string) {
  editingLink.value = null
  linkSectionId.value = sectionId
  linkForm.value = {
    name: '',
    url: '',
    description: '',
    is_active: true,
    sort_order: 0
  }
  showLinkModal.value = true
}

function openEditLink(link: LinkItem) {
  editingLink.value = link
  linkSectionId.value = link.section_id
  linkForm.value = {
    name: link.name,
    url: link.url,
    description: link.description,
    is_active: link.is_active === '1',
    sort_order: link.sort_order
  }
  showLinkModal.value = true
}

async function saveLink() {
  if (!linkForm.value.name || !linkForm.value.url) {
    showActionMessage('请填写链接名称和地址', 'error')
    return
  }

  try {
    const action = editingLink.value ? 'update_link' : 'create_link'
    const payload: Record<string, any> = {
      action,
      section_id: linkSectionId.value,
      name: linkForm.value.name,
      url: linkForm.value.url,
      description: linkForm.value.description,
      is_active: linkForm.value.is_active,
      sort_order: linkForm.value.sort_order,
      password_hash: getPasswordHash()
    }

    if (editingLink.value) {
      payload.id = editingLink.value.id
    }

    const response = await fetch(API_BASE, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    })

    const result = await response.json()

    if (result.success) {
      showActionMessage(editingLink.value ? '链接更新成功' : '链接创建成功', 'success')
      showLinkModal.value = false
      fetchSections()
    } else {
      showActionMessage(result.error || '操作失败', 'error')
    }
  } catch (error) {
    showActionMessage('操作失败', 'error')
  }
}

async function deleteLink(id: string) {
  if (!confirm('确定要删除该链接吗？')) {
    return
  }

  try {
    const response = await fetch(API_BASE, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'delete_link',
        id,
        password_hash: getPasswordHash()
      })
    })

    const result = await response.json()

    if (result.success) {
      showActionMessage('链接已删除', 'success')
      fetchSections()
    } else {
      showActionMessage('删除失败', 'error')
    }
  } catch (error) {
    showActionMessage('操作失败', 'error')
  }
}

async function toggleLink(id: string) {
  try {
    const response = await fetch(API_BASE, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'toggle_link',
        id,
        password_hash: getPasswordHash()
      })
    })

    const result = await response.json()

    if (result.success) {
      showActionMessage('状态已更新', 'success')
      fetchSections()
    } else {
      showActionMessage('更新失败', 'error')
    }
  } catch (error) {
    showActionMessage('操作失败', 'error')
  }
}

function showActionMessage(msg: string, type: 'success' | 'error') {
  actionMessage.value = msg
  actionType.value = type
  setTimeout(() => {
    actionMessage.value = ''
    actionType.value = ''
  }, 3000)
}

onMounted(() => {
  fetchSections()
})
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

<style scoped lang="scss">
.admin-links {
  min-height: 100vh;
  padding: 40px 20px;
}

.content-wrapper {
  max-width: 1000px;
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

  .header-content {
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

  .header-actions {
    display: flex;
    gap: 12px;
    align-items: center;
  }

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

  .btn-logout {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    background: rgba(239, 68, 68, 0.9);
    border: none;
    border-radius: 12px;
    color: white;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;

    &:hover {
      background: rgba(239, 68, 68, 1);
      transform: translateY(-2px);
    }
  }

  .add-section-btn {
    padding: 10px 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 6px;

    &:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }
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

.loading-state,
.empty-state {
  text-align: center;
  padding: 60px 20px;
  background: rgba(255, 255, 255, 0.3);
  backdrop-filter: blur(15px);
  border-radius: 16px;
  border: 1px solid rgba(255, 255, 255, 0.4);

  .loading-spinner {
    animation: spin 1s linear infinite;
    color: rgba(30, 30, 30, 0.6);
  }

  p {
    color: rgba(30, 30, 30, 0.7);
    font-size: 1.1rem;
    margin-top: 15px;
  }

  .empty-btn {
    margin-top: 20px;
    padding: 12px 24px;
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
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.sections-container {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.section-card {
  background: rgba(255, 255, 255, 0.3);
  backdrop-filter: blur(15px);
  border-radius: 16px;
  padding: 20px;
  border: 1px solid rgba(255, 255, 255, 0.4);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);

  &.disabled {
    opacity: 0.6;
  }

  .section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.3);

    .section-title-row {
      display: flex;
      align-items: center;
      gap: 10px;

      .section-icon {
        color: rgba(30, 30, 30, 0.9);
      }

      .section-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: rgba(30, 30, 30, 0.95);
        margin: 0;
      }

      .section-badge {
        padding: 4px 10px;
        background: rgba(241, 196, 15, 0.3);
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
        color: rgba(30, 30, 30, 0.8);

        &.active {
          background: rgba(46, 204, 113, 0.3);
          color: rgba(39, 174, 96, 0.9);
        }
      }
    }

    .section-actions {
      display: flex;
      gap: 8px;
    }
  }

  .links-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
  }

  .link-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    background: rgba(255, 255, 255, 0.4);
    border-radius: 8px;
    transition: all 0.3s ease;

    &.disabled {
      opacity: 0.5;
    }

    &:hover {
      background: rgba(255, 255, 255, 0.6);
    }

    .link-info {
      display: flex;
      flex-direction: column;
      gap: 4px;
      flex: 1;

      .link-name {
        font-size: 1rem;
        font-weight: 600;
        color: rgba(30, 30, 30, 0.95);
      }

      .link-url {
        font-size: 0.8rem;
        color: rgba(102, 126, 234, 0.9);
        word-break: break-all;
      }

      .link-desc {
        font-size: 0.85rem;
        color: rgba(30, 30, 30, 0.7);
      }
    }

    .link-actions {
      display: flex;
      align-items: center;
      gap: 10px;

      .link-status {
        padding: 4px 8px;
        background: rgba(241, 196, 15, 0.3);
        border-radius: 10px;
        font-size: 0.7rem;
        font-weight: 600;
        color: rgba(30, 30, 30, 0.8);

        &.active {
          background: rgba(46, 204, 113, 0.3);
          color: rgba(39, 174, 96, 0.9);
        }
      }
    }
  }

  .empty-links {
    text-align: center;
    padding: 20px;
    color: rgba(30, 30, 30, 0.6);
    font-size: 0.9rem;
  }

  .add-link-btn {
    width: 100%;
    padding: 10px;
    margin-top: 15px;
    background: rgba(255, 255, 255, 0.4);
    border: 2px dashed rgba(255, 255, 255, 0.5);
    border-radius: 8px;
    font-size: 0.9rem;
    font-weight: 600;
    color: rgba(30, 30, 30, 0.8);
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;

    &:hover {
      background: rgba(255, 255, 255, 0.6);
      border-color: rgba(102, 126, 234, 0.5);
    }
  }
}

.action-btn {
  padding: 6px 12px;
  background: rgba(102, 126, 234, 0.8);
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;

  &.small {
    padding: 6px 10px;
    font-size: 0.8rem;
  }

  &.edit {
    background: rgba(52, 152, 219, 0.8);
  }

  &.add {
    background: rgba(46, 204, 113, 0.8);
  }

  &.delete {
    background: rgba(231, 76, 60, 0.8);
  }

  &.icon {
    padding: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  &:hover {
    transform: translateY(-1px);
  }
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}

.modal {
  background: white;
  border-radius: 16px;
  padding: 30px;
  max-width: 500px;
  width: 100%;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);

  .modal-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: rgba(30, 30, 30, 0.95);
    margin: 0 0 20px 0;
  }

  .modal-form {
    .form-group {
      margin-bottom: 15px;

      label {
        display: block;
        font-size: 0.9rem;
        font-weight: 600;
        color: rgba(30, 30, 30, 0.8);
        margin-bottom: 6px;
      }

      input[type="text"],
      input[type="number"] {
        width: 100%;
        padding: 10px 14px;
        background: rgba(240, 240, 240, 0.6);
        border: 1px solid rgba(200, 200, 200, 0.5);
        border-radius: 8px;
        font-size: 0.95rem;
        box-sizing: border-box;

        &:focus {
          outline: none;
          border-color: rgba(102, 126, 234, 0.6);
          background: white;
        }
      }

      &.checkbox {
        label {
          display: flex;
          align-items: center;
          gap: 8px;
          cursor: pointer;

          input[type="checkbox"] {
            width: 18px;
            height: 18px;
          }
        }
      }
    }
  }

  .modal-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 20px;

    .modal-btn {
      padding: 10px 24px;
      border: none;
      border-radius: 8px;
      font-size: 0.95rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;

      &.cancel {
        background: rgba(240, 240, 240, 0.8);
        color: rgba(30, 30, 30, 0.8);

        &:hover {
          background: rgba(220, 220, 220, 0.8);
        }
      }

      &.confirm {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;

        &:hover {
          transform: translateY(-2px);
          box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
      }
    }
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
  .admin-links {
    padding: 20px 15px;
  }

  .page-header {
    flex-direction: column;
    gap: 16px;
    align-items: flex-start;

    .header-content {
      .page-title {
        font-size: 1.5rem;
      }
    }

    .header-actions {
      align-self: flex-end;
      flex-wrap: wrap;
      justify-content: flex-end;
    }
  }

  .section-header {
    flex-direction: column;
    gap: 10px;
    align-items: flex-start;

    .section-actions {
      width: 100%;
      flex-wrap: wrap;
    }
  }

  .link-item {
    flex-direction: column;
    gap: 10px;
    align-items: flex-start;

    .link-actions {
      width: 100%;
      justify-content: flex-end;
    }
  }
}
</style>

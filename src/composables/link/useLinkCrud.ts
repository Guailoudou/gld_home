import { ref, onMounted } from 'vue'
import { getPasswordHash } from '@/composables/common/useAuth'
import { useActionMessage } from '@/composables/common/useActionMessage'
import type { LinkItem, SectionItem, SectionFormData, LinkFormData } from '@/models/link/types'

const API_BASE = '/api/links.php'

export function useLinkCrud() {
  const sections = ref<SectionItem[]>([])
  const loading = ref(false)

  const { actionMessage, actionType, showActionMessage } = useActionMessage()

  const showSectionModal = ref(false)
  const showLinkModal = ref(false)
  const editingSection = ref<SectionItem | null>(null)
  const editingLink = ref<LinkItem | null>(null)
  const linkSectionId = ref('')

  const sectionForm = ref<SectionFormData>({
    name: '',
    icon: '',
    is_active: true,
    sort_order: 0
  })

  const linkForm = ref<LinkFormData>({
    name: '',
    url: '',
    description: '',
    is_active: true,
    sort_order: 0
  })

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
      sort_order: section.sort_order ?? 0
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

  onMounted(() => {
    fetchSections()
  })

  return {
    sections,
    loading,
    actionMessage,
    actionType,
    showSectionModal,
    showLinkModal,
    editingSection,
    editingLink,
    linkSectionId,
    sectionForm,
    linkForm,
    showActionMessage,
    fetchSections,
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
  }
}

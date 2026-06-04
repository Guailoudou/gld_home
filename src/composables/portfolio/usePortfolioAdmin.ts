import { ref, onMounted } from 'vue'
import type { PortfolioItem, PortfolioConfig, ItemFormData } from '@/models/portfolio/types'

const API_BASE = '/api/portfolio.php'

export function usePortfolioAdmin() {
  const config = ref<PortfolioConfig>({
    title: '作品展示',
    description: '这里展示我的个人项目和作品'
  })

  const items = ref<PortfolioItem[]>([])
  const loading = ref(false)
  const actionMessage = ref('')
  const actionType = ref<'success' | 'error' | ''>('')

  // 页面配置编辑
  const showConfigModal = ref(false)
  const configForm = ref({
    title: '',
    description: ''
  })

  // 作品编辑弹窗
  const showItemModal = ref(false)
  const editingItem = ref<PortfolioItem | null>(null)
  const itemForm = ref<ItemFormData>({
    title: '',
    description: '',
    image: '',
    tags: '',
    link: '',
    github: '',
    is_active: true,
    sort_order: 0
  })

  // 拖拽排序
  const draggedIndex = ref<number | null>(null)

  const getPasswordHash = (): string => {
    return localStorage.getItem('admin_password_hash') || ''
  }

  async function fetchData() {
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
        if (result.data.config) {
          config.value = result.data.config
        }
        items.value = result.data.items || []
      }
    } catch (error) {
      console.error('获取作品列表失败:', error)
    } finally {
      loading.value = false
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

  // ========== 页面配置 ==========

  function openConfigEdit() {
    configForm.value = {
      title: config.value.title,
      description: config.value.description
    }
    showConfigModal.value = true
  }

  async function saveConfig() {
    if (!configForm.value.title) {
      showActionMessage('请填写页面标题', 'error')
      return
    }

    try {
      const response = await fetch(API_BASE, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          action: 'update_config',
          title: configForm.value.title,
          description: configForm.value.description,
          password_hash: getPasswordHash()
        })
      })

      const result = await response.json()

      if (result.success) {
        config.value.title = configForm.value.title
        config.value.description = configForm.value.description
        showActionMessage('配置更新成功', 'success')
        showConfigModal.value = false
      } else {
        showActionMessage(result.error || '更新失败', 'error')
      }
    } catch (error) {
      showActionMessage('操作失败', 'error')
    }
  }

  // ========== 作品 CRUD ==========

  function openCreateItem() {
    editingItem.value = null
    itemForm.value = {
      title: '',
      description: '',
      image: '',
      tags: '',
      link: '',
      github: '',
      is_active: true,
      sort_order: items.value.length
    }
    showItemModal.value = true
  }

  function openEditItem(item: PortfolioItem) {
    editingItem.value = item
    itemForm.value = {
      title: item.title,
      description: item.description,
      image: item.image || '',
      tags: Array.isArray(item.tags) ? item.tags.join(', ') : '',
      link: item.link || '',
      github: item.github || '',
      is_active: item.is_active === '1',
      sort_order: item.sort_order
    }
    showItemModal.value = true
  }

  async function saveItem() {
    if (!itemForm.value.title || !itemForm.value.description) {
      showActionMessage('请填写作品标题和描述', 'error')
      return
    }

    const tagsArray = itemForm.value.tags
      .split(',')
      .map(t => t.trim())
      .filter(t => t.length > 0)

    try {
      const action = editingItem.value ? 'update_item' : 'create_item'
      const payload: Record<string, any> = {
        action,
        title: itemForm.value.title,
        description: itemForm.value.description,
        image: itemForm.value.image,
        tags: tagsArray,
        link: itemForm.value.link,
        github: itemForm.value.github,
        is_active: itemForm.value.is_active,
        sort_order: itemForm.value.sort_order,
        password_hash: getPasswordHash()
      }

      if (editingItem.value) {
        payload.id = editingItem.value.id
      }

      const response = await fetch(API_BASE, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      })

      const result = await response.json()

      if (result.success) {
        showActionMessage(editingItem.value ? '作品更新成功' : '作品创建成功', 'success')
        showItemModal.value = false
        fetchData()
      } else {
        showActionMessage(result.error || '操作失败', 'error')
      }
    } catch (error) {
      showActionMessage('操作失败', 'error')
    }
  }

  async function deleteItem(id: string) {
    if (!confirm('确定要删除该作品吗？')) {
      return
    }

    try {
      const response = await fetch(API_BASE, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          action: 'delete_item',
          id,
          password_hash: getPasswordHash()
        })
      })

      const result = await response.json()

      if (result.success) {
        showActionMessage('作品已删除', 'success')
        fetchData()
      } else {
        showActionMessage('删除失败', 'error')
      }
    } catch (error) {
      showActionMessage('操作失败', 'error')
    }
  }

  async function toggleItem(id: string) {
    try {
      const response = await fetch(API_BASE, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          action: 'toggle_item',
          id,
          password_hash: getPasswordHash()
        })
      })

      const result = await response.json()

      if (result.success) {
        showActionMessage('状态已更新', 'success')
        fetchData()
      } else {
        showActionMessage('更新失败', 'error')
      }
    } catch (error) {
      showActionMessage('操作失败', 'error')
    }
  }

  // ========== 拖拽排序 ==========

  function onDragStart(index: number) {
    draggedIndex.value = index
  }

  function onDragOver(e: DragEvent, index: number) {
    e.preventDefault()
    if (draggedIndex.value === null || draggedIndex.value === index) return

    const draggedItem = items.value[draggedIndex.value]
    const targetItem = items.value[index]

    if (!draggedItem || !targetItem) return

    // 交换 sort_order
    const tempSort = draggedItem.sort_order
    draggedItem.sort_order = targetItem.sort_order
    targetItem.sort_order = tempSort

    // 交换数组位置
    items.value[draggedIndex.value] = targetItem
    items.value[index] = draggedItem

    draggedIndex.value = index
  }

  function onDragEnd() {
    if (draggedIndex.value === null) return
    draggedIndex.value = null
    saveSortOrder()
  }

  async function saveSortOrder() {
    const sortItems = items.value.map((item, index) => ({
      id: item.id,
      sort_order: index
    }))

    try {
      const response = await fetch(API_BASE, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          action: 'update_sort',
          items: sortItems,
          password_hash: getPasswordHash()
        })
      })

      const result = await response.json()

      if (result.success) {
        showActionMessage('排序已保存', 'success')
        fetchData()
      }
    } catch (error) {
      console.error('保存排序失败:', error)
    }
  }

  onMounted(() => {
    fetchData()
  })

  return {
    API_BASE,
    config,
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
    fetchData,
    showActionMessage,
    openConfigEdit,
    saveConfig,
    openCreateItem,
    openEditItem,
    saveItem,
    deleteItem,
    toggleItem,
    onDragStart,
    onDragOver,
    onDragEnd,
    saveSortOrder
  }
}

import { ref } from 'vue'
import { getPasswordHash } from '@/composables/common/useAuth'
import { useActionMessage } from '@/composables/common/useActionMessage'
import type { DownloadItem, DownloadFormData } from '@/models/download/types'

const API_BASE = '/api/downloads.php'

/**
 * 下载管理 CRUD composable
 */
export function useDownloadCrud() {
  const downloads = ref<DownloadItem[]>([])
  const loading = ref(false)
  const { actionMessage, actionType, showActionMessage } = useActionMessage()

  // 表单数据
  const formData = ref<DownloadFormData>({
    id: '',
    name: '',
    size: '',
    url: '',
    is_default: false,
    is_featured: false,
    priority: 0,
    password: ''
  })

  const isEditing = ref(false)
  const showForm = ref(false)

  // 获取下载列表
  async function fetchDownloads() {
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
        downloads.value = result.data
      } else if (result.error === '密码错误') {
        showActionMessage('密码错误，请重新登录', 'error')
      }
    } catch (error) {
      console.error('获取下载列表失败:', error)
      showActionMessage('网络错误', 'error')
    } finally {
      loading.value = false
    }
  }

  // 打开新增表单
  function openAddForm() {
    formData.value = {
      id: '',
      name: '',
      size: '',
      url: '',
      is_default: false,
      is_featured: false,
      priority: 0,
      password: ''
    }
    isEditing.value = false
    showForm.value = true
  }

  // 打开编辑表单
  function openEditForm(item: DownloadItem) {
    formData.value = {
      id: item.id,
      name: item.name,
      size: item.size,
      url: item.url,
      is_default: item.is_default === '1',
      is_featured: item.is_featured === '1',
      priority: item.priority || 0,
      password: ''
    }
    isEditing.value = true
    showForm.value = true
  }

  // 保存数据（新增或编辑）
  async function saveDownload() {
    if (!formData.value.name || !formData.value.size || !formData.value.url) {
      showActionMessage('请填写所有必填字段', 'error')
      return
    }

    loading.value = true
    try {
      const action = isEditing.value ? 'update' : 'create'
      const requestData = {
        action,
        password_hash: getPasswordHash(),
        ...formData.value
      }
      const response = await fetch(API_BASE, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(requestData)
      })

      const result = await response.json()

      if (result.success) {
        showActionMessage(isEditing.value ? '更新成功' : '创建成功', 'success')
        showForm.value = false
        fetchDownloads()
      } else if (result.error === '密码错误') {
        showActionMessage('密码错误，请重新登录', 'error')
      } else {
        showActionMessage(result.error || '操作失败', 'error')
      }
    } catch (error) {
      console.error('保存失败:', error)
      showActionMessage('网络错误', 'error')
    } finally {
      loading.value = false
    }
  }

  // 删除下载项
  async function deleteDownload(id: string, name: string) {
    if (!confirm(`确定要删除"${name}"吗？`)) {
      return
    }

    loading.value = true
    try {
      const response = await fetch(API_BASE, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          action: 'delete',
          password_hash: getPasswordHash(),
          id
        })
      })

      const result = await response.json()

      if (result.success) {
        showActionMessage('删除成功', 'success')
        fetchDownloads()
      } else if (result.error === '密码错误') {
        showActionMessage('密码错误，请重新登录', 'error')
      } else {
        showActionMessage(result.error || '删除失败', 'error')
      }
    } catch (error) {
      console.error('删除失败:', error)
      showActionMessage('网络错误', 'error')
    } finally {
      loading.value = false
    }
  }

  // 取消表单
  function cancelForm() {
    showForm.value = false
  }

  // 拖拽排序
  const draggedIndex = ref<number | null>(null)

  function onDragStart(index: number) {
    draggedIndex.value = index
  }

  function onDragOver(e: DragEvent, index: number) {
    e.preventDefault()
    if (draggedIndex.value === null || draggedIndex.value === index) return

    const draggedItem = downloads.value[draggedIndex.value]
    const targetItem = downloads.value[index]

    if (!draggedItem || !targetItem) return

    // 交换优先级
    const tempPriority = draggedItem.priority
    draggedItem.priority = targetItem.priority
    targetItem.priority = tempPriority

    // 交换数组位置
    downloads.value[draggedIndex.value] = targetItem
    downloads.value[index] = draggedItem

    draggedIndex.value = index
  }

  function onDragEnd() {
    if (draggedIndex.value === null) return
    draggedIndex.value = null
    saveSortOrder()
  }

  async function saveSortOrder() {
    const passwordHash = getPasswordHash()
    try {
      const response = await fetch(API_BASE, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          action: 'update_priority',
          items: downloads.value.map((item, index) => ({
            id: item.id,
            priority: index
          })),
          password_hash: passwordHash
        })
      })

      const result = await response.json()

      if (result.success) {
        showActionMessage('排序已保存', 'success')
        fetchDownloads()
      }
    } catch (error) {
      console.error('保存排序失败:', error)
    }
  }

  return {
    downloads,
    loading,
    formData,
    isEditing,
    showForm,
    draggedIndex,
    actionMessage,
    actionType,
    fetchDownloads,
    openAddForm,
    openEditForm,
    saveDownload,
    deleteDownload,
    cancelForm,
    onDragStart,
    onDragOver,
    onDragEnd,
    saveSortOrder
  }
}

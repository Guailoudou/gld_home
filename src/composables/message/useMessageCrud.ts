import { ref, computed } from 'vue'
import { getPasswordHash } from '@/composables/common/useAuth'
import { useActionMessage } from '@/composables/common/useActionMessage'
import type { Message, Pagination } from '@/models/message/types'

const API_BASE = '/api/messages.php'

/**
 * 留言管理 CRUD composable
 */
export function useMessageCrud() {
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

  const { actionMessage, actionType, showActionMessage } = useActionMessage()

  // 计算属性
  const displayedCount = computed(() => messages.value.filter(m => m.is_displayed === 1).length)
  const pendingCount = computed(() => messages.value.filter(m => m.is_displayed === 0).length)

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
    } catch {
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
    } catch {
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
    } catch {
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
    } catch {
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

  return {
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
  }
}

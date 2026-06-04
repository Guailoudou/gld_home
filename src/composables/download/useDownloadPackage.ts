import { ref, onMounted } from 'vue'
import type { DownloadItem, DownloadPackage, PackageFormData } from '@/models/download/types'
import { getPasswordHash } from '@/composables/common/useAuth'
import { useToast } from '@/composables/common/useToast'
import { useClipboard } from '@/composables/common/useClipboard'

const API_BASE = '/api/downloads.php'

export function useDownloadPackage() {
  // 下载列表
  const downloads = ref<DownloadItem[]>([])
  const selectedDownloads = ref<Set<string>>(new Set())

  // 下载包列表
  const packages = ref<DownloadPackage[]>([])
  const loading = ref(false)

  // Toast 消息提示
  const { showMessage, messageText, messageType, showToast } = useToast()

  // 剪贴板操作
  const { copyToClipboard } = useClipboard()

  // 表单数据
  const formData = ref<PackageFormData>({
    id: '',
    code: '',
    name: '',
    description: '',
    download_ids: [] as string[],
    is_active: false
  })

  const isEditing = ref(false)
  const showForm = ref(false)

  // 获取所有数据
  const fetchAll = async () => {
    loading.value = true
    try {
      const passwordHash = getPasswordHash()
      const response = await fetch(API_BASE, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'get_all', password_hash: passwordHash })
      })
      const result = await response.json()

      if (result.success) {
        // 按优先级排序
        downloads.value = result.data.sort((a: DownloadItem, b: DownloadItem) => {
          const priorityA = a.priority || 0
          const priorityB = b.priority || 0
          if (priorityA !== priorityB) {
            return priorityA - priorityB
          }
          // 优先级相同时按创建时间倒序
          return 0
        })
        fetchPackages()
      }
    } catch (error) {
      console.error('获取数据失败:', error)
      showToast('网络错误', 'error')
    } finally {
      loading.value = false
    }
  }

  // 获取下载包列表
  const fetchPackages = async () => {
    try {
      const passwordHash = getPasswordHash()
      const response = await fetch(API_BASE, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'get_packages', password_hash: passwordHash })
      })
      const result = await response.json()

      if (result.success) {
        // 解析每个下载包的 download_ids
        packages.value = result.data.map((pkg: any) => {
          let downloadIds: string[] = []
          if (typeof pkg.download_ids === 'string') {
            try {
              downloadIds = JSON.parse(pkg.download_ids)
            } catch (e) {
              downloadIds = []
            }
          } else if (Array.isArray(pkg.download_ids)) {
            downloadIds = pkg.download_ids
          }
          return {
            ...pkg,
            download_ids: downloadIds
          }
        })
      }
    } catch (error) {
      console.error('获取下载包失败:', error)
    }
  }

  // 切换选择下载项
  const toggleDownloadSelection = (id: string) => {
    if (selectedDownloads.value.has(id)) {
      selectedDownloads.value.delete(id)
    } else {
      selectedDownloads.value.add(id)
    }
  }

  // 打开创建下载包表单
  const openCreatePackage = () => {
    if (selectedDownloads.value.size === 0) {
      showToast('请至少选择一个下载项', 'error')
      return
    }

    formData.value = {
      id: '',
      code: '',
      name: '',
      description: '',
      download_ids: Array.from(selectedDownloads.value),
      is_active: true
    }
    isEditing.value = false
    showForm.value = true
  }

  // 打开编辑下载包表单
  const openEditPackage = (pkg: DownloadPackage) => {
    // 解析 download_ids，如果是字符串则解析 JSON
    let downloadIds: string[] = []
    if (typeof pkg.download_ids === 'string') {
      try {
        downloadIds = JSON.parse(pkg.download_ids)
      } catch (e) {
        downloadIds = pkg.download_ids as any
      }
    } else {
      downloadIds = pkg.download_ids
    }

    formData.value = {
      id: pkg.id,
      code: pkg.code,
      name: pkg.name,
      description: pkg.description,
      download_ids: downloadIds,
      is_active: pkg.is_active === '1'
    }
    isEditing.value = true
    showForm.value = true
  }

  // 保存下载包
  const savePackage = async () => {
    if (!formData.value.name || formData.value.download_ids.length === 0) {
      showToast('请填写名称并选择至少一个下载项', 'error')
      return
    }

    loading.value = true
    try {
      const passwordHash = getPasswordHash()
      const requestData = {
        action: isEditing.value ? 'update_package' : 'create_package',
        ...formData.value,
        is_active: formData.value.is_active,
        password_hash: passwordHash
      }

      const response = await fetch(API_BASE, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(requestData)
      })

      const result = await response.json()

      if (result.success) {
        showToast(isEditing.value ? '更新成功' : '创建成功', 'success')
        showForm.value = false
        fetchPackages()
      } else {
        showToast(result.error || '操作失败', 'error')
      }
    } catch (error) {
      console.error('保存失败:', error)
      showToast('网络错误', 'error')
    } finally {
      loading.value = false
    }
  }

  // 删除下载包
  const deletePackage = async (id: string, name: string) => {
    if (!confirm(`确定要删除"${name}"吗？`)) {
      return
    }

    loading.value = true
    try {
      const passwordHash = getPasswordHash()
      const response = await fetch(API_BASE, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'delete_package', id, password_hash: passwordHash })
      })

      const result = await response.json()

      if (result.success) {
        showToast('删除成功', 'success')
        fetchPackages()
      } else {
        showToast(result.error || '删除失败', 'error')
      }
    } catch (error) {
      console.error('删除失败:', error)
      showToast('网络错误', 'error')
    } finally {
      loading.value = false
    }
  }

  // 复制下载码
  const copyDownloadCode = async (code: string) => {
    const success = await copyToClipboard(code, '下载码')
    if (success) {
      showToast('下载码已复制', 'success')
    } else {
      showToast('复制失败', 'error')
    }
  }

  // 取消表单
  const cancelForm = () => {
    showForm.value = false
  }

  onMounted(() => {
    fetchAll()
  })

  return {
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
    fetchAll,
    fetchPackages,
    toggleDownloadSelection,
    openCreatePackage,
    openEditPackage,
    savePackage,
    deletePackage,
    copyDownloadCode,
    cancelForm
  }
}

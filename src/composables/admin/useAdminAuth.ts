import { ref, onMounted } from 'vue'
import { computePasswordHash } from '@/composables/common/useAuth'
import { useActionMessage } from '@/composables/common/useActionMessage'
import type { AuthState } from '@/models/admin/types'

/**
 * 管理后台登录验证 composable
 */
export function useAdminAuth() {
  const authState = ref<AuthState>({
    adminPassword: '',
    isAuthenticated: false,
    showPasswordModal: false,
    passwordInput: '',
    passwordError: '',
    passwordHash: '',
    loading: false,
    actionMessage: '',
    actionType: ''
  })

  const { actionMessage, actionType, showActionMessage } = useActionMessage()

  // 验证密码
  async function authenticate() {
    if (!authState.value.passwordInput) {
      showActionMessage('请输入密码', 'error')
      return
    }

    authState.value.passwordError = ''
    authState.value.loading = true
    const hash = await computePasswordHash(authState.value.passwordInput)

    try {
      // 使用 downloads API 验证密码
      const response = await fetch('/api/downloads.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          action: 'get_all',
          password_hash: hash
        })
      })

      const result = await response.json()

      if (result.success) {
        authState.value.isAuthenticated = true
        authState.value.adminPassword = authState.value.passwordInput
        authState.value.passwordHash = hash
        localStorage.setItem('admin_password', authState.value.adminPassword)
        localStorage.setItem('admin_password_hash', hash)
        authState.value.showPasswordModal = false
        showActionMessage('验证成功', 'success')
      } else if (result.error === '密码错误') {
        authState.value.passwordError = '密码错误'
        localStorage.removeItem('admin_password')
        localStorage.removeItem('admin_password_hash')
      } else {
        authState.value.passwordError = '验证失败'
      }
    } catch {
      authState.value.passwordError = '验证失败，请重试'
    } finally {
      authState.value.loading = false
    }
  }

  // 退出登录
  function logout() {
    localStorage.removeItem('admin_password')
    localStorage.removeItem('admin_password_hash')
    authState.value.adminPassword = ''
    authState.value.isAuthenticated = false
    authState.value.showPasswordModal = true
    authState.value.passwordInput = ''
    showActionMessage('已退出登录', 'success')
  }

  // 初始化：检查本地存储的登录状态
  function initAuth() {
    const savedPassword = localStorage.getItem('admin_password')
    const savedHash = localStorage.getItem('admin_password_hash')
    if (savedPassword && savedHash) {
      authState.value.adminPassword = savedPassword
      authState.value.passwordHash = savedHash
      authState.value.isAuthenticated = true
      authState.value.showPasswordModal = false
    } else {
      authState.value.showPasswordModal = true
    }
  }

  onMounted(initAuth)

  return {
    authState,
    actionMessage,
    actionType,
    authenticate,
    logout
  }
}

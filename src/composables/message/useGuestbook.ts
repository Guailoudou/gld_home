import { ref, onMounted } from 'vue'
import type { Message, Pagination, MessageFormData } from '@/models/message/types'

const API_BASE = '/api/messages.php'
const CAPTCHA_IMAGE_API = '/api/captcha_image.php'

export function useGuestbook() {
  const messages = ref<Message[]>([])
  const loading = ref(false)
  const submitting = ref(false)

  // 分页
  const pagination = ref<Pagination>({
    page: 1,
    limit: 10,
    total: 0,
    totalPages: 0
  })

  // 表单数据
  const formData = ref<MessageFormData>({
    nickname: '',
    email: '',
    content: ''
  })

  // 验证码
  const captchaKey = ref('')
  const captchaAnswer = ref('')
  const captchaImageUrl = ref('')
  const captchaLoading = ref(false)

  const formErrors = ref<Record<string, string>>({})
  const submitSuccess = ref(false)
  const submitError = ref('')
  const showForm = ref(false)

  // 获取验证码图片
  async function fetchCaptcha() {
    captchaLoading.value = true
    captchaAnswer.value = ''
    
    try {
      // 使用时间戳防止浏览器缓存
      const timestamp = Date.now()
      const response = await fetch(`${CAPTCHA_IMAGE_API}?t=${timestamp}`)
      
      if (!response.ok) {
        throw new Error('获取验证码失败')
      }
      
      // 从 Header 获取验证码 key
      const key = response.headers.get('X-Captcha-Key')
      if (key) {
        captchaKey.value = key
      }
      
      // 将响应转为 Blob URL
      const blob = await response.blob()
      captchaImageUrl.value = URL.createObjectURL(blob)
    } catch (error) {
      console.error('获取验证码失败:', error)
    } finally {
      captchaLoading.value = false
    }
  }

  // 验证表单
  function validateForm(): boolean {
    formErrors.value = {}

    if (!formData.value.nickname.trim()) {
      formErrors.value.nickname = '请填写昵称'
    } else if (formData.value.nickname.length > 50) {
      formErrors.value.nickname = '昵称不能超过 50 个字符'
    }

    if (formData.value.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.value.email)) {
      formErrors.value.email = '邮箱格式不正确'
    }

    if (!formData.value.content.trim()) {
      formErrors.value.content = '请填写留言内容'
    } else if (formData.value.content.length < 5) {
      formErrors.value.content = '留言内容至少需要 5 个字符'
    } else if (formData.value.content.length > 500) {
      formErrors.value.content = '留言内容不能超过 500 个字符'
    }

    if (!captchaAnswer.value.trim()) {
      formErrors.value.captcha = '请输入验证码答案'
    }

    return Object.keys(formErrors.value).length === 0
  }

  // 提交留言
  async function submitMessage() {
    if (!validateForm()) {
      return
    }

    submitting.value = true
    submitSuccess.value = false
    submitError.value = ''

    try {
      const response = await fetch(API_BASE, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          action: 'submit',
          nickname: formData.value.nickname,
          email: formData.value.email,
          content: formData.value.content,
          captcha_key: captchaKey.value,
          captcha_answer: captchaAnswer.value.trim()
        })
      })

      const result = await response.json()

      if (result.success) {
        submitSuccess.value = true
        formData.value = {
          nickname: '',
          email: '',
          content: ''
        }
        // 提交成功后刷新验证码
        fetchCaptcha()
        // 5 秒后清除成功提示
        setTimeout(() => {
          submitSuccess.value = false
        }, 5000)
      } else {
        submitError.value = result.error || '提交失败，请重试'
        // 提交失败也刷新验证码
        fetchCaptcha()
      }
    } catch (error) {
      submitError.value = '网络错误，请重试'
    } finally {
      submitting.value = false
    }
  }

  // 获取留言列表
  async function fetchMessages(page = 1) {
    loading.value = true
    try {
      const response = await fetch(`${API_BASE}?page=${page}&limit=${pagination.value.limit}`)
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

  // 格式化时间
  function formatTime(time: string) {
    const date = new Date(time)
    const now = new Date()
    const diff = now.getTime() - date.getTime()

    if (diff < 3600000) {
      const minutes = Math.floor(diff / 60000)
      return minutes < 1 ? '刚刚' : `${minutes} 分钟前`
    }

    if (diff < 86400000) {
      const hours = Math.floor(diff / 3600000)
      return `${hours} 小时前`
    }

    return date.toLocaleString('zh-CN', {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit'
    })
  }

  // 获取昵称首字母头像颜色
  function getAvatarColor(nickname: string) {
    const colors = [
      '#667eea', '#764ba2', '#f093fb', '#f5576c',
      '#11998e', '#38ef7d', '#4facfe', '#00f2fe',
      '#fa709a', '#fee140', '#a8edea', '#fed6e3'
    ]
    const index = nickname.charCodeAt(0) % colors.length
    return colors[index]
  }

  onMounted(() => {
    fetchMessages(1)
    fetchCaptcha()
  })

  return {
    messages,
    loading,
    submitting,
    pagination,
    formData,
    formErrors,
    submitSuccess,
    submitError,
    showForm,
    captchaKey,
    captchaAnswer,
    captchaImageUrl,
    captchaLoading,
    submitMessage,
    fetchMessages,
    fetchCaptcha,
    formatTime,
    getAvatarColor
  }
}

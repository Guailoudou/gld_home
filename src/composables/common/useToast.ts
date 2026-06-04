import { ref } from 'vue'

/**
 * Toast 消息提示 composable（3秒自动消失）
 */
export function useToast() {
  const showMessage = ref(false)
  const messageText = ref('')
  const messageType = ref<'success' | 'error'>('success')

  function showToast(text: string, type: 'success' | 'error' = 'success') {
    messageText.value = text
    messageType.value = type
    showMessage.value = true
    setTimeout(() => {
      showMessage.value = false
    }, 3000)
  }

  return {
    showMessage,
    messageText,
    messageType,
    showToast
  }
}

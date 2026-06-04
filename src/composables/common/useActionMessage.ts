import { ref } from 'vue'

/**
 * 操作消息提示 composable
 */
export function useActionMessage() {
  const actionMessage = ref('')
  const actionType = ref<'success' | 'error' | ''>('')

  function showActionMessage(msg: string, type: 'success' | 'error') {
    actionMessage.value = msg
    actionType.value = type
    setTimeout(() => {
      actionMessage.value = ''
      actionType.value = ''
    }, 3000)
  }

  return {
    actionMessage,
    actionType,
    showActionMessage
  }
}

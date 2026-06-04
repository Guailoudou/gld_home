/**
 * 剪贴板操作 composable
 */
export function useClipboard() {
  const copyToClipboard = async (text: string, _label: string): Promise<boolean> => {
    try {
      await navigator.clipboard.writeText(text)
      return true
    } catch (error) {
      console.error('复制失败:', error)
      return false
    }
  }

  return {
    copyToClipboard
  }
}

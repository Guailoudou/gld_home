/**
 * 获取管理员密码哈希
 */
export function getPasswordHash(): string {
  return localStorage.getItem('admin_password_hash') || ''
}

/**
 * 计算密码 SHA-256 哈希
 */
export async function computePasswordHash(password: string): Promise<string> {
  const encoder = new TextEncoder()
  const data = encoder.encode(password)
  const hashBuffer = await crypto.subtle.digest('SHA-256', data)
  const hashArray = Array.from(new Uint8Array(hashBuffer))
  return hashArray.map(b => b.toString(16).padStart(2, '0')).join('')
}

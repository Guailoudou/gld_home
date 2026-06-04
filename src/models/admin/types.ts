// Admin module shared types

export interface AdminModule {
  name: string
  path: string
  icon: string
  description: string
  color: string
}

export interface AuthState {
  adminPassword: string
  isAuthenticated: boolean
  showPasswordModal: boolean
  passwordInput: string
  passwordError: string
  passwordHash: string
  loading: boolean
  actionMessage: string
  actionType: 'success' | 'error' | ''
}

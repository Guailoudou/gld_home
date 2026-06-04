// Message types

export interface Message {
  id: number
  nickname: string
  email: string
  content: string
  ip_address: string | null
  is_displayed: number
  created_at: string
}

export interface Pagination {
  page: number
  limit: number
  total: number
  totalPages: number
}

export interface MessageFormData {
  nickname: string
  email: string
  content: string
}

// Portfolio types

export interface PortfolioItem {
  id: string
  title: string
  description: string
  image: string
  tags: string[]
  link: string
  github: string
  is_active: string
  sort_order: number
  created_at: string
}

export interface PortfolioConfig {
  id?: number
  title: string
  description: string
}

export interface ItemFormData {
  title: string
  description: string
  image: string
  tags: string
  link: string
  github: string
  is_active: boolean
  sort_order: number
}

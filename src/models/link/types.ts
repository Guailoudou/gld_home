// Link types

export interface LinkItem {
  id: string
  section_id: string
  name: string
  url: string
  description: string
  is_active: string
  sort_order: number
  created_at?: string
}

export interface SectionItem {
  id: string
  title: string
  icon: string
  is_active?: string
  sort_order?: number
  links: LinkItem[]
}

export interface SectionFormData {
  name: string
  icon: string
  is_active: boolean
  sort_order: number
}

export interface LinkFormData {
  name: string
  url: string
  description: string
  is_active: boolean
  sort_order: number
}

// Download management types

export interface DownloadItem {
  id: string
  name: string
  size: string
  url: string
  is_default: string
  is_featured: string
  priority: number
}

export interface ApiDownloadItem {
  id: string
  name: string
  size: string
  url: string
  is_default: string
  is_featured: string
  priority: number
}

export interface DownloadPackage {
  id: string
  code: string
  name: string
  description: string
  download_ids: string[]
  is_active: string
}

export interface DownloadFormData {
  id: string
  name: string
  size: string
  url: string
  is_default: boolean
  is_featured: boolean
  priority: number
  password: string
}

export interface PackageFormData {
  id: string
  code: string
  name: string
  description: string
  download_ids: string[]
  is_active: boolean
}

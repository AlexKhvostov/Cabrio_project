import { useTelegramStore } from '@/stores/telegram'

// Базовый URL API - используем туннельный домен
const API_BASE_URL = 'https://contributed-cm-component-consideration.trycloudflare.com/app/backend/test_data.php'

// Базовый URL для загрузки файлов
const UPLOADS_BASE_URL = import.meta.env.VITE_UPLOADS_BASE_URL || 'https://contributed-cm-component-consideration.trycloudflare.com/app'

// Типы ответов API
export interface ApiResponse<T = any> {
  success: boolean
  data: T
  error?: {
    code: string
    message: string
  }
  meta?: any
}

// Интерфейсы для данных
export interface ApiMember {
  id: number
  telegram_id: number
  username?: string
  first_name: string
  last_name?: string
  first_name_tg: string
  last_name_tg?: string
  first_name_app?: string
  last_name_app?: string
  birth_date?: string
  city: string
  country?: string
  email?: string
  phone?: string
  about?: string
  created_at: string
  updated_at: string
  join_date: string
  left_date?: string
  status_id: number
  role_id: number
  have_auto: boolean
  block: boolean
  respect: number
  weight: number
  messages_count: number
  last_activity?: string
  host_user_id?: number
  referrer_id?: number
  notes?: string
  photo_url?: string
  role: {
    id: number
    code: string
    name: string
  }
  photo?: {
    id: number
    url: string
    description?: string
  } | null
}

export interface ApiCar {
  id: number
  car_brand_id: number
  model: string
  color: string
  year: number
  reg_number: string
  show_reg_number: boolean
  engine_power?: number
  engine_volume: number
  vin?: string
  roof_type?: string
  description?: string
  created_at: string
  updated_at: string
  create_user_id: number
  owner_user_id: number
  status_id: number
  notes?: string
  brand: {
    id: number
    name: string
  }
  owner?: {
    id: number
    first_name: string
    last_name?: string
  } | null
  status: {
    id: number
    code: string
    name: string
  }
  photo?: {
    id: number
    url: string
    description?: string
  } | null
}

// Класс для работы с API
class ApiService {
  private getAuthHeaders(): HeadersInit {
    const telegramStore = useTelegramStore()
    const headers: HeadersInit = {
      'Content-Type': 'application/json'
    }

    // Добавляем Telegram данные для авторизации (если доступны)
    if (telegramStore.webApp?.initData) {
      headers['X-Telegram-Init-Data'] = telegramStore.webApp.initData
    }
    
    // Добавляем данные пользователя из Telegram WebApp
    if (telegramStore.webApp?.initDataUnsafe?.user) {
      const user = telegramStore.webApp.initDataUnsafe.user
      headers['X-Telegram-User-Id'] = user.id?.toString()
      headers['X-Telegram-First-Name'] = user.first_name
      headers['X-Telegram-Last-Name'] = user.last_name || ''
      headers['X-Telegram-Username'] = user.username || ''
      headers['X-Telegram-Photo-URL'] = user.photo_url || ''
      headers['X-Telegram-Auth-Date'] = Math.floor(Date.now() / 1000).toString()
      headers['X-Telegram-Hash'] = 'webapp_hash_' + Date.now()
    } else {
      // Для разработки без Telegram WebApp используем заглушку
      console.log('Telegram user data недоступен, используем тестовый режим')
    }

    return headers
  }

  private async makeRequest<T>(
    endpoint: string, 
    options: RequestInit = {}
  ): Promise<ApiResponse<T>> {
    // Формируем URL с GET параметром route
    const url = `${API_BASE_URL}?route=${endpoint}`
    
    const config: RequestInit = {
      ...options,
      headers: {
        ...this.getAuthHeaders(),
        ...options.headers
      }
    }

    // Отладочная информация
    console.log('🌐 API Request:', {
      url: url,
      method: config.method || 'GET',
      headers: config.headers
    })

    try {
      console.log('🌐 Отправляем запрос к:', url)
      const response = await fetch(url, config)
      
      console.log('📡 Получен ответ:', {
        status: response.status,
        statusText: response.statusText,
        ok: response.ok
      })

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`)
      }

      const data = await response.json()

      console.log('📡 API Response:', {
        status: response.status,
        statusText: response.statusText,
        data: data
      })

      // Проверяем успешность ответа в JSON
      if (!data.success) {
        throw new Error(data.error?.message || 'API request failed')
      }

      return data
    } catch (error) {
      console.error('❌ API Error:', {
        message: error instanceof Error ? error.message : String(error),
        url: url,
        config: config
      })
      throw error
    }
  }

  // Получить список пользователей
  async getMembers(): Promise<ApiMember[]> {
    const response = await this.makeRequest<ApiMember[]>('/api/users')
    return response.data
  }

  // Получить список автомобилей
  async getCars(): Promise<ApiCar[]> {
    const response = await this.makeRequest<ApiCar[]>('/api/cars')
    return response.data
  }

  // Получить автомобиль по ID
  async getCar(id: number): Promise<ApiCar> {
    const response = await this.makeRequest<ApiCar>(`/api/cars/${id}`)
    return response.data
  }

  // Получить пользователя по ID
  async getMember(id: number): Promise<ApiMember> {
    const response = await this.makeRequest<ApiMember>(`/api/users/${id}`)
    return response.data
  }

  // Получить профиль текущего пользователя
  async getProfile(): Promise<ApiMember> {
    const response = await this.makeRequest<ApiMember>('/api/users/profile')
    return response.data
  }

  // Проверка состояния API
  async getHealth(): Promise<{ status: string; message: string }> {
    const response = await this.makeRequest<{ status: string; message: string }>('/api/health')
    return response.data
  }

  // Функция для обработки URL фотографий
  processPhotoUrl(photoUrl: string | null | undefined): string | null {
    if (!photoUrl) return null
    
    // Если URL уже полный (начинается с http), возвращаем как есть
    if (photoUrl.startsWith('http')) {
      return photoUrl
    }
    
    // Если URL относительный, добавляем базовый URL
    if (photoUrl.startsWith('/')) {
      return `${UPLOADS_BASE_URL}${photoUrl}`
    }
    
    // Если URL без слеша в начале, добавляем базовый URL и слеш
    return `${UPLOADS_BASE_URL}/${photoUrl}`
  }
}

// Экспортируем экземпляр сервиса
export const apiService = new ApiService() 
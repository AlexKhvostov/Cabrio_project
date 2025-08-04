import { defineStore } from 'pinia'
import { ref } from 'vue'
import { apiService, type ApiMember, type ApiCar } from '@/services/api'

// Интерфейсы для совместимости с компонентами
export interface Member {
  id: number
  first_name: string
  last_name?: string
  username?: string
  city: string
  photo_url?: string
  status: string // для совместимости
  nickname?: string // для совместимости
  message_count?: number // для совместимости
  flags?: string[] // для совместимости
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
  cars: any[] // для совместимости
  weight: number
}

export interface Car {
  id: number
  brand: {
    id: number
    name: string
  }
  model: string
  year: number
  reg_number: string
  color: string
  engine_volume: number
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
  owner_name?: string // для совместимости
  owner_nickname?: string // для совместимости
  photos?: string[] // для совместимости
  member_id?: number // для совместимости
}

export interface Event {
  id: number
  title: string
  description: string
  date: string
  location: string
  participants: number
  max_participants: number
  status: string
}

export interface Service {
  id: number
  name: string
  description: string
  type: string
  location: string
  rating: number
  reviews_count: number
}

// Функции трансформации
function transformApiMember(apiMember: ApiMember): Member {
  return {
    id: apiMember.id,
    first_name: apiMember.first_name_app || apiMember.first_name_tg,
    last_name: apiMember.last_name_app || apiMember.last_name_tg,
    username: apiMember.username,
    city: apiMember.city,
    photo_url: apiService.processPhotoUrl(apiMember.photo?.url),
    status: apiMember.role?.name || 'Участник', // для совместимости
    nickname: apiMember.username, // для совместимости
    message_count: apiMember.messages_count, // для совместимости
    flags: [], // для совместимости
    role: apiMember.role || { id: 3, code: 'member', name: 'Участник' },
    photo: apiMember.photo ? {
      ...apiMember.photo,
      url: apiService.processPhotoUrl(apiMember.photo.url)
    } : null,
    cars: [], // для совместимости
    weight: apiMember.weight || 0
  }
}

function transformApiCar(apiCar: ApiCar): Car {
  return {
    id: apiCar.id,
    brand: apiCar.brand,
    model: apiCar.model,
    year: apiCar.year,
    reg_number: apiCar.reg_number,
    color: apiCar.color,
    engine_volume: apiCar.engine_volume,
    owner: apiCar.owner,
    status: apiCar.status,
    photo: apiCar.photo ? {
      ...apiCar.photo,
      url: apiService.processPhotoUrl(apiCar.photo.url)
    } : null,
    owner_name: apiCar.owner ? `${apiCar.owner.first_name} ${apiCar.owner.last_name || ''}`.trim() : undefined, // для совместимости
    owner_nickname: apiCar.owner?.first_name, // для совместимости
    photos: apiCar.photo ? [apiService.processPhotoUrl(apiCar.photo.url)] : [], // для совместимости
    member_id: apiCar.owner?.id // для совместимости
  }
}

export const useDataStore = defineStore('data', () => {
  const members = ref<Member[]>([])
  const cars = ref<Car[]>([])
  const events = ref<Event[]>([])
  const services = ref<Service[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)

  async function fetchMembers() {
    loading.value = true
    error.value = null
    
    try {
      console.log('Начинаем загрузку пользователей...')
      // Загружаем реальные данные с API
      const apiMembers = await apiService.getMembers()
      console.log('Получены данные от API:', apiMembers)
      members.value = apiMembers.map(transformApiMember)
      console.log('Загружено пользователей:', members.value.length)
    } catch (err) {
      console.error('Ошибка загрузки пользователей:', err)
      error.value = `Ошибка загрузки пользователей: ${err instanceof Error ? err.message : String(err)}`
      // Оставляем пустой массив вместо моковых данных
      members.value = []
    } finally {
      loading.value = false
    }
  }

  async function fetchCars() {
    loading.value = true
    error.value = null
    
    try {
      console.log('Начинаем загрузку автомобилей...')
      // Загружаем реальные данные с API
      const apiCars = await apiService.getCars()
      console.log('Получены данные от API:', apiCars)
      cars.value = apiCars.map(transformApiCar)
      console.log('Загружено автомобилей:', cars.value.length)
    } catch (err) {
      console.error('Ошибка загрузки автомобилей:', err)
      error.value = `Ошибка загрузки автомобилей: ${err instanceof Error ? err.message : String(err)}`
      // Оставляем пустой массив вместо моковых данных
      cars.value = []
    } finally {
      loading.value = false
    }
  }

  async function fetchEvents() {
    loading.value = true
    error.value = null
    
    try {
      // Пока используем моковые данные для событий
      events.value = [
        {
          id: 1,
          title: 'Встреча кабриолетов',
          description: 'Еженедельная встреча владельцев кабриолетов',
          date: '2024-08-10',
          location: 'Парк Горького',
          participants: 12,
          max_participants: 20,
          status: 'active'
        }
      ]
    } catch (err) {
      console.error('Ошибка загрузки событий:', err)
      error.value = 'Не удалось загрузить события'
    } finally {
      loading.value = false
    }
  }

  async function fetchServices() {
    loading.value = true
    error.value = null
    
    try {
      // Пока используем моковые данные для сервисов
      services.value = [
        {
          id: 1,
          name: 'Автосервис "Кабриолет"',
          description: 'Специализированный сервис для кабриолетов',
          type: 'service',
          location: 'Москва',
          rating: 4.8,
          reviews_count: 15
        }
      ]
    } catch (err) {
      console.error('Ошибка загрузки сервисов:', err)
      error.value = 'Не удалось загрузить сервисы'
    } finally {
      loading.value = false
    }
  }

  function retryLoad() {
    error.value = null
    fetchMembers()
    fetchCars()
    fetchEvents()
    fetchServices()
  }

  return {
    members,
    cars,
    events,
    services,
    loading,
    error,
    fetchMembers,
    fetchCars,
    fetchEvents,
    fetchServices,
    retryLoad
  }
})
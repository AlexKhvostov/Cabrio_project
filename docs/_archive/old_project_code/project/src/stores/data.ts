import { defineStore } from 'pinia'
import { ref } from 'vue'

export interface Member {
  id: number
  // Основные идентификаторы
  telegram_id: number
  username?: string
  
  // Имена
  first_name: string
  last_name?: string
  first_name_tg: string
  last_name_tg?: string
  first_name_app?: string
  last_name_app?: string
  
  // Персональная информация
  birth_date?: string
  city: string
  country?: string
  email?: string
  phone?: string
  about?: string
  
  // Системные поля
  created_at: string
  updated_at: string
  join_date: string
  left_date?: string
  
  // Статусы и роли
  status_id: number
  role_id: number
  have_auto: boolean
  block: boolean
  
  // Социальные
  respect: number
  weight: number
  messages_count: number
  last_activity?: string
  host_user_id?: number
  referrer_id?: number
  
  // Заметки
  notes?: string
  
  // Фото и связи
  photo_url?: string
  cars: Car[]
  user_locations?: {
    latitude: number
    longitude: number
    updated_at: string
  }[]
}

export interface Car {
  id: number
  // Основная информация
  car_model_id: number
  brand: string
  model: string
  year: number
  reg_number: string
  show_reg_number: boolean
  color: string
  engine_volume: number
  engine_power?: number
  vin?: string
  roof_type?: string
  description?: string
  
  // Системные поля
  created_at: string
  updated_at: string
  
  // Владение
  owner_user_id: number
  create_user_id: number
  
  // Статус
  status_id: number
  
  // Заметки и фото
  notes?: string
  photos: string[]
  
  // Связанные пользователи (через link_user_cars)
  users?: Member[]
}

export interface Event {
  id: number
  title: string
  description: string
  event_date: string
  event_time: string
  location: string
  city: string
  type: 'заезд' | 'встреча' | 'фотосессия' | 'поездка' | 'банкет'
  status: 'запланировано' | 'завершено' | 'отменено'
  organizer_id: number
  organizer_name: string
  organizer_nickname: string
  participants_count: number
  max_participants: number
  price: number
  photos?: string[]
  created_at: string
}

export interface Service {
  id: number
  name: string
  description: string
  city: string
  address?: string
  phone?: string
  website?: string
  services: string[]
  category: 'фотосессии' | 'питание' | 'ремонт' | 'обслуживание' | 'отдых'
  type: string
  recommendation: 'рекомендуется' | 'не рекомендуется'
  rating: number
  reviews_count: number
  added_by_id: number
  added_by_name: string
  added_by_nickname: string
  created_at: string
  reviews: ServiceReview[]
}

export interface ServiceReview {
  id: number
  member_id: number
  member_name: string
  member_nickname: string
  rating: number
  comment: string
  date: string
}

export interface Stats {
  total_members: number
  active_members: number
  total_cars: number
  total_events: number
  upcoming_events: number
  total_guide_objects: number
  total_reviews: number
}

export const useDataStore = defineStore('data', () => {
  const loading = ref(false)
  const stats = ref<Stats>({
    total_members: 27,
    active_members: 20,
    total_cars: 35,
    total_events: 12,
    upcoming_events: 4,
    total_guide_objects: 23,
    total_reviews: 89
  })

  const members = ref<Member[]>([
    {
      id: 1,
      telegram_id: '287536885',
      first_name: 'Александр',
      last_name: 'Петров',
      nickname: 'alex_petrov',
      city: 'Москва',
      join_date: '2024-01-15',
      status: 'активный',
      role: 'администратор',
      flags: [],
      message_count: 245,
      photo_url: 'https://images.pexels.com/photos/220453/pexels-photo-220453.jpeg?auto=compress&cs=tinysrgb&w=64&h=64&dpr=1',
      cars: []
    },
    {
      id: 2,
      telegram_id: '123456789',
      first_name: 'Мария',
      last_name: 'Иванова',
      nickname: 'maria_drive',
      city: 'Санкт-Петербург',
      join_date: '2024-02-03',
      status: 'активный',
      role: 'модератор',
      flags: [],
      message_count: 189,
      photo_url: 'https://images.pexels.com/photos/415829/pexels-photo-415829.jpeg?auto=compress&cs=tinysrgb&w=64&h=64&dpr=1',
      cars: []
    },
    {
      id: 3,
      telegram_id: '987654321',
      first_name: 'Дмитрий',
      last_name: 'Козлов',
      nickname: 'dmitry_cabrio',
      city: 'Краснодар',
      join_date: '2024-01-28',
      status: 'зарегистрирован',
      role: 'участник',
      flags: ['нет авто'],
      message_count: 67,
      cars: []
    },
    {
      id: 4,
      telegram_id: '555666777',
      first_name: 'Елена',
      last_name: 'Смирнова',
      nickname: 'elena_cabrio',
      city: 'Екатеринбург',
      join_date: '2023-12-10',
      status: 'активный',
      role: 'участник',
      flags: ['почётный участник'],
      message_count: 456,
      photo_url: 'https://images.pexels.com/photos/774909/pexels-photo-774909.jpeg?auto=compress&cs=tinysrgb&w=64&h=64&dpr=1',
      cars: []
    },
    {
      id: 5,
      telegram_id: '888999000',
      first_name: 'Михаил',
      last_name: 'Волков',
      nickname: 'mike_drive',
      city: 'Новосибирск',
      join_date: '2024-03-05',
      status: 'вышедший',
      role: 'участник',
      flags: [],
      message_count: 89,
      cars: []
    }
  ])

  const cars = ref<Car[]>([
    {
      id: 1,
      member_id: 1,
      brand: 'BMW',
      model: 'Z4',
      year: 2019,
      reg_number: 'М123АВ777',
      color: 'Белый',
      engine_volume: '2.0',
      status: 'активный',
      photos: [
        'https://images.pexels.com/photos/3802510/pexels-photo-3802510.jpeg?auto=compress&cs=tinysrgb&w=400',
        'https://images.pexels.com/photos/1545743/pexels-photo-1545743.jpeg?auto=compress&cs=tinysrgb&w=400'
      ],
      owner_name: 'Александр Петров',
      owner_nickname: 'alex_petrov'
    },
    {
      id: 2,
      member_id: 2,
      brand: 'Mercedes-Benz',
      model: 'SLK-Class',
      year: 2016,
      reg_number: 'А456ВС199',
      color: 'Красный',
      engine_volume: '1.8',
      status: 'активный',
      photos: [
        'https://images.pexels.com/photos/1592384/pexels-photo-1592384.jpeg?auto=compress&cs=tinysrgb&w=400'
      ],
      owner_name: 'Мария Иванова',
      owner_nickname: 'maria_drive'
    },
    {
      id: 3,
      member_id: 4,
      brand: 'Audi',
      model: 'A5 Cabriolet',
      year: 2020,
      reg_number: 'Е789КМ77',
      color: 'Синий',
      engine_volume: '2.0',
      status: 'активный',
      photos: [
        'https://images.pexels.com/photos/3802510/pexels-photo-3802510.jpeg?auto=compress&cs=tinysrgb&w=400',
        'https://images.pexels.com/photos/1545743/pexels-photo-1545743.jpeg?auto=compress&cs=tinysrgb&w=400',
        'https://images.pexels.com/photos/1592384/pexels-photo-1592384.jpeg?auto=compress&cs=tinysrgb&w=400'
      ],
      owner_name: 'Елена Смирнова',
      owner_nickname: 'elena_cabrio'
    }
  ])

  const events = ref<Event[]>([
    {
      id: 1,
      title: 'Встреча участников клуба',
      description: 'Ежемесячная встреча участников клуба кабриолетов. Обсуждение планов, знакомство с новыми участниками.',
      event_date: '2024-04-15',
      event_time: '10:00',
      location: 'Москва, Красная площадь',
      city: 'Москва',
      type: 'встреча клуба',
      status: 'запланировано',
      organizer_id: 1,
      organizer_name: 'Александр Петров',
      organizer_nickname: 'alex_petrov',
      participants_count: 8,
      max_participants: 20,
      price: 0,
      photos: [
        'https://images.pexels.com/photos/1545743/pexels-photo-1545743.jpeg?auto=compress&cs=tinysrgb&w=400'
      ],
      created_at: '2024-03-01'
    },
    {
      id: 2,
      title: 'Поездка в Суздаль',
      description: 'Двухдневная поездка по Золотому кольцу с остановками в исторических городах.',
      event_date: '2024-04-22',
      event_time: '08:00',
      location: 'Суздаль, центральная площадь',
      city: 'Суздаль',
      type: 'поездка/круиз',
      status: 'запланировано',
      organizer_id: 2,
      organizer_name: 'Мария Иванова',
      organizer_nickname: 'maria_drive',
      participants_count: 15,
      max_participants: 25,
      price: 3500,
      photos: [],
      created_at: '2024-03-05'
    },
    {
      id: 3,
      title: 'Мастер-класс по детейлингу',
      description: 'Практический мастер-класс по уходу за кабриолетами от профессионалов.',
      event_date: '2024-04-28',
      event_time: '14:00',
      location: 'Автосервис "Кабрио Центр"',
      city: 'Москва',
      type: 'мастер-класс',
      status: 'запланировано',
      organizer_id: 1,
      organizer_name: 'Александр Петров',
      organizer_nickname: 'alex_petrov',
      participants_count: 6,
      max_participants: 12,
      price: 1500,
      photos: [],
      created_at: '2024-03-10'
    },
    {
      id: 4,
      title: 'Выезд на природу - озеро Селигер',
      description: 'Отдых на природе с барбекю, играми и фотосессией у озера.',
      event_date: '2024-05-05',
      event_time: '09:00',
      location: 'База отдыха "Селигер"',
      city: 'Осташков',
      type: 'выезд на природу',
      status: 'запланировано',
      organizer_id: 4,
      organizer_name: 'Елена Смирнова',
      organizer_nickname: 'elena_cabrio',
      participants_count: 18,
      max_participants: 30,
      price: 2000,
      photos: [],
      created_at: '2024-03-12'
    },
    {
      id: 5,
      title: 'Благотворительный автопробег',
      description: 'Участие в благотворительном автопробеге в поддержку детского дома.',
      event_date: '2024-05-12',
      event_time: '11:00',
      location: 'Парк Сокольники',
      city: 'Москва',
      type: 'волонтёрство/благотворительность',
      status: 'запланировано',
      organizer_id: 2,
      organizer_name: 'Мария Иванова',
      organizer_nickname: 'maria_drive',
      participants_count: 22,
      max_participants: 40,
      price: 0,
      photos: [],
      created_at: '2024-03-15'
    }
  ])

  const services = ref<Service[]>([
    {
      id: 1,
      name: 'Автосервис "Кабрио Центр"',
      description: 'Специализированный сервис по обслуживанию кабриолетов. Ремонт крыш, диагностика, ТО.',
      city: 'Москва',
      address: 'ул. Автомобильная, 15',
      phone: '+7 (495) 123-45-67',
      website: 'www.cabrio-center.ru',
      services: ['Ремонт крыш', 'Диагностика', 'ТО', 'Кузовной ремонт'],
      category: 'ремонт',
      type: 'автосервис',
      recommendation: 'рекомендуется',
      rating: 4.8,
      reviews_count: 15,
      added_by_id: 1,
      added_by_name: 'Александр Петров',
      added_by_nickname: 'alex_petrov',
      created_at: '2024-01-10',
      reviews: [
        {
          id: 1,
          member_id: 1,
          member_name: 'Александр Петров',
          member_nickname: 'alex_petrov',
          rating: 5,
          comment: 'Отличный сервис! Быстро и качественно отремонтировали крышу на Z4. Рекомендую!',
          date: '2024-02-15'
        }
      ]
    },
    {
      id: 2,
      name: 'Кафе "Престиж"',
      description: 'Уютное кафе для встреч участников клуба. Отличная кухня, удобная парковка для кабриолетов.',
      city: 'Москва',
      address: 'ул. Тверская, 25',
      phone: '+7 (495) 234-56-78',
      website: 'www.prestige-cafe.ru',
      services: ['Завтраки', 'Бизнес-ланчи', 'Банкеты', 'Парковка'],
      category: 'питание',
      type: 'кафе',
      recommendation: 'рекомендуется',
      rating: 4.6,
      reviews_count: 23,
      added_by_id: 2,
      added_by_name: 'Мария Иванова',
      added_by_nickname: 'maria_drive',
      created_at: '2024-02-15',
      reviews: [
        {
          id: 2,
          member_id: 2,
          member_name: 'Мария Иванова',
          member_nickname: 'maria_drive',
          rating: 5,
          comment: 'Отличное место для встреч! Вкусная еда и удобная парковка прямо у входа.',
          date: '2024-03-01'
        },
        {
          id: 3,
          member_id: 4,
          member_name: 'Елена Смирнова',
          member_nickname: 'elena_cabrio',
          rating: 4,
          comment: 'Хорошее кафе, но цены немного завышены. Атмосфера приятная.',
          date: '2024-03-10'
        }
      ]
    },
    {
      id: 3,
      name: 'Пропитка FOG для мягких крыш',
      description: 'Профессиональная пропитка для защиты мягких крыш кабриолетов от влаги и УФ-излучения. Продлевает срок службы крыши в 2-3 раза.',
      city: 'Санкт-Петербург',
      address: 'Автомагазин "КабриоПарт", пр. Невский, 120',
      phone: '+7 (812) 345-67-89',
      website: 'www.cabriopart.ru',
      services: ['Защита от влаги', 'УФ-защита', 'Восстановление цвета', 'Консультация'],
      category: 'обслуживание',
      type: 'средство',
      recommendation: 'рекомендуется',
      rating: 4.9,
      reviews_count: 18,
      added_by_id: 4,
      added_by_name: 'Елена Смирнова',
      added_by_nickname: 'elena_cabrio',
      created_at: '2024-01-25',
      reviews: [
        {
          id: 4,
          member_id: 4,
          member_name: 'Елена Смирнова',
          member_nickname: 'elena_cabrio',
          rating: 5,
          comment: 'Отличная пропитка! Крыша как новая после обработки. Рекомендую всем владельцам кабриолетов.',
          date: '2024-02-20'
        },
        {
          id: 5,
          member_id: 1,
          member_name: 'Александр Петров',
          member_nickname: 'alex_petrov',
          rating: 5,
          comment: 'Использую уже полгода - результат превосходный! Крыша не выгорает и отлично отталкивает воду.',
          date: '2024-03-05'
        }
      ]
    },
    {
      id: 4,
      name: 'Парковка на крыше ТЦ GREEN',
      description: 'Красивая открытая парковка на крыше торгового центра с панорамным видом на город. Идеальное место для фотосессий с кабриолетами и встреч участников клуба.',
      city: 'Москва',
      address: 'ул. Зеленая, 42, крыша ТЦ GREEN',
      phone: '+7 (495) 456-78-90',
      website: 'www.green-mall.ru/parking',
      services: ['Открытая парковка', 'Панорамный вид', 'Фотозона', 'Охрана 24/7'],
      category: 'фотосессии',
      type: 'парковка',
      recommendation: 'рекомендуется',
      rating: 4.7,
      reviews_count: 12,
      added_by_id: 1,
      added_by_name: 'Александр Петров',
      added_by_nickname: 'alex_petrov',
      created_at: '2024-02-20',
      reviews: [
        {
          id: 6,
          member_id: 1,
          member_name: 'Александр Петров',
          member_nickname: 'alex_petrov',
          rating: 5,
          comment: 'Потрясающий вид на город! Отличное место для фотосессий с кабриолетами. Безопасно и красиво.',
          date: '2024-03-12'
        },
        {
          id: 7,
          member_id: 2,
          member_name: 'Мария Иванова',
          member_nickname: 'maria_drive',
          rating: 4,
          comment: 'Хорошая парковка, но иногда много народу. Лучше приезжать утром для фотосессий.',
          date: '2024-03-18'
        }
      ]
    }
  ])

  // Simulate API calls
  const fetchStats = async () => {
    loading.value = true
    await new Promise(resolve => setTimeout(resolve, 500))
    loading.value = false
    return stats.value
  }

  const fetchMembers = async () => {
    loading.value = true
    await new Promise(resolve => setTimeout(resolve, 600))
    // Link cars to members
    members.value.forEach(member => {
      member.cars = cars.value.filter(car => car.member_id === member.id)
    })
    loading.value = false
    return members.value
  }

  const fetchCars = async () => {
    loading.value = true
    await new Promise(resolve => setTimeout(resolve, 400))
    loading.value = false
    return cars.value
  }

  const fetchEvents = async () => {
    loading.value = true
    await new Promise(resolve => setTimeout(resolve, 450))
    loading.value = false
    return events.value
  }

  const fetchServices = async () => {
    loading.value = true
    await new Promise(resolve => setTimeout(resolve, 350))
    loading.value = false
    return services.value
  }

  return {
    loading,
    stats,
    members,
    cars,
    events,
    services,
    fetchStats,
    fetchMembers,
    fetchCars,
    fetchEvents,
    fetchServices
  }
})
<template>
  <div class="service-card" @click="$emit('select', service)">
    <!-- Основная информация слева -->
    <div class="service-info">
      <!-- Тип сервиса -->
      <div class="service-type">
        <component :is="getServiceIcon(service.category)" :size="12" />
        <span>{{ getServiceCategory(service.category) }}</span>
      </div>
      
      <!-- Название -->
      <h3 class="service-name">{{ service.name }}</h3>
      
      <!-- Виды услуг -->
      <div class="service-services">
        <span
          v-for="(serviceItem, index) in getMainServices(service.services)"
          :key="serviceItem"
          class="service-tag"
        >
          {{ serviceItem }}{{ index < getMainServices(service.services).length - 1 ? ',' : '' }}
        </span>
      </div>
      
      <!-- Местоположение -->
      <div class="service-location">
        <MapPin :size="10" />
        <span>{{ service.city }}</span>
        <span v-if="service.address" class="address-separator">•</span>
        <span v-if="service.address" class="service-address">{{ service.address }}</span>
      </div>
    </div>
    
    <!-- Картинка справа с рейтингом -->
    <div class="service-image">
      <!-- Рейтинг в правом верхнем углу -->
      <div class="service-rating">
        <Star :size="10" class="filled" />
        <span class="rating-value">{{ service.rating.toFixed(1) }}</span>
        <span class="reviews-count">({{ service.reviews_count }})</span>
      </div>
      
      <img
        v-if="getServiceImage(service.category)"
        :src="getServiceImage(service.category)"
        :alt="service.name"
        class="service-photo"
      />
      <div v-else class="service-placeholder">
        <component :is="getServiceIcon(service.category)" :size="20" />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { 
  MapPin, 
  Star, 
  Wrench, 
  Droplets, 
  Car, 
  Zap,
  Coffee,
  ShoppingBag,
  ParkingCircle
} from 'lucide-vue-next'
import type { Service } from '@/stores/data'

interface Props {
  service: Service
}

const props = defineProps<Props>()
defineEmits<{
  select: [service: Service]
}>()

const getServiceIcon = (type: string) => {
  switch (type) {
    case 'фотосессии': return ParkingCircle
    case 'питание': return Coffee
    case 'ремонт': return Wrench
    case 'обслуживание': return ShoppingBag
    case 'отдых': return Coffee
    default: return Wrench
  }
}

const getServiceTypeLabel = (type: string) => {
  const labels = {
    'автосервис': 'Сервис',
    'детейлинг': 'Детейлинг',
    'шиномонтаж': 'Шиномонтаж',
    'электрик': 'Электрик',
    'автомойка': 'Автомойка',
    'кафе': 'Кафе',
    'средство': 'Средство',
    'парковка': 'Парковка',
    'ресторан': 'Ресторан',
    'бар': 'Бар',
    'смотровая': 'Смотровая',
    'локация': 'Локация',
    'инструмент': 'Инструмент',
    'лайфхак': 'Лайфхак',
    'база_отдыха': 'База отдыха',
    'пляж': 'Пляж'
  }
  return labels[type as keyof typeof labels] || type
}

const getServiceCategory = (type: string) => {
  const categories = {
    'фотосессии': 'Фотосессии',
    'питание': 'Питание', 
    'ремонт': 'Ремонт',
    'обслуживание': 'Обслуживание',
    'отдых': 'Отдых'
  }
  return categories[type as keyof typeof categories] || 'Сервис'
}

const getMainServices = (services: string[]) => {
  // Показываем максимум 3 основные услуги
  return services.slice(0, 3)
}

const getServiceImage = (type: string) => {
  // Возвращаем placeholder изображения для разных типов сервисов
  const images = {
    'фотосессии': 'https://images.pexels.com/photos/753876/pexels-photo-753876.jpeg?auto=compress&cs=tinysrgb&w=200',
    'питание': 'https://images.pexels.com/photos/1581384/pexels-photo-1581384.jpeg?auto=compress&cs=tinysrgb&w=200',
    'ремонт': 'https://images.pexels.com/photos/3806288/pexels-photo-3806288.jpeg?auto=compress&cs=tinysrgb&w=200',
    'обслуживание': 'https://images.pexels.com/photos/3806288/pexels-photo-3806288.jpeg?auto=compress&cs=tinysrgb&w=200',
    'отдых': 'https://images.pexels.com/photos/1581384/pexels-photo-1581384.jpeg?auto=compress&cs=tinysrgb&w=200'
  }
  return images[type as keyof typeof images] || null
}
</script>

<style scoped>
.service-card {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
  padding: var(--spacing-sm);
  background: var(--card-bg);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-md);
  cursor: pointer;
  transition: all 0.2s ease;
  min-height: 70px;
}

.service-card:hover {
  background: var(--hover-bg);
  border-color: var(--primary-color);
  transform: translateY(-1px);
  box-shadow: var(--shadow-md);
}

.service-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 2px;
  min-width: 0;
}

.service-type {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 10px;
  color: var(--primary-color);
  font-weight: var(--font-weight-medium);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.service-type svg {
  color: var(--primary-color);
  flex-shrink: 0;
}

.service-name {
  font-size: var(--font-size-sm);
  font-weight: var(--font-weight-semibold);
  color: var(--tg-theme-text-color);
  margin: 0;
  line-height: 1.2;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.service-services {
  display: flex;
  flex-wrap: wrap;
  gap: 2px;
  align-items: center;
}

.service-tag {
  font-size: 10px;
  color: var(--tg-theme-hint-color);
  font-weight: var(--font-weight-medium);
  line-height: 1;
}

.service-location {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 10px;
  color: var(--tg-theme-hint-color);
}

.service-location svg {
  color: var(--tg-theme-hint-color);
  flex-shrink: 0;
}

.address-separator {
  color: var(--tg-theme-hint-color);
  margin: 0 4px;
}

.service-address {
  color: var(--tg-theme-hint-color);
  font-weight: var(--font-weight-normal);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  flex: 1;
}

.service-image {
  position: relative;
  width: 120px;
  height: 60px;
  border-radius: var(--radius-md);
  overflow: hidden;
  flex-shrink: 0;
}

.service-rating {
  position: absolute;
  top: 2px;
  right: 2px;
  background: rgba(0, 0, 0, 0.8);
  color: white;
  padding: 1px 4px;
  border-radius: var(--radius-sm);
  font-size: 9px;
  font-weight: var(--font-weight-medium);
  display: flex;
  align-items: center;
  gap: 2px;
  z-index: 2;
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
}

.service-rating svg.filled {
  color: #ffd700;
}

.rating-value {
  color: white;
}

.reviews-count {
  color: rgba(255, 255, 255, 0.7);
  font-size: 8px;
}

.service-photo {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.2s ease;
}

.service-card:hover .service-photo {
  transform: scale(1.05);
}

.service-placeholder {
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, var(--border-color) 0%, rgba(255, 255, 255, 0.1) 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--tg-theme-hint-color);
}

@media (max-width: 480px) {
  .service-card {
    padding: 6px;
    gap: 6px;
    min-height: 60px;
  }
  
  .service-image {
    width: 100px;
    height: 50px;
  }
  
  .service-name {
    font-size: 12px;
  }
  
  .service-type,
  .service-location {
    font-size: 9px;
  }
  
  .service-tag {
    font-size: 9px;
  }
  
  .service-rating {
    font-size: 8px;
    padding: 1px 3px;
  }
  
  .reviews-count {
    font-size: 7px;
  }
}
</style>
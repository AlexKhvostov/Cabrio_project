<template>
  <div class="event-card-compact" @click="$emit('select', event)">
    <!-- Левая часть: картинка события -->
    <div class="event-image-container">
      <img
        :src="getEventImage(event.type || 'meeting')"
        :alt="event.title"
        class="event-image"
      />
      
      <!-- Дата поверх картинки -->
      <div class="event-date-overlay">
        <div class="date-day">{{ formatDay(event.event_date || event.date) }}</div>
        <div class="date-month">{{ formatMonth(event.event_date || event.date) }}</div>
      </div>
      
      <!-- Статус события -->
      <div class="event-status-overlay">
        <span :class="['status-dot', `status-${getStatusColor(event.status)}`]"></span>
      </div>
    </div>
    
    <!-- Основной контент -->
    <div class="event-content-compact">
      <!-- Заголовок и статус -->
      <div class="event-header-compact">
        <h3 class="event-title-compact">{{ event.title }}</h3>
        <span :class="['type-badge-compact', `type-${getTypeColor(event.type || 'meeting')}`]">
          {{ getTypeIcon(event.type || 'meeting') }} {{ getTypeShortLabel(event.type || 'meeting') }}
        </span>
      </div>
      
      <!-- Дата и время текстом -->
      <div class="event-datetime-text">
        <Calendar :size="10" />
        <span>{{ formatDateText(event.event_date || event.date) }} в {{ event.event_time || '19:00' }}</span>
      </div>
      
      <!-- Детали -->
      <div class="event-details-compact">
        <div class="detail-item-compact">
          <MapPin :size="10" />
          <span>{{ event.city || event.location }}</span>
        </div>
        <div class="detail-item-compact">
          <Users :size="10" />
          <span>{{ event.participants_count || event.participants }}/{{ event.max_participants }}</span>
        </div>
        <div class="detail-item-compact price-compact">
          <span v-if="event.price && event.price > 0" class="price-value">{{ event.price }}₽</span>
          <span v-else class="price-free">Free</span>
        </div>
      </div>
      
      <!-- Участие -->
      <div class="participation-section" @click.stop>
        <span class="participation-label">Участие:</span>
        <div class="participation-buttons">
          <button 
            class="participation-btn yes"
            :class="{ active: participation === 'yes' }"
            @click="setParticipation('yes')"
          >
            ✓
          </button>
          <button 
            class="participation-btn no"
            :class="{ active: participation === 'no' }"
            @click="setParticipation('no')"
          >
            ✗
          </button>
          <button 
            class="participation-btn maybe"
            :class="{ active: participation === 'maybe' }"
            @click="setParticipation('maybe')"
          >
            ?
          </button>
          <button 
            v-if="participation === 'yes'"
            class="plus-one-btn"
            :class="{ active: plusOne }"
            @click="togglePlusOne"
          >
            +1
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { MapPin, Users, Calendar } from 'lucide-vue-next'
import type { Event } from '@/stores/data'
import { useTelegramStore } from '@/stores/telegram'

interface Props {
  event: Event
}

defineProps<Props>()
defineEmits<{
  select: [event: Event]
  participate: [eventId: number, status: string, plusOne: boolean]
}>()

const telegramStore = useTelegramStore()
const participation = ref<'yes' | 'no' | 'maybe' | null>(null)
const plusOne = ref(false)

const getStatusColor = (status: string) => {
  switch (status) {
    case 'запланировано': return 'planned'
    case 'завершено': return 'completed'
    case 'отменено': return 'cancelled'
    default: return 'planned'
  }
}

const getTypeColor = (type: string) => {
  switch (type) {
    case 'встреча клуба': return 'meeting'
    case 'поездка/круиз': return 'trip'
    case 'мастер-класс': return 'workshop'
    case 'выезд на природу': return 'nature'
    case 'волонтёрство/благотворительность': return 'charity'
    default: return 'meeting'
  }
}

const getTypeIcon = (type: string) => {
  switch (type) {
    case 'встреча клуба': return '🤝'
    case 'поездка/круиз': return '🚗'
    case 'мастер-класс': return '🎓'
    case 'выезд на природу': return '🌲'
    case 'волонтёрство/благотворительность': return '❤️'
    default: return '📅'
  }
}

const getTypeShortLabel = (type: string) => {
  switch (type) {
    case 'встреча клуба': return 'Встреча'
    case 'поездка/круиз': return 'Поездка'
    case 'мастер-класс': return 'М-класс'
    case 'выезд на природу': return 'Природа'
    case 'волонтёрство/благотворительность': return 'Волонтёрство'
    default: return 'Событие'
  }
}

const getEventImage = (type: string) => {
  const images = {
    'встреча клуба': 'https://images.pexels.com/photos/1595385/pexels-photo-1595385.jpeg?auto=compress&cs=tinysrgb&w=200',
    'поездка/круиз': 'https://images.pexels.com/photos/1545743/pexels-photo-1545743.jpeg?auto=compress&cs=tinysrgb&w=200',
    'мастер-класс': 'https://images.pexels.com/photos/3806288/pexels-photo-3806288.jpeg?auto=compress&cs=tinysrgb&w=200',
    'выезд на природу': 'https://images.pexels.com/photos/1402787/pexels-photo-1402787.jpeg?auto=compress&cs=tinysrgb&w=200',
    'волонтёрство/благотворительность': 'https://images.pexels.com/photos/6646918/pexels-photo-6646918.jpeg?auto=compress&cs=tinysrgb&w=200'
  }
  return images[type as keyof typeof images] || images['встреча клуба']
}

const formatDateText = (dateString: string) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('ru-RU', {
    day: 'numeric',
    month: 'short'
  })
}

const formatDay = (dateString: string) => {
  const date = new Date(dateString)
  return date.getDate().toString().padStart(2, '0')
}

const formatMonth = (dateString: string) => {
  const date = new Date(dateString)
  const months = ['янв', 'фев', 'мар', 'апр', 'май', 'июн', 'июл', 'авг', 'сен', 'окт', 'ноя', 'дек']
  return months[date.getMonth()]
}

const setParticipation = (status: 'yes' | 'no' | 'maybe') => {
  telegramStore.hapticFeedback('selection')
  participation.value = status
  if (status !== 'yes') {
    plusOne.value = false
  }
  // TODO: Emit participation change
  console.log('Participation:', status)
}

const togglePlusOne = () => {
  telegramStore.hapticFeedback('selection')
  plusOne.value = !plusOne.value
  // TODO: Emit plus one change
  console.log('Plus one:', plusOne.value)
}
</script>

<style scoped>
.event-card-compact {
  display: flex;
  gap: 8px;
  padding: 8px;
  background: var(--card-bg);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-md);
  cursor: pointer;
  transition: all 0.2s ease;
  min-height: 70px;
}

.event-card-compact:hover {
  background: var(--hover-bg);
  border-color: var(--primary-color);
  transform: translateY(-1px);
}

/* Картинка события */
.event-image-container {
  position: relative;
  width: 80px;
  height: 70px;
  border-radius: var(--radius-lg);
  overflow: hidden;
  flex-shrink: 0;
}

.event-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.2s ease;
}

.event-card-compact:hover .event-image {
  transform: scale(1.05);
}

.event-date-overlay {
  position: absolute;
  top: 4px;
  left: 4px;
  background: rgba(0, 0, 0, 0.8);
  color: white;
  border-radius: var(--radius-sm);
  padding: 2px 4px;
  text-align: center;
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
}

.date-day {
  font-size: 10px;
  font-weight: var(--font-weight-bold);
  line-height: 1;
}

.date-month {
  font-size: 7px;
  font-weight: var(--font-weight-medium);
  text-transform: uppercase;
  opacity: 0.9;
  margin-top: 1px;
}

.event-status-overlay {
  position: absolute;
  top: 4px;
  right: 4px;
}

.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  border: 1px solid rgba(255, 255, 255, 0.8);
}

/* Основной контент */
.event-content-compact {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.event-header-compact {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 6px;
}

.event-title-compact {
  font-size: 13px;
  font-weight: var(--font-weight-semibold);
  color: var(--tg-theme-text-color);
  margin: 0;
  line-height: 1.2;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  flex: 1;
}

.type-badge-compact {
  font-size: 8px;
  padding: 2px 4px;
  border-radius: var(--radius-sm);
  display: flex;
  align-items: center;
  gap: 2px;
  flex-shrink: 0;
  font-weight: var(--font-weight-semibold);
  text-transform: uppercase;
  letter-spacing: 0.3px;
}

.type-meeting { background: rgba(46, 166, 255, 0.15); color: var(--primary-color); }
.type-trip { background: rgba(76, 175, 80, 0.15); color: var(--success-color); }
.type-workshop { background: rgba(255, 152, 0, 0.15); color: var(--warning-color); }
.type-nature { background: rgba(76, 175, 80, 0.15); color: #4caf50; }
.type-charity { background: rgba(233, 30, 99, 0.15); color: #e91e63; }

/* Дата и время текстом */
.event-datetime-text {
  display: flex;
  align-items: center;
  gap: 3px;
  font-size: 9px;
  color: var(--tg-theme-hint-color);
  font-weight: var(--font-weight-medium);
  margin-bottom: 2px;
}

.event-datetime-text svg {
  color: var(--tg-theme-hint-color);
  flex-shrink: 0;
}

.event-datetime-text span {
  color: var(--tg-theme-text-color);
}

/* Детали */
.event-details-compact {
  display: flex;
  gap: 8px;
  align-items: center;
  flex-wrap: wrap;
  margin-bottom: 4px;
}

.detail-item-compact {
  display: flex;
  align-items: center;
  gap: 3px;
  font-size: 10px;
  color: var(--tg-theme-hint-color);
}

.detail-item-compact svg {
  color: var(--tg-theme-hint-color);
  flex-shrink: 0;
}

.detail-item-compact span {
  font-weight: var(--font-weight-medium);
  color: var(--tg-theme-text-color);
}

.price-compact {
  margin-left: auto;
}

.price-value {
  color: var(--tg-theme-text-color);
  font-weight: var(--font-weight-bold);
}

.price-free {
  color: var(--success-color);
  font-weight: var(--font-weight-bold);
}

/* Участие */
.participation-section {
  display: flex;
  align-items: center;
  gap: 4px;
  margin-top: 3px;
}

.participation-label {
  font-size: 9px;
  color: var(--tg-theme-hint-color);
  font-weight: var(--font-weight-medium);
}

.participation-buttons {
  display: flex;
  gap: 3px;
  align-items: center;
}

.participation-btn {
  width: 18px;
  height: 18px;
  border: 1px solid var(--border-color);
  border-radius: var(--radius-sm);
  background: var(--card-bg);
  color: var(--tg-theme-hint-color);
  font-size: 9px;
  font-weight: var(--font-weight-bold);
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.participation-btn:hover {
  background: var(--hover-bg);
}

.participation-btn.yes.active {
  background: var(--success-color);
  border-color: var(--success-color);
  color: white;
}

.participation-btn.no.active {
  background: var(--error-color);
  border-color: var(--error-color);
  color: white;
}

.participation-btn.maybe.active {
  background: var(--warning-color);
  border-color: var(--warning-color);
  color: white;
}

.plus-one-btn {
  width: 22px;
  height: 18px;
  border: 1px solid var(--primary-color);
  border-radius: var(--radius-sm);
  background: var(--card-bg);
  color: var(--primary-color);
  font-size: 8px;
  font-weight: var(--font-weight-bold);
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.plus-one-btn:hover {
  background: rgba(46, 166, 255, 0.1);
}

.plus-one-btn.active {
  background: var(--primary-color);
  color: white;
}

@media (max-width: 480px) {
  .event-card-compact {
    padding: 6px;
    gap: 6px;
    min-height: 60px;
  }
  
  .event-image-container {
    width: 70px;
    height: 60px;
  }
  
  .date-day {
    font-size: 9px;
  }
  
  .date-month {
    font-size: 6px;
  }
  
  .event-title-compact {
    font-size: 12px;
  }
  
  .event-datetime-text {
    font-size: 8px;
  }
  
  .event-details-compact {
    gap: 6px;
  }
  
  .detail-item-compact {
    font-size: 9px;
  }
  
  .participation-label {
    font-size: 9px;
  }
  
  .participation-btn {
    width: 16px;
    height: 16px;
    font-size: 9px;
  }
  
  .plus-one-btn {
    width: 20px;
    height: 16px;
    font-size: 8px;
  }
}
</style>
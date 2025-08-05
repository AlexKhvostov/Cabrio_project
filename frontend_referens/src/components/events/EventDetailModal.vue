<template>
  <div v-if="show" class="modal-overlay" @click="$emit('close')">
    <div class="modal-content-compact" @click.stop>
      <div class="modal-header">
        <h2>{{ event.title }}</h2>
        <button class="modal-close" @click="$emit('close')">
          <X :size="24" />
        </button>
      </div>
      
      <div class="modal-body">
        <!-- Основная информация о событии -->
        <div class="event-main-info-mobile">
          <div class="date-time-row">
            <Calendar :size="16" />
            <span class="date-time-text">{{ formatDate(event.event_date) }}, {{ formatDayOfWeek(event.event_date) }} в {{ event.event_time }}</span>
          </div>
          
          <div class="event-location">
            <MapPin :size="16" />
            <div class="location-info">
              <div class="location-primary">{{ event.location }}</div>
              <div class="location-secondary">{{ event.city }}</div>
            </div>
          </div>
        </div>
        
        <!-- Статус и тип -->
        <div class="event-badges-compact">
          <div class="badge-group">
            <span class="badge-label">Тип события:</span>
            <span :class="['type-badge', `type-${getTypeColor(event.type)}`]">
              {{ getTypeIcon(event.type) }} {{ event.type }}
            </span>
          </div>
          <div class="badge-group">
            <span class="badge-label">Статус:</span>
            <span :class="['status-badge', `status-${getStatusColor(event.status)}`]">
              {{ getStatusIcon(event.status) }} {{ event.status }}
            </span>
          </div>
        </div>
        
        <!-- Описание -->
        <div class="event-description-compact">
          <h4>
            <FileText :size="16" />
            Описание
          </h4>
          <p>{{ event.description }}</p>
        </div>
        
        <!-- Участники -->
        <div class="participants-compact">
          <h4>
            <Users :size="16" />
            Участники
          </h4>
          <div class="participants-info">
            <div class="participants-count">
              <span class="count-current">{{ event.participants_count }}</span>
              <span class="count-separator">/</span>
              <span class="count-max">{{ event.max_participants }}</span>
              <span class="count-label">участников</span>
            </div>
            <div class="participants-progress">
              <div class="progress-bar">
                <div 
                  class="progress-fill" 
                  :style="{ width: `${(event.participants_count / event.max_participants) * 100}%` }"
                ></div>
              </div>
              <span class="progress-percent">
                {{ Math.round((event.participants_count / event.max_participants) * 100) }}%
              </span>
            </div>
          </div>
        </div>
        
        <!-- Стоимость -->
        <div class="price-compact">
          <h4>
            <CreditCard :size="16" />
            Стоимость участия
          </h4>
          <div class="price-info">
            <div v-if="event.price > 0" class="price-paid">
              <span class="price-amount">{{ event.price }} ₽</span>
              <span class="price-label">с участника</span>
            </div>
            <div v-else class="price-free">
              <span class="price-amount">Бесплатно</span>
              <span class="price-label">участие без оплаты</span>
            </div>
          </div>
        </div>
        
        <!-- Организатор -->
        <div class="organizer-compact">
          <h4>
            <User :size="16" />
            Организатор
          </h4>
          <div class="organizer-info">
            <div class="organizer-avatar">
              <span class="avatar-initials">
                {{ getOrganizerInitials() }}
              </span>
            </div>
            <div class="organizer-details">
              <div class="organizer-name">{{ event.organizer_name }}</div>
              <div v-if="event.organizer_nickname" class="organizer-nickname">
                @{{ event.organizer_nickname }}
              </div>
            </div>
          </div>
        </div>
        
        <!-- Фотографии -->
        <div v-if="event.photos && event.photos.length > 0" class="photos-compact">
          <h4>
            <Camera :size="16" />
            Фотографии ({{ event.photos.length }})
          </h4>
          <div class="photos-grid">
            <img
              v-for="(photo, index) in event.photos"
              :key="index"
              :src="photo"
              :alt="`Фото события ${index + 1}`"
              class="event-photo"
              @click="openPhotoViewer(photo)"
            />
          </div>
        </div>
        
        <!-- Дополнительная информация -->
        <div class="additional-info-compact">
          <div class="info-item">
            <span class="info-label">Создано:</span>
            <span class="info-value">{{ formatFullDate(event.created_at) }}</span>
          </div>
        </div>
      </div>
      
      <!-- Действия -->
      <div class="participation-section">
        <div class="participation-header">
          <span class="participation-label">Участие:</span>
          <div class="participation-buttons-row">
            <button class="participation-btn-compact yes" :class="{ active: participation === 'yes' }" @click="setParticipation('yes')">
              <Check :size="14" />
              Да
            </button>
            <button class="participation-btn-compact maybe" :class="{ active: participation === 'maybe' }" @click="setParticipation('maybe')">
              <HelpCircle :size="14" />
              Возможно
            </button>
            <button class="participation-btn-compact no" :class="{ active: participation === 'no' }" @click="setParticipation('no')">
              <X :size="14" />
              Нет
            </button>
            <button v-if="participation === 'yes'" class="plus-one-btn-compact" :class="{ active: plusOne }" @click="togglePlusOne">
              <UserPlus :size="14" />
              +1
            </button>
          </div>
        </div>
      </div>
      
      <div class="modal-footer">
        <button class="btn-secondary" @click="shareEvent">
          <Share2 :size="16" />
          Поделиться
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { 
  X, 
  Calendar, 
  Clock, 
  MapPin, 
  FileText, 
  Users, 
  CreditCard, 
  User, 
  Camera,
  UserPlus,
  Share2,
  Check,
  HelpCircle
} from 'lucide-vue-next'
import type { Event } from '@/stores/data'
import { useTelegramStore } from '@/stores/telegram'

interface Props {
  show: boolean
  event: Event
}

const props = defineProps<Props>()
defineEmits<{
  close: []
  join: [event: Event]
}>()

const telegramStore = useTelegramStore()

const participation = ref<'yes' | 'no' | 'maybe' | null>(null)
const plusOne = ref(false)

const getStatusColor = (status: string) => {
  switch (status) {
    case 'запланировано': return 'primary'
    case 'завершено': return 'success'
    case 'отменено': return 'error'
    default: return 'primary'
  }
}

const getStatusIcon = (status: string) => {
  switch (status) {
    case 'запланировано': return '⏳'
    case 'завершено': return '✓'
    case 'отменено': return '✗'
    default: return '⏳'
  }
}

const getTypeColor = (type: string) => {
  switch (type) {
    case 'встреча клуба': return 'primary'
    case 'поездка/круиз': return 'success'
    case 'мастер-класс': return 'warning'
    case 'выезд на природу': return 'nature'
    case 'волонтёрство/благотворительность': return 'charity'
    default: return 'primary'
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

const formatDate = (dateString: string) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('ru-RU', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
}

const formatDayOfWeek = (dateString: string) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('ru-RU', { weekday: 'long' })
}

const formatFullDate = (dateString: string) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('ru-RU', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getOrganizerInitials = () => {
  const names = props.event.organizer_name.split(' ')
  return (names[0]?.charAt(0) + (names[1]?.charAt(0) || '')).toUpperCase()
}

const setParticipation = (status: 'yes' | 'no' | 'maybe') => {
  telegramStore.hapticFeedback('selection')
  participation.value = status
  if (status !== 'yes') {
    plusOne.value = false
  }
  console.log('Participation:', status)
}

const togglePlusOne = () => {
  telegramStore.hapticFeedback('selection')
  plusOne.value = !plusOne.value
  console.log('Plus one:', plusOne.value)
}

const joinEvent = () => {
  telegramStore.hapticFeedback('notification')
  // TODO: Implement join event logic
  console.log('Joining event:', props.event)
}

const shareEvent = () => {
  telegramStore.hapticFeedback('selection')
  // TODO: Implement share event logic
  console.log('Sharing event:', props.event)
}

const openPhotoViewer = (photo: string) => {
  telegramStore.hapticFeedback('selection')
  // TODO: Open photo viewer
  console.log('Opening photo:', photo)
}
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: var(--spacing-md);
  backdrop-filter: blur(5px);
  -webkit-backdrop-filter: blur(5px);
}

.modal-content-compact {
  background: var(--card-bg);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-lg);
  width: 100%;
  max-width: 600px;
  max-height: 90vh;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: var(--spacing-lg);
  border-bottom: 1px solid var(--border-color);
}

.modal-header h2 {
  font-size: var(--font-size-xl);
  font-weight: var(--font-weight-semibold);
  color: var(--tg-theme-text-color);
  margin: 0;
  flex: 1;
  padding-right: var(--spacing-md);
}

.modal-close {
  background: none;
  border: none;
  color: var(--tg-theme-hint-color);
  cursor: pointer;
  padding: var(--spacing-xs);
  border-radius: var(--radius-sm);
  transition: all 0.2s ease;
  flex-shrink: 0;
}

.modal-close:hover {
  background: var(--hover-bg);
  color: var(--tg-theme-text-color);
}

.modal-body {
  flex: 1;
  overflow-y: auto;
  padding: var(--spacing-md);
  display: flex;
  flex-direction: column;
  gap: var(--spacing-md);
}

/* Основная информация */
.event-main-info-mobile {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-sm);
}

.date-time-row {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
  padding: var(--spacing-xs) var(--spacing-sm);
  background: rgba(46, 166, 255, 0.1);
  border: 1px solid rgba(46, 166, 255, 0.2);
  border-radius: var(--radius-sm);
}

.date-time-row svg {
  color: var(--primary-color);
  flex-shrink: 0;
}

.date-time-text {
  font-size: var(--font-size-xs);
  font-weight: var(--font-weight-semibold);
  color: var(--tg-theme-text-color);
  line-height: 1.3;
}

.event-location {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
  padding: var(--spacing-xs) var(--spacing-sm);
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-sm);
}

.event-location svg {
  color: var(--primary-color);
  flex-shrink: 0;
}

.location-primary {
  font-size: var(--font-size-xs);
  font-weight: var(--font-weight-semibold);
  color: var(--tg-theme-text-color);
  line-height: 1.2;
}

.location-secondary {
  font-size: var(--font-size-xs);
  color: var(--tg-theme-hint-color);
  line-height: 1;
}

/* Бейджи */
.event-badges-compact {
  display: flex;
  flex-wrap: wrap;
  gap: var(--spacing-sm);
}

.badge-group {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
}

.badge-label {
  font-size: var(--font-size-xs);
  color: var(--tg-theme-hint-color);
  font-weight: var(--font-weight-medium);
}

.type-badge {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: var(--spacing-xs) var(--spacing-sm);
  border-radius: var(--radius-sm);
  font-size: var(--font-size-xs);
  font-weight: var(--font-weight-medium);
}

.type-primary { background: rgba(46, 166, 255, 0.15); color: var(--primary-color); }
.type-success { background: rgba(76, 175, 80, 0.15); color: var(--success-color); }
.type-warning { background: rgba(255, 152, 0, 0.15); color: var(--warning-color); }
.type-nature { background: rgba(76, 175, 80, 0.15); color: #4caf50; }
.type-charity { background: rgba(233, 30, 99, 0.15); color: #e91e63; }

.status-badge {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: var(--spacing-xs) var(--spacing-sm);
  border-radius: var(--radius-sm);
  font-size: var(--font-size-xs);
  font-weight: var(--font-weight-medium);
}

.status-primary { background: rgba(46, 166, 255, 0.15); color: var(--primary-color); }
.status-success { background: rgba(76, 175, 80, 0.15); color: var(--success-color); }
.status-error { background: rgba(244, 67, 54, 0.15); color: var(--error-color); }

/* Секции */
.event-description-compact,
.participants-compact,
.price-compact,
.organizer-compact,
.photos-compact {
  border-top: 1px solid var(--border-color);
  padding-top: var(--spacing-sm);
}

.event-description-compact h4,
.participants-compact h4,
.price-compact h4,
.organizer-compact h4,
.photos-compact h4 {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
  font-size: var(--font-size-xs);
  font-weight: var(--font-weight-semibold);
  color: var(--tg-theme-text-color);
  margin: 0 0 var(--spacing-sm) 0;
}

.event-description-compact h4 svg,
.participants-compact h4 svg,
.price-compact h4 svg,
.organizer-compact h4 svg,
.photos-compact h4 svg {
  color: var(--primary-color);
}

.event-description-compact p {
  font-size: var(--font-size-xs);
  color: var(--tg-theme-text-color);
  line-height: 1.6;
  margin: 0;
}

/* Участники */
.participants-info {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-sm);
}

.participants-count {
  display: flex;
  align-items: baseline;
  gap: var(--spacing-xs);
}

.count-current {
  font-size: var(--font-size-md);
  font-weight: var(--font-weight-bold);
  color: var(--primary-color);
}

.count-separator {
  font-size: var(--font-size-sm);
  color: var(--tg-theme-hint-color);
}

.count-max {
  font-size: var(--font-size-sm);
  font-weight: var(--font-weight-semibold);
  color: var(--tg-theme-text-color);
}

.count-label {
  font-size: var(--font-size-xs);
  color: var(--tg-theme-hint-color);
  margin-left: var(--spacing-sm);
}

.participants-progress {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
}

.progress-bar {
  flex: 1;
  height: 6px;
  background: rgba(46, 166, 255, 0.2);
  border-radius: 3px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: var(--primary-color);
  border-radius: 3px;
  transition: width 0.3s ease;
}

.progress-percent {
  font-size: var(--font-size-xs);
  font-weight: var(--font-weight-semibold);
  color: var(--primary-color);
  min-width: 35px;
  text-align: right;
}

/* Стоимость */
.price-info {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
}

.price-paid,
.price-free {
  display: flex;
  align-items: baseline;
  gap: var(--spacing-sm);
}

.price-amount {
  font-size: var(--font-size-md);
  font-weight: var(--font-weight-bold);
}

.price-paid .price-amount {
  color: var(--tg-theme-text-color);
}

.price-free .price-amount {
  color: var(--success-color);
}

.price-label {
  font-size: var(--font-size-xs);
  color: var(--tg-theme-hint-color);
}

/* Организатор */
.organizer-info {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
}

.organizer-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: var(--primary-color);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.avatar-initials {
  color: white;
  font-weight: var(--font-weight-bold);
  font-size: var(--font-size-xs);
}

.organizer-details {
  flex: 1;
}

.organizer-name {
  font-size: var(--font-size-xs);
  font-weight: var(--font-weight-semibold);
  color: var(--tg-theme-text-color);
  margin-bottom: 2px;
}

.organizer-nickname {
  font-size: var(--font-size-xs);
  color: var(--primary-color);
}

/* Фотографии */
.photos-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
  gap: var(--spacing-sm);
}

.event-photo {
  width: 100%;
  height: 60px;
  object-fit: cover;
  border-radius: var(--radius-sm);
  cursor: pointer;
  transition: all 0.2s ease;
}

.event-photo:hover {
  transform: scale(1.02);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

/* Дополнительная информация */
.additional-info-compact {
  border-top: 1px solid var(--border-color);
  padding-top: var(--spacing-sm);
}

.info-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: var(--spacing-xs) 0;
}

.info-label {
  font-size: var(--font-size-xs);
  color: var(--tg-theme-hint-color);
  font-weight: var(--font-weight-medium);
}

.info-value {
  font-size: var(--font-size-xs);
  color: var(--tg-theme-text-color);
  font-weight: var(--font-weight-medium);
}

/* Участие */
.participation-section {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-md);
  padding: var(--spacing-sm);
}

.participation-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: var(--spacing-sm);
}

.participation-label {
  font-size: var(--font-size-xs);
  font-weight: var(--font-weight-semibold);
  color: var(--tg-theme-text-color);
  flex-shrink: 0;
}

.participation-buttons-row {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
  flex: 1;
  justify-content: flex-end;
}

.participation-btn-compact {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: var(--spacing-xs);
  padding: 4px 8px;
  border: 1px solid var(--border-color);
  border-radius: var(--radius-sm);
  background: var(--card-bg);
  color: var(--tg-theme-text-color);
  font-size: var(--font-size-xs);
  font-weight: var(--font-weight-semibold);
  cursor: pointer;
  transition: all 0.2s ease;
  min-width: 50px;
}

.participation-btn-compact:hover {
  background: var(--hover-bg);
}

.participation-btn-compact.yes.active {
  background: var(--success-color);
  border-color: var(--success-color);
  color: white;
}

.participation-btn-compact.no.active {
  background: var(--error-color);
  border-color: var(--error-color);
  color: white;
}

.participation-btn-compact.maybe.active {
  background: var(--warning-color);
  border-color: var(--warning-color);
  color: white;
}

.plus-one-btn-compact {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: var(--spacing-xs);
  padding: 4px 8px;
  border: 1px solid var(--primary-color);
  border-radius: var(--radius-sm);
  background: var(--card-bg);
  color: var(--primary-color);
  font-size: var(--font-size-xs);
  font-weight: var(--font-weight-semibold);
  cursor: pointer;
  transition: all 0.2s ease;
  min-width: 40px;
}

.plus-one-btn-compact:hover {
  background: rgba(46, 166, 255, 0.1);
}

.plus-one-btn-compact.active {
  background: var(--primary-color);
  color: white;
}

/* Footer */
.modal-footer {
  padding: var(--spacing-md);
  border-top: 1px solid var(--border-color);
}

.btn-secondary {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: var(--spacing-sm);
  padding: var(--spacing-xs) var(--spacing-sm);
  border: none;
  border-radius: var(--radius-sm);
  font-size: var(--font-size-xs);
  font-weight: var(--font-weight-semibold);
  cursor: pointer;
  transition: all 0.2s ease;
  background: var(--card-bg);
  color: var(--tg-theme-text-color);
  border: 1px solid var(--border-color);
}

.btn-secondary:hover {
  background: var(--hover-bg);
}

@media (max-width: 480px) {
  .modal-content-compact {
    margin: var(--spacing-sm);
    max-height: calc(100vh - 32px);
  }
  
  .modal-body {
    padding: var(--spacing-sm);
    gap: var(--spacing-sm);
  }
  
  .event-badges-compact {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .photos-grid {
    grid-template-columns: repeat(3, 1fr);
  }
  
  .participation-header {
    flex-direction: column;
    align-items: stretch;
    gap: var(--spacing-sm);
  }
  
  .participation-buttons-row {
    justify-content: center;
  }
}
</style>
<template>
  <div v-if="show" class="modal-overlay" @click="$emit('close')">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <h2>Профиль участника</h2>
        <button class="modal-close" @click="$emit('close')">
          <X :size="24" />
        </button>
      </div>
      
      <div class="modal-body">
        <div class="member-profile">
          <div class="profile-avatar-compact">
            <img
              v-if="member.photo_url"
              :src="member.photo_url"
              :alt="member.first_name"
              class="avatar-image"
            />
            <span v-else class="avatar-initials">
              {{ getInitials(member.first_name, member.last_name) }}
            </span>
            
            <!-- Индикаторы -->
            <div :class="['status-indicator', `status-${getStatusColor(member.status)}`]">
              ●
            </div>
            <div :class="['role-indicator', `role-${getRoleColor(member.role)}`]">
              ★
            </div>
            <div v-if="member.flags.includes('почётный участник')" class="honor-indicator">
              ♦
            </div>
          </div>
          
          <div class="profile-info-compact">
            <h3 class="profile-name-compact">
              {{ member.first_name }} {{ member.last_name }}
            </h3>
            <p v-if="member.nickname" class="profile-nickname-compact">
              @{{ member.nickname }}
            </p>
            
            <div class="profile-badges-compact">
              <span :class="['badge', `badge-${getStatusColor(member.status)}`]">
                {{ member.status }}
              </span>
              <span :class="['badge', `badge-${getRoleColor(member.role)}`]">
                {{ member.role }}
              </span>
              <span v-if="member.flags.includes('почётный участник')" class="badge badge-honor">
                Почётный участник
              </span>
            </div>
          </div>
        </div>
        
        <div class="profile-details-compact">
          <div class="detail-section-compact">
            <h4>
              <Info :size="16" />
              Основная информация
            </h4>
            <div class="detail-grid-compact">
              <div class="detail-item-compact">
                <span class="detail-label">Telegram ID:</span>
                <span class="detail-value">{{ member.telegram_id }}</span>
              </div>
              <div class="detail-item-compact">
                <span class="detail-label">Город:</span>
                <span class="detail-value">{{ member.city }}</span>
              </div>
              <div class="detail-item-compact">
                <span class="detail-label">Дата вступления:</span>
                <span class="detail-value">{{ formatFullDate(member.join_date) }}</span>
              </div>
              <div class="detail-item-compact">
                <span class="detail-label">Сообщений:</span>
                <span class="detail-value">{{ member.message_count }}</span>
              </div>
            </div>
          </div>
          
          <div v-if="member.cars.length > 0" class="detail-section-compact">
            <h4>
              <Car :size="16" />
              Автомобили ({{ member.cars.length }})
            </h4>
            <div class="cars-grid-modal">
              <CarCard
                v-for="car in member.cars"
                :key="car.id"
                :car="car"
                @select="$emit('selectCar', car)"
              />
            </div>
          </div>
          
          <div v-if="member.flags.length > 0" class="detail-section-compact">
            <h4>
              <Flag :size="16" />
              Флаги
            </h4>
            <div class="flags-list-compact">
              <span
                v-for="flag in member.flags"
                :key="flag"
                :class="['flag-badge', { 'flag-honor': flag === 'почётный участник' }]"
              >
                {{ flag }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { X, Info, Car, Flag } from 'lucide-vue-next'
import type { Member, Car as CarType } from '@/stores/data'
import CarCard from '@/components/cars/CarCard.vue'

interface Props {
  show: boolean
  member: Member
}

defineProps<Props>()
defineEmits<{
  close: []
  selectCar: [car: CarType]
}>()

const getInitials = (firstName: string, lastName?: string) => {
  const first = firstName.charAt(0).toUpperCase()
  const last = lastName ? lastName.charAt(0).toUpperCase() : ''
  return first + last
}

const getStatusColor = (status: string) => {
  switch (status) {
    case 'активный': return 'success'
    case 'зарегистрирован': return 'warning'
    case 'новый': return 'primary'
    case 'вышедший': return 'error'
    default: return 'primary'
  }
}

const getRoleColor = (role: string) => {
  switch (role) {
    case 'участник': return 'secondary'
    case 'модератор': return 'primary'
    case 'администратор': return 'warning'
    case 'root': return 'error'
    default: return 'secondary'
  }
}

const getCarStatusColor = (status: string) => {
  switch (status) {
    case 'активный': return 'success'
    case 'потенциальный': return 'warning'
    case 'в ожидании': return 'primary'
    default: return 'error'
  }
}

const formatFullDate = (dateString: string) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('ru-RU', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
}

const selectCar = (car: CarType) => {
  // TODO: Implement car selection
  console.log('Selected car:', car)
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

.modal-content {
  background: var(--card-bg);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-lg);
  width: 100%;
  max-width: 500px;
  max-height: 90vh;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.modal-header {
  display: flex;
  justify-content: between;
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
}

.modal-close {
  background: none;
  border: none;
  color: var(--tg-theme-hint-color);
  cursor: pointer;
  padding: var(--spacing-xs);
  border-radius: var(--radius-sm);
  transition: all 0.2s ease;
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

.member-profile {
  display: flex;
  gap: var(--spacing-md);
  align-items: center;
}

.profile-avatar-compact {
  position: relative;
  width: 64px;
  height: 64px;
  border-radius: 50%;
  overflow: visible;
  background: var(--primary-color);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.avatar-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 50%;
}

.avatar-initials {
  color: white;
  font-weight: var(--font-weight-bold);
  font-size: var(--font-size-xl);
}

.status-indicator {
  position: absolute;
  bottom: -2px;
  right: -2px;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: var(--card-bg);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  border: 2px solid var(--card-bg);
}

.status-active { color: #4caf50; }
.status-registered { color: #ff9800; }
.status-new { color: #2196f3; }
.status-left { color: #f44336; }

.role-indicator {
  position: absolute;
  top: -4px;
  right: -4px;
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  text-shadow: 0 0 2px rgba(0,0,0,0.5);
}

.role-member { color: rgba(255, 255, 255, 0.3); }
.role-moderator { color: #ffffff; }
.role-admin { color: #ffd700; }
.role-root { color: #ff4444; }

.honor-indicator {
  position: absolute;
  top: -4px;
  left: -4px;
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  color: #00bcd4;
  text-shadow: 0 0 4px rgba(0, 188, 212, 0.5);
}

.profile-info-compact {
  flex: 1;
}

.profile-name-compact {
  font-size: var(--font-size-lg);
  font-weight: var(--font-weight-bold);
  color: var(--tg-theme-text-color);
  margin: 0 0 4px 0;
}

.profile-nickname-compact {
  font-size: var(--font-size-sm);
  color: var(--primary-color);
  margin: 0 0 var(--spacing-sm) 0;
}

.profile-badges-compact {
  display: flex;
  flex-wrap: wrap;
  gap: var(--spacing-xs);
}

.badge {
  padding: var(--spacing-xs) var(--spacing-sm);
  border-radius: var(--radius-sm);
  font-size: var(--font-size-xs);
  font-weight: var(--font-weight-medium);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.badge-success {
  background: rgba(76, 175, 80, 0.2);
  color: var(--success-color);
}

.badge-warning {
  background: rgba(255, 152, 0, 0.2);
  color: var(--warning-color);
}

.badge-primary {
  background: rgba(46, 166, 255, 0.2);
  color: var(--primary-color);
}

.badge-error {
  background: rgba(244, 67, 54, 0.2);
  color: var(--error-color);
}

.badge-secondary {
  background: rgba(158, 158, 158, 0.2);
  color: var(--tg-theme-hint-color);
}

.badge-honor {
  background: rgba(0, 188, 212, 0.2);
  color: #00bcd4;
}

.profile-details-compact {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-md);
}

.detail-section-compact {
  border-top: 1px solid var(--border-color);
  padding-top: var(--spacing-sm);
}

.detail-section-compact h4 {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
  font-size: var(--font-size-sm);
  font-weight: var(--font-weight-semibold);
  color: var(--tg-theme-text-color);
  margin: 0 0 var(--spacing-sm) 0;
}

.detail-section-compact h4 svg {
  color: var(--primary-color);
}

.detail-grid-compact {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: var(--spacing-sm);
}

.detail-item-compact {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-xs);
}

.detail-label {
  font-size: var(--font-size-sm);
  color: var(--tg-theme-hint-color);
  font-weight: var(--font-weight-medium);
}

.detail-value {
  font-size: var(--font-size-sm);
  color: var(--tg-theme-text-color);
  font-weight: var(--font-weight-medium);
}

.cars-grid-modal {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: var(--spacing-sm);
}

.flags-list-compact {
  display: flex;
  flex-wrap: wrap;
  gap: var(--spacing-xs);
}

.flag-badge {
  padding: var(--spacing-xs) var(--spacing-sm);
  background: rgba(255, 152, 0, 0.2);
  color: var(--warning-color);
  border-radius: var(--radius-sm);
  font-size: var(--font-size-xs);
  font-weight: var(--font-weight-medium);
}

.flag-honor {
  background: rgba(0, 188, 212, 0.2);
  color: #00bcd4;
}

@media (max-width: 480px) {
  .modal-content {
    margin: var(--spacing-sm);
    max-height: calc(100vh - 32px);
  }
  
  .modal-body {
    padding: var(--spacing-sm);
    gap: var(--spacing-sm);
  }
  
  .member-profile {
    flex-direction: column;
    text-align: center;
    gap: var(--spacing-sm);
  }
  
  .detail-grid-compact {
    grid-template-columns: 1fr;
  }
  
  .cars-grid-modal {
    grid-template-columns: 1fr;
  }
}
</style>
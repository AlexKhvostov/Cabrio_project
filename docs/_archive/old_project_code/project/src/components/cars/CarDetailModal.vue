<template>
  <div v-if="show" class="modal-overlay" @click="$emit('close')">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <h2>{{ car.brand }} {{ car.model }}</h2>
        <button class="modal-close" @click="$emit('close')">
          <X :size="24" />
        </button>
      </div>
      
      <div class="modal-body">
        <!-- Фотографии автомобиля -->
        <div v-if="car.photos.length > 0" class="car-photos">
          <div class="main-photo-compact">
            <img
              :src="car.photos[currentPhotoIndex]"
              :alt="`${car.brand} ${car.model}`"
              class="main-image"
            />
            <div v-if="car.photos.length > 1" class="photo-nav">
              <button
                class="nav-btn prev"
                :disabled="currentPhotoIndex === 0"
                @click="prevPhoto"
              >
                <ChevronLeft :size="16" />
              </button>
              <button
                class="nav-btn next"
                :disabled="currentPhotoIndex === car.photos.length - 1"
                @click="nextPhoto"
              >
                <ChevronRight :size="16" />
              </button>
            </div>
            <div v-if="car.photos.length > 1" class="photo-counter">
              {{ currentPhotoIndex + 1 }} / {{ car.photos.length }}
            </div>
          </div>
          
          <div v-if="car.photos.length > 1" class="photo-thumbnails-compact">
            <img
              v-for="(photo, index) in car.photos"
              :key="index"
              :src="photo"
              :alt="`${car.brand} ${car.model} - фото ${index + 1}`"
              class="thumbnail-compact"
              :class="{ active: index === currentPhotoIndex }"
              @click="currentPhotoIndex = index"
            />
          </div>
        </div>
        
        <!-- Информация об автомобиле -->
        <div class="car-info-section-compact">
          <div class="car-header-compact">
            <div class="car-title-compact">
              <h3 class="car-name-compact">{{ car.brand }} {{ car.model }}</h3>
              <span :class="['status-badge', `status-${getStatusColor(car.status)}`]">
                {{ car.status }}
              </span>
            </div>
            <div class="car-number-compact">{{ car.reg_number }}</div>
          </div>
          
          <div class="car-specs-compact">
            <div class="spec-item-compact">
              <Calendar :size="14" />
              <div>
                <span class="spec-label">Год выпуска</span>
                <span class="spec-value">{{ car.year }}</span>
              </div>
            </div>
            <div class="spec-item-compact">
              <Palette :size="14" />
              <div>
                <span class="spec-label">Цвет</span>
                <span class="spec-value">{{ car.color }}</span>
              </div>
            </div>
            <div class="spec-item-compact">
              <Gauge :size="14" />
              <div>
                <span class="spec-label">Объем двигателя</span>
                <span class="spec-value">{{ car.engine_volume }}л</span>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Информация о владельце -->
        <div class="owner-section-compact">
          <h4>
            <User :size="16" />
            Владелец
          </h4>
          <MemberCard
            v-if="ownerData"
            :member="ownerData"
            @select="$emit('selectMember', ownerData)"
          />
          <div v-else class="owner-not-found">
            <User :size="24" />
            <span>Владелец не найден</span>
          </div>
        </div>
        
        <!-- Дополнительные пилоты -->
        <div v-if="car.second_pilots && car.second_pilots.length > 0" class="pilots-section-compact">
          <h4>
            <Users :size="16" />
            Дополнительные пилоты ({{ car.second_pilots.length }})
          </h4>
          <div class="pilots-list-compact">
            <div
              v-for="pilot in car.second_pilots"
              :key="pilot.id"
              class="pilot-item-compact"
            >
              <div class="pilot-avatar-compact">
                <img
                  v-if="pilot.photo_url"
                  :src="pilot.photo_url"
                  :alt="pilot.first_name"
                  class="avatar-image"
                />
                <span v-else class="avatar-initials">
                  {{ getInitials(pilot.first_name, pilot.last_name) }}
                </span>
              </div>
              <div class="pilot-info-compact">
                <span class="pilot-name-compact">{{ pilot.first_name }} {{ pilot.last_name }}</span>
                <span v-if="pilot.nickname" class="pilot-nickname-compact">@{{ pilot.nickname }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { 
  X, 
  ChevronLeft, 
  ChevronRight, 
  Calendar, 
  Palette, 
  Gauge, 
  User, 
  MapPin, 
  MessageCircle, 
  Users 
} from 'lucide-vue-next'
import type { Car, Member } from '@/stores/data'
import { useDataStore } from '@/stores/data'
import MemberCard from '@/components/members/MemberCard.vue'

interface Props {
  show: boolean
  car: Car
}

const props = defineProps<Props>()
defineEmits<{
  close: []
  selectMember: [member: Member]
}>()

const dataStore = useDataStore()
const currentPhotoIndex = ref(0)

const ownerData = computed(() => {
  return dataStore.members.find(member => 
    member.first_name + ' ' + member.last_name === props.car.owner_name ||
    member.nickname === props.car.owner_nickname
  )
})

const getStatusColor = (status: string) => {
  switch (status) {
    case 'активный': return 'success'
    case 'потенциальный': return 'warning'
    case 'в ожидании': return 'primary'
    default: return 'error'
  }
}

const getOwnerStatusColor = (status: string) => {
  switch (status) {
    case 'активный': return 'active'
    case 'зарегистрирован': return 'registered'
    case 'новый': return 'new'
    case 'вышедший': return 'left'
    default: return 'new'
  }
}

const getOwnerRoleColor = (role: string) => {
  switch (role) {
    case 'участник': return 'member'
    case 'модератор': return 'moderator'
    case 'администратор': return 'admin'
    case 'root': return 'root'
    default: return 'member'
  }
}

const getOwnerInitials = () => {
  const names = props.car.owner_name?.split(' ') || ['', '']
  return (names[0]?.charAt(0) + (names[1]?.charAt(0) || '')).toUpperCase()
}

const getInitials = (firstName: string, lastName?: string) => {
  const first = firstName.charAt(0).toUpperCase()
  const last = lastName ? lastName.charAt(0).toUpperCase() : ''
  return first + last
}

const formatDate = (dateString: string) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('ru-RU', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  })
}

const prevPhoto = () => {
  if (currentPhotoIndex.value > 0) {
    currentPhotoIndex.value--
  }
}

const nextPhoto = () => {
  if (currentPhotoIndex.value < props.car.photos.length - 1) {
    currentPhotoIndex.value++
  }
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

.car-photos {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-sm);
}

.main-photo-compact {
  position: relative;
  width: 100%;
  height: 200px;
  border-radius: var(--radius-lg);
  overflow: hidden;
}

.main-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.photo-nav {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  width: 100%;
  display: flex;
  justify-content: space-between;
  padding: 0 var(--spacing-md);
  pointer-events: none;
}

.nav-btn {
  background: rgba(0, 0, 0, 0.8);
  color: white;
  border: none;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  pointer-events: all;
}

.nav-btn:hover:not(:disabled) {
  background: rgba(0, 0, 0, 0.9);
  transform: scale(1.1);
}

.nav-btn:disabled {
  opacity: 0.3;
  cursor: not-allowed;
}

.photo-counter {
  position: absolute;
  bottom: var(--spacing-md);
  left: 50%;
  transform: translateX(-50%);
  background: rgba(0, 0, 0, 0.8);
  color: white;
  padding: var(--spacing-xs) var(--spacing-sm);
  border-radius: var(--radius-lg);
  font-size: var(--font-size-sm);
  font-weight: var(--font-weight-medium);
}

.photo-thumbnails-compact {
  display: flex;
  gap: var(--spacing-sm);
  overflow-x: auto;
  padding: var(--spacing-xs) 0;
}

.thumbnail-compact {
  width: 48px;
  height: 48px;
  border-radius: var(--radius-sm);
  object-fit: cover;
  cursor: pointer;
  border: 2px solid transparent;
  transition: all 0.2s ease;
  flex-shrink: 0;
}

.thumbnail-compact:hover {
  border-color: var(--primary-color);
  transform: scale(1.05);
}

.thumbnail-compact.active {
  border-color: var(--primary-color);
}

.car-info-section-compact {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-sm);
}

.car-header-compact {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: var(--spacing-sm);
}

.car-title-compact {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-sm);
}

.car-name-compact {
  font-size: var(--font-size-lg);
  font-weight: var(--font-weight-bold);
  color: var(--tg-theme-text-color);
  margin: 0;
}

.status-badge {
  padding: var(--spacing-xs) var(--spacing-sm);
  border-radius: var(--radius-sm);
  font-size: var(--font-size-xs);
  font-weight: var(--font-weight-medium);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  align-self: flex-start;
}

.status-success {
  background: rgba(76, 175, 80, 0.2);
  color: var(--success-color);
}

.status-warning {
  background: rgba(255, 152, 0, 0.2);
  color: var(--warning-color);
}

.status-primary {
  background: rgba(46, 166, 255, 0.2);
  color: var(--primary-color);
}

.status-error {
  background: rgba(244, 67, 54, 0.2);
  color: var(--error-color);
}

.car-number-compact {
  background: var(--border-color);
  color: var(--tg-theme-text-color);
  padding: var(--spacing-xs) var(--spacing-sm);
  border-radius: var(--radius-md);
  font-size: var(--font-size-sm);
  font-family: monospace;
  font-weight: var(--font-weight-bold);
  text-align: center;
  min-width: 100px;
}

.car-specs-compact {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: var(--spacing-sm);
}

.spec-item-compact {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
  padding: var(--spacing-sm);
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-md);
}

.spec-item-compact svg {
  color: var(--primary-color);
  flex-shrink: 0;
}

.spec-item-compact div {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.spec-label {
  font-size: var(--font-size-xs);
  color: var(--tg-theme-hint-color);
  font-weight: var(--font-weight-medium);
}

.spec-value {
  font-size: var(--font-size-sm);
  color: var(--tg-theme-text-color);
  font-weight: var(--font-weight-semibold);
}

.owner-section-compact,
.pilots-section-compact {
  border-top: 1px solid var(--border-color);
  padding-top: var(--spacing-sm);
}

.owner-section-compact h4,
.pilots-section-compact h4 {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
  font-size: var(--font-size-sm);
  font-weight: var(--font-weight-semibold);
  color: var(--tg-theme-text-color);
  margin: 0 0 var(--spacing-sm) 0;
}

.owner-section-compact h4 svg,
.pilots-section-compact h4 svg {
  color: var(--primary-color);
}

.owner-not-found {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: var(--spacing-sm);
  padding: var(--spacing-lg);
  color: var(--tg-theme-hint-color);
  text-align: center;
}

.pilots-list-compact {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-sm);
}

.pilot-item-compact {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
  padding: var(--spacing-sm);
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-md);
}

.pilot-avatar-compact {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  overflow: hidden;
  background: var(--primary-color);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.pilot-info-compact {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.pilot-name-compact {
  font-size: var(--font-size-sm);
  font-weight: var(--font-weight-medium);
  color: var(--tg-theme-text-color);
}

.pilot-nickname-compact {
  font-size: var(--font-size-xs);
  color: var(--primary-color);
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
  
  .main-photo-compact {
    height: 160px;
  }
  
  .car-header-compact {
    flex-direction: column;
    align-items: stretch;
    gap: var(--spacing-sm);
  }
  
  .car-number-compact {
    text-align: center;
    min-width: auto;
  }
  
  .car-specs-compact {
    grid-template-columns: 1fr;
  }
  
  .nav-btn {
    width: 32px;
    height: 32px;
  }
}
</style>
<template>
  <div class="car-card-compact" @click="$emit('select', car)">
    <div class="car-image-container">
      <img
        v-if="car.photos.length > 0"
        :src="car.photos[0]"
        :alt="`${car.brand} ${car.model}`"
        class="car-image"
      />
      <div v-else class="car-placeholder">
        <Car :size="24" />
      </div>
      
      <!-- Индикатор количества фото -->
      <div v-if="car.photos.length > 1" class="photos-count">
        <Camera :size="10" />
        {{ car.photos.length }}
      </div>
      
      <!-- Статус автомобиля -->
      <div class="car-status-overlay">
        <span :class="['status-badge', `status-${getStatusColor(car.status)}`]">
          {{ getStatusIcon(car.status) }}
        </span>
      </div>
    </div>
    
    <div class="car-info-compact">
      <h3 class="car-title-compact">{{ car.brand }} {{ car.model }}</h3>
      <div class="car-specs-compact">
        <span>{{ car.year }}, {{ car.engine_volume }}L</span>
      </div>
      
      <!-- Владелец компактно -->
      <div class="car-owner-compact">
        <div class="owner-avatar-small">
          <img
            v-if="ownerData?.photo_url"
            :src="ownerData.photo_url"
            :alt="car.owner_name"
            class="avatar-image"
          />
          <span v-else class="avatar-initials">
            {{ getOwnerInitials() }}
          </span>
        </div>
        <span class="owner-name-compact">{{ getOwnerFirstName() }}</span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Car, Camera } from 'lucide-vue-next'
import type { Car as CarType } from '@/stores/data'
import { useDataStore } from '@/stores/data'

interface Props {
  car: CarType
}

const props = defineProps<Props>()
defineEmits<{
  select: [car: CarType]
}>()

const dataStore = useDataStore()

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

const getStatusIcon = (status: string) => {
  switch (status) {
    case 'активный': return '✓'
    case 'потенциальный': return '?'
    case 'в ожидании': return '⏳'
    default: return '✗'
  }
}

const getOwnerInitials = () => {
  const names = props.car.owner_name?.split(' ') || ['', '']
  return (names[0]?.charAt(0) + (names[1]?.charAt(0) || '')).toUpperCase()
}

const getOwnerFirstName = () => {
  return props.car.owner_name?.split(' ')[0] || 'Владелец'
}
</script>

<style scoped>
.car-card-compact {
  background: var(--card-bg);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-xl);
  overflow: hidden;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  flex-direction: column;
}

.car-card-compact:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
  border-color: var(--primary-color);
}

.car-image-container {
  position: relative;
  width: 100%;
  height: 120px;
  overflow: hidden;
}

.car-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.2s ease;
}

.car-card-compact:hover .car-image {
  transform: scale(1.02);
}

.car-placeholder {
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, var(--border-color) 0%, rgba(255, 255, 255, 0.1) 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--tg-theme-hint-color);
}

.photos-count {
  position: absolute;
  top: var(--spacing-xs);
  right: var(--spacing-xs);
  background: rgba(0, 0, 0, 0.7);
  color: white;
  padding: 2px var(--spacing-xs);
  border-radius: var(--radius-md);
  font-size: 10px;
  font-weight: var(--font-weight-medium);
  display: flex;
  align-items: center;
  gap: 2px;
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
}

.car-status-overlay {
  position: absolute;
  top: var(--spacing-xs);
  left: var(--spacing-xs);
}

.status-badge {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 10px;
  font-weight: var(--font-weight-bold);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.status-success {
  background: rgba(76, 175, 80, 0.9);
  color: white;
}

.status-warning {
  background: rgba(255, 152, 0, 0.9);
  color: white;
}

.status-primary {
  background: rgba(46, 166, 255, 0.9);
  color: white;
}

.status-error {
  background: rgba(244, 67, 54, 0.9);
  color: white;
}

.car-info-compact {
  padding: var(--spacing-sm);
  display: flex;
  flex-direction: column;
  gap: var(--spacing-xs);
  flex: 1;
}

.car-title-compact {
  font-size: var(--font-size-md);
  font-weight: var(--font-weight-bold);
  color: var(--tg-theme-text-color);
  margin: 0;
  line-height: 1.2;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.car-specs-compact {
  font-size: var(--font-size-sm);
  color: var(--tg-theme-hint-color);
  font-weight: var(--font-weight-medium);
}

.car-owner-compact {
  display: flex;
  align-items: center;
  gap: var(--spacing-xs);
  margin-top: auto;
}

.owner-avatar-small {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  overflow: hidden;
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
}

.avatar-initials {
  color: white;
  font-weight: var(--font-weight-bold);
  font-size: 8px;
}

.owner-name-compact {
  font-size: var(--font-size-xs);
  color: var(--tg-theme-hint-color);
  font-weight: var(--font-weight-medium);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  flex: 1;
}

@media (max-width: 480px) {
  .car-image-container {
    height: 100px;
  }
  
  .car-title-compact {
    font-size: var(--font-size-sm);
  }
  
  .car-specs-compact {
    font-size: var(--font-size-xs);
  }
  
  .owner-avatar-small {
    width: 16px;
    height: 16px;
  }
  
  .avatar-initials {
    font-size: 7px;
  }
  
  .owner-name-compact {
    font-size: 10px;
  }
}
</style>
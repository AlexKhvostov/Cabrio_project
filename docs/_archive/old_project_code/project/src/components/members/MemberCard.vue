<template>
  <div class="member-card" @click="$emit('select', member)">
    <div class="member-avatar">
      <img
        v-if="member.photo_url"
        :src="member.photo_url"
        :alt="member.first_name"
        class="avatar-image"
      />
      <span v-else class="avatar-initials">
        {{ getInitials(member.first_name, member.last_name) }}
      </span>
      
      <!-- Статус (кружочек) -->
      <div :class="['status-indicator', `status-${getStatusColor(member.status)}`]">
        ●
      </div>
      
      <!-- Роль (звездочка) -->
      <div :class="['role-indicator', `role-${getRoleColor(member.role)}`]">
        ★
      </div>
      
      <!-- Почётный участник (кристалл) -->
      <div v-if="member.flags.includes('почётный участник')" class="honor-indicator">
        ♦
      </div>
    </div>
    
    <div class="member-info">
      <div class="member-main">
        <h3 class="member-name">
          {{ member.first_name }} {{ member.last_name }}
        </h3>
        <span v-if="member.nickname" class="member-nickname">
          @{{ member.nickname }}
        </span>
      </div>
      
      <div class="member-details">
        <div v-if="member.cars.length > 0" class="member-car">
          <div class="car-photo-mini">
            <img
              v-if="member.cars[0].photos && member.cars[0].photos.length > 0"
              :src="member.cars[0].photos[0]"
              :alt="`${member.cars[0].brand} ${member.cars[0].model}`"
              class="car-mini-image"
            />
            <Car v-else :size="12" />
          </div>
          <span class="car-info">
            {{ member.cars[0].brand }} {{ member.cars[0].model }} ({{ member.cars[0].year }})
          </span>
          <span v-if="member.cars.length > 1" class="cars-count">
            +{{ member.cars.length - 1 }}
          </span>
        </div>
        
        <div v-else class="member-car no-car">
          <div class="car-photo-mini no-car-icon">
            <Car :size="12" />
          </div>
          <span class="no-car-text">Нет автомобиля</span>
        </div>
      </div>
    </div>
    
    <div class="member-actions">
      <ChevronRight :size="20" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { Car, MessageCircle, ChevronRight } from 'lucide-vue-next'
import type { Member } from '@/stores/data'

interface Props {
  member: Member
}

defineProps<Props>()
defineEmits<{
  select: (member: Member) => void
}>()
function getInitials(firstName: string, lastName: string) {
  const first = firstName?.charAt(0)?.toUpperCase() || ''
  const last = lastName?.charAt(0)?.toUpperCase() || ''
  return first + last
}

const getStatusColor = (status: string) => {
  switch (status) {
    case 'активный': return 'active'
    case 'зарегистрирован': return 'registered'
    case 'новый': return 'new'
    case 'вышедший': return 'left'
    default: return 'new'
  }
}

const getRoleColor = (role: string) => {
  switch (role) {
    case 'участник': return 'member'
    case 'модератор': return 'moderator'
    case 'администратор': return 'admin'
    case 'root': return 'root'
    default: return 'member'
  }
}

const formatDate = (dateString: string) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('ru-RU', {
    day: 'numeric',
    month: 'short'
  })
}
</script>

<style scoped>
.member-card {
  display: flex;
  align-items: center;
  gap: var(--spacing-md);
  padding: var(--spacing-md);
  background: var(--card-bg);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-md);
  cursor: pointer;
  transition: all 0.2s ease;
}

.member-card:hover {
  background: var(--hover-bg);
  border-color: var(--primary-color);
  transform: translateX(4px);
}

.member-avatar {
  position: relative;
  width: 48px;
  height: 48px;
  border-radius: 50%;
  overflow: visible;
  flex-shrink: 0;
  background: var(--primary-color);
  display: flex;
  align-items: center;
  justify-content: center;
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
  font-size: var(--font-size-md);
}

/* Индикаторы статуса, роли и почёта */
.status-indicator {
  position: absolute;
  bottom: -2px;
  right: -2px;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  background: var(--card-bg);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 10px;
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
  width: 16px;
  height: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
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
  width: 16px;
  height: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  color: #00bcd4;
  text-shadow: 0 0 4px rgba(0, 188, 212, 0.5);
}

.member-info {
  flex: 1;
  min-width: 0;
}

.member-main {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
  margin-bottom: var(--spacing-xs);
}

.member-name {
  font-size: var(--font-size-md);
  font-weight: var(--font-weight-semibold);
  color: var(--tg-theme-text-color);
  margin: 0;
}

.member-nickname {
  font-size: var(--font-size-sm);
  color: var(--primary-color);
  font-weight: var(--font-weight-medium);
}

.member-details {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-xs);
}

.detail-row {
  display: flex;
  align-items: center;
  gap: var(--spacing-xs);
  font-size: var(--font-size-sm);
  color: var(--tg-theme-hint-color);
}

.detail-row svg {
  color: var(--tg-theme-hint-color);
  flex-shrink: 0;
}

.join-date {
  margin-left: auto;
  font-size: var(--font-size-xs);
  color: var(--tg-theme-hint-color);
}

.member-car {
  display: flex;
  align-items: center;
  gap: var(--spacing-xs);
  font-size: var(--font-size-sm);
  color: var(--tg-theme-text-color);
}

.car-photo-mini {
  width: 20px;
  height: 20px;
  border-radius: var(--radius-sm);
  overflow: hidden;
  background: var(--border-color);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.car-mini-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.no-car-icon {
  color: var(--tg-theme-hint-color);
}

.car-info {
  font-weight: var(--font-weight-medium);
  flex: 1;
  min-width: 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.cars-count {
  background: var(--primary-color);
  color: white;
  padding: 2px var(--spacing-xs);
  border-radius: var(--radius-sm);
  font-size: var(--font-size-xs);
  font-weight: var(--font-weight-bold);
  flex-shrink: 0;
}

.member-car.no-car {
  color: var(--tg-theme-hint-color);
}

.no-car-text {
  font-style: italic;
}

.member-actions {
  display: flex;
  align-items: center;
  color: var(--tg-theme-hint-color);
  flex-shrink: 0;
}

@media (max-width: 480px) {
  .member-card {
    padding: var(--spacing-sm);
    gap: var(--spacing-sm);
  }
  
  .member-avatar {
    width: 40px;
    height: 40px;
  }
  
  .status-indicator,
  .role-indicator,
  .honor-indicator {
    width: 14px;
    height: 14px;
    font-size: 10px;
  }
  
  .member-main {
    flex-direction: column;
    align-items: flex-start;
    gap: 2px;
  }
  
  .car-info {
    font-size: var(--font-size-xs);
  }
}
</style>
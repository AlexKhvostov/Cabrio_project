<template>
  <div v-if="isOpen" class="members-dropdown">
    <div class="members-header">
      <Users :size="14" />
      <span>Онлайн ({{ members.length }})</span>
    </div>
    
    <div class="members-list">
      <div
        v-for="member in members"
        :key="member.id"
        class="member-item"
        @click="$emit('focus-member', member.id)"
      >
        <div class="member-avatar">
          <img
            v-if="member.photo_url"
            :src="member.photo_url"
            :alt="member.name"
            class="avatar-image"
          />
          <span v-else class="avatar-initials">
            {{ getInitials(member.name) }}
          </span>
        </div>
        
        <div class="member-info">
          <div class="member-name">{{ member.name.split(' ')[0] }}</div>
          <div class="member-speed">
            {{ member.location.speed ? Math.round(member.location.speed) + ' км/ч' : 'Стоит' }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Users } from 'lucide-vue-next'

interface ConnectedMember {
  id: number
  name: string
  nickname?: string
  photo_url?: string
  location: {
    lat: number
    lng: number
    heading?: number
    speed?: number
  }
  lastUpdate: Date
}

interface Props {
  isOpen: boolean
  members: ConnectedMember[]
}

defineProps<Props>()
defineEmits<{
  'focus-member': [memberId: number]
}>()

const getInitials = (name: string) => {
  return name.split(' ').map(n => n.charAt(0)).join('').toUpperCase().slice(0, 2)
}
</script>

<style scoped>
.members-dropdown {
  position: absolute;
  top: 100%;
  right: var(--spacing-sm);
  width: 50vw;
  background: rgba(0, 0, 0, 0.9);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border-radius: var(--radius-lg);
  color: white;
  margin-top: var(--spacing-xs);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
  z-index: 20;
}

.members-header {
  display: flex;
  align-items: center;
  gap: var(--spacing-xs);
  padding: var(--spacing-sm);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  font-size: var(--font-size-sm);
  font-weight: var(--font-weight-medium);
}

.members-list {
  max-height: 200px;
  overflow-y: auto;
}

.member-item {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
  padding: var(--spacing-sm);
  cursor: pointer;
  transition: all 0.2s ease;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.member-item:hover {
  background: rgba(255, 255, 255, 0.1);
}

.member-item:last-child {
  border-bottom: none;
}

.member-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  overflow: hidden;
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
}

.avatar-initials {
  color: white;
  font-weight: var(--font-weight-bold);
  font-size: var(--font-size-xs);
}

.member-info {
  flex: 1;
  min-width: 0;
}

.member-name {
  font-size: var(--font-size-sm);
  font-weight: var(--font-weight-medium);
  margin-bottom: 2px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.member-speed {
  font-size: var(--font-size-xs);
  color: rgba(255, 255, 255, 0.7);
}

/* Кастомный скроллбар */
.members-list::-webkit-scrollbar {
  width: 3px;
}

.members-list::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.1);
}

.members-list::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.3);
  border-radius: 2px;
}

@media (max-width: 480px) {
  .members-dropdown {
    margin-top: 4px;
  }
  
  .members-header {
    padding: 8px;
    font-size: var(--font-size-xs);
  }
  
  .member-item {
    padding: 8px;
    gap: 8px;
  }
  
  .member-avatar {
    width: 28px;
    height: 28px;
  }
  
  .member-name {
    font-size: var(--font-size-xs);
  }
  
  .member-speed {
    font-size: 10px;
  }
}
</style>
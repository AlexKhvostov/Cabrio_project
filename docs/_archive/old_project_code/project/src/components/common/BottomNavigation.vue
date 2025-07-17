<template>
  <nav class="bottom-nav">
    <router-link
      v-for="item in navItems"
      :key="item.name"
      :to="item.path"
      class="nav-item"
      :class="{ active: $route.name === item.name }"
      @click="hapticFeedback"
    >
      <component :is="item.icon" :size="20" />
      <span class="nav-label">{{ item.label }}</span>
    </router-link>
  </nav>
</template>

<script setup lang="ts">
import { 
  BarChart3, 
  Users, 
  Car, 
  MapPin,
  Calendar, 
  BookOpen, 
  User 
} from 'lucide-vue-next'
import { useTelegramStore } from '@/stores/telegram'

const telegramStore = useTelegramStore()

const navItems = [
  {
    name: 'members',
    path: '/members',
    label: 'Участники',
    icon: Users
  },
  {
    name: 'cars',
    path: '/cars',
    label: 'Авто',
    icon: Car
  },
  {
    name: 'map',
    path: '/map',
    label: 'Карта',
    icon: MapPin
  },
  {
    name: 'events',
    path: '/events',
    label: 'События',
    icon: Calendar
  },
  {
    name: 'services',
    path: '/services', 
    label: 'Гид',
    icon: BookOpen
  }
]

const hapticFeedback = () => {
  telegramStore.hapticFeedback('selection')
}
</script>

<style scoped>
.bottom-nav {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  background: var(--tg-theme-bg-color);
  border-top: 1px solid var(--dashboard-card-border);
  display: flex;
  padding: var(--spacing-xs) 0;
  padding-bottom: calc(var(--spacing-xs) + env(safe-area-inset-bottom));
  z-index: 100;
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
}

.nav-item {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: var(--spacing-xs);
  text-decoration: none;
  color: #666666;
  transition: all 0.2s ease;
  min-height: 60px;
  position: relative;
}

.nav-item.active {
  color: var(--tg-theme-text-color);
}

.nav-item.active::before {
  content: '';
  position: absolute;
  top: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 24px;
  height: 2px;
  background: var(--tg-theme-text-color);
  border-radius: 1px;
}

.nav-label {
  font-size: var(--font-size-xs);
  font-weight: var(--font-weight-medium);
  margin-top: var(--spacing-xs);
  text-align: center;
  line-height: 1;
}

@media (max-width: 480px) {
  .nav-label {
    font-size: 10px;
  }
  
  .nav-item {
    min-height: 56px;
  }
}
</style>
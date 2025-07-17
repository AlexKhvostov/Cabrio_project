<template>
  <header class="app-header">
    <button class="header-btn" @click="goToDashboard">
      <BarChart3 :size="20" />
    </button>
    
    <h1 class="header-title">{{ getPageTitle() }}</h1>
    
    <button class="header-btn" @click="goToProfile">
      <User :size="20" />
    </button>
  </header>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { BarChart3, User } from 'lucide-vue-next'
import { useTelegramStore } from '@/stores/telegram'

const route = useRoute()
const router = useRouter()
const telegramStore = useTelegramStore()

const getPageTitle = () => {
  const titles: Record<string, string> = {
    'dashboard': 'Статистика',
    'members': 'Участники',
    'cars': 'Автомобили',
    'map': 'Карта',
    'events': 'События',
    'services': 'Гид'
  }
  return titles[route.name as string] || 'CabrioRide'
}

const goToDashboard = () => {
  telegramStore.hapticFeedback('selection')
  router.push('/')
}

const goToProfile = () => {
  telegramStore.hapticFeedback('selection')
  router.push('/profile')
}
</script>

<style scoped>
.app-header {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  height: 60px;
  background: var(--card-bg);
  border-bottom: 1px solid var(--border-color);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 var(--spacing-md);
  z-index: 1000;
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
}

.header-btn {
  width: 40px;
  height: 40px;
  border: none;
  background: none;
  color: var(--tg-theme-text-color);
  border-radius: var(--radius-md);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.header-btn:hover {
  background: var(--hover-bg);
  color: var(--primary-color);
}

.header-title {
  font-size: var(--font-size-lg);
  font-weight: var(--font-weight-semibold);
  color: var(--tg-theme-text-color);
  margin: 0;
  text-align: center;
  flex: 1;
}

@media (max-width: 480px) {
  .app-header {
    padding: 0 var(--spacing-sm);
  }
  
  .header-btn {
    width: 36px;
    height: 36px;
  }
  
  .header-title {
    font-size: var(--font-size-md);
  }
}
</style>
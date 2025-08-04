<template>
  <header class="app-header">
    <button class="header-btn" @click="goToDashboard">
      <BarChart3 :size="20" />
    </button>
    
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
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 60px;
  background: transparent;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 var(--spacing-md);
  z-index: 1000;
  pointer-events: none;
}

.header-btn {
  width: 44px;
  height: 44px;
  border: 2px solid var(--tg-theme-text-color);
  background: rgba(0, 0, 0, 0.3);
  color: var(--tg-theme-text-color);
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  pointer-events: auto;
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
}

.header-btn:hover {
  background: rgba(255, 255, 255, 0.1);
  border-color: var(--tg-theme-text-color);
  transform: scale(1.05);
}

.header-btn:active {
  transform: scale(0.95);
}

@media (max-width: 480px) {
  .app-header {
    padding: 0 var(--spacing-sm);
  }
  
  .header-btn {
    width: 40px;
    height: 40px;
  }
}
</style>
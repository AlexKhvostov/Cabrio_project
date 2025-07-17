<template>
  <div id="app" class="app">
    <AppHeader v-if="showNavigation" />
    <div class="app-content">
      <RouterView />
    </div>
    <BottomNavigation v-if="showNavigation" />
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { RouterView, useRoute } from 'vue-router'
import AppHeader from './components/common/AppHeader.vue'
import BottomNavigation from './components/common/BottomNavigation.vue'
import { useTelegramStore } from './stores/telegram'

const route = useRoute()
const telegramStore = useTelegramStore()

const showNavigation = computed(() => {
  const excludedRoutes = ['login', 'registration', 'access-denied']
  return !excludedRoutes.includes(route.name as string) && telegramStore.isAuthenticated
})

onMounted(() => {
  telegramStore.initTelegram()
})
</script>

<style scoped>
.app {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background: var(--tg-theme-bg-color, #1a1a1a);
  color: var(--tg-theme-text-color, #ffffff);
}

.app-content {
  flex: 1;
  padding-top: 0; /* Remove padding for map view */
  padding-bottom: 80px; /* Space for bottom navigation */
}

.app-content:not(.map-view) {
  padding-top: 60px; /* Space for header on other pages */
}
</style>
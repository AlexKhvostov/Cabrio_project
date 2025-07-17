<template>
  <div class="login-view">
    <div class="login-container">
      <div class="logo-section">
        <div class="logo">
          <Car :size="48" />
        </div>
        <h1>CabrioRide</h1>
        <p class="subtitle">Клуб владельцев кабриолетов</p>
      </div>

      <div class="features">
        <div class="feature">
          <Users :size="24" />
          <div>
            <h3>Сообщество</h3>
            <p>Участники клуба и их автомобили</p>
          </div>
        </div>
        <div class="feature">
          <Calendar :size="24" />
          <div>
            <h3>События</h3>
            <p>Заезды и встречи участников</p>
          </div>
        </div>
        <div class="feature">
          <Wrench :size="24" />
          <div>
            <h3>Сервисы</h3>
            <p>Проверенные автосервисы</p>
          </div>
        </div>
      </div>

      <div class="auth-section">
        <div v-if="loading" class="loading">
          <div class="spinner"></div>
          <p>Подключение к Telegram...</p>
        </div>
        <div v-else-if="telegramStore.isAuthenticated" class="success">
          <CheckCircle :size="32" />
          <p>Добро пожаловать, {{ telegramStore.user?.first_name }}!</p>
          <button class="btn btn-primary" @click="goToDashboard">
            Войти в приложение
          </button>
        </div>
        <div v-else class="waiting">
          <AlertCircle :size="32" />
          <p>Ожидание авторизации через Telegram...</p>
          <p class="hint">Убедитесь, что приложение запущено через бота</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { Car, Users, Calendar, Wrench, CheckCircle, AlertCircle } from 'lucide-vue-next'
import { useTelegramStore } from '@/stores/telegram'

const router = useRouter()
const telegramStore = useTelegramStore()
const loading = ref(true)

onMounted(() => {
  setTimeout(() => {
    loading.value = false
    if (telegramStore.isAuthenticated) {
      setTimeout(() => {
        goToDashboard()
      }, 1500)
    }
  }, 2000)
})

const goToDashboard = () => {
  telegramStore.hapticFeedback('notification')
  router.push('/')
}
</script>

<style scoped>
.login-view {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: var(--spacing-lg);
  background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
}

.login-container {
  max-width: 400px;
  width: 100%;
  text-align: center;
}

.logo-section {
  margin-bottom: var(--spacing-xl);
}

.logo {
  width: 80px;
  height: 80px;
  background: linear-gradient(135deg, var(--primary-color) 0%, #1e90ff 100%);
  border-radius: var(--radius-xl);
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto var(--spacing-lg);
  box-shadow: var(--shadow-lg);
  color: white;
}

.logo-section h1 {
  font-size: var(--font-size-2xl);
  font-weight: var(--font-weight-bold);
  margin-bottom: var(--spacing-sm);
  background: linear-gradient(135deg, var(--tg-theme-text-color) 0%, var(--tg-theme-hint-color) 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.subtitle {
  color: var(--tg-theme-hint-color);
  font-size: var(--font-size-md);
  font-weight: var(--font-weight-medium);
}

.features {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-md);
  margin-bottom: var(--spacing-xl);
}

.feature {
  display: flex;
  align-items: center;
  gap: var(--spacing-md);
  padding: var(--spacing-md);
  background: var(--card-bg);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-lg);
  text-align: left;
}

.feature svg {
  color: var(--primary-color);
  flex-shrink: 0;
}

.feature h3 {
  font-size: var(--font-size-md);
  font-weight: var(--font-weight-semibold);
  margin-bottom: var(--spacing-xs);
}

.feature p {
  font-size: var(--font-size-sm);
  color: var(--tg-theme-hint-color);
  margin: 0;
}

.auth-section {
  background: var(--card-bg);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-lg);
  padding: var(--spacing-xl);
}

.loading,
.success,
.waiting {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: var(--spacing-md);
}

.loading svg,
.success svg,
.waiting svg {
  color: var(--primary-color);
}

.success svg {
  color: var(--success-color);
}

.waiting svg {
  color: var(--warning-color);
}

.loading p,
.success p,
.waiting p {
  margin: 0;
  font-weight: var(--font-weight-medium);
}

.hint {
  font-size: var(--font-size-sm);
  color: var(--tg-theme-hint-color);
  font-weight: var(--font-weight-normal) !important;
}

.btn {
  margin-top: var(--spacing-md);
}

@media (max-width: 480px) {
  .login-view {
    padding: var(--spacing-md);
  }
  
  .logo {
    width: 64px;
    height: 64px;
  }
  
  .logo svg {
    width: 32px;
    height: 32px;
  }
  
  .logo-section h1 {
    font-size: var(--font-size-xl);
  }
}
</style>
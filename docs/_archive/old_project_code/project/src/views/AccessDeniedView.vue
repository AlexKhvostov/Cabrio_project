<template>
  <div class="access-denied-view">
    <div class="access-denied-container">
      <!-- Иконка блокировки -->
      <div class="lock-icon">
        <Shield :size="64" />
      </div>
      
      <!-- Заголовок -->
      <h1 class="title">Доступ ограничен</h1>
      
      <!-- Описание -->
      <div class="description">
        <p class="main-text">
          Вы попали в приложение клуба владельцев кабриолетов <strong>CabrioRide</strong>
        </p>
        
        <div class="reason-block">
          <h3>Почему я не могу войти?</h3>
          <ul class="reason-list">
            <li>Доступ к приложению предоставляется только участникам клуба</li>
            <li>Ваш аккаунт может быть на модерации</li>
            <li>Возможно, вы были временно заблокированы</li>
          </ul>
        </div>
        
        <div class="info-block">
          <h3>Как получить доступ?</h3>
          <p>
            Для вступления в клуб обратитесь к администраторам через 
            официальные каналы связи или к действующим участникам клуба.
          </p>
        </div>
      </div>
      
      <!-- Действия -->
      <div class="actions">
        <button class="btn-primary" @click="contactSupport">
          <MessageCircle :size="20" />
          Связаться с поддержкой
        </button>
        
        <button class="btn-secondary" @click="refreshAccess">
          <RefreshCw :size="20" />
          Проверить доступ повторно
        </button>
        
        <button class="btn-link" @click="learnMore">
          Узнать больше о клубе
        </button>
      </div>
      
      <!-- Информация о клубе -->
      <div class="club-info">
        <div class="info-item">
          <Users :size="16" />
          <span>Сообщество энтузиастов</span>
        </div>
        <div class="info-item">
          <Car :size="16" />
          <span>Владельцы кабриолетов</span>
        </div>
        <div class="info-item">
          <Calendar :size="16" />
          <span>Регулярные встречи и поездки</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Shield, MessageCircle, RefreshCw, Users, Car, Calendar } from 'lucide-vue-next'
import { useTelegramStore } from '@/stores/telegram'

const telegramStore = useTelegramStore()

const contactSupport = () => {
  telegramStore.hapticFeedback('impact')
  // TODO: Открыть чат с поддержкой или показать контакты
  if (telegramStore.webApp?.openTelegramLink) {
    telegramStore.webApp.openTelegramLink('https://t.me/cabrioride_support')
  } else {
    telegramStore.showAlert('Свяжитесь с поддержкой: @cabrioride_support')
  }
}

const refreshAccess = () => {
  telegramStore.hapticFeedback('selection')
  // TODO: Проверить статус пользователя повторно
  console.log('Checking access again...')
  telegramStore.showAlert('Проверяем ваш статус...')
  
  // Имитация проверки
  setTimeout(() => {
    telegramStore.showAlert('Доступ пока не предоставлен. Обратитесь к администраторам.')
  }, 2000)
}

const learnMore = () => {
  telegramStore.hapticFeedback('selection')
  // TODO: Открыть информацию о клубе
  if (telegramStore.webApp?.openLink) {
    telegramStore.webApp.openLink('https://cabrioride.club/about')
  } else {
    telegramStore.showAlert('Посетите наш сайт: cabrioride.club')
  }
}
</script>

<style scoped>
.access-denied-view {
  min-height: 100vh;
  background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: var(--spacing-lg);
}

.access-denied-container {
  max-width: 500px;
  width: 100%;
  text-align: center;
  display: flex;
  flex-direction: column;
  gap: var(--spacing-xl);
}

/* Lock icon */
.lock-icon {
  display: flex;
  justify-content: center;
  margin-bottom: var(--spacing-md);
}

.lock-icon svg {
  color: var(--warning-color);
  filter: drop-shadow(0 0 10px rgba(255, 152, 0, 0.3));
}

/* Title */
.title {
  font-size: var(--font-size-2xl);
  font-weight: var(--font-weight-bold);
  color: var(--tg-theme-text-color);
  margin: 0;
}

/* Description */
.description {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-lg);
  text-align: left;
}

.main-text {
  font-size: var(--font-size-lg);
  color: var(--tg-theme-text-color);
  text-align: center;
  margin: 0;
}

.reason-block,
.info-block {
  background: var(--card-bg);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-lg);
  padding: var(--spacing-lg);
}

.reason-block h3,
.info-block h3 {
  font-size: var(--font-size-md);
  font-weight: var(--font-weight-semibold);
  color: var(--tg-theme-text-color);
  margin: 0 0 var(--spacing-md) 0;
}

.reason-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: var(--spacing-sm);
}

.reason-list li {
  font-size: var(--font-size-sm);
  color: var(--tg-theme-hint-color);
  position: relative;
  padding-left: var(--spacing-md);
}

.reason-list li::before {
  content: '•';
  color: var(--warning-color);
  position: absolute;
  left: 0;
  font-weight: bold;
}

.info-block p {
  font-size: var(--font-size-sm);
  color: var(--tg-theme-hint-color);
  line-height: 1.5;
  margin: 0;
}

/* Actions */
.actions {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-md);
}

.btn-primary,
.btn-secondary {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: var(--spacing-sm);
  padding: var(--spacing-md) var(--spacing-lg);
  border: none;
  border-radius: var(--radius-lg);
  font-size: var(--font-size-md);
  font-weight: var(--font-weight-semibold);
  cursor: pointer;
  transition: all 0.2s ease;
  min-height: 48px;
}

.btn-primary {
  background: var(--primary-color);
  color: white;
}

.btn-primary:hover {
  background: #1e90ff;
  transform: translateY(-2px);
  box-shadow: 0 4px 16px rgba(46, 166, 255, 0.3);
}

.btn-secondary {
  background: var(--card-bg);
  color: var(--tg-theme-text-color);
  border: 1px solid var(--border-color);
}

.btn-secondary:hover {
  background: var(--hover-bg);
  transform: translateY(-1px);
}

.btn-link {
  background: none;
  border: none;
  color: var(--tg-theme-hint-color);
  font-size: var(--font-size-sm);
  text-decoration: underline;
  cursor: pointer;
  padding: var(--spacing-sm);
  transition: color 0.2s ease;
}

.btn-link:hover {
  color: var(--tg-theme-text-color);
}

/* Club info */
.club-info {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-sm);
  padding: var(--spacing-lg);
  background: rgba(46, 166, 255, 0.1);
  border: 1px solid rgba(46, 166, 255, 0.2);
  border-radius: var(--radius-lg);
}

.info-item {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
  font-size: var(--font-size-sm);
  color: var(--tg-theme-text-color);
}

.info-item svg {
  color: var(--primary-color);
  flex-shrink: 0;
}

@media (max-width: 480px) {
  .access-denied-view {
    padding: var(--spacing-md);
  }
  
  .access-denied-container {
    gap: var(--spacing-lg);
  }
  
  .lock-icon svg {
    width: 48px;
    height: 48px;
  }
  
  .title {
    font-size: var(--font-size-xl);
  }
  
  .main-text {
    font-size: var(--font-size-md);
  }
  
  .reason-block,
  .info-block {
    padding: var(--spacing-md);
  }
  
  .actions {
    gap: var(--spacing-sm);
  }
  
  .club-info {
    padding: var(--spacing-md);
  }
}
</style>
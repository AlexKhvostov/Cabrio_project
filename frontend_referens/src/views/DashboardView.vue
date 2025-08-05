<template>
  <div class="dashboard-view">
    <div class="container">
      <div v-if="dataStore.loading" class="loading">
        <div class="spinner"></div>
      </div>

      <div v-else class="dashboard-content">
        <!-- Stats Cards -->
        <div class="stats-grid-compact">
          <div class="stat-card">
            <div class="stat-number">{{ stats.total_members }}</div>
            <div class="stat-label">Участники</div>
          </div>
          
          <div class="stat-card">
            <div class="stat-number">{{ stats.total_cars }}</div>
            <div class="stat-label">Авто</div>
          </div>
          
          <div class="stat-card">
            <div class="stat-number">{{ stats.total_events }}</div>
            <div class="stat-label">События</div>
          </div>
          
          <div class="stat-card">
            <div class="stat-number">{{ stats.total_guide_objects }}</div>
            <div class="stat-label">Объекты</div>
          </div>
          
          <div class="stat-card">
            <div class="stat-number">{{ stats.total_reviews }}</div>
            <div class="stat-label">Отзывы</div>
          </div>
        </div>

        <!-- Action Button -->
        <button class="action-button" @click="findCarAction">
          Find Car & Leave Card
        </button>

        <!-- Recent Activity -->
        <div class="activity-section">
          <h2 class="activity-title">Recent Activity</h2>
          
          <div class="activity-list">
            <div class="activity-item">
              <div class="activity-avatar">
                <img 
                  src="https://images.pexels.com/photos/220453/pexels-photo-220453.jpeg?auto=compress&cs=tinysrgb&w=64&h=64&dpr=1" 
                  alt="Ethan Harper"
                />
              </div>
              <div class="activity-content">
                <div class="activity-title-text">Ethan Harper</div>
                <div class="activity-description">New member joined the club</div>
              </div>
            </div>

            <div class="activity-item">
              <div class="activity-icon event">
                <Calendar :size="20" />
              </div>
              <div class="activity-content">
                <div class="activity-title-text">Summer Drive</div>
                <div class="activity-description">New event created</div>
              </div>
            </div>

            <div class="activity-item">
              <div class="activity-icon car">
                <Car :size="20" />
              </div>
              <div class="activity-content">
                <div class="activity-title-text">Mercedes-Benz SL</div>
                <div class="activity-description">New car added to the fleet</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { Calendar, Car } from 'lucide-vue-next'
import { useDataStore } from '@/stores/data'
import { useTelegramStore } from '@/stores/telegram'

const dataStore = useDataStore()
const telegramStore = useTelegramStore()

const stats = computed(() => dataStore.stats)

const findCarAction = () => {
  telegramStore.hapticFeedback('impact')
  // TODO: Implement find car functionality
  console.log('Find Car & Leave Card action')
}

onMounted(() => {
  dataStore.fetchStats()
})
</script>

<style scoped>
.dashboard-view {
  min-height: 100vh;
  background: var(--tg-theme-bg-color);
  padding-bottom: 100px; /* Space for bottom navigation */
}

.container {
  max-width: 100%;
  padding: 0 var(--spacing-lg);
}

/* Dashboard Content */
.dashboard-content {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-xl);
  padding-top: var(--spacing-lg);
}

/* Stats Grid */
.stats-grid-compact {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: var(--spacing-md);
}

.stat-card {
  min-width: 0; /* Позволяет карточкам сжиматься */
  background: var(--dashboard-card-bg);
  border: 1px solid var(--dashboard-card-border);
  border-radius: var(--radius-xl);
  padding: var(--spacing-sm) var(--spacing-xs);
  text-align: center;
  transition: all 0.2s ease;
}

.stat-card:hover {
  background: var(--hover-bg);
  transform: translateY(-2px);
}

.stat-number {
  font-size: 1.5rem;
  font-weight: var(--font-weight-bold);
  color: var(--tg-theme-text-color);
  line-height: 1;
  margin-bottom: var(--spacing-sm);
}

.stat-label {
  font-size: var(--font-size-xs);
  color: var(--tg-theme-hint-color);
  font-weight: var(--font-weight-medium);
  line-height: 1.2;
}

/* Action Button */
.action-button {
  background: linear-gradient(135deg, #e8f4fd 0%, #c8e6fc 100%);
  color: #1a1a1a;
  border: none;
  border-radius: 50px;
  padding: var(--spacing-lg) var(--spacing-xl);
  font-size: var(--font-size-lg);
  font-weight: var(--font-weight-semibold);
  cursor: pointer;
  transition: all 0.2s ease;
  width: 100%;
  max-width: 400px;
  margin: 0 auto;
  box-shadow: 0 4px 20px rgba(232, 244, 253, 0.3);
}

.action-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 25px rgba(232, 244, 253, 0.4);
}

.action-button:active {
  transform: translateY(0);
}

/* Activity Section */
.activity-section {
  margin-top: var(--spacing-lg);
}

.activity-title {
  font-size: var(--font-size-xl);
  font-weight: var(--font-weight-bold);
  color: var(--tg-theme-text-color);
  margin: 0 0 var(--spacing-lg) 0;
}

.activity-list {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-md);
}

.activity-item {
  display: flex;
  align-items: center;
  gap: var(--spacing-md);
  padding: var(--spacing-md);
  background: var(--activity-item-bg);
  border-radius: var(--radius-lg);
  transition: all 0.2s ease;
}

.activity-item:hover {
  background: var(--hover-bg);
}

.activity-avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  overflow: hidden;
  flex-shrink: 0;
}

.activity-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.activity-icon {
  width: 48px;
  height: 48px;
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.activity-icon.event {
  background: rgba(255, 152, 0, 0.2);
  color: var(--warning-color);
}

.activity-icon.car {
  background: rgba(46, 166, 255, 0.2);
  color: var(--primary-color);
}

.activity-content {
  flex: 1;
  min-width: 0;
}

.activity-title-text {
  font-size: var(--font-size-md);
  font-weight: var(--font-weight-semibold);
  color: var(--tg-theme-text-color);
  margin-bottom: 2px;
}

.activity-description {
  font-size: var(--font-size-sm);
  color: var(--tg-theme-hint-color);
}

/* Mobile Responsive */
@media (max-width: 480px) {
  .container {
    padding: 0 var(--spacing-md);
  }
  
  .stats-grid-compact {
    gap: var(--spacing-xs);
  }
  
  .stat-card {
    padding: var(--spacing-xs) 2px;
  }
  
  .stat-number {
    font-size: 1.2rem;
  }
  
  .stat-label {
    font-size: 10px;
  }

  .action-button {
    padding: var(--spacing-md) var(--spacing-lg);
    font-size: var(--font-size-md);
  }
  
  .activity-title {
    font-size: var(--font-size-lg);
  }
  
  .activity-item {
    padding: var(--spacing-sm);
    gap: var(--spacing-sm);
  }
  
  .activity-avatar,
  .activity-icon {
    width: 40px;
    height: 40px;
  }
  
  .activity-title-text {
    font-size: var(--font-size-sm);
  }
  
  .activity-description {
    font-size: var(--font-size-xs);
  }
}
</style>
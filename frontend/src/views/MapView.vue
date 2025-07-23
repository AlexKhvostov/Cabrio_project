<template>
  <div class="map-view full-screen">
    <div class="map-container">
      <!-- Карта -->
      <div class="map-area">
        <MapComponent 
          :members="connectedMembers"
          :markers="markers"
          @add-marker="addMarker"
          @remove-marker="removeMarker"
        />
      </div>
    </div>
    
    <!-- Голосовая связь -->
    <VoiceChat
      :is-connected="isConnected"
      :is-recording="isRecording"
      @start-recording="startRecording"
      @stop-recording="stopRecording"
    />

      <!-- Панель управления -->
      <div class="map-controls">
        <!-- Статус подключения -->
        <div class="top-controls">
          <div class="connection-status-half">
            <div :class="['status-indicator', { connected: isConnected }]"></div>
            <span class="status-text">
              {{ isConnected ? 'Подключен к карте' : 'Подключение...' }}
            </span>
          </div>
          
          <!-- Кнопка списка участников -->
          <button class="members-list-btn-half" @click="showMembersList = !showMembersList">
            <Users :size="16" />
            <span>{{ connectedMembers.length }}</span>
          </button>
          
          <!-- Список участников онлайн -->
          <MembersDropdown
            :is-open="showMembersList"
            :members="connectedMembers"
            @focus-member="focusOnMember"
          />
          
          <!-- Список участников онлайн -->
          <MembersDropdown
            :is-open="showMembersList"
            :members="connectedMembers"
            @focus-member="focusOnMember"
          />
        </div>

    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { Users } from 'lucide-vue-next'
import { useTelegramStore } from '@/stores/telegram'
import { useDataStore } from '@/stores/data'
import MapComponent from '@/components/map/MapComponent.vue'
import VoiceChat from '@/components/map/VoiceChat.vue'
import MembersDropdown from '@/components/map/MembersDropdown.vue'
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

interface MapMarker {
  id: string
  type: 'police' | 'accident' | 'roadwork' | 'fuel' | 'parking' | 'meeting'
  location: {
    lat: number
    lng: number
  }
  title: string
  description?: string
  author: {
    id: number
    name: string
  }
  createdAt: Date
  expiresAt?: Date
}

const telegramStore = useTelegramStore()
const dataStore = useDataStore()

const isConnected = ref(false)
const isRecording = ref(false)
const connectedMembers = ref<ConnectedMember[]>([])
const markers = ref<MapMarker[]>([])
const showMembersList = ref(false)

// Подключение к карте
const connectToMap = () => {
  // TODO: Подключение к WebSocket или другому real-time сервису
  setTimeout(() => {
    isConnected.value = true
    // Симуляция подключенных участников в Минске
    connectedMembers.value = [
      {
        id: 1,
        name: 'Александр Петров',
        nickname: 'alex_petrov',
        photo_url: 'https://images.pexels.com/photos/220453/pexels-photo-220453.jpeg?auto=compress&cs=tinysrgb&w=64&h=64&dpr=1',
        location: {
          lat: 53.9045,
          lng: 27.5615,
          heading: 45,
          speed: 60
        },
        lastUpdate: new Date()
      },
      {
        id: 2,
        name: 'Мария Иванова',
        nickname: 'maria_drive',
        photo_url: 'https://images.pexels.com/photos/415829/pexels-photo-415829.jpeg?auto=compress&cs=tinysrgb&w=64&h=64&dpr=1',
        location: {
          lat: 53.9000,
          lng: 27.5700,
          heading: 120,
          speed: 45
        },
        lastUpdate: new Date()
      },
      {
        id: 3,
        name: 'Дмитрий Козлов',
        nickname: 'dmitry_cabrio',
        photo_url: 'https://images.pexels.com/photos/774909/pexels-photo-774909.jpeg?auto=compress&cs=tinysrgb&w=64&h=64&dpr=1',
        location: {
          lat: 53.9100,
          lng: 27.5500,
          heading: 200,
          speed: 0
        },
        lastUpdate: new Date()
      }
    ]
    
    // Симуляция меток в Минске
    markers.value = [
      {
        id: 'police-1',
        type: 'police',
        location: {
          lat: 53.9080,
          lng: 27.5550
        },
        title: 'Пост ДПС',
        description: 'Проверяют документы',
        author: {
          id: 1,
          name: 'Александр Петров'
        },
        createdAt: new Date(),
        expiresAt: new Date(Date.now() + 2 * 60 * 60 * 1000) // 2 часа
      }
    ]
  }, 2000)
}

const disconnectFromMap = () => {
  isConnected.value = false
  connectedMembers.value = []
  markers.value = []
}

// Голосовая связь
const startRecording = () => {
  telegramStore.hapticFeedback('impact')
  isRecording.value = true
  // TODO: Начать запись голосового сообщения
  console.log('Started voice recording')
}

const stopRecording = () => {
  telegramStore.hapticFeedback('selection')
  isRecording.value = false
  // TODO: Остановить запись и отправить голосовое сообщение
  console.log('Stopped voice recording')
}

// Метки на карте
const addMarker = (marker: Omit<MapMarker, 'id' | 'author' | 'createdAt'>) => {
  const newMarker: MapMarker = {
    ...marker,
    id: `marker-${Date.now()}`,
    author: {
      id: telegramStore.user?.id || 0,
      name: `${telegramStore.user?.first_name} ${telegramStore.user?.last_name}`.trim()
    },
    createdAt: new Date()
  }
  
  markers.value.push(newMarker)
  telegramStore.hapticFeedback('notification')
  // TODO: Отправить метку на сервер
  console.log('Added marker:', newMarker)
}

const removeMarker = (markerId: string) => {
  const index = markers.value.findIndex(m => m.id === markerId)
  if (index !== -1) {
    markers.value.splice(index, 1)
    telegramStore.hapticFeedback('selection')
    // TODO: Удалить метку на сервере
    console.log('Removed marker:', markerId)
  }
}

const focusOnMember = (memberId: number) => {
  const member = connectedMembers.value.find(m => m.id === memberId)
  if (member) {
    telegramStore.hapticFeedback('selection')
    // TODO: Центрировать карту на участнике
    console.log('Focus on member:', member)
  }
}

onMounted(() => {
  connectToMap()
})

onUnmounted(() => {
  disconnectFromMap()
})
</script>

<style scoped>
.map-view.full-screen {
  height: 100vh;
  display: flex;
  flex-direction: column;
  background: var(--tg-theme-bg-color);
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 1;
}

.map-container {
  flex: 1;
  display: flex;
  flex-direction: column;
  position: relative;
}

.map-area {
  flex: 1;
  position: relative;
  min-height: 0;
}

.map-controls {
  position: absolute;
  top: 70px; /* Отступ от хедера */
  left: 0;
  right: 0;
  z-index: 10;
  padding: var(--spacing-sm);
  display: flex;
  flex-direction: column;
  gap: var(--spacing-sm);
  pointer-events: none;
}

.map-controls > * {
  pointer-events: all;
}

.top-controls {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: var(--spacing-sm);
}

.connection-status-half {
  display: flex;
  align-items: center;
  gap: var(--spacing-xs);
  padding: var(--spacing-xs) var(--spacing-sm);
  background: rgba(0, 0, 0, 0.8);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border-radius: var(--radius-lg);
  color: white;
  font-size: var(--font-size-sm);
  width: 100%;
  justify-content: center;
}

.members-list-btn-half {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: var(--spacing-xs);
  padding: var(--spacing-xs) var(--spacing-sm);
  background: rgba(0, 0, 0, 0.8);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  border-radius: var(--radius-lg);
  color: white;
  border: none;
  cursor: pointer;
  font-size: var(--font-size-sm);
  font-weight: var(--font-weight-medium);
  transition: all 0.2s ease;
  width: 100%;
}

.members-list-btn-half:hover {
  background: rgba(46, 166, 255, 0.8);
}

.members-button-container {
  position: relative;
  position: relative;
  grid-column: 2;
}

.status-indicator {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--error-color);
  animation: pulse 2s infinite;
}

.status-indicator.connected {
  background: var(--success-color);
}

@keyframes pulse {
  0% { opacity: 1; }
  50% { opacity: 0.5; }
  100% { opacity: 1; }
}

.status-text {
  font-weight: var(--font-weight-medium);
}

.members-count {
  color: rgba(255, 255, 255, 0.7);
  font-size: var(--font-size-xs);
  margin-left: auto;
}

@media (max-width: 480px) {
  .map-controls {
    top: 60px; /* Меньший отступ на мобильных */
    padding: var(--spacing-xs);
    gap: var(--spacing-xs);
  }
  
  .connection-status {
    font-size: var(--font-size-xs);
    padding: 6px var(--spacing-xs);
  }
}
</style>
<template>
  <div class="map-component">
    <div class="map-area">
      <!-- Yandex карта -->
      <div id="yandex-map" class="yandex-map"></div>
      
      <!-- Участники на карте (будут добавлены как метки на Yandex карту) -->
      <!-- Метки на карте (будут добавлены как метки на Yandex карту) -->
    </div>
    
    <!-- Кнопки управления -->
    <div class="map-controls">
      <button 
        class="control-btn my-location"
        @click="centerOnMyLocation"
      >
        <Navigation :size="20" />
      </button>
      
      <button 
        class="control-btn add-marker"
        @click="showAddMarkerDialog"
      >
        <Plus :size="20" />
      </button>
    </div>
    
    <!-- Диалог добавления метки -->
    <div v-if="showMarkerDialog" class="marker-dialog-overlay" @click="closeMarkerDialog">
      <div class="marker-dialog" @click.stop>
        <h3>Добавить метку</h3>
        <div class="marker-types">
          <button
            v-for="type in markerTypes"
            :key="type.id"
            class="marker-type-btn"
            @click="addMarkerOfType(type.id)"
          >
            <component :is="type.icon" :size="20" />
            <span>{{ type.label }}</span>
          </button>
        </div>
        <button class="cancel-btn" @click="closeMarkerDialog">
          Отмена
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, nextTick } from 'vue'
import { 
  Plus,
  Navigation,
  Shield,
  AlertTriangle,
  Construction,
  Fuel,
  ParkingCircle,
  Users
} from 'lucide-vue-next'

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

interface Props {
  members: ConnectedMember[]
  markers: MapMarker[]
}

const props = defineProps<Props>()
defineEmits<{
  'add-marker': [marker: Omit<MapMarker, 'id' | 'author' | 'createdAt'>]
  'remove-marker': [markerId: string]
  'focus-member': [memberId: number]
}>()

const showMarkerDialog = ref(false)
let yandexMap: any = null
let memberPlacemarks: Map<number, any> = new Map()
let markerPlacemarks: Map<string, any> = new Map()

const markerTypes = [
  { id: 'police' as const, label: 'ДПС', icon: Shield },
  { id: 'accident' as const, label: 'ДТП', icon: AlertTriangle },
  { id: 'roadwork' as const, label: 'Ремонт', icon: Construction },
  { id: 'fuel' as const, label: 'Заправка', icon: Fuel },
  { id: 'parking' as const, label: 'Парковка', icon: ParkingCircle },
  { id: 'meeting' as const, label: 'Встреча', icon: Users }
]

const initYandexMap = async () => {
  if (typeof window.ymaps === 'undefined') {
    console.warn('Yandex Maps API не загружен')
    return
  }

  await window.ymaps.ready()
  
  yandexMap = new window.ymaps.Map('yandex-map', {
    center: [53.9045, 27.5615], // Минск
    zoom: 12,
    controls: ['zoomControl', 'typeSelector']
  })

  // Добавляем существующих участников
  updateMemberPlacemarks()
  
  // Добавляем существующие метки
  updateMarkerPlacemarks()
}

const updateMemberPlacemarks = () => {
  if (!yandexMap) return

  // Удаляем старые метки участников
  memberPlacemarks.forEach(placemark => {
    yandexMap.geoObjects.remove(placemark)
  })
  memberPlacemarks.clear()

  // Добавляем новые метки участников
  props.members.forEach(member => {
    // Создаем HTML для аватарки с подписью скорости
    const avatarHtml = `
      <div style="pointer-events: none;">
        <div style="
          width: 32px; 
          height: 32px; 
          border-radius: 50%; 
          overflow: hidden; 
          border: 3px solid #2ea6ff;
          background: #2ea6ff;
          display: flex;
          align-items: center;
          justify-content: center;
          position: relative;
        ">
          ${member.photo_url ? 
            `<img src="${member.photo_url}" style="width: 100%; height: 100%; object-fit: cover; position: absolute; top: 0; left: 0;" alt="${member.name}">` :
            `<span style="color: white; font-weight: bold; font-size: 14px; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">${member.name.split(' ').map(n => n.charAt(0)).join('').slice(0, 2)}</span>`
          }
        </div>
      </div>
    `

    const placemark = new window.ymaps.Placemark(
      [member.location.lat, member.location.lng],
      {
        balloonContent: `
          <div style="padding: 8px;">
            <strong>${member.name}</strong><br>
            ${member.location.speed ? `Скорость: ${Math.round(member.location.speed)} км/ч` : 'Стоит'}
          </div>
        `
      },
      {
        iconLayout: 'default#imageWithContent',
        iconContentLayout: window.ymaps.templateLayoutFactory.createClass(avatarHtml),
        iconImageSize: [32, 32],
        iconImageOffset: [-16, -16]
      }
    )

    yandexMap.geoObjects.add(placemark)
    memberPlacemarks.set(member.id, placemark)
  })
}

const updateMarkerPlacemarks = () => {
  if (!yandexMap) return

  // Удаляем старые метки
  markerPlacemarks.forEach(placemark => {
    yandexMap.geoObjects.remove(placemark)
  })
  markerPlacemarks.clear()

  // Добавляем новые метки
  props.markers.forEach(marker => {
    const colors = {
      police: '#3b82f6',
      accident: '#f44336',
      roadwork: '#ff9800',
      fuel: '#10b981',
      parking: '#6366f1',
      meeting: '#2ea6ff'
    }

    const placemark = new window.ymaps.Placemark(
      [marker.location.lat, marker.location.lng],
      {
        balloonContent: `
          <div style="padding: 8px;">
            <strong>${marker.title}</strong><br>
            ${marker.description || ''}<br>
            <small>Добавил: ${marker.author.name}</small>
          </div>
        `
      },
      {
        preset: 'islands#circleIcon',
        iconColor: colors[marker.type]
      }
    )

    yandexMap.geoObjects.add(placemark)
    markerPlacemarks.set(marker.id, placemark)
  })
}

const showAddMarkerDialog = () => {
  showMarkerDialog.value = true
}

const closeMarkerDialog = () => {
  showMarkerDialog.value = false
}

const addMarkerOfType = (type: MapMarker['type']) => {
  // TODO: Получить текущее местоположение или позволить выбрать на карте
  const center = yandexMap ? yandexMap.getCenter() : [53.9045, 27.5615]
  const location = {
    lat: center[0] + (Math.random() - 0.5) * 0.01,
    lng: center[1] + (Math.random() - 0.5) * 0.01
  }
  
  const markerTitles = {
    police: 'Пост ДПС',
    accident: 'ДТП',
    roadwork: 'Дорожные работы',
    fuel: 'Заправка',
    parking: 'Парковка',
    meeting: 'Место встречи'
  }
  
  closeMarkerDialog()
  
  emits('add-marker', {
    type,
    location,
    title: markerTitles[type],
    description: `Добавлено ${new Date().toLocaleTimeString()}`
  })
}

const centerOnMyLocation = () => {
  if (navigator.geolocation && yandexMap) {
    navigator.geolocation.getCurrentPosition(
      (position) => {
        const coords = [position.coords.latitude, position.coords.longitude]
        yandexMap.setCenter(coords, 15)
      },
      (error) => {
        console.warn('Не удалось получить местоположение:', error)
        // Центрируем на Минске по умолчанию
        yandexMap.setCenter([53.9045, 27.5615], 12)
      }
    )
  }
}

onMounted(async () => {
  await nextTick()
  initYandexMap()
})

onUnmounted(() => {
  if (yandexMap) {
    yandexMap.destroy()
    yandexMap = null
  }
})

// Следим за изменениями участников и меток через watch
import { watch } from 'vue'

watch(() => props.members, () => {
  if (yandexMap) {
    updateMemberPlacemarks()
  }
}, { deep: true })

watch(() => props.markers, () => {
  if (yandexMap) {
    updateMarkerPlacemarks()
  }
}, { deep: true })
</script>

<style scoped>
.map-component {
  position: relative;
  width: 100%;
  height: 100%;
  background: #f8f9fa;
  overflow: hidden;
}

.map-area {
  width: 100%;
  height: 100%;
  position: relative;
}

.yandex-map {
  width: 100%;
  height: 100%;
}

.map-controls {
  position: absolute;
  bottom: 110px; /* Отступ от нижнего меню */
  right: var(--spacing-md);
  display: flex;
  flex-direction: column;
  gap: var(--spacing-sm);
}

.control-btn {
  width: 48px;
  height: 48px;
  border-radius: var(--radius-lg);
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 12px rgba(0,0,0,0.15);
  transition: all 0.2s ease;
}

.control-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 16px rgba(0,0,0,0.2);
}

.add-marker {
  background: var(--primary-color);
  color: white;
}

.my-location {
  background: white;
  color: var(--tg-theme-text-color);
}

.marker-dialog-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 100;
  backdrop-filter: blur(5px);
  -webkit-backdrop-filter: blur(5px);
}

.marker-dialog {
  background: var(--card-bg);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-lg);
  padding: var(--spacing-xl);
  max-width: 320px;
  width: 90%;
  box-shadow: 0 8px 32px rgba(0,0,0,0.3);
}

.marker-dialog h3 {
  font-size: var(--font-size-lg);
  font-weight: var(--font-weight-semibold);
  margin-bottom: var(--spacing-lg);
  color: var(--tg-theme-text-color);
  text-align: center;
}

.marker-types {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: var(--spacing-sm);
  margin-bottom: var(--spacing-lg);
}

.marker-type-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: var(--spacing-sm);
  padding: var(--spacing-md);
  background: var(--primary-color);
  border: 2px solid var(--primary-color);
  border-radius: var(--radius-lg);
  color: white;
  cursor: pointer;
  transition: all 0.2s ease;
  min-height: 80px;
  font-weight: var(--font-weight-semibold);
}

.marker-type-btn:hover {
  background: #1e90ff;
  border-color: #1e90ff;
  transform: translateY(-2px);
  box-shadow: 0 4px 16px rgba(46, 166, 255, 0.3);
}

.marker-type-btn span {
  font-size: var(--font-size-sm);
  font-weight: var(--font-weight-semibold);
  text-align: center;
}

.cancel-btn {
  width: 100%;
  padding: var(--spacing-md);
  background: var(--error-color);
  color: white;
  border: none;
  border-radius: var(--radius-lg);
  font-size: var(--font-size-md);
  font-weight: var(--font-weight-semibold);
  cursor: pointer;
  transition: all 0.2s ease;
}

.cancel-btn:hover {
  background: #d32f2f;
  transform: translateY(-1px);
}

@media (max-width: 480px) {
  .map-controls {
    bottom: 100px; /* Отступ от нижнего меню на мобильных */
    right: var(--spacing-sm);
    gap: var(--spacing-xs);
    z-index: 15; /* Убеждаемся что кнопки видны */
  }
  
  .control-btn {
    width: 44px;
    height: 44px;
  }
  
  .marker-types {
    grid-template-columns: 1fr;
    gap: var(--spacing-sm);
  }
  
  .marker-dialog {
    max-width: 280px;
    padding: var(--spacing-lg);
  }
  
  .marker-type-btn {
    min-height: 60px;
    padding: var(--spacing-sm);
  }
}
</style>
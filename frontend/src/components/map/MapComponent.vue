<template>
  <div class="map-component">
    <div id="yandex-map" ref="mapContainer" class="map-container"></div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch } from 'vue'
import { useDataStore } from '@/stores/data'
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

// Типы для Yandex Maps
declare global {
  interface Window {
    ymaps: {
      ready(): Promise<void>
      Map: new (element: string, options: any) => any
      Placemark: new (coordinates: number[], properties: any, options: any) => any
      templateLayoutFactory: {
        createClass(template: string): any
      }
    }
  }
}

interface Props {
  members: any[]
  cars: any[]
  center?: [number, number]
  zoom?: number
}

const props = withDefaults(defineProps<Props>(), {
  center: () => [55.7558, 37.6176], // Москва
  zoom: () => 10
})

const emit = defineEmits<{
  'add-marker': [data: { type: string; id: number; coordinates: [number, number] }]
}>()

const dataStore = useDataStore()
const mapContainer = ref<HTMLElement>()
let yandexMap: any = null
let markers: any[] = []

// Функция для загрузки Yandex Maps
async function loadYandexMaps() {
  if (typeof window.ymaps === 'undefined') {
    // Загружаем скрипт Yandex Maps
    const script = document.createElement('script')
    script.src = 'https://api-maps.yandex.ru/2.1/?apikey=your-api-key&lang=ru_RU'
    script.async = true
    document.head.appendChild(script)
    
    await new Promise<void>((resolve) => {
      script.onload = () => resolve()
    })
  }
  
  await window.ymaps.ready()
}

// Инициализация карты
async function initMap() {
  if (!mapContainer.value) return
  
  try {
    await loadYandexMaps()
    
    yandexMap = new window.ymaps.Map('yandex-map', {
      center: props.center,
      zoom: props.zoom,
      controls: ['zoomControl', 'fullscreenControl']
    })
    
    // Добавляем обработчики событий
    yandexMap.events.add('click', (e: any) => {
      const coords = e.get('coords')
      emit('add-marker', {
        type: 'custom',
        id: Date.now(),
        coordinates: coords
      })
    })
    
    updateMarkers()
  } catch (error) {
    console.error('Ошибка инициализации карты:', error)
  }
}

// Обновление маркеров
function updateMarkers() {
  if (!yandexMap) return
  
  // Очищаем существующие маркеры
  markers.forEach(marker => yandexMap.geoObjects.remove(marker))
  markers = []
  
  // Добавляем маркеры участников
  props.members.forEach(member => {
    if (member.coordinates) {
      const avatarHtml = `
        <div style="width: 40px; height: 40px; border-radius: 50%; overflow: hidden; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);">
          <img src="${member.photo_url || '/default-avatar.png'}" 
               style="width: 100%; height: 100%; object-fit: cover;" 
               alt="${member.first_name}">
        </div>
      `
      
      const placemark = new window.ymaps.Placemark(
        member.coordinates,
        {
          balloonContent: `
            <div style="padding: 10px;">
              <h3>${member.first_name} ${member.last_name || ''}</h3>
              <p>${member.city}</p>
            </div>
          `
        },
        {
          iconLayout: 'default#image',
          iconImageHref: member.photo_url || '/default-avatar.png',
          iconImageSize: [40, 40],
          iconImageOffset: [-20, -20],
          iconContentLayout: window.ymaps.templateLayoutFactory.createClass(avatarHtml),
        }
      )
      
      yandexMap.geoObjects.add(placemark)
      markers.push(placemark)
    }
  })
  
  // Добавляем маркеры автомобилей
  props.cars.forEach(car => {
    if (car.coordinates) {
      const placemark = new window.ymaps.Placemark(
        car.coordinates,
        {
          balloonContent: `
            <div style="padding: 10px;">
              <h3>${car.brand.name} ${car.model}</h3>
              <p>${car.reg_number}</p>
              <p>Владелец: ${car.owner?.first_name || 'Неизвестно'}</p>
            </div>
          `
        },
        {
          iconLayout: 'default#image',
          iconImageHref: '/car-marker.png',
          iconImageSize: [30, 30],
          iconImageOffset: [-15, -15]
        }
      )
      
      yandexMap.geoObjects.add(placemark)
      markers.push(placemark)
    }
  })
}

// Следим за изменениями данных
watch(() => [props.members, props.cars], updateMarkers, { deep: true })

onMounted(() => {
  initMap()
})

onUnmounted(() => {
  if (yandexMap) {
    yandexMap.destroy()
  }
})
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
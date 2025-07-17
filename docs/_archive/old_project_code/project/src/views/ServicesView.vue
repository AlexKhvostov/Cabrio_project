<template>
  <div class="services-view">
    <div class="container">
      <FiltersSection
        v-model:searchQuery="searchQuery"
        searchPlaceholder="Поиск по названию, городу или услугам..."
        :filters="filterOptions"
        :selectedFilters="[
          { value: categoryFilter },
          { value: typeFilter }
        ]"
        @update:filter="updateFilter"
      />

      <div v-if="dataStore.loading" class="loading">
        <div class="spinner"></div>
      </div>

      <div v-else-if="filteredServices.length === 0" class="empty-state">
        <BookOpen :size="48" />
        <h3>Сервисы не найдены</h3>
        <p>Попробуйте изменить фильтры поиска</p>
      </div>

      <div v-else class="services-list">
        <ServiceCard
          v-for="service in filteredServices"
          :key="service.id"
          :service="service"
          @select="selectService"
        />
      </div>
    </div>
  </div>
  
  <!-- Guide Object Detail Modal -->
  <GuideObjectDetailModal
    :show="showGuideModal"
    :guide-object="selectedGuideObject"
    @close="closeGuideModal"
    @add-review="addReview"
  />
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { BookOpen } from 'lucide-vue-next'
import { useDataStore, type Service } from '@/stores/data'
import { useTelegramStore } from '@/stores/telegram'
import ServiceCard from '@/components/services/ServiceCard.vue'
import FiltersSection from '@/components/common/FiltersSection.vue'
import GuideObjectDetailModal from '@/components/services/GuideObjectDetailModal.vue'

const dataStore = useDataStore()
const telegramStore = useTelegramStore()

const searchQuery = ref('')
const categoryFilter = ref('')
const typeFilter = ref('')
const showGuideModal = ref(false)
const selectedGuideObject = ref<Service | null>(null)

// Маппинг категорий к типам услуг
const categoryToTypes = {
  'фотосессии': [
    { value: 'парковка', label: 'Парковка' },
    { value: 'смотровая', label: 'Смотровая площадка' },
    { value: 'локация', label: 'Фото-локация' }
  ],
  'питание': [
    { value: 'кафе', label: 'Кафе' },
    { value: 'ресторан', label: 'Ресторан' },
    { value: 'бар', label: 'Бар' },
    { value: 'фастфуд', label: 'Быстрое питание' }
  ],
  'ремонт': [
    { value: 'автосервис', label: 'Автосервис' },
    { value: 'детейлинг', label: 'Детейлинг' },
    { value: 'шиномонтаж', label: 'Шиномонтаж' },
    { value: 'электрик', label: 'Автоэлектрик' },
    { value: 'кузовной', label: 'Кузовной ремонт' }
  ],
  'обслуживание': [
    { value: 'средство', label: 'Средство' },
    { value: 'инструмент', label: 'Инструмент' },
    { value: 'лайфхак', label: 'Лайфхак' },
    { value: 'запчасти', label: 'Запчасти' }
  ],
  'отдых': [
    { value: 'база_отдыха', label: 'База отдыха' },
    { value: 'пляж', label: 'Пляж' },
    { value: 'развлечения', label: 'Развлечения' },
    { value: 'отель', label: 'Отель' }
  ]
}

const filterOptions = computed(() => {
  const filters = [
    {
      placeholder: 'Все типы',
      options: [
        { value: 'фотосессии', label: 'Фотосессии' },
        { value: 'питание', label: 'Питание' },
        { value: 'ремонт', label: 'Ремонт' },
        { value: 'обслуживание', label: 'Обслуживание' },
        { value: 'отдых', label: 'Отдых' }
      ]
    }
  ]

  // Второй фильтр зависит от выбранной категории
  if (categoryFilter.value && categoryToTypes[categoryFilter.value as keyof typeof categoryToTypes]) {
    filters.push({
      placeholder: 'Все виды',
      options: categoryToTypes[categoryFilter.value as keyof typeof categoryToTypes]
    })
  } else {
    filters.push({
      placeholder: 'Все виды',
      options: []
    })
  }

  return filters
})

const updateFilter = ({ index, value }: { index: number, value: string }) => {
  if (index === 0) {
    categoryFilter.value = value
    // Сбрасываем фильтр типа при смене категории
    typeFilter.value = ''
  } else if (index === 1) {
    typeFilter.value = value
  }
}

const filteredServices = computed(() => {
  let filtered = dataStore.services

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(service => 
      service.name.toLowerCase().includes(query) ||
      service.city.toLowerCase().includes(query) ||
      service.category.toLowerCase().includes(query) ||
      service.type.toLowerCase().includes(query) ||
      service.services.some(s => s.toLowerCase().includes(query))
    )
  }

  if (categoryFilter.value) {
    filtered = filtered.filter(service => service.category === categoryFilter.value)
  }

  if (typeFilter.value) {
    filtered = filtered.filter(service => service.type === typeFilter.value)
  }

  return filtered
})

const selectService = (service: Service) => {
  telegramStore.hapticFeedback('impact')
  selectedGuideObject.value = service
  showGuideModal.value = true
}

const closeGuideModal = () => {
  showGuideModal.value = false
  selectedGuideObject.value = null
}

onMounted(() => {
  dataStore.fetchServices()
})
</script>

<style scoped>
.services-view {
  padding: var(--spacing-lg) 0;
}

.services-list {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-sm);
}

.empty-state {
  text-align: center;
  padding: var(--spacing-xl);
  color: var(--tg-theme-hint-color);
}

.empty-state svg {
  margin-bottom: var(--spacing-md);
  opacity: 0.5;
}

.empty-state h3 {
  font-size: var(--font-size-lg);
  font-weight: var(--font-weight-semibold);
  margin-bottom: var(--spacing-sm);
  color: var(--tg-theme-hint-color);
}

.empty-state p {
  font-size: var(--font-size-sm);
  margin: 0;
}
</style>
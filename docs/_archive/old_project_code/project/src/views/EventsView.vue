<template>
  <div class="events-view">
    <div class="container">
      <FiltersSection
        v-model:searchQuery="searchQuery"
        searchPlaceholder="Поиск по названию или описанию..."
        :filters="filterOptions"
        :selectedFilters="[
          { value: typeFilter },
          { value: statusFilter }
        ]"
        @update:filter="updateFilter"
      />

      <div v-if="dataStore.loading" class="loading">
        <div class="spinner"></div>
      </div>

      <div v-else-if="filteredEvents.length === 0" class="empty-state">
        <Calendar :size="48" />
        <h3>События не найдены</h3>
        <p>Попробуйте изменить фильтры поиска</p>
      </div>

      <div v-else class="events-list">
        <EventCard
          v-for="event in filteredEvents"
          :key="event.id"
          :event="event"
          @select="selectEvent"
        />
      </div>
    </div>
  </div>
  
  <!-- Event Detail Modal -->
  <EventDetailModal
    :show="showEventModal"
    :event="selectedEvent"
    @close="closeEventModal"
    @join="joinEvent"
  />
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Calendar } from 'lucide-vue-next'
import { useDataStore, type Event } from '@/stores/data'
import { useTelegramStore } from '@/stores/telegram'
import EventCard from '@/components/events/EventCard.vue'
import FiltersSection from '@/components/common/FiltersSection.vue'
import EventDetailModal from '@/components/events/EventDetailModal.vue'

const dataStore = useDataStore()
const telegramStore = useTelegramStore()

const searchQuery = ref('')
const typeFilter = ref('')
const statusFilter = ref('')
const showEventModal = ref(false)
const selectedEvent = ref<Event | null>(null)

const filterOptions = computed(() => [
  {
    placeholder: 'Все типы',
    options: [
      { value: 'встреча клуба', label: 'Встреча клуба' },
      { value: 'поездка/круиз', label: 'Поездка/круиз' },
      { value: 'мастер-класс', label: 'Мастер-класс' },
      { value: 'выезд на природу', label: 'Выезд на природу' },
      { value: 'волонтёрство/благотворительность', label: 'Волонтёрство/благотворительность' }
    ]
  },
  {
    placeholder: 'Все статусы',
    options: [
      { value: 'запланировано', label: 'Запланировано' },
      { value: 'завершено', label: 'Завершено' },
      { value: 'отменено', label: 'Отменено' }
    ]
  }
])

const updateFilter = ({ index, value }: { index: number, value: string }) => {
  if (index === 0) {
    typeFilter.value = value
  } else if (index === 1) {
    statusFilter.value = value
  }
}

const filteredEvents = computed(() => {
  let filtered = dataStore.events

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(event => 
      event.title.toLowerCase().includes(query) ||
      event.description.toLowerCase().includes(query) ||
      event.city.toLowerCase().includes(query) ||
      event.organizer_name.toLowerCase().includes(query)
    )
  }

  if (typeFilter.value) {
    filtered = filtered.filter(event => event.type === typeFilter.value)
  }

  if (statusFilter.value) {
    filtered = filtered.filter(event => event.status === statusFilter.value)
  }

  return filtered.sort((a, b) => new Date(a.event_date).getTime() - new Date(b.event_date).getTime())
})

const selectEvent = (event: Event) => {
  telegramStore.hapticFeedback('impact')
  selectedEvent.value = event
  showEventModal.value = true
}

const closeEventModal = () => {
  showEventModal.value = false
  selectedEvent.value = null
}

onMounted(() => {
  dataStore.fetchEvents()
})
</script>

<style scoped>
.events-view {
  padding: var(--spacing-lg) 0;
}

.events-list {
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
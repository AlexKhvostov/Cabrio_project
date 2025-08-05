<template>
  <div class="cars-view">
    <div class="container">
      <FiltersSection
        v-model:searchQuery="searchQuery"
        searchPlaceholder="Поиск по марке, модели или номеру..."
        :filters="filterOptions"
        :selectedFilters="[{ value: brandFilter }]"
        :yearFilter="true"
        :yearFilterValue="yearFilter"
        @update:filter="updateFilter"
        @update:yearFilter="yearFilter = $event"
      />

      <div v-if="dataStore.loading" class="loading">
        <div class="spinner"></div>
      </div>

      <div v-else-if="dataStore.error" class="error-state">
        <h3>Ошибка загрузки</h3>
        <p>{{ dataStore.error }}</p>
        <button @click="retryLoad" class="retry-button">
          Попробовать снова
        </button>
      </div>

      <div v-else-if="filteredCars.length === 0" class="empty-state">
        <Car :size="48" />
        <h3>Автомобили не найдены</h3>
        <p>Попробуйте изменить фильтры поиска</p>
      </div>

      <div v-else class="cars-list">
        <div class="cars-grid">
          <CarCard
            v-for="car in filteredCars"
            :key="car.id"
            :car="car"
            @select="selectCar"
          />
        </div>
      </div>
    </div>
    
    <!-- Car Detail Modal -->
    <CarDetailModal
      v-if="selectedCar"
      :show="showCarModal"
      :car="selectedCar"
      @close="closeCarModal"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Car } from 'lucide-vue-next'
import { useDataStore, type Car as CarType } from '@/stores/data'
import { useTelegramStore } from '@/stores/telegram'
import CarCard from '@/components/cars/CarCard.vue'
import CarDetailModal from '@/components/cars/CarDetailModal.vue'
import FiltersSection from '@/components/common/FiltersSection.vue'

const dataStore = useDataStore()
const telegramStore = useTelegramStore()

const searchQuery = ref('')
const brandFilter = ref('')
const yearFilter = ref('')
const showCarModal = ref(false)
const selectedCar = ref<CarType | null>(null)

const brands = computed(() => {
  const allBrands = dataStore.cars.map(car => car.brand.name)
  return [...new Set(allBrands)].sort()
})

const filterOptions = computed(() => [
  {
    placeholder: 'Все марки',
    options: brands.value.map(brand => ({ value: brand, label: brand }))
  }
])

const updateFilter = ({ index, value }: { index: number, value: string }) => {
  if (index === 0) {
    brandFilter.value = value
  }
}

const filteredCars = computed(() => {
  let filtered = dataStore.cars

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(car => 
      car.brand.name.toLowerCase().includes(query) ||
      car.model.toLowerCase().includes(query) ||
      car.reg_number.toLowerCase().includes(query) ||
      car.owner?.first_name?.toLowerCase().includes(query) ||
      car.owner?.last_name?.toLowerCase().includes(query)
    )
  }

  if (brandFilter.value) {
    filtered = filtered.filter(car => car.brand.name === brandFilter.value)
  }

  if (yearFilter.value) {
    filtered = filtered.filter(car => car.year.toString() === yearFilter.value)
  }

  return filtered
})

const selectCar = (car: CarType) => {
  telegramStore.hapticFeedback('impact')
  selectedCar.value = car
  showCarModal.value = true
}

const closeCarModal = () => {
  showCarModal.value = false
  selectedCar.value = null
}

const retryLoad = async () => {
  try {
    await dataStore.fetchCars()
  } catch (error) {
    console.error('Failed to retry loading cars:', error)
  }
}

onMounted(async () => {
  try {
    await dataStore.fetchCars()
  } catch (error) {
    console.error('Failed to load cars:', error)
  }
})
</script>

<style scoped>
.cars-view {
  padding: var(--spacing-lg) 0;
}

.cars-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: var(--spacing-md);
}

.cars-list {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-md);
}

.empty-state,
.error-state {
  text-align: center;
  padding: var(--spacing-xl);
  color: var(--tg-theme-hint-color);
}

.empty-state svg,
.error-state svg {
  margin-bottom: var(--spacing-md);
  opacity: 0.5;
}

.empty-state h3,
.error-state h3 {
  font-size: var(--font-size-lg);
  font-weight: var(--font-weight-semibold);
  margin-bottom: var(--spacing-sm);
  color: var(--tg-theme-hint-color);
}

.empty-state p,
.error-state p {
  font-size: var(--font-size-sm);
  margin: 0;
}

.retry-button {
  margin-top: var(--spacing-md);
  padding: var(--spacing-sm) var(--spacing-md);
  background: var(--primary-color);
  color: white;
  border: none;
  border-radius: var(--radius-md);
  cursor: pointer;
  font-size: var(--font-size-sm);
}

.retry-button:hover {
  background: var(--primary-color-hover);
}

@media (max-width: 768px) {
  .cars-grid {
    gap: var(--spacing-sm);
  }
}

@media (max-width: 480px) {
  .cars-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: var(--spacing-sm);
  }
}
</style>
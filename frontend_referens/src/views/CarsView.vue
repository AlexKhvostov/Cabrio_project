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
  const allBrands = dataStore.cars.map(car => car.brand)
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
      car.brand.toLowerCase().includes(query) ||
      car.model.toLowerCase().includes(query) ||
      car.reg_number.toLowerCase().includes(query) ||
      car.owner_name?.toLowerCase().includes(query)
    )
  }

  if (brandFilter.value) {
    filtered = filtered.filter(car => car.brand === brandFilter.value)
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

onMounted(() => {
  dataStore.fetchCars()
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
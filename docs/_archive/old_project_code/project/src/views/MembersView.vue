<template>
  <div class="members-view">
    <div class="container">
      <FiltersSection
        v-model:searchQuery="searchQuery"
        searchPlaceholder="Поиск по имени или никнейму..."
        :filters="filterOptions"
        :selectedFilters="[
          { value: statusFilter },
          { value: cityFilter }
        ]"
        @update:filter="updateFilter"
      />

      <div v-if="dataStore.loading" class="loading">
        <div class="spinner"></div>
      </div>

      <div v-else-if="filteredMembers.length === 0" class="empty-state">
        <Users :size="48" />
        <h3>Участники не найдены</h3>
        <p>Попробуйте изменить фильтры поиска</p>
      </div>

      <div v-else class="members-list">
        <MemberCard
          v-for="member in filteredMembers"
          :key="member.id"
          :member="member"
          @select="selectMember"
        />
      </div>
    </div>
    
    <!-- Member Detail Modal -->
    <MemberDetailModal
      :show="showMemberModal"
      :member="selectedMember"
      @close="closeMemberModal"
      @select-car="selectCar"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Users } from 'lucide-vue-next'
import { useDataStore, type Member } from '@/stores/data'
import { useTelegramStore } from '@/stores/telegram'
import MemberCard from '@/components/members/MemberCard.vue'
import MemberDetailModal from '@/components/members/MemberDetailModal.vue'
import FiltersSection from '@/components/common/FiltersSection.vue'

const dataStore = useDataStore()
const telegramStore = useTelegramStore()

const searchQuery = ref('')
const statusFilter = ref('')
const cityFilter = ref('')
const showMemberModal = ref(false)
const selectedMember = ref<Member | null>(null)

const cities = computed(() => {
  const allCities = dataStore.members.map(member => member.city)
  return [...new Set(allCities)].sort()
})

const filterOptions = computed(() => [
  {
    placeholder: 'Все статусы',
    options: [
      { value: 'активный', label: 'Активный' },
      { value: 'зарегистрирован', label: 'Зарегистрирован' },
      { value: 'новый', label: 'Новый' }
    ]
  },
  {
    placeholder: 'Все города',
    options: cities.value.map(city => ({ value: city, label: city }))
  }
])

const updateFilter = ({ index, value }: { index: number, value: string }) => {
  if (index === 0) {
    statusFilter.value = value
  } else if (index === 1) {
    cityFilter.value = value
  }
}

const filteredMembers = computed(() => {
  let filtered = dataStore.members

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(member => 
      member.first_name.toLowerCase().includes(query) ||
      member.last_name?.toLowerCase().includes(query) ||
      member.nickname?.toLowerCase().includes(query)
    )
  }

  if (statusFilter.value) {
    filtered = filtered.filter(member => member.status === statusFilter.value)
  }

  if (cityFilter.value) {
    filtered = filtered.filter(member => member.city === cityFilter.value)
  }

  return filtered
})

const selectMember = (member: Member) => {
  telegramStore.hapticFeedback('impact')
  selectedMember.value = member
  showMemberModal.value = true
}

const closeMemberModal = () => {
  showMemberModal.value = false
  selectedMember.value = null
}

const selectCar = (car: any) => {
  telegramStore.hapticFeedback('selection')
  // TODO: Show car details
  console.log('Selected car:', car)
}

onMounted(() => {
  dataStore.fetchMembers()
})
</script>

<style scoped>
.members-view {
  padding: var(--spacing-lg) 0;
}

.members-list {
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
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

      <div v-else-if="dataStore.error" class="error-state">
        <h3>Ошибка загрузки</h3>
        <p>{{ dataStore.error }}</p>
        <button @click="retryLoad" class="retry-button">
          Попробовать снова
        </button>
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
      v-if="selectedMember"
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
    placeholder: 'Все роли',
    options: [
      { value: 'admin', label: 'Администратор' },
      { value: 'moderator', label: 'Модератор' },
      { value: 'member', label: 'Участник' },
      { value: 'registered', label: 'Зарегистрирован' },
      { value: 'new', label: 'Новый' },
      { value: 'guest', label: 'Гость' }
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
      member.username?.toLowerCase().includes(query)
    )
  }

  if (statusFilter.value) {
    filtered = filtered.filter(member => member.role.code === statusFilter.value)
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

const retryLoad = async () => {
  try {
    await dataStore.fetchMembers()
  } catch (error) {
    console.error('Failed to retry loading members:', error)
  }
}

onMounted(async () => {
  try {
    await dataStore.fetchMembers()
  } catch (error) {
    console.error('Failed to load members:', error)
  }
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
</style>
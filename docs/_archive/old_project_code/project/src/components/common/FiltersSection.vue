<template>
  <div class="filters-section">
    <div class="search-bar">
      <Search :size="20" />
      <input
        :value="searchQuery"
        @input="$emit('update:searchQuery', $event.target.value)"
        type="text"
        :placeholder="searchPlaceholder"
        class="search-input"
      />
    </div>
    
    <div class="filter-row">
      <select 
        v-if="filters.length > 0"
        :value="selectedFilters[0]?.value || ''"
        @change="$emit('update:filter', { index: 0, value: $event.target.value })"
        class="filter-select"
      >
        <option value="">{{ filters[0]?.placeholder || 'Все' }}</option>
        <option 
          v-for="option in filters[0]?.options || []" 
          :key="option.value" 
          :value="option.value"
        >
          {{ option.label }}
        </option>
      </select>
      
      <select 
        v-if="filters.length > 1"
        :value="selectedFilters[1]?.value || ''"
        @change="$emit('update:filter', { index: 1, value: $event.target.value })"
        class="filter-select"
      >
        <option value="">{{ filters[1]?.placeholder || 'Все' }}</option>
        <option 
          v-for="option in filters[1]?.options || []" 
          :key="option.value" 
          :value="option.value"
        >
          {{ option.label }}
        </option>
      </select>
      
      <input
        v-if="yearFilter"
        :value="yearFilterValue"
        @input="$emit('update:yearFilter', $event.target.value)"
        type="number"
        placeholder="Год"
        class="filter-input"
        min="1990"
        max="2024"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { Search } from 'lucide-vue-next'

interface FilterOption {
  value: string
  label: string
}

interface Filter {
  placeholder: string
  options: FilterOption[]
}

interface SelectedFilter {
  value: string
}

interface Props {
  searchQuery: string
  searchPlaceholder: string
  filters: Filter[]
  selectedFilters?: SelectedFilter[]
  yearFilter?: boolean
  yearFilterValue?: string
}

withDefaults(defineProps<Props>(), {
  selectedFilters: () => []
})
defineEmits<{
  'update:searchQuery': [value: string]
  'update:filter': [data: { index: number, value: string }]
  'update:yearFilter': [value: string]
}>()
</script>

<style scoped>
.filters-section {
  margin-bottom: var(--spacing-lg);
}

.search-bar {
  position: relative;
  margin-bottom: var(--spacing-md);
}

.search-bar svg {
  position: absolute;
  left: var(--spacing-md);
  top: 50%;
  transform: translateY(-50%);
  color: var(--tg-theme-hint-color);
  pointer-events: none;
}

.search-input {
  width: 100%;
  padding: var(--spacing-sm) var(--spacing-md) var(--spacing-sm) 48px;
  border: 1px solid var(--border-color);
  border-radius: var(--radius-md);
  background: var(--card-bg);
  color: var(--tg-theme-text-color);
  font-size: var(--font-size-md);
  transition: border-color 0.2s ease;
}

.search-input:focus {
  outline: none;
  border-color: var(--primary-color);
}

.search-input::placeholder {
  color: var(--tg-theme-hint-color);
}

.filter-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
  gap: var(--spacing-md);
}

.filter-select,
.filter-input {
  padding: var(--spacing-sm) var(--spacing-md);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-md);
  background: var(--card-bg);
  color: var(--tg-theme-text-color);
  font-size: var(--font-size-sm);
}

.filter-select:focus,
.filter-input:focus {
  outline: none;
  border-color: var(--primary-color);
}

.filter-input::placeholder {
  color: var(--tg-theme-hint-color);
}

@media (max-width: 480px) {
  .filter-row {
    grid-template-columns: 1fr;
    gap: var(--spacing-sm);
  }
}
</style>
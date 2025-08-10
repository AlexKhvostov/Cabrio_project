<template>
  <div class="view">
    <header class="header">
      <h2>Автомобили</h2>
    </header>

    <div v-if="loading" class="loading">Загрузка...</div>
    <div v-else class="grid">
      <div v-for="c in cars" :key="c.id" class="card">
        <div class="row">
          <div class="left">
            <div class="brand">{{ c.brand?.name }}</div>
            <div class="model">{{ c.model }}</div>
            <div class="plate">{{ c.reg_number }}</div>
          </div>
          <img v-if="c.photo" :src="c.photo.url" class="photo" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { getCars } from '../services/api';

const loading = ref(true);
const cars = ref<any[]>([]);

onMounted(async () => {
  try {
    const res = await getCars();
    if (res?.success) cars.value = res.data;
  } finally {
    loading.value = false;
  }
});
</script>

<style scoped>
.view { padding: 12px; }
.grid { display: grid; gap: 10px; }
.card { padding: 10px; border-radius: 12px; background: var(--tg-theme-secondary-bg-color, #f6f6f6); }
.row { display: flex; gap: 10px; align-items: center; justify-content: space-between; }
.left { display: grid; gap: 4px; }
.brand { font-weight: 700; }
.photo { width: 84px; height: 56px; object-fit: cover; border-radius: 8px; }
.plate { opacity: 0.7; }
</style>

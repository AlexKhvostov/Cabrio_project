<template>
  <div class="view">
    <header class="header">
      <h2>Участники</h2>
      <input v-model="q" placeholder="Поиск" class="search" />
    </header>

    <div v-if="loading" class="loading">Загрузка...</div>
    <div v-else>
      <div v-for="u in filtered" :key="u.id" class="card">
        <div class="row">
          <img v-if="u.photo" :src="u.photo.url" alt="" class="avatar" />
          <div class="info">
            <div class="name">@{{ u.username || 'no-username' }}</div>
            <div class="role">{{ u.role?.name }}</div>
          </div>
        </div>
        <div class="cars" v-if="u.cars && u.cars.length">
          <div class="car" v-for="c in u.cars" :key="c.id">
            <span class="brand">{{ c.brand?.name }}</span>
            <span class="model">{{ c.model }}</span>
            <span class="plate">{{ c.reg_number }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref, computed } from 'vue';
import { getUsers } from '../services/api';

const loading = ref(true);
const users = ref<any[]>([]);
const q = ref('');

const filtered = computed(() => {
  const qq = q.value.toLowerCase();
  if (!qq) return users.value;
  return users.value.filter(u =>
    (u.username || '').toLowerCase().includes(qq) ||
    (u.first_name || '').toLowerCase().includes(qq) ||
    (u.last_name || '').toLowerCase().includes(qq)
  );
});

onMounted(async () => {
  try {
    const res = await getUsers();
    if (res?.success) users.value = res.data;
  } catch (e) {
    // no-op
  } finally {
    loading.value = false;
  }
});
</script>

<style scoped>
.view { padding: 12px; }
.header { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
.search { flex: 1; padding: 10px 12px; border-radius: 10px; border: 1px solid rgba(0,0,0,0.1); background: var(--tg-theme-secondary-bg-color, #f1f1f1); color: inherit; }
.card { padding: 10px; border-radius: 12px; background: var(--tg-theme-secondary-bg-color, #f6f6f6); margin: 10px 0; }
.row { display: flex; gap: 10px; align-items: center; }
.avatar { width: 44px; height: 44px; object-fit: cover; border-radius: 50%; }
.info .name { font-weight: 600; }
.cars { margin-top: 8px; display: grid; gap: 6px; }
.car { display: flex; gap: 8px; font-size: 13px; opacity: 0.9; }
.brand { font-weight: 600; }
.plate { margin-left: auto; opacity: 0.7; }
</style>

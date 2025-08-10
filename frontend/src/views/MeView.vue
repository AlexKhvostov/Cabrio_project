<template>
  <div class="view">
    <header class="header">
      <h2>Мой профиль</h2>
    </header>
    <div v-if="loading" class="loading">Загрузка...</div>
    <div v-else-if="!me" class="error">Не авторизован</div>
    <div v-else class="card">
      <div class="row">
        <img v-if="me.photo" :src="me.photo.url" class="avatar" />
        <div class="info">
          <div class="name">@{{ me.username || 'no-username' }}</div>
          <div class="role">{{ me.role?.name }}</div>
        </div>
      </div>
      <div class="cars" v-if="me.cars?.length">
        <h3>Мои авто</h3>
        <div class="car" v-for="c in me.cars" :key="c.id">
          <span class="brand">{{ c.brand?.name }}</span>
          <span class="model">{{ c.model }}</span>
          <span class="plate">{{ c.reg_number }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { getProfile } from '../services/api';

const loading = ref(true);
const me = ref<any | null>(null);

onMounted(async () => {
  try {
    const res = await getProfile();
    if (res?.success) me.value = res.data;
  } finally {
    loading.value = false;
  }
});
</script>

<style scoped>
.view { padding: 12px; }
.card { padding: 10px; border-radius: 12px; background: var(--tg-theme-secondary-bg-color, #f6f6f6); }
.row { display: flex; gap: 10px; align-items: center; }
.avatar { width: 60px; height: 60px; object-fit: cover; border-radius: 50%; }
.name { font-weight: 700; }
.cars { margin-top: 10px; display: grid; gap: 6px; }
.car { display: flex; gap: 8px; font-size: 13px; }
.plate { margin-left: auto; opacity: 0.7; }
</style>

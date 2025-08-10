<template>
  <div class="view">
    <header class="header">
      <h1>CabrioRide</h1>
      <nav class="tabs">
        <router-link to="/">Домой</router-link>
        <router-link to="/users">Участники</router-link>
        <router-link to="/cars">Авто</router-link>
        <router-link to="/me">Профиль</router-link>
      </nav>
    </header>
    <section class="content">
      <p>Добро пожаловать в клуб. Откройте раздел ниже.</p>
    </section>
    <UiTabBar />
  </div>
</template>

<script setup lang="ts">
import UiTabBar from '../components/UiTabBar.vue';
</script>

<style scoped>
.view { padding: 12px; }
.header { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
.header h1 { font-size: 20px; margin: 0; }
.tabs { display: flex; gap: 8px; }
.tabs a { color: var(--tg-theme-link-color, #2a7); text-decoration: none; padding: 6px 10px; border-radius: 8px; background: rgba(0,0,0,0.06); }
.tabs a.router-link-exact-active { background: rgba(0,0,0,0.12); }
</style>

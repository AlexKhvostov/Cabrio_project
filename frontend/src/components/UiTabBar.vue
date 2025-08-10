<template>
  <nav class="tabbar">
    <router-link to="/">Домой</router-link>
    <router-link to="/users">Участники</router-link>
    <router-link to="/cars">Авто</router-link>
    <router-link to="/events">События</router-link>
    <router-link to="/guide">Гид</router-link>
    <router-link to="/settings">Настройки</router-link>
  </nav>
</template>

<script setup lang="ts"></script>

<style scoped>
.tabbar {
  position: sticky;
  bottom: 0;
  display: grid;
  grid-template-columns: repeat(6, 1fr);
  gap: 6px;
  padding: 8px;
  background: var(--tg-theme-secondary-bg-color, rgba(0,0,0,0.06));
  backdrop-filter: blur(6px);
}
.tabbar a {
  text-align: center;
  font-size: 12px;
  color: var(--tg-theme-link-color, #2a7);
  text-decoration: none;
  padding: 8px 6px;
  border-radius: 10px;
}
.tabbar a.router-link-exact-active { background: rgba(0,0,0,0.08); }
</style>

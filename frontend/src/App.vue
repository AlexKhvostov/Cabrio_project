<template>
  <div class="app-root" :class="colorScheme">
    <router-view />
  </div>
  
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useTelegram } from './services/telegram';

const colorScheme = ref('light');

onMounted(() => {
  const tg = useTelegram();
  tg.ready();
  try { tg.expand(); } catch {}
  colorScheme.value = tg.colorScheme || 'light';
});
</script>

<style>
.app-root {
  min-height: 100vh;
  min-height: 100dvh;
  box-sizing: border-box;
  padding-bottom: env(safe-area-inset-bottom);
  padding-top: env(safe-area-inset-top);
  padding-left: env(safe-area-inset-left);
  padding-right: env(safe-area-inset-right);
  background: var(--tg-theme-bg-color, #111);
  color: var(--tg-theme-text-color, #fff);
}
.app-root.light {
  background: var(--tg-theme-bg-color, #fff);
  color: var(--tg-theme-text-color, #111);
}
</style>

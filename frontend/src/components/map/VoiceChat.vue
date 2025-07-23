<template>
  <div class="voice-chat">
    <button 
      class="voice-btn"
      :class="{ recording: isRecording, disabled: !isConnected }"
      @mousedown="startRecording"
      @mouseup="stopRecording"
      @touchstart="startRecording"
      @touchend="stopRecording"
      :disabled="!isConnected"
    >
      <Mic v-if="!isRecording" :size="24" />
      <MicOff v-else :size="24" />
      <div class="voice-ripple" v-if="isRecording"></div>
    </button>
    <div class="voice-hint">
      {{ isRecording ? 'Говорите...' : 'Удерживайте для записи' }}
    </div>
  </div>
</template>

<script setup lang="ts">
import { Mic, MicOff } from 'lucide-vue-next'

interface Props {
  isConnected: boolean
  isRecording: boolean
}

defineProps<Props>()
defineEmits<{
  'start-recording': []
  'stop-recording': []
}>()

const startRecording = () => {
  // Эмитим событие начала записи
}

const stopRecording = () => {
  // Эмитим событие окончания записи
}
</script>

<style scoped>
.voice-chat {
  position: fixed;
  bottom: 110px; /* Отступ от нижнего меню */
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: var(--spacing-xs);
  z-index: 20;
}

.voice-btn {
  position: relative;
  width: 64px;
  height: 64px;
  border-radius: 50%;
  background: var(--primary-color);
  color: white;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 20px rgba(46, 166, 255, 0.3);
  transition: all 0.2s ease;
  user-select: none;
  -webkit-user-select: none;
}

.voice-btn:hover:not(:disabled) {
  transform: scale(1.05);
  box-shadow: 0 6px 25px rgba(46, 166, 255, 0.4);
}

.voice-btn:active:not(:disabled) {
  transform: scale(0.95);
}

.voice-btn.recording {
  background: var(--error-color);
  animation: pulse-recording 1s infinite;
}

.voice-btn.disabled {
  background: var(--border-color);
  cursor: not-allowed;
  box-shadow: none;
}

.voice-ripple {
  position: absolute;
  top: -8px;
  left: -8px;
  right: -8px;
  bottom: -8px;
  border: 2px solid var(--error-color);
  border-radius: 50%;
  animation: ripple 1.5s infinite;
}

.voice-hint {
  background: rgba(0, 0, 0, 0.8);
  color: white;
  padding: var(--spacing-xs) var(--spacing-sm);
  border-radius: var(--radius-lg);
  font-size: var(--font-size-xs);
  font-weight: var(--font-weight-medium);
  white-space: nowrap;
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
}

@keyframes pulse-recording {
  0% { box-shadow: 0 4px 20px rgba(244, 67, 54, 0.3); }
  50% { box-shadow: 0 6px 30px rgba(244, 67, 54, 0.6); }
  100% { box-shadow: 0 4px 20px rgba(244, 67, 54, 0.3); }
}

@keyframes ripple {
  0% {
    transform: scale(1);
    opacity: 1;
  }
  100% {
    transform: scale(1.5);
    opacity: 0;
  }
}

@media (max-width: 480px) {
  .voice-btn {
    width: 56px;
    height: 56px;
  }
  
  .voice-chat {
    bottom: 100px; /* Отступ от нижнего меню на мобильных */
  }
}
</style>
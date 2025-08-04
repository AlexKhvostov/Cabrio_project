<template>
  <div v-if="showDebug" class="debug-info">
    <div class="debug-header">
      <h3>🐛 Debug Info</h3>
      <button @click="showDebug = false" class="debug-close">✕</button>
    </div>
    
    <div class="debug-content">
      <div class="debug-section">
        <h4>📡 API Requests</h4>
        <div v-for="(request, index) in apiRequests" :key="index" class="debug-request">
          <div class="request-url">{{ request.url }}</div>
          <div class="request-status" :class="request.success ? 'success' : 'error'">
            {{ request.status }} - {{ request.success ? 'OK' : 'ERROR' }}
          </div>
          <div v-if="request.error" class="request-error">
            {{ request.error }}
          </div>
        </div>
      </div>
      
      <div class="debug-section">
        <h4>🔧 Telegram WebApp</h4>
        <div class="debug-item">
          <strong>Available:</strong> {{ telegramInfo.available }}
        </div>
        <div class="debug-item">
          <strong>User:</strong> {{ telegramInfo.user }}
        </div>
        <div class="debug-item">
          <strong>InitData:</strong> {{ telegramInfo.initData }}
        </div>
        <div class="debug-item">
          <strong>Platform:</strong> {{ telegramInfo.platform }}
        </div>
      </div>
      
      <div class="debug-section">
        <h4>❌ Errors</h4>
        <div v-for="(error, index) in errors" :key="index" class="debug-error">
          {{ error }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useTelegramStore } from '@/stores/telegram'

const showDebug = ref(true)
const apiRequests = ref<any[]>([])
const errors = ref<string[]>([])
const telegramInfo = ref({
  available: 'Unknown',
  user: 'Unknown',
  initData: 'Unknown',
  platform: 'Unknown'
})

// Перехватываем console.log для отладки
const originalConsoleLog = console.log
const originalConsoleError = console.error

console.log = (...args) => {
  originalConsoleLog(...args)
  
  const message = args.join(' ')
  if (message.includes('🌐 API Request:')) {
    const requestData = args[1]
    apiRequests.value.push({
      url: requestData.url,
      method: requestData.method,
      success: false,
      status: 'Pending'
    })
  }
  
  if (message.includes('📡 API Response:')) {
    const responseData = args[1]
    const lastRequest = apiRequests.value[apiRequests.value.length - 1]
    if (lastRequest) {
      lastRequest.status = responseData.status
      lastRequest.success = responseData.status === 200
    }
  }
  
  if (message.includes('🔧 Telegram WebApp Debug:')) {
    const debugData = args[1]
    telegramInfo.value = {
      available: debugData.webAppAvailable ? 'Yes' : 'No',
      user: debugData.user ? `${debugData.user.first_name} ${debugData.user.last_name}` : 'Not available',
      initData: debugData.initData,
      platform: debugData.platform || 'Unknown'
    }
  }
}

console.error = (...args) => {
  originalConsoleError(...args)
  
  const message = args.join(' ')
  if (message.includes('❌ API Error:')) {
    errors.value.push(message)
  }
}

onMounted(() => {
  // Добавляем кнопку для показа отладки
  const debugButton = document.createElement('button')
  debugButton.textContent = '🐛 Debug'
  debugButton.style.cssText = `
    position: fixed;
    top: 10px;
    right: 10px;
    z-index: 9999;
    background: #ff6b6b;
    color: white;
    border: none;
    border-radius: 5px;
    padding: 5px 10px;
    font-size: 12px;
    cursor: pointer;
  `
  debugButton.onclick = () => {
    showDebug.value = !showDebug.value
  }
  document.body.appendChild(debugButton)
})
</script>

<style scoped>
.debug-info {
  position: fixed;
  top: 50px;
  right: 10px;
  width: 400px;
  max-height: 80vh;
  background: #1a1a1a;
  border: 1px solid #333;
  border-radius: 8px;
  color: white;
  font-family: monospace;
  font-size: 12px;
  z-index: 9998;
  overflow-y: auto;
}

.debug-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px;
  background: #333;
  border-radius: 8px 8px 0 0;
}

.debug-header h3 {
  margin: 0;
  font-size: 14px;
}

.debug-close {
  background: none;
  border: none;
  color: white;
  cursor: pointer;
  font-size: 16px;
}

.debug-content {
  padding: 10px;
}

.debug-section {
  margin-bottom: 15px;
}

.debug-section h4 {
  margin: 0 0 8px 0;
  font-size: 13px;
  color: #ff6b6b;
}

.debug-request {
  margin-bottom: 8px;
  padding: 5px;
  background: #2a2a2a;
  border-radius: 4px;
}

.request-url {
  font-size: 11px;
  color: #74b9ff;
  word-break: break-all;
}

.request-status {
  font-size: 11px;
  margin-top: 2px;
}

.request-status.success {
  color: #00b894;
}

.request-status.error {
  color: #ff6b6b;
}

.request-error {
  font-size: 11px;
  color: #ff6b6b;
  margin-top: 2px;
}

.debug-item {
  margin-bottom: 4px;
  font-size: 11px;
}

.debug-error {
  font-size: 11px;
  color: #ff6b6b;
  margin-bottom: 4px;
  word-break: break-all;
}
</style> 
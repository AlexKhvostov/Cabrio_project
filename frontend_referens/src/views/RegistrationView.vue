<template>
  <div class="registration-view">
    <div class="registration-container">
      <!-- Логотип и заголовок -->
      <div class="header-section">
        <h1 class="app-title">CabrioRide</h1>
        <h2 class="welcome-title">Welcome to the Club!</h2>
        <p class="welcome-subtitle">
          Join our community of convertible enthusiasts.<br>
          Let's get you started by adding your ride.
        </p>
      </div>

      <!-- Индикатор прогресса -->
      <div class="progress-indicators">
        <div class="indicator active"></div>
        <div class="indicator"></div>
      </div>

      <!-- Форма добавления автомобиля -->
      <div class="car-form">
        <!-- Фото автомобиля -->
        <div class="photo-upload" @click="selectPhoto">
          <div v-if="!carPhoto" class="photo-placeholder">
            <Camera :size="32" />
            <span>Tap to add photo</span>
          </div>
          <img v-else :src="carPhoto" alt="Car photo" class="car-photo" />
          <input
            ref="photoInput"
            type="file"
            accept="image/*"
            @change="handlePhotoSelect"
            style="display: none"
          />
        </div>

        <!-- Марка -->
        <div class="form-group">
          <select v-model="carData.make" class="form-select" @change="onMakeChange">
            <option value="">Make</option>
            <option v-for="make in carMakes" :key="make" :value="make">{{ make }}</option>
          </select>
          <ChevronDown :size="20" class="select-arrow" />
        </div>

        <!-- Модель -->
        <div class="form-group">
          <select v-model="carData.model" class="form-select" :disabled="!carData.make">
            <option value="">Model</option>
            <option v-for="model in availableModels" :key="model" :value="model">{{ model }}</option>
          </select>
          <ChevronDown :size="20" class="select-arrow" />
        </div>

        <!-- Номер -->
        <div class="form-group">
          <input
            v-model="carData.licensePlate"
            type="text"
            placeholder="License Plate (e.g., ABC-123)"
            class="form-input"
          />
        </div>

        <!-- Чекбокс показа номера -->
        <div class="checkbox-group">
          <label class="checkbox-label">
            <input
              v-model="carData.showLicensePlate"
              type="checkbox"
              class="checkbox-input"
            />
            <div class="checkbox-custom"></div>
            <span>Show my license plate to other users</span>
          </label>
        </div>

        <!-- Информация о модерации -->
        <div class="moderation-info">
          <p>
            Your profile will be reviewed by a moderator. Real-time
            validation ensures accuracy. Clear error messages will
            guide you.
          </p>
        </div>
      </div>

      <!-- Кнопки действий -->
      <div class="action-buttons">
        <button
          class="btn-primary"
          :disabled="!isFormValid"
          @click="submitCar"
        >
          Continue
        </button>
        
        <button class="btn-link" @click="skipCarRegistration">
          I don't have a car (passenger)
        </button>
        
        <button class="btn-link" @click="askQuestion">
          Ask a question
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { Camera, ChevronDown } from 'lucide-vue-next'
import { useTelegramStore } from '@/stores/telegram'

const router = useRouter()
const telegramStore = useTelegramStore()

const photoInput = ref<HTMLInputElement>()
const carPhoto = ref<string>('')

const carData = ref({
  make: '',
  model: '',
  licensePlate: '',
  showLicensePlate: false
})

const carMakes = [
  'BMW', 'Mercedes-Benz', 'Audi', 'Porsche', 'Volkswagen',
  'Ford', 'Chevrolet', 'Toyota', 'Honda', 'Nissan',
  'Mazda', 'Peugeot', 'Renault', 'Fiat', 'Mini'
]

const carModels: Record<string, string[]> = {
  'BMW': ['Z4', 'Z3', '3 Series Convertible', '4 Series Convertible', '6 Series Convertible'],
  'Mercedes-Benz': ['SLK-Class', 'SL-Class', 'C-Class Cabriolet', 'E-Class Cabriolet', 'S-Class Cabriolet'],
  'Audi': ['A3 Cabriolet', 'A4 Cabriolet', 'A5 Cabriolet', 'TT Roadster', 'R8 Spyder'],
  'Porsche': ['911 Cabriolet', 'Boxster', 'Cayman', 'Panamera Convertible'],
  'Volkswagen': ['Beetle Convertible', 'Eos', 'Golf Cabriolet'],
  'Ford': ['Mustang Convertible', 'Focus Cabriolet'],
  'Chevrolet': ['Camaro Convertible', 'Corvette Convertible'],
  'Toyota': ['Solara Convertible', 'MR2 Spyder'],
  'Honda': ['S2000', 'Del Sol'],
  'Nissan': ['350Z Roadster', '370Z Roadster'],
  'Mazda': ['MX-5 Miata', 'RX-7 Convertible'],
  'Peugeot': ['206 CC', '307 CC', '308 CC'],
  'Renault': ['Megane CC', 'Wind'],
  'Fiat': ['500C', '124 Spider'],
  'Mini': ['Cooper Convertible', 'Clubman Convertible']
}

const availableModels = computed(() => {
  return carData.value.make ? carModels[carData.value.make] || [] : []
})

const isFormValid = computed(() => {
  return carData.value.make && carData.value.model && carData.value.licensePlate.trim()
})

const onMakeChange = () => {
  carData.value.model = ''
}

const selectPhoto = () => {
  photoInput.value?.click()
}

const handlePhotoSelect = (event: Event) => {
  const file = (event.target as HTMLInputElement).files?.[0]
  if (file) {
    const reader = new FileReader()
    reader.onload = (e) => {
      carPhoto.value = e.target?.result as string
    }
    reader.readAsDataURL(file)
  }
}

const submitCar = () => {
  telegramStore.hapticFeedback('notification')
  // TODO: Отправить данные автомобиля на сервер
  console.log('Car data:', { ...carData.value, photo: carPhoto.value })
  
  // Перенаправляем на главную страницу
  router.push('/')
}

const skipCarRegistration = () => {
  telegramStore.hapticFeedback('selection')
  // TODO: Отметить пользователя как пассажира
  console.log('User skipped car registration')
  
  // Перенаправляем на главную страницу
  router.push('/')
}

const askQuestion = () => {
  telegramStore.hapticFeedback('selection')
  // TODO: Открыть чат с поддержкой или FAQ
  console.log('User wants to ask a question')
}
</script>

<style scoped>
.registration-view {
  min-height: 100vh;
  background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: var(--spacing-lg);
}

.registration-container {
  max-width: 400px;
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: var(--spacing-xl);
}

/* Header */
.header-section {
  text-align: center;
}

.app-title {
  font-size: var(--font-size-2xl);
  font-weight: var(--font-weight-bold);
  color: var(--tg-theme-text-color);
  margin-bottom: var(--spacing-lg);
}

.welcome-title {
  font-size: var(--font-size-xl);
  font-weight: var(--font-weight-bold);
  color: var(--tg-theme-text-color);
  margin-bottom: var(--spacing-md);
}

.welcome-subtitle {
  color: var(--tg-theme-hint-color);
  font-size: var(--font-size-md);
  line-height: 1.5;
  margin: 0;
}

/* Progress indicators */
.progress-indicators {
  display: flex;
  justify-content: center;
  gap: var(--spacing-sm);
}

.indicator {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--border-color);
  transition: all 0.3s ease;
}

.indicator.active {
  background: var(--primary-color);
}

/* Photo upload */
.photo-upload {
  border: 2px dashed var(--border-color);
  border-radius: var(--radius-lg);
  padding: var(--spacing-xl);
  text-align: center;
  cursor: pointer;
  transition: all 0.2s ease;
  margin-bottom: var(--spacing-lg);
}

.photo-upload:hover {
  border-color: var(--primary-color);
  background: rgba(46, 166, 255, 0.05);
}

.photo-placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: var(--spacing-sm);
  color: var(--tg-theme-hint-color);
}

.photo-placeholder svg {
  color: var(--tg-theme-hint-color);
}

.car-photo {
  max-width: 100%;
  max-height: 200px;
  border-radius: var(--radius-md);
  object-fit: cover;
}

/* Form */
.car-form {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-md);
}

.form-group {
  position: relative;
}

.form-select,
.form-input {
  width: 100%;
  padding: var(--spacing-md);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-md);
  background: var(--card-bg);
  color: var(--tg-theme-text-color);
  font-size: var(--font-size-md);
  appearance: none;
  transition: border-color 0.2s ease;
}

.form-select:focus,
.form-input:focus {
  outline: none;
  border-color: var(--primary-color);
}

.form-select:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.select-arrow {
  position: absolute;
  right: var(--spacing-md);
  top: 50%;
  transform: translateY(-50%);
  color: var(--tg-theme-hint-color);
  pointer-events: none;
}

.form-input::placeholder {
  color: var(--tg-theme-hint-color);
}

/* Checkbox */
.checkbox-group {
  margin: var(--spacing-md) 0;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
  cursor: pointer;
  font-size: var(--font-size-sm);
  color: var(--tg-theme-text-color);
}

.checkbox-input {
  display: none;
}

.checkbox-custom {
  width: 20px;
  height: 20px;
  border: 2px solid var(--border-color);
  border-radius: var(--radius-sm);
  background: var(--card-bg);
  transition: all 0.2s ease;
  position: relative;
  flex-shrink: 0;
}

.checkbox-input:checked + .checkbox-custom {
  background: var(--primary-color);
  border-color: var(--primary-color);
}

.checkbox-input:checked + .checkbox-custom::after {
  content: '✓';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  color: white;
  font-size: 12px;
  font-weight: bold;
}

/* Moderation info */
.moderation-info {
  background: rgba(46, 166, 255, 0.1);
  border: 1px solid rgba(46, 166, 255, 0.2);
  border-radius: var(--radius-md);
  padding: var(--spacing-md);
  margin: var(--spacing-md) 0;
}

.moderation-info p {
  font-size: var(--font-size-sm);
  color: var(--tg-theme-hint-color);
  line-height: 1.4;
  margin: 0;
}

/* Action buttons */
.action-buttons {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-md);
}

.btn-primary {
  width: 100%;
  padding: var(--spacing-md) var(--spacing-lg);
  background: linear-gradient(135deg, #e8f4fd 0%, #c8e6fc 100%);
  color: #1a1a1a;
  border: none;
  border-radius: 50px;
  font-size: var(--font-size-md);
  font-weight: var(--font-weight-semibold);
  cursor: pointer;
  transition: all 0.2s ease;
  min-height: 48px;
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 25px rgba(232, 244, 253, 0.4);
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
}

.btn-link {
  background: none;
  border: none;
  color: var(--tg-theme-hint-color);
  font-size: var(--font-size-sm);
  text-decoration: underline;
  cursor: pointer;
  padding: var(--spacing-sm);
  transition: color 0.2s ease;
}

.btn-link:hover {
  color: var(--tg-theme-text-color);
}

@media (max-width: 480px) {
  .registration-view {
    padding: var(--spacing-md);
  }
  
  .registration-container {
    gap: var(--spacing-lg);
  }
  
  .app-title {
    font-size: var(--font-size-xl);
  }
  
  .welcome-title {
    font-size: var(--font-size-lg);
  }
  
  .photo-upload {
    padding: var(--spacing-lg);
  }
}
</style>
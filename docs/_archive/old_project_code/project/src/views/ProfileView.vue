<template>
  <div class="profile-view">
    <div class="container">
      <!-- Основная карточка профиля -->
      <div class="profile-card">
        <div class="profile-header">
          <div class="profile-avatar">
            <img
              v-if="memberData?.photo_url"
              :src="memberData.photo_url"
              :alt="memberData.first_name"
              class="avatar-image"
            />
            <span v-else class="avatar-initials">
              {{ getInitials() }}
            </span>
            
            <!-- Индикаторы статуса -->
            <div class="status-indicators">
              <div v-if="memberData?.block" class="indicator blocked" title="Заблокирован">
                <Shield :size="12" />
              </div>
              <div v-if="memberData?.have_auto" class="indicator has-car" title="Есть автомобиль">
                <Car :size="12" />
              </div>
              <div v-if="memberData?.respect && memberData.respect > 0" class="indicator respect" :title="`Respect: ${memberData.respect}`">
                <Star :size="12" />
              </div>
            </div>
          </div>
          
          <div class="profile-info">
            <!-- Имя -->
            <div class="name-section">
              <div v-if="!editMode.name" class="name-display">
                <h2>{{ getDisplayName() }}</h2>
                <button class="edit-btn" @click="startEdit('name')" title="Редактировать имя">
                  <Edit2 :size="16" />
                </button>
              </div>
              <div v-else class="name-edit">
                <input
                  v-model="editData.first_name_app"
                  type="text"
                  placeholder="Имя в приложении"
                  class="edit-input"
                />
                <input
                  v-model="editData.last_name_app"
                  type="text"
                  placeholder="Фамилия в приложении"
                  class="edit-input"
                />
                <div class="edit-actions">
                  <button class="save-btn" @click="saveEdit('name')" title="Сохранить">
                    <Check :size="14" />
                  </button>
                  <button class="cancel-btn" @click="cancelEdit('name')" title="Отмена">
                    <X :size="14" />
                  </button>
                </div>
              </div>
            </div>
            
            <!-- Username -->
            <div v-if="memberData?.username" class="username">
              @{{ memberData.username }}
            </div>
            
            <!-- Статистика -->
            <div class="profile-stats">
              <div class="stat">
                <span class="stat-value">{{ memberData?.messages_count || 0 }}</span>
                <span class="stat-label">сообщений</span>
              </div>
              <div class="stat">
                <span class="stat-value">{{ memberData?.cars?.length || 0 }}</span>
                <span class="stat-label">авто</span>
              </div>
              <div class="stat">
                <span class="stat-value">{{ memberData?.respect || 0 }}</span>
                <span class="stat-label">respect</span>
              </div>
              <div class="stat">
                <span class="stat-value">{{ daysSinceJoin }}</span>
                <span class="stat-label">дней</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Личная информация -->
      <div class="section-card">
        <div class="section-header">
          <h3>
            <User :size="18" />
            Личная информация
          </h3>
        </div>
        
        <div class="info-grid">
          <!-- Дата рождения -->
          <div class="info-item">
            <label>Дата рождения</label>
            <div v-if="!editMode.birth_date" class="info-display">
              <span>{{ formatBirthDate(memberData?.birth_date) }}</span>
              <button class="edit-btn-small" @click="startEdit('birth_date')">
                <Edit2 :size="12" />
              </button>
            </div>
            <div v-else class="info-edit">
              <input
                v-model="editData.birth_date"
                type="date"
                class="edit-input"
              />
              <div class="edit-actions-small">
                <button class="save-btn-small" @click="saveEdit('birth_date')">
                  <Check :size="12" />
                </button>
                <button class="cancel-btn-small" @click="cancelEdit('birth_date')">
                  <X :size="12" />
                </button>
              </div>
            </div>
          </div>
          
          <!-- Город -->
          <div class="info-item">
            <label>Город</label>
            <div v-if="!editMode.city" class="info-display">
              <span>{{ memberData?.city || 'Не указан' }}</span>
              <button class="edit-btn-small" @click="startEdit('city')">
                <Edit2 :size="12" />
              </button>
            </div>
            <div v-else class="info-edit">
              <input
                v-model="editData.city"
                type="text"
                placeholder="Город"
                class="edit-input"
              />
              <div class="edit-actions-small">
                <button class="save-btn-small" @click="saveEdit('city')">
                  <Check :size="12" />
                </button>
                <button class="cancel-btn-small" @click="cancelEdit('city')">
                  <X :size="12" />
                </button>
              </div>
            </div>
          </div>
          
          <!-- Страна -->
          <div class="info-item">
            <label>Страна</label>
            <div v-if="!editMode.country" class="info-display">
              <span>{{ memberData?.country || 'Не указана' }}</span>
              <button class="edit-btn-small" @click="startEdit('country')">
                <Edit2 :size="12" />
              </button>
            </div>
            <div v-else class="info-edit">
              <input
                v-model="editData.country"
                type="text"
                placeholder="Страна"
                class="edit-input"
              />
              <div class="edit-actions-small">
                <button class="save-btn-small" @click="saveEdit('country')">
                  <Check :size="12" />
                </button>
                <button class="cancel-btn-small" @click="cancelEdit('country')">
                  <X :size="12" />
                </button>
              </div>
            </div>
          </div>
          
          <!-- Email -->
          <div class="info-item">
            <label>Email</label>
            <div v-if="!editMode.email" class="info-display">
              <span>{{ memberData?.email || 'Не указан' }}</span>
              <button class="edit-btn-small" @click="startEdit('email')">
                <Edit2 :size="12" />
              </button>
            </div>
            <div v-else class="info-edit">
              <input
                v-model="editData.email"
                type="email"
                placeholder="Email"
                class="edit-input"
              />
              <div class="edit-actions-small">
                <button class="save-btn-small" @click="saveEdit('email')">
                  <Check :size="12" />
                </button>
                <button class="cancel-btn-small" @click="cancelEdit('email')">
                  <X :size="12" />
                </button>
              </div>
            </div>
          </div>
          
          <!-- Телефон -->
          <div class="info-item">
            <label>Телефон</label>
            <div v-if="!editMode.phone" class="info-display">
              <span>{{ memberData?.phone || 'Не указан' }}</span>
              <button class="edit-btn-small" @click="startEdit('phone')">
                <Edit2 :size="12" />
              </button>
            </div>
            <div v-else class="info-edit">
              <input
                v-model="editData.phone"
                type="tel"
                placeholder="Телефон"
                class="edit-input"
              />
              <div class="edit-actions-small">
                <button class="save-btn-small" @click="saveEdit('phone')">
                  <Check :size="12" />
                </button>
                <button class="cancel-btn-small" @click="cancelEdit('phone')">
                  <X :size="12" />
                </button>
              </div>
            </div>
          </div>
        </div>
        
        <!-- О себе -->
        <div class="about-section">
          <label>О себе</label>
          <div v-if="!editMode.about" class="about-display">
            <p>{{ memberData?.about || 'Расскажите о себе...' }}</p>
            <button class="edit-btn" @click="startEdit('about')">
              <Edit2 :size="16" />
            </button>
          </div>
          <div v-else class="about-edit">
            <textarea
              v-model="editData.about"
              placeholder="Расскажите о себе..."
              class="edit-textarea"
              rows="3"
            ></textarea>
            <div class="edit-actions">
              <button class="save-btn" @click="saveEdit('about')">
                <Check :size="14" />
              </button>
              <button class="cancel-btn" @click="cancelEdit('about')">
                <X :size="14" />
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Автомобили -->
      <div class="section-card">
        <div class="section-header">
          <h3>
            <Car :size="18" />
            Мои автомобили
          </h3>
          <button class="add-btn" @click="showAddCar = true">
            <Plus :size="16" />
            Добавить
          </button>
        </div>
        
        <div v-if="memberData?.cars && memberData.cars.length > 0" class="cars-grid">
          <CarCard
            v-for="car in memberData.cars"
            :key="car.id"
            :car="car"
            @select="selectCar"
          />
        </div>
        
        <div v-else class="no-cars">
          <Car :size="48" />
          <p>У вас пока нет добавленных автомобилей</p>
          <button class="btn primary" @click="showAddCar = true">
            Добавить первый автомобиль
          </button>
        </div>
      </div>

      <!-- Системная информация -->
      <div class="section-card">
        <div class="section-header">
          <h3>
            <Info :size="18" />
            Системная информация
          </h3>
        </div>
        
        <div class="system-info">
          <div class="system-item">
            <span class="system-label">Telegram ID:</span>
            <span class="system-value">{{ memberData?.telegram_id }}</span>
          </div>
          <div class="system-item">
            <span class="system-label">Дата регистрации:</span>
            <span class="system-value">{{ formatFullDate(memberData?.created_at) }}</span>
          </div>
          <div class="system-item">
            <span class="system-label">Дата вступления:</span>
            <span class="system-value">{{ formatFullDate(memberData?.join_date) }}</span>
          </div>
          <div v-if="memberData?.last_activity" class="system-item">
            <span class="system-label">Последняя активность:</span>
            <span class="system-value">{{ formatRelativeTime(memberData.last_activity) }}</span>
          </div>
          <div class="system-item">
            <span class="system-label">Рейтинг:</span>
            <span class="system-value">{{ memberData?.weight || 0 }} лайков</span>
          </div>
        </div>
      </div>

      <!-- Действия -->
      <div class="actions-card">
        <button class="action-btn secondary" @click="refreshData">
          <RefreshCw :size="18" />
          Обновить данные
        </button>
        <button class="action-btn danger" @click="logout">
          <LogOut :size="18" />
          Выйти
        </button>
      </div>
    </div>

    <!-- Модальное окно добавления автомобиля -->
    <div v-if="showAddCar" class="modal-overlay" @click="showAddCar = false">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h3>Добавить автомобиль</h3>
          <button class="modal-close" @click="showAddCar = false">
            <X :size="20" />
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Марка</label>
            <input v-model="newCar.brand" type="text" placeholder="BMW, Mercedes, Audi..." />
          </div>
          <div class="form-group">
            <label>Модель</label>
            <input v-model="newCar.model" type="text" placeholder="Z4, SLK, A5..." />
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Год</label>
              <input v-model="newCar.year" type="number" min="1990" max="2024" />
            </div>
            <div class="form-group">
              <label>Объем (л)</label>
              <input v-model="newCar.engine_volume" type="number" step="0.1" placeholder="2.0" />
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Мощность (л.с.)</label>
              <input v-model="newCar.engine_power" type="number" placeholder="200" />
            </div>
            <div class="form-group">
              <label>Цвет</label>
              <input v-model="newCar.color" type="text" placeholder="Белый, Черный..." />
            </div>
          </div>
          <div class="form-group">
            <label>Гос. номер</label>
            <input v-model="newCar.reg_number" type="text" placeholder="А123ВС777" />
          </div>
          <div class="form-group">
            <label class="checkbox-label">
              <input v-model="newCar.show_reg_number" type="checkbox" />
              <span class="checkbox-text">Показывать номер публично</span>
            </label>
          </div>
          <div class="form-group">
            <label>Описание</label>
            <textarea 
              v-model="newCar.description" 
              placeholder="Дополнительная информация об автомобиле..."
              rows="3"
            ></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn secondary" @click="showAddCar = false">Отмена</button>
          <button class="btn primary" @click="addCar">Добавить</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { 
  Car, 
  RefreshCw, 
  LogOut,
  Edit2,
  Check,
  X,
  Plus,
  User,
  Info,
  Shield,
  Star
} from 'lucide-vue-next'
import { useTelegramStore } from '@/stores/telegram'
import { useDataStore, type Member } from '@/stores/data'
import CarCard from '@/components/cars/CarCard.vue'

const router = useRouter()
const telegramStore = useTelegramStore()
const dataStore = useDataStore()

const memberData = ref<Member | null>(null)
const showAddCar = ref(false)

// Режимы редактирования
const editMode = ref({
  name: false,
  birth_date: false,
  city: false,
  country: false,
  email: false,
  phone: false,
  about: false
})

// Данные для редактирования
const editData = ref({
  first_name_app: '',
  last_name_app: '',
  birth_date: '',
  city: '',
  country: '',
  email: '',
  phone: '',
  about: ''
})

// Новый автомобиль
const newCar = ref({
  brand: '',
  model: '',
  year: new Date().getFullYear(),
  engine_volume: 0,
  engine_power: 0,
  color: '',
  reg_number: '',
  show_reg_number: true,
  description: ''
})

const daysSinceJoin = computed(() => {
  if (!memberData.value?.join_date) return 0
  const joinDate = new Date(memberData.value.join_date)
  const today = new Date()
  const diffTime = Math.abs(today.getTime() - joinDate.getTime())
  return Math.ceil(diffTime / (1000 * 60 * 60 * 24))
})

const getInitials = () => {
  const firstName = memberData.value?.first_name_app || memberData.value?.first_name_tg || memberData.value?.first_name || ''
  const lastName = memberData.value?.last_name_app || memberData.value?.last_name_tg || memberData.value?.last_name || ''
  return (firstName.charAt(0) + lastName.charAt(0)).toUpperCase()
}

const getDisplayName = () => {
  const firstName = memberData.value?.first_name_app || memberData.value?.first_name_tg || memberData.value?.first_name || ''
  const lastName = memberData.value?.last_name_app || memberData.value?.last_name_tg || memberData.value?.last_name || ''
  return `${firstName} ${lastName}`.trim()
}

const formatBirthDate = (dateString?: string) => {
  if (!dateString) return 'Не указана'
  const date = new Date(dateString)
  return date.toLocaleDateString('ru-RU', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
}

const formatFullDate = (dateString?: string) => {
  if (!dateString) return 'Не указана'
  const date = new Date(dateString)
  return date.toLocaleDateString('ru-RU', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
}

const formatRelativeTime = (dateString: string) => {
  const date = new Date(dateString)
  const now = new Date()
  const diffMs = now.getTime() - date.getTime()
  const diffHours = Math.floor(diffMs / (1000 * 60 * 60))
  const diffDays = Math.floor(diffHours / 24)
  
  if (diffDays > 0) {
    return `${diffDays} дн. назад`
  } else if (diffHours > 0) {
    return `${diffHours} ч. назад`
  } else {
    return 'Недавно'
  }
}

// Редактирование
const startEdit = (field: string) => {
  if (!memberData.value) return
  
  switch (field) {
    case 'name':
      editData.value.first_name_app = memberData.value.first_name_app || ''
      editData.value.last_name_app = memberData.value.last_name_app || ''
      break
    case 'birth_date':
      editData.value.birth_date = memberData.value.birth_date || ''
      break
    case 'city':
      editData.value.city = memberData.value.city || ''
      break
    case 'country':
      editData.value.country = memberData.value.country || ''
      break
    case 'email':
      editData.value.email = memberData.value.email || ''
      break
    case 'phone':
      editData.value.phone = memberData.value.phone || ''
      break
    case 'about':
      editData.value.about = memberData.value.about || ''
      break
  }
  
  editMode.value[field as keyof typeof editMode.value] = true
}

const saveEdit = (field: string) => {
  telegramStore.hapticFeedback('notification')
  
  if (!memberData.value) return
  
  switch (field) {
    case 'name':
      memberData.value.first_name_app = editData.value.first_name_app
      memberData.value.last_name_app = editData.value.last_name_app
      break
    case 'birth_date':
      memberData.value.birth_date = editData.value.birth_date
      break
    case 'city':
      memberData.value.city = editData.value.city
      break
    case 'country':
      memberData.value.country = editData.value.country
      break
    case 'email':
      memberData.value.email = editData.value.email
      break
    case 'phone':
      memberData.value.phone = editData.value.phone
      break
    case 'about':
      memberData.value.about = editData.value.about
      break
  }
  
  // TODO: Сохранить на сервере
  console.log(`Saving ${field}:`, editData.value[field as keyof typeof editData.value])
  
  editMode.value[field as keyof typeof editMode.value] = false
}

const cancelEdit = (field: string) => {
  telegramStore.hapticFeedback('selection')
  editMode.value[field as keyof typeof editMode.value] = false
}

// Автомобили
const selectCar = (car: any) => {
  telegramStore.hapticFeedback('selection')
  console.log('Selected car:', car)
}

const editCar = (car: any) => {
  telegramStore.hapticFeedback('selection')
  console.log('Edit car:', car)
}

const addCar = () => {
  if (!newCar.value.brand || !newCar.value.model) {
    telegramStore.showAlert('Заполните марку и модель автомобиля')
    return
  }
  
  telegramStore.hapticFeedback('notification')
  // TODO: Добавить автомобиль на сервер
  console.log('Adding car:', newCar.value)
  
  // Сброс формы
  newCar.value = {
    brand: '',
    model: '',
    year: new Date().getFullYear(),
    engine_volume: 0,
    engine_power: 0,
    color: '',
    reg_number: '',
    show_reg_number: true,
    description: ''
  }
  showAddCar.value = false
}

const refreshData = async () => {
  telegramStore.hapticFeedback('impact')
  await dataStore.fetchMembers()
  loadMemberData()
}

const logout = async () => {
  const confirmed = await telegramStore.showConfirm('Вы уверены, что хотите выйти?')
  if (confirmed) {
    telegramStore.hapticFeedback('notification')
    router.push('/login')
  }
}

const loadMemberData = () => {
  if (telegramStore.user?.id) {
    // Создаем mock данные с новой структурой
    memberData.value = {
      id: 1,
      telegram_id: telegramStore.user.id,
      username: telegramStore.user.username,
      first_name: telegramStore.user.first_name,
      last_name: telegramStore.user.last_name,
      first_name_tg: telegramStore.user.first_name,
      last_name_tg: telegramStore.user.last_name,
      first_name_app: telegramStore.user.first_name,
      last_name_app: telegramStore.user.last_name,
      birth_date: '1990-01-01',
      city: 'Москва',
      country: 'Россия',
      email: 'user@example.com',
      phone: '+7 (999) 123-45-67',
      about: 'Люблю кабриолеты и путешествия!',
      created_at: '2024-01-15T10:00:00Z',
      updated_at: '2024-03-15T15:30:00Z',
      join_date: '2024-01-15T10:00:00Z',
      status_id: 1,
      role_id: 1,
      have_auto: true,
      block: false,
      respect: 150,
      weight: 25,
      messages_count: 245,
      last_activity: '2024-03-15T14:30:00Z',
      photo_url: 'https://images.pexels.com/photos/220453/pexels-photo-220453.jpeg?auto=compress&cs=tinysrgb&w=64&h=64&dpr=1',
      cars: [
        {
          id: 1,
          car_model_id: 1,
          brand: 'BMW',
          model: 'Z4',
          year: 2019,
          reg_number: 'М123АВ777',
          show_reg_number: true,
          color: 'Белый',
          engine_volume: 2.0,
          engine_power: 197,
          description: 'Отличное состояние, полная комплектация',
          created_at: '2024-01-20T12:00:00Z',
          updated_at: '2024-03-10T09:15:00Z',
          owner_user_id: 1,
          create_user_id: 1,
          status_id: 1,
          photos: [
            'https://images.pexels.com/photos/3802510/pexels-photo-3802510.jpeg?auto=compress&cs=tinysrgb&w=400',
            'https://images.pexels.com/photos/1545743/pexels-photo-1545743.jpeg?auto=compress&cs=tinysrgb&w=400'
          ]
        }
      ]
    }
  }
}

onMounted(() => {
  loadMemberData()
})
</script>

<style scoped>
.profile-view {
  padding: var(--spacing-md) 0;
  min-height: calc(100vh - 140px);
}

.container {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-md);
  max-width: 600px;
  margin: 0 auto;
}

/* Основная карточка профиля */
.profile-card {
  background: var(--card-bg);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-lg);
  padding: var(--spacing-lg);
}

.profile-header {
  display: flex;
  gap: var(--spacing-md);
  align-items: flex-start;
}

.profile-avatar {
  position: relative;
  width: 64px;
  height: 64px;
  border-radius: 50%;
  overflow: hidden;
  background: var(--primary-color);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.avatar-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.avatar-initials {
  color: white;
  font-weight: var(--font-weight-bold);
  font-size: var(--font-size-lg);
}

.status-indicators {
  position: absolute;
  top: -4px;
  right: -4px;
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.indicator {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 10px;
  border: 2px solid var(--card-bg);
}

.indicator.blocked {
  background: var(--error-color);
}

.indicator.has-car {
  background: var(--primary-color);
}

.indicator.respect {
  background: var(--warning-color);
}

.profile-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: var(--spacing-sm);
}

/* Имя */
.name-section {
  margin-bottom: var(--spacing-xs);
}

.name-display {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
}

.name-display h2 {
  font-size: var(--font-size-lg);
  font-weight: var(--font-weight-semibold);
  color: var(--tg-theme-text-color);
  margin: 0;
}

.name-edit {
  display: flex;
  gap: var(--spacing-xs);
  align-items: center;
  flex-wrap: wrap;
}

.username {
  color: var(--primary-color);
  font-size: var(--font-size-sm);
  font-weight: var(--font-weight-medium);
  margin-bottom: var(--spacing-xs);
}

/* Статистика */
.profile-stats {
  display: flex;
  gap: var(--spacing-lg);
}

.stat {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

.stat-value {
  font-size: var(--font-size-lg);
  font-weight: var(--font-weight-bold);
  color: var(--primary-color);
  line-height: 1;
}

.stat-label {
  font-size: var(--font-size-xs);
  color: var(--tg-theme-hint-color);
  font-weight: var(--font-weight-medium);
}

/* Секции */
.section-card {
  background: var(--card-bg);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-lg);
  padding: var(--spacing-md);
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: var(--spacing-md);
}

.section-header h3 {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
  font-size: var(--font-size-md);
  font-weight: var(--font-weight-semibold);
  color: var(--tg-theme-text-color);
  margin: 0;
}

.section-header h3 svg {
  color: var(--primary-color);
}

/* Информационная сетка */
.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: var(--spacing-md);
  margin-bottom: var(--spacing-lg);
}

.info-item {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-xs);
}

.info-item label {
  font-size: var(--font-size-sm);
  font-weight: var(--font-weight-medium);
  color: var(--tg-theme-hint-color);
}

.info-display {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
}

.info-display span {
  color: var(--tg-theme-text-color);
  font-size: var(--font-size-sm);
}

.info-edit {
  display: flex;
  gap: var(--spacing-xs);
  align-items: center;
}

/* О себе */
.about-section {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-xs);
}

.about-section label {
  font-size: var(--font-size-sm);
  font-weight: var(--font-weight-medium);
  color: var(--tg-theme-hint-color);
}

.about-display {
  display: flex;
  gap: var(--spacing-sm);
  align-items: flex-start;
}

.about-display p {
  flex: 1;
  color: var(--tg-theme-text-color);
  font-size: var(--font-size-sm);
  line-height: 1.5;
  margin: 0;
}

.about-edit {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-sm);
}

/* Кнопки редактирования */
.edit-btn, .edit-btn-small {
  background: none;
  border: none;
  color: var(--tg-theme-hint-color);
  cursor: pointer;
  padding: var(--spacing-xs);
  border-radius: var(--radius-sm);
  transition: all 0.2s ease;
  flex-shrink: 0;
}

.edit-btn:hover, .edit-btn-small:hover {
  background: var(--hover-bg);
  color: var(--primary-color);
}

.edit-input, .edit-textarea {
  padding: var(--spacing-xs) var(--spacing-sm);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-sm);
  background: var(--card-bg);
  color: var(--tg-theme-text-color);
  font-size: var(--font-size-sm);
  min-width: 80px;
  font-family: inherit;
}

.edit-input:focus, .edit-textarea:focus {
  outline: none;
  border-color: var(--primary-color);
}

.edit-textarea {
  resize: vertical;
  min-height: 60px;
}

.edit-actions, .edit-actions-small {
  display: flex;
  gap: var(--spacing-xs);
}

.save-btn, .cancel-btn, .save-btn-small, .cancel-btn-small {
  width: 24px;
  height: 24px;
  border: none;
  border-radius: var(--radius-sm);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  flex-shrink: 0;
}

.save-btn, .save-btn-small {
  background: var(--success-color);
  color: white;
}

.cancel-btn, .cancel-btn-small {
  background: var(--error-color);
  color: white;
}

/* Автомобили */
.add-btn {
  display: flex;
  align-items: center;
  gap: var(--spacing-xs);
  padding: var(--spacing-xs) var(--spacing-sm);
  background: var(--primary-color);
  color: white;
  border: none;
  border-radius: var(--radius-sm);
  font-size: var(--font-size-sm);
  font-weight: var(--font-weight-medium);
  cursor: pointer;
  transition: all 0.2s ease;
}

.add-btn:hover {
  background: #1e90ff;
}

.cars-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: var(--spacing-sm);
}

.no-cars {
  text-align: center;
  padding: var(--spacing-xl);
  color: var(--tg-theme-hint-color);
}

.no-cars svg {
  margin-bottom: var(--spacing-md);
  opacity: 0.5;
}

.no-cars p {
  margin-bottom: var(--spacing-md);
}

/* Системная информация */
.system-info {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-sm);
}

.system-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: var(--spacing-xs) 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.system-item:last-child {
  border-bottom: none;
}

.system-label {
  font-size: var(--font-size-sm);
  color: var(--tg-theme-hint-color);
  font-weight: var(--font-weight-medium);
}

.system-value {
  font-size: var(--font-size-sm);
  color: var(--tg-theme-text-color);
  font-weight: var(--font-weight-medium);
}

/* Действия */
.actions-card {
  display: flex;
  gap: var(--spacing-sm);
}

.action-btn {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: var(--spacing-sm);
  padding: var(--spacing-md);
  border: none;
  border-radius: var(--radius-md);
  font-size: var(--font-size-sm);
  font-weight: var(--font-weight-medium);
  cursor: pointer;
  transition: all 0.2s ease;
}

.action-btn.secondary {
  background: var(--card-bg);
  color: var(--tg-theme-text-color);
  border: 1px solid var(--border-color);
}

.action-btn.secondary:hover {
  background: var(--hover-bg);
}

.action-btn.danger {
  background: var(--error-color);
  color: white;
}

.action-btn.danger:hover {
  background: #d32f2f;
}

/* Модальное окно */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: var(--spacing-md);
}

.modal-content {
  background: var(--card-bg);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-lg);
  width: 100%;
  max-width: 400px;
  max-height: 90vh;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: var(--spacing-md);
  border-bottom: 1px solid var(--border-color);
}

.modal-header h3 {
  font-size: var(--font-size-md);
  font-weight: var(--font-weight-semibold);
  color: var(--tg-theme-text-color);
  margin: 0;
}

.modal-close {
  background: none;
  border: none;
  color: var(--tg-theme-hint-color);
  cursor: pointer;
  padding: var(--spacing-xs);
  border-radius: var(--radius-sm);
  transition: all 0.2s ease;
}

.modal-close:hover {
  background: var(--hover-bg);
  color: var(--tg-theme-text-color);
}

.modal-body {
  flex: 1;
  overflow-y: auto;
  padding: var(--spacing-md);
}

.modal-footer {
  display: flex;
  gap: var(--spacing-sm);
  padding: var(--spacing-md);
  border-top: 1px solid var(--border-color);
}

/* Форма */
.form-group {
  margin-bottom: var(--spacing-md);
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: var(--spacing-sm);
}

.form-group label {
  display: block;
  margin-bottom: var(--spacing-xs);
  font-size: var(--font-size-sm);
  font-weight: var(--font-weight-medium);
  color: var(--tg-theme-text-color);
}

.form-group input, .form-group textarea {
  width: 100%;
  padding: var(--spacing-sm);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-sm);
  background: var(--card-bg);
  color: var(--tg-theme-text-color);
  font-size: var(--font-size-sm);
  font-family: inherit;
}

.form-group input:focus, .form-group textarea:focus {
  outline: none;
  border-color: var(--primary-color);
}

.form-group textarea {
  resize: vertical;
  min-height: 60px;
}

.checkbox-label {
  display: flex !important;
  align-items: center;
  gap: var(--spacing-sm);
  cursor: pointer;
  margin-bottom: 0 !important;
}

.checkbox-label input[type="checkbox"] {
  width: auto !important;
  margin: 0;
}

.checkbox-text {
  font-size: var(--font-size-sm);
  color: var(--tg-theme-text-color);
}

.btn {
  flex: 1;
  padding: var(--spacing-sm) var(--spacing-md);
  border: none;
  border-radius: var(--radius-sm);
  font-size: var(--font-size-sm);
  font-weight: var(--font-weight-medium);
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn.primary {
  background: var(--primary-color);
  color: white;
}

.btn.primary:hover {
  background: #1e90ff;
}

.btn.secondary {
  background: var(--card-bg);
  color: var(--tg-theme-text-color);
  border: 1px solid var(--border-color);
}

.btn.secondary:hover {
  background: var(--hover-bg);
}

@media (max-width: 480px) {
  .profile-header {
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: var(--spacing-sm);
  }
  
  .profile-stats {
    justify-content: center;
  }
  
  .info-grid {
    grid-template-columns: 1fr;
  }
  
  .cars-grid {
    grid-template-columns: 1fr;
  }
  
  .actions-card {
    flex-direction: column;
  }
  
  .form-row {
    grid-template-columns: 1fr;
  }
  
  .name-edit {
    flex-direction: column;
    align-items: stretch;
  }
  
  .info-edit {
    flex-direction: column;
    align-items: stretch;
  }
  
  .about-display {
    flex-direction: column;
    align-items: stretch;
  }
}
</style>
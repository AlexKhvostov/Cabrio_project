<template>
  <div v-if="show" class="modal-overlay" @click="$emit('close')">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <h2>{{ guideObject.name }}</h2>
        <button class="modal-close" @click="$emit('close')">
          <X :size="24" />
        </button>
      </div>
      
      <div class="modal-body">
        <!-- Основная информация -->
        <div class="guide-main-info-compact">
          <div class="info-badges-compact">
            <span :class="['category-badge', `category-${getCategoryColor(guideObject.category)}`]">
              <component :is="getCategoryIcon(guideObject.category)" :size="14" />
              {{ getCategoryLabel(guideObject.category) }}
            </span>
            <span :class="['type-badge', `type-${getTypeColor(guideObject.type)}`]">
              {{ getTypeLabel(guideObject.type) }}
            </span>
            <span :class="['recommendation-badge', `rec-${getRecommendationColor(guideObject.recommendation)}`]">
              {{ getRecommendationIcon(guideObject.recommendation) }}
              {{ guideObject.recommendation }}
            </span>
          </div>
          
          <div class="rating-section-compact">
            <div class="rating-display-compact">
              <div class="stars">
                <Star 
                  v-for="i in 5" 
                  :key="i" 
                  :size="14" 
                  :class="{ filled: i <= Math.round(guideObject.rating) }"
                />
              </div>
              <span class="rating-value-compact">{{ guideObject.rating.toFixed(1) }}</span>
              <span class="reviews-count-compact">({{ guideObject.reviews_count }})</span>
            </div>
          </div>
        </div>
        
        <!-- Описание -->
        <div class="description-section-compact">
          <h4>
            <FileText :size="16" />
            Описание
          </h4>
          <p>{{ guideObject.description }}</p>
        </div>
        
        <!-- Местоположение и контакты -->
        <div class="location-contacts-compact">
          <div class="location-info-compact">
            <h4>
              <MapPin :size="16" />
              Местоположение
            </h4>
            <div class="location-details-compact">
              <div class="location-item-compact">
                <MapPin :size="12" />
                <span>{{ guideObject.city }}</span>
              </div>
              <div v-if="guideObject.address" class="location-item-compact">
                <Navigation :size="12" />
                <span>{{ guideObject.address }}</span>
              </div>
            </div>
          </div>
          
          <div v-if="guideObject.phone || guideObject.website" class="contacts-info-compact">
            <h4>
              <Phone :size="16" />
              Контакты
            </h4>
            <div class="contacts-details-compact">
              <div v-if="guideObject.phone" class="contact-item-compact">
                <Phone :size="12" />
                <a :href="`tel:${guideObject.phone}`" class="contact-link">
                  {{ guideObject.phone }}
                </a>
              </div>
              <div v-if="guideObject.website" class="contact-item-compact">
                <Globe :size="12" />
                <a :href="guideObject.website" target="_blank" class="contact-link">
                  {{ guideObject.website }}
                </a>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Услуги -->
        <div class="services-section-compact">
          <h4>
            <List :size="16" />
            Услуги и возможности
          </h4>
          <div class="services-grid-compact">
            <span
              v-for="service in guideObject.services"
              :key="service"
              class="service-tag-compact"
            >
              {{ service }}
            </span>
          </div>
        </div>
        
        <!-- Добавлено участником -->
        <div class="added-by-section-compact">
          <h4>
            <UserPlus :size="16" />
            Добавлено участником
          </h4>
          <div class="added-by-info-compact">
            <div class="member-avatar-compact">
              <span class="avatar-initials">
                {{ getOrganizerInitials() }}
              </span>
            </div>
            <div class="member-details-compact">
              <div class="member-name-compact">{{ guideObject.added_by_name }}</div>
              <div v-if="guideObject.added_by_nickname" class="member-nickname-compact">
                @{{ guideObject.added_by_nickname }}
              </div>
              <div class="added-date-compact">
                {{ formatDate(guideObject.created_at) }}
              </div>
            </div>
          </div>
        </div>
        
        <!-- Отзывы -->
        <div v-if="guideObject.reviews && guideObject.reviews.length > 0" class="reviews-section-compact">
          <h4>
            <MessageSquare :size="16" />
            Отзывы участников ({{ guideObject.reviews.length }})
          </h4>
          <div class="reviews-list-compact">
            <div
              v-for="review in guideObject.reviews"
              :key="review.id"
              class="review-item-compact"
            >
              <div class="review-header-compact">
                <div class="reviewer-info-compact">
                  <div class="reviewer-avatar-compact">
                    <span class="avatar-initials">
                      {{ getReviewerInitials(review.member_name) }}
                    </span>
                  </div>
                  <div class="reviewer-details-compact">
                    <div class="reviewer-name-compact">{{ review.member_name }}</div>
                    <div v-if="review.member_nickname" class="reviewer-nickname-compact">
                      @{{ review.member_nickname }}
                    </div>
                  </div>
                </div>
                <div class="review-rating-compact">
                  <div class="review-stars-compact">
                    <Star 
                      v-for="i in 5" 
                      :key="i" 
                      :size="12" 
                      :class="{ filled: i <= review.rating }"
                    />
                  </div>
                  <span class="review-date-compact">{{ formatDate(review.date) }}</span>
                </div>
              </div>
              <div class="review-comment-compact">
                {{ review.comment }}
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Действия -->
      <div class="modal-footer">
        <button class="btn-primary" @click="addReview">
          <MessageSquare :size="16" />
          Оставить отзыв
        </button>
        <button class="btn-secondary" @click="shareGuideObject">
          <Share2 :size="16" />
          Поделиться
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { 
  X, 
  FileText, 
  MapPin, 
  Navigation,
  Phone, 
  Globe,
  List,
  UserPlus,
  MessageSquare,
  Share2,
  Star,
  Camera,
  Coffee,
  Wrench,
  ShoppingBag,
  ParkingCircle,
  Sparkles,
  Circle,
  Zap,
  Droplets,
  Package,
  Utensils
} from 'lucide-vue-next'
import type { Service } from '@/stores/data'
import { useTelegramStore } from '@/stores/telegram'

interface Props {
  show: boolean
  guideObject: Service
}

const props = defineProps<Props>()
defineEmits<{
  close: []
  addReview: [guideObject: Service]
}>()

const telegramStore = useTelegramStore()

const getCategoryColor = (category: string | undefined) => {
  if (!category) return 'default'
  switch (category) {
    case 'автосервис': return 'service'
    case 'детейлинг': return 'detailing'
    case 'шиномонтаж': return 'tire'
    case 'электрик': return 'electric'
    case 'автомойка': return 'wash'
    case 'кафе': return 'cafe'
    case 'средство': return 'product'
    case 'парковка': return 'parking'
    case 'ресторан': return 'restaurant'
    default: return 'default'
  }
}

const getCategoryIcon = (category: string | undefined) => {
  if (!category) return Wrench
  switch (category) {
    case 'автосервис': return Wrench
    case 'детейлинг': return Sparkles
    case 'шиномонтаж': return Circle
    case 'электрик': return Zap
    case 'автомойка': return Droplets
    case 'кафе': return Coffee
    case 'средство': return Package
    case 'парковка': return ParkingCircle
    case 'ресторан': return Utensils
    default: return Wrench
  }
}

const getCategoryLabel = (category: string | undefined) => {
  if (!category) return 'Сервис'
  const labels = {
    'автосервис': 'Сервис',
    'детейлинг': 'Детейлинг',
    'шиномонтаж': 'Шиномонтаж',
    'электрик': 'Электрик',
    'автомойка': 'Автомойка',
    'кафе': 'Кафе',
    'средство': 'Средство',
    'парковка': 'Парковка',
    'ресторан': 'Ресторан'
  }
  return labels[category as keyof typeof labels] || category
}

const getTypeLabel = (type: string) => {
  const labels = {
    'автосервис': 'Автосервис',
    'детейлинг': 'Детейлинг',
    'шиномонтаж': 'Шиномонтаж',
    'электрик': 'Электрик',
    'кафе': 'Кафе',
    'ресторан': 'Ресторан',
    'парковка': 'Парковка',
    'средство': 'Средство'
  }
  return labels[type as keyof typeof labels] || type
}

const getTypeColor = (type: string) => {
  return 'default'
}

const getRecommendationColor = (recommendation: string | undefined) => {
  if (!recommendation) return 'neutral'
  switch (recommendation) {
    case 'рекомендую': return 'positive'
    case 'не рекомендую': return 'negative'
    case 'нейтрально': return 'neutral'
    default: return 'neutral'
  }
}

const getRecommendationIcon = (recommendation: string | undefined) => {
  if (!recommendation) return '🤔'
  switch (recommendation) {
    case 'рекомендую': return '👍'
    case 'не рекомендую': return '👎'
    case 'нейтрально': return '🤔'
    default: return '🤔'
  }
}

const getAddedByInitials = () => {
  const organizerName = props.guideObject.added_by_name || 'Организатор'
  const names = organizerName.split(' ')
  return (names[0]?.charAt(0) + (names[1]?.charAt(0) || '')).toUpperCase()
}

const getReviewerInitials = (name: string) => {
  const names = name.split(' ')
  return (names[0]?.charAt(0) + (names[1]?.charAt(0) || '')).toUpperCase()
}

const formatDate = (dateString: string | undefined) => {
  if (!dateString) return 'Не указана'
  const date = new Date(dateString)
  return date.toLocaleDateString('ru-RU', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
}

const getOrganizerInitials = () => {
  const organizerName = props.guideObject.added_by_name || 'Организатор'
  const names = organizerName.split(' ')
  return (names[0]?.charAt(0) + (names[1]?.charAt(0) || '')).toUpperCase()
}

const addReview = () => {
  telegramStore.hapticFeedback('impact')
  // TODO: Implement add review logic
  console.log('Adding review for:', props.guideObject)
}

const shareGuideObject = () => {
  telegramStore.hapticFeedback('selection')
  // TODO: Implement share logic
  console.log('Sharing guide object:', props.guideObject)
}
</script>

<style scoped>
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
  backdrop-filter: blur(5px);
  -webkit-backdrop-filter: blur(5px);
}

.modal-content {
  background: var(--card-bg);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-lg);
  width: 100%;
  max-width: 600px;
  max-height: 90vh;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: var(--spacing-lg);
  border-bottom: 1px solid var(--border-color);
}

.modal-header h2 {
  font-size: var(--font-size-xl);
  font-weight: var(--font-weight-semibold);
  color: var(--tg-theme-text-color);
  margin: 0;
  flex: 1;
  padding-right: var(--spacing-md);
}

.modal-close {
  background: none;
  border: none;
  color: var(--tg-theme-hint-color);
  cursor: pointer;
  padding: var(--spacing-xs);
  border-radius: var(--radius-sm);
  transition: all 0.2s ease;
  flex-shrink: 0;
}

.modal-close:hover {
  background: var(--hover-bg);
  color: var(--tg-theme-text-color);
}

.modal-body {
  flex: 1;
  overflow-y: auto;
  padding: var(--spacing-md);
  display: flex;
  flex-direction: column;
  gap: var(--spacing-md);
}

/* Основная информация */
.guide-main-info-compact {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-sm);
}

.info-badges-compact {
  display: flex;
  flex-wrap: wrap;
  gap: var(--spacing-sm);
}

.category-badge,
.type-badge,
.recommendation-badge {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: var(--spacing-xs) var(--spacing-sm);
  border-radius: var(--radius-sm);
  font-size: var(--font-size-xs);
  font-weight: var(--font-weight-medium);
}

.category-photo { background: rgba(233, 30, 99, 0.15); color: #e91e63; }
.category-food { background: rgba(255, 152, 0, 0.15); color: var(--warning-color); }
.category-repair { background: rgba(46, 166, 255, 0.15); color: var(--primary-color); }
.category-service { background: rgba(76, 175, 80, 0.15); color: var(--success-color); }
.category-rest { background: rgba(156, 39, 176, 0.15); color: #9c27b0; }

.type-badge {
  background: rgba(158, 158, 158, 0.15);
  color: var(--tg-theme-hint-color);
}

.rec-positive { background: rgba(76, 175, 80, 0.15); color: var(--success-color); }
.rec-negative { background: rgba(244, 67, 54, 0.15); color: var(--error-color); }

/* Рейтинг */
.rating-section-compact {
  padding: var(--spacing-sm);
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-md);
}

.rating-display-compact {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
}

.stars {
  display: flex;
  gap: 2px;
}

.stars svg {
  color: var(--border-color);
  transition: color 0.2s ease;
}

.stars svg.filled {
  color: #ffd700;
}

.rating-value-compact {
  font-size: var(--font-size-sm);
  font-weight: var(--font-weight-bold);
  color: var(--tg-theme-text-color);
}

.reviews-count-compact {
  font-size: var(--font-size-sm);
  color: var(--tg-theme-hint-color);
}

/* Секции */
.description-section-compact,
.location-contacts-compact,
.services-section-compact,
.added-by-section-compact,
.reviews-section-compact {
  border-top: 1px solid var(--border-color);
  padding-top: var(--spacing-sm);
}

.description-section-compact h4,
.location-info-compact h4,
.contacts-info-compact h4,
.services-section-compact h4,
.added-by-section-compact h4,
.reviews-section-compact h4 {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
  font-size: var(--font-size-sm);
  font-weight: var(--font-weight-semibold);
  color: var(--tg-theme-text-color);
  margin: 0 0 var(--spacing-sm) 0;
}

.description-section-compact h4 svg,
.location-info-compact h4 svg,
.contacts-info-compact h4 svg,
.services-section-compact h4 svg,
.added-by-section-compact h4 svg,
.reviews-section-compact h4 svg {
  color: var(--primary-color);
}

.description-section-compact p {
  font-size: var(--font-size-sm);
  color: var(--tg-theme-text-color);
  line-height: 1.6;
  margin: 0;
}

/* Местоположение и контакты */
.location-contacts-compact {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: var(--spacing-md);
}

.location-details-compact,
.contacts-details-compact {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-sm);
}

.location-item-compact,
.contact-item-compact {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
  font-size: var(--font-size-sm);
  color: var(--tg-theme-text-color);
}

.location-item-compact svg,
.contact-item-compact svg {
  color: var(--tg-theme-hint-color);
  flex-shrink: 0;
}

.contact-link {
  color: var(--primary-color);
  text-decoration: none;
  transition: color 0.2s ease;
}

.contact-link:hover {
  color: #1e90ff;
  text-decoration: underline;
}

/* Услуги */
.services-grid-compact {
  display: flex;
  flex-wrap: wrap;
  gap: var(--spacing-xs);
}

.service-tag-compact {
  padding: var(--spacing-xs) var(--spacing-sm);
  background: rgba(46, 166, 255, 0.1);
  color: var(--primary-color);
  border: 1px solid rgba(46, 166, 255, 0.2);
  border-radius: var(--radius-sm);
  font-size: var(--font-size-xs);
  font-weight: var(--font-weight-medium);
}

/* Добавлено участником */
.added-by-info-compact {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
}

.member-avatar-compact {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: var(--primary-color);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.avatar-initials {
  color: white;
  font-weight: var(--font-weight-bold);
  font-size: var(--font-size-sm);
}

.member-details-compact {
  flex: 1;
}

.member-name-compact {
  font-size: var(--font-size-sm);
  font-weight: var(--font-weight-semibold);
  color: var(--tg-theme-text-color);
  margin-bottom: 2px;
}

.member-nickname-compact {
  font-size: var(--font-size-xs);
  color: var(--primary-color);
  margin-bottom: 2px;
}

.added-date-compact {
  font-size: var(--font-size-xs);
  color: var(--tg-theme-hint-color);
}

/* Отзывы */
.reviews-list-compact {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-sm);
}

.review-item-compact {
  padding: var(--spacing-sm);
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-md);
}

.review-header-compact {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: var(--spacing-sm);
}

.reviewer-info-compact {
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
}

.reviewer-avatar-compact {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: var(--primary-color);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.reviewer-details-compact {
  flex: 1;
}

.reviewer-name-compact {
  font-size: var(--font-size-sm);
  font-weight: var(--font-weight-semibold);
  color: var(--tg-theme-text-color);
  margin-bottom: 2px;
}

.reviewer-nickname-compact {
  font-size: var(--font-size-xs);
  color: var(--primary-color);
}

.review-rating-compact {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 2px;
}

.review-stars-compact {
  display: flex;
  gap: 1px;
}

.review-stars-compact svg {
  color: var(--border-color);
}

.review-stars-compact svg.filled {
  color: #ffd700;
}

.review-date-compact {
  font-size: var(--font-size-xs);
  color: var(--tg-theme-hint-color);
}

.review-comment-compact {
  font-size: var(--font-size-sm);
  color: var(--tg-theme-text-color);
  line-height: 1.5;
}

/* Footer */
.modal-footer {
  display: flex;
  gap: var(--spacing-sm);
  padding: var(--spacing-md);
  border-top: 1px solid var(--border-color);
}

.btn-primary,
.btn-secondary {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: var(--spacing-sm);
  padding: var(--spacing-sm);
  border: none;
  border-radius: var(--radius-md);
  font-size: var(--font-size-sm);
  font-weight: var(--font-weight-medium);
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-primary {
  background: var(--primary-color);
  color: white;
}

.btn-primary:hover {
  background: #1e90ff;
}

.btn-secondary {
  background: var(--card-bg);
  color: var(--tg-theme-text-color);
  border: 1px solid var(--border-color);
}

.btn-secondary:hover {
  background: var(--hover-bg);
}

@media (max-width: 480px) {
  .modal-content {
    margin: var(--spacing-sm);
    max-height: calc(100vh - 32px);
  }
  
  .modal-body {
    padding: var(--spacing-sm);
    gap: var(--spacing-sm);
  }
  
  .location-contacts-compact {
    grid-template-columns: 1fr;
    gap: var(--spacing-sm);
  }
  
  .info-badges-compact {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .rating-display-compact {
    flex-wrap: wrap;
  }
  
  .review-header-compact {
    flex-direction: column;
    align-items: flex-start;
    gap: var(--spacing-sm);
  }
  
  .modal-footer {
    flex-direction: column;
  }
}
</style>
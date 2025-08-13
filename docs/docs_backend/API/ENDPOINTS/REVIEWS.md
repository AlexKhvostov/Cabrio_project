# Reviews API - Отзывы 📝

> **Назначение:** API для управления отзывами о гид-объектах

---

## 🎯 Назначение

API Reviews предоставляет функционал для работы с отзывами:
- Создание и управление отзывами о гид-объектах
- Система рейтингов (качество, скорость, цена)
- Модерация отзывов
- Аналитика и статистика

---

## 🏗️ Архитектура

### **Базовые URL:**
- **Короткий:** `http://localhost/app/api/reviews`
- **Длинный:** `http://localhost/app/backend/routes/api.php`

### **JavaScript примеры:**
```javascript
// Получение отзывов объекта
const response = await fetch('http://localhost/app/api/reviews?guide_object_id=789');

// Создание отзыва
const createReview = await fetch('http://localhost/app/api/reviews', {
  method: 'POST',
  body: JSON.stringify(reviewData)
});
```

---

## 🔐 Авторизация

### **Требуемые роли:**
- **Просмотр:** `guest`, `user`, `member`, `moderator`, `admin`
- **Создание:** `member`, `moderator`, `admin`
- **Редактирование:** `moderator`, `admin` (или автор отзыва)
- **Модерация:** `moderator`, `admin`

### **Проверка доступа:**
```php
// В контроллере
$this->requireAccess('reviews_create'); // Создание отзыва
$this->requireAccess('reviews_view');   // Просмотр отзывов
```

---

## 📋 Эндпоинты

### **Основные операции:**
- `GET http://localhost/app/api/reviews` — получение списка отзывов
- `POST http://localhost/app/api/reviews` — создание нового отзыва
- `GET http://localhost/app/api/reviews/{id}` — получение отзыва по ID
- `PUT http://localhost/app/api/reviews/{id}` — обновление отзыва
- `DELETE http://localhost/app/api/reviews/{id}` — удаление отзыва

### **Специальные операции:**
- `GET http://localhost/app/api/guide-objects/{id}/reviews` — отзывы объекта
- `POST http://localhost/app/api/guide-objects/{id}/reviews` — добавить отзыв к объекту

---

## 📝 Примеры запросов

### **1. Получение списка отзывов**

#### **Запрос:**
```http
GET http://localhost/app/api/reviews?page=1&per_page=20&guide_object_id=789&status=approved
```

#### **Ответ:**
```json
{
  "success": true,
  "data": [
    {
      "id": 15,
      "guide_object_id": 789,
      "quality_rating": 8,
      "speed_rating": 9,
      "price_rating": 7,
      "feedback": "Отличный сервис, быстро и качественно",
      "status": {
        "id": 2,
        "code": "approved",
        "name": "Одобрен",
        "color": "#28a745"
      },
      "author": {
        "id": 124,
        "first_name": "Мария",
        "last_name": "Петрова",
        "username": "maria_user"
      },
      "guide_object": {
        "id": 789,
        "name": "Автосервис BMW",
        "type": "service"
      },
      "created_at": "2024-01-10T12:00:00Z",
      "updated_at": "2024-01-10T12:00:00Z"
    }
  ],
  "pagination": {
    "page": 1,
    "per_page": 20,
    "total": 45,
    "pages": 3
  }
}
```

### **2. Создание отзыва**

#### **Запрос:**
```http
POST http://localhost/app/api/reviews
Content-Type: application/json
Authorization: Bearer {token}

{
  "guide_object_id": 789,
  "quality_rating": 9,
  "speed_rating": 8,
  "price_rating": 7,
  "feedback": "Отличный сервис, рекомендую всем!"
}
```

#### **Ответ:**
```json
{
  "success": true,
  "data": {
    "id": 16,
    "guide_object_id": 789,
    "quality_rating": 9,
    "speed_rating": 8,
    "price_rating": 7,
    "feedback": "Отличный сервис, рекомендую всем!",
    "status": {
      "id": 1,
      "code": "pending",
      "name": "На модерации",
      "color": "#ffc107"
    },
    "author": {
      "id": 125,
      "first_name": "Алексей",
      "last_name": "Сидоров",
      "username": "alex_user"
    },
    "guide_object": {
      "id": 789,
      "name": "Автосервис BMW",
      "type": "service"
    },
    "created_at": "2024-01-15T16:00:00Z",
    "updated_at": "2024-01-15T16:00:00Z"
  }
}
```

### **3. Получение отзыва по ID**

#### **Запрос:**
```http
GET http://localhost/app/api/reviews/15
```

#### **Ответ:**
```json
{
  "success": true,
  "data": {
    "id": 15,
    "guide_object_id": 789,
    "quality_rating": 8,
    "speed_rating": 9,
    "price_rating": 7,
    "feedback": "Отличный сервис, быстро и качественно",
    "status": {
      "id": 2,
      "code": "approved",
      "name": "Одобрен",
      "color": "#28a745"
    },
    "author": {
      "id": 124,
      "first_name": "Мария",
      "last_name": "Петрова",
      "username": "maria_user"
    },
    "guide_object": {
      "id": 789,
      "name": "Автосервис BMW",
      "type": "service",
      "city": "Москва",
      "address": "ул. Тверская, 15"
    },
    "photos": [
      {
        "id": 30,
        "url": "http://localhost/app/uploads/review/review_15_photo.jpg",
        "file_name": "review_15_photo.jpg"
      }
    ],
    "created_at": "2024-01-10T12:00:00Z",
    "updated_at": "2024-01-10T12:00:00Z"
  }
}
```

### **4. Добавление отзыва к объекту**

#### **Запрос:**
```http
POST http://localhost/app/api/guide-objects/789/reviews
Content-Type: application/json
Authorization: Bearer {token}

{
  "quality_rating": 9,
  "speed_rating": 8,
  "price_rating": 7,
  "feedback": "Отличный сервис, рекомендую всем!"
}
```

#### **Ответ:**
```json
{
  "success": true,
  "data": {
    "id": 17,
    "guide_object_id": 789,
    "quality_rating": 9,
    "speed_rating": 8,
    "price_rating": 7,
    "feedback": "Отличный сервис, рекомендую всем!",
    "status": {
      "id": 1,
      "code": "pending",
      "name": "На модерации",
      "color": "#ffc107"
    },
    "author": {
      "id": 125,
      "first_name": "Алексей",
      "last_name": "Сидоров",
      "username": "alex_user"
    },
    "created_at": "2024-01-15T16:30:00Z"
  }
}
```

---

## 🚨 Обработка ошибок

### **Ошибка валидации:**
```json
{
  "success": false,
  "error": "validation_error",
  "message": "Ошибка валидации данных",
  "details": {
    "guide_object_id": "ID объекта обязателен",
    "quality_rating": "Оценка качества должна быть от 1 до 10",
    "feedback": "Текст отзыва обязателен"
  }
}
```

### **Ошибка доступа:**
```json
{
  "success": false,
  "error": "access_denied",
  "message": "Недостаточно прав для создания отзыва"
}
```

### **Отзыв не найден:**
```json
{
  "success": false,
  "error": "not_found",
  "message": "Отзыв с ID 999 не найден"
}
```

### **Дублирование отзыва:**
```json
{
  "success": false,
  "error": "duplicate_review",
  "message": "Вы уже оставляли отзыв для этого объекта"
}
```

### **Объект не найден:**
```json
{
  "success": false,
  "error": "guide_object_not_found",
  "message": "Гид-объект с ID 999 не найден"
}
```

---

## 🔐 Права доступа

### **Просмотр отзывов:**
- **guest** — только одобренные отзывы
- **user** — все отзывы
- **member** — все отзывы + создание своих
- **moderator** — все отзывы + модерация
- **admin** — полный доступ

### **Создание отзывов:**
- **member** — создание отзывов к любым объектам
- **moderator** — создание любых отзывов
- **admin** — создание любых отзывов

### **Редактирование отзывов:**
- **Автор отзыва** — редактирование своего отзыва
- **moderator** — редактирование любых отзывов
- **admin** — редактирование любых отзывов

### **Модерация отзывов:**
- **moderator** — одобрение/отклонение отзывов
- **admin** — полная модерация

---

## 📊 Структура данных

### **Review (Отзыв):**
```typescript
interface Review {
  id: number;
  guide_object_id: number;
  quality_rating: number;    // 1-10
  speed_rating: number;      // 1-10
  price_rating: number;      // 1-10
  feedback: string;
  status: Status;
  author: User;
  guide_object: GuideObject;
  photos?: Photo[];
  created_at: string;
  updated_at: string;
}
```

### **Rating (Рейтинг):**
```typescript
interface Rating {
  quality_rating: number;    // Оценка качества (1-10)
  speed_rating: number;      // Оценка скорости (1-10)
  price_rating: number;      // Оценка цены (1-10)
  average_rating: number;    // Средняя оценка
}
```

### **ReviewStatistics (Статистика отзывов):**
```typescript
interface ReviewStatistics {
  total_reviews: number;
  average_quality: number;
  average_speed: number;
  average_price: number;
  average_overall: number;
  rating_distribution: {
    [rating: number]: number; // Количество отзывов с каждой оценкой
  };
}
```

---

## 🔗 Интеграция

### **С Telegram Bot:**
```javascript
// Создание отзыва через бота
const reviewData = {
  guide_object_id: 789,
  quality_rating: 9,
  speed_rating: 8,
  price_rating: 7,
  feedback: "Отличный сервис!"
};

const response = await fetch('http://localhost/app/api/reviews', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${telegramToken}`
  },
  body: JSON.stringify(reviewData)
});
```

### **С Frontend:**
```javascript
// Получение отзывов объекта
const reviews = await fetch('http://localhost/app/api/reviews?guide_object_id=789', {
  headers: {
    'Authorization': `Bearer ${userToken}`
  }
});

// Создание отзыва
const review = await fetch('http://localhost/app/api/reviews', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${userToken}`
  },
  body: JSON.stringify({
    guide_object_id: 789,
    quality_rating: 9,
    speed_rating: 8,
    price_rating: 7,
    feedback: "Отличный сервис!"
  })
});
```

### **Добавление фото к отзыву:**
```javascript
// Загрузка фото к отзыву
const formData = new FormData();
formData.append('photo', file);
formData.append('entity_type', 'review');
formData.append('entity_id', '15');

const response = await fetch('http://localhost/app/api/photos', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${userToken}`
  },
  body: formData
});

const result = await response.json();
console.log(result.data.url); // http://localhost/app/uploads/review/review_15_photo.jpg
```

---

## 📈 Мониторинг

### **Метрики для отслеживания:**
- Количество созданных отзывов
- Средние рейтинги по объектам
- Количество модераций
- Популярные объекты по отзывам
- Качество отзывов

### **Логирование:**
```php
// В контроллере
Logger::info('Review created', [
    'review_id' => $reviewId,
    'user_id' => $userId,
    'guide_object_id' => $guideObjectId,
    'average_rating' => $averageRating
]);
```

---

## 🔧 Конфигурация

### **Рейтинги:**
- **Диапазон:** 1-10 баллов
- **Обязательные поля:** quality_rating, speed_rating, price_rating
- **Средний рейтинг:** (quality + speed + price) / 3

### **Модерация:**
- **Автоматическая:** Отзывы от новых пользователей
- **Ручная:** Отзывы от проверенных пользователей
- **Фильтрация:** Автоматическая проверка на спам

---

## 🔗 Связанные документы

- [API Overview](../OVERVIEW.md) — общие принципы API
- [Guide Objects API](GUIDE_OBJECTS.md) — управление гид-объектами
- [Photos API](PHOTOS.md) — управление фотографиями
- [Database Schema](../../DATABASE/SCHEMA.md) — структура БД
- [Authentication](../../AUTHENTICATION/OVERVIEW.md) — авторизация

---

> **Примечание:** Все рейтинги передаются в диапазоне 1-10. Средний рейтинг вычисляется автоматически. Один пользователь может оставить только один отзыв на один объект. 
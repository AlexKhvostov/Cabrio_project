# Guide Objects API - Гид-объекты 🏛️

> **Назначение:** API для управления объектами гида (кафе, сервисы, достопримечательности)

---

## 🎯 Назначение

API Guide Objects предоставляет функционал для работы с объектами гида:
- Создание и управление объектами гида
- Поиск объектов по типу и местоположению
- Система отзывов и рейтингов
- Модерация объектов

---

## 🏗️ Архитектура

### **Базовые URL:**
- **Короткий:** `http://localhost/app/api/guide-objects`
- **Длинный:** `http://localhost/app/backend/routes/api.php`

### **Файлы объектов:**
- **Фото объекта:** `http://localhost/app/uploads/guide_object/guide_789_photo.jpg`
- **Логотип объекта:** `http://localhost/app/uploads/guide_object/guide_789_logo.jpg`

### **JavaScript примеры:**
```javascript
// Получение списка объектов
const response = await fetch('http://localhost/app/api/guide-objects?type=service&city=Москва');

// Получение объекта по ID
const object = await fetch('http://localhost/app/api/guide-objects/789');

// Создание объекта
const createObject = await fetch('http://localhost/app/api/guide-objects', {
  method: 'POST',
  body: JSON.stringify(objectData)
});
```

---

## 🔐 Авторизация

### **Требуемые роли:**
- **Просмотр:** `guest`, `user`, `member`, `moderator`, `admin`
- **Создание:** `member`, `moderator`, `admin`
- **Редактирование:** `moderator`, `admin` (или создатель объекта)
- **Модерация:** `moderator`, `admin`

### **Проверка доступа:**
```php
// В контроллере
$this->requireAccess('guide_objects_create'); // Создание объекта
$this->requireAccess('guide_objects_view');   // Просмотр объектов
```

---

## 📋 Эндпоинты

### **Основные операции:**
- `GET http://localhost/app/api/guide-objects` — получение списка объектов
- `POST http://localhost/app/api/guide-objects` — создание нового объекта
- `GET http://localhost/app/api/guide-objects/{id}` — получение объекта по ID
- `PUT http://localhost/app/api/guide-objects/{id}` — обновление объекта
- `DELETE http://localhost/app/api/guide-objects/{id}` — удаление объекта

### **Управление отзывами:**
- `GET http://localhost/app/api/guide-objects/{id}/reviews` — отзывы объекта
- `POST http://localhost/app/api/guide-objects/{id}/reviews` — добавить отзыв
- `PUT http://localhost/app/api/guide-objects/{id}/reviews/{review_id}` — обновить отзыв
- `DELETE http://localhost/app/api/guide-objects/{id}/reviews/{review_id}` — удалить отзыв

---

## 📝 Примеры запросов

### **1. Получение списка объектов**

#### **Запрос:**
```http
GET http://localhost/app/api/guide-objects?page=1&per_page=20&type=service&city=Москва&status=approved
```

#### **Ответ:**
```json
{
  "success": true,
  "data": [
    {
      "id": 789,
      "name": "Автосервис BMW",
      "description": "Специализированный сервис для автомобилей BMW",
      "city": "Москва",
      "address": "ул. Тверская, 15",
      "website": "https://bmw-service.ru",
      "phone": "+7 (495) 123-45-67",
      "Instagram": "https://instagram.com/bmw_service",
      "Telegram": "https://t.me/bmw_service",
      "Viber": "+7 (495) 123-45-67",
      "WhatsApp": "+7 (495) 123-45-67",
      "service_list": ["Диагностика", "Ремонт двигателя", "Замена масла"],
      "price": 5000.00,
      "brand": "BMW",
      "status": {
        "id": 2,
        "code": "approved",
        "name": "Одобрено",
        "color": "#28a745"
      },
      "guide_object_type": {
        "id": 1,
        "code": "service",
        "name": "Сервис",
        "color": "#007bff"
      },
      "guide_object_kind": {
        "id": 3,
        "code": "repair",
        "name": "Ремонт",
        "color": "#dc3545"
      },
      "creator": {
        "id": 123,
        "first_name": "Иван",
        "last_name": "Иванов",
        "username": "ivan_user"
      },
      "photo": {
        "id": 20,
        "url": "http://localhost/app/uploads/guide_object/guide_789_photo.jpg",
        "file_name": "guide_789_photo.jpg"
      },
      "average_rating": 4.5,
      "reviews_count": 12,
      "created_at": "2024-01-15T10:30:00Z",
      "updated_at": "2024-01-15T12:00:00Z"
    }
  ],
  "pagination": {
    "page": 1,
    "per_page": 20,
    "total": 156,
    "pages": 8
  }
}
```

### **2. Создание объекта**

#### **Запрос:**
```http
POST http://localhost/app/api/guide-objects
Content-Type: application/json
Authorization: Bearer {token}

{
  "name": "Кафе на трассе",
  "description": "Уютное кафе для остановки в пути",
  "city": "Солнечногорск",
  "address": "Ленинградское шоссе, 45 км",
  "website": "https://cafe-na-trasse.ru",
  "phone": "+7 (495) 987-65-43",
  "Instagram": "https://instagram.com/cafe_na_trasse",
  "service_list": ["Завтрак", "Обед", "Ужин", "Кофе"],
  "price": 1500.00,
  "guide_object_type_id": 2,
  "guide_object_kind_id": 5
}
```

#### **Ответ:**
```json
{
  "success": true,
  "data": {
    "id": 790,
    "name": "Кафе на трассе",
    "description": "Уютное кафе для остановки в пути",
    "city": "Солнечногорск",
    "address": "Ленинградское шоссе, 45 км",
    "website": "https://cafe-na-trasse.ru",
    "phone": "+7 (495) 987-65-43",
    "Instagram": "https://instagram.com/cafe_na_trasse",
    "Telegram": null,
    "Viber": null,
    "WhatsApp": null,
    "service_list": ["Завтрак", "Обед", "Ужин", "Кофе"],
    "price": 1500.00,
    "brand": null,
    "status": {
      "id": 1,
      "code": "pending",
      "name": "На модерации",
      "color": "#ffc107"
    },
    "guide_object_type": {
      "id": 2,
      "code": "cafe",
      "name": "Кафе",
      "color": "#28a745"
    },
    "guide_object_kind": {
      "id": 5,
      "code": "breakfast",
      "name": "Завтрак",
      "color": "#fd7e14"
    },
    "creator": {
      "id": 123,
      "first_name": "Иван",
      "last_name": "Иванов",
      "username": "ivan_user"
    },
    "average_rating": 0,
    "reviews_count": 0,
    "created_at": "2024-01-15T14:30:00Z",
    "updated_at": "2024-01-15T14:30:00Z"
  }
}
```

### **3. Получение объекта по ID**

#### **Запрос:**
```http
GET http://localhost/app/api/guide-objects/789
```

#### **Ответ:**
```json
{
  "success": true,
  "data": {
    "id": 789,
    "name": "Автосервис BMW",
    "description": "Специализированный сервис для автомобилей BMW",
    "city": "Москва",
    "address": "ул. Тверская, 15",
    "website": "https://bmw-service.ru",
    "phone": "+7 (495) 123-45-67",
    "Instagram": "https://instagram.com/bmw_service",
    "Telegram": "https://t.me/bmw_service",
    "Viber": "+7 (495) 123-45-67",
    "WhatsApp": "+7 (495) 123-45-67",
    "service_list": ["Диагностика", "Ремонт двигателя", "Замена масла"],
    "price": 5000.00,
    "brand": "BMW",
    "status": {
      "id": 2,
      "code": "approved",
      "name": "Одобрено",
      "color": "#28a745"
    },
    "guide_object_type": {
      "id": 1,
      "code": "service",
      "name": "Сервис",
      "color": "#007bff"
    },
    "guide_object_kind": {
      "id": 3,
      "code": "repair",
      "name": "Ремонт",
      "color": "#dc3545"
    },
    "creator": {
      "id": 123,
      "first_name": "Иван",
      "last_name": "Иванов",
      "username": "ivan_user"
    },
    "photo": {
      "id": 20,
      "url": "http://localhost/app/uploads/guide_object/guide_789_photo.jpg",
      "file_name": "guide_789_photo.jpg"
    },
    "reviews": [
      {
        "id": 15,
        "quality_rating": 8,
        "speed_rating": 9,
        "price_rating": 7,
        "feedback": "Отличный сервис, быстро и качественно",
        "author": {
          "id": 124,
          "first_name": "Мария",
          "last_name": "Петрова",
          "username": "maria_user"
        },
        "created_at": "2024-01-10T12:00:00Z"
      }
    ],
    "average_rating": 4.5,
    "reviews_count": 12,
    "created_at": "2024-01-15T10:30:00Z",
    "updated_at": "2024-01-15T12:00:00Z"
  }
}
```

### **4. Добавление отзыва**

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
    "id": 16,
    "guide_object_id": 789,
    "quality_rating": 9,
    "speed_rating": 8,
    "price_rating": 7,
    "feedback": "Отличный сервис, рекомендую всем!",
    "author": {
      "id": 125,
      "first_name": "Алексей",
      "last_name": "Сидоров",
      "username": "alex_user"
    },
    "status": {
      "id": 2,
      "code": "approved",
      "name": "Одобрен",
      "color": "#28a745"
    },
    "created_at": "2024-01-15T16:00:00Z"
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
    "name": "Название объекта обязательно",
    "city": "Город обязателен",
    "guide_object_type_id": "Тип объекта обязателен"
  }
}
```

### **Ошибка доступа:**
```json
{
  "success": false,
  "error": "access_denied",
  "message": "Недостаточно прав для создания объекта"
}
```

### **Объект не найден:**
```json
{
  "success": false,
  "error": "not_found",
  "message": "Объект с ID 999 не найден"
}
```

### **Дублирование объекта:**
```json
{
  "success": false,
  "error": "duplicate_object",
  "message": "Объект с таким названием уже существует в этом городе"
}
```

---

## 🔐 Права доступа

### **Просмотр объектов:**
- **guest** — только одобренные объекты
- **user** — все объекты
- **member** — все объекты + создание своих
- **moderator** — все объекты + модерация
- **admin** — полный доступ

### **Создание объектов:**
- **member** — создание своих объектов
- **moderator** — создание любых объектов
- **admin** — создание любых объектов

### **Редактирование объектов:**
- **Создатель объекта** — редактирование своего объекта
- **moderator** — редактирование любых объектов
- **admin** — редактирование любых объектов

### **Модерация объектов:**
- **moderator** — одобрение/отклонение объектов
- **admin** — полная модерация

---

## 📊 Структура данных

### **GuideObject (Объект гида):**
```typescript
interface GuideObject {
  id: number;
  name: string;
  description: string;
  city: string;
  address: string;
  website?: string;
  phone?: string;
  Instagram?: string;
  Telegram?: string;
  Viber?: string;
  WhatsApp?: string;
  service_list: string[];
  price?: number;
  brand?: string;
  status: Status;
  guide_object_type: GuideObjectType;
  guide_object_kind: GuideObjectKind;
  creator: User;
  photo?: Photo;
  average_rating: number;
  reviews_count: number;
  created_at: string;
  updated_at: string;
}
```

### **GuideObjectType (Тип объекта):**
```typescript
interface GuideObjectType {
  id: number;
  code: string;              // 'service', 'cafe', 'hotel'
  name: string;              // 'Сервис', 'Кафе', 'Отель'
  color: string;             // '#007bff'
}
```

### **GuideObjectKind (Вид объекта):**
```typescript
interface GuideObjectKind {
  id: number;
  type_id: number;
  code: string;              // 'repair', 'breakfast', 'luxury'
  name: string;              // 'Ремонт', 'Завтрак', 'Люкс'
  color: string;             // '#dc3545'
}
```

### **Review (Отзыв):**
```typescript
interface Review {
  id: number;
  guide_object_id: number;
  quality_rating: number;    // 1-10
  speed_rating: number;      // 1-10
  price_rating: number;      // 1-10
  feedback: string;
  author: User;
  status: Status;
  created_at: string;
}
```

---

## 🔗 Интеграция

### **С Telegram Bot:**
```javascript
// Создание объекта через бота
const objectData = {
  name: "Автосервис BMW",
  description: "Специализированный сервис",
  city: "Москва",
  address: "ул. Тверская, 15",
  phone: "+7 (495) 123-45-67",
  service_list: ["Диагностика", "Ремонт"],
  price: 5000,
  guide_object_type_id: 1,
  guide_object_kind_id: 3
};

const response = await fetch('http://localhost/app/api/guide-objects', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${telegramToken}`
  },
  body: JSON.stringify(objectData)
});
```

### **С Frontend:**
```javascript
// Поиск объектов по городу
const objects = await fetch('http://localhost/app/api/guide-objects?city=Москва&type=service', {
  headers: {
    'Authorization': `Bearer ${userToken}`
  }
});

// Добавление отзыва
const review = await fetch('http://localhost/app/api/guide-objects/789/reviews', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${userToken}`
  },
  body: JSON.stringify({
    quality_rating: 9,
    speed_rating: 8,
    price_rating: 7,
    feedback: "Отличный сервис!"
  })
});
```

### **Загрузка фото объекта:**
```javascript
// Загрузка фото объекта
const formData = new FormData();
formData.append('photo', file);
formData.append('entity_type', 'guide_object');
formData.append('entity_id', '789');

const response = await fetch('http://localhost/app/api/photos', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${userToken}`
  },
  body: formData
});

const result = await response.json();
console.log(result.data.url); // http://localhost/app/uploads/guide_object/guide_789_photo.jpg
```

---

## 📈 Мониторинг

### **Метрики для отслеживания:**
- Количество созданных объектов
- Количество отзывов
- Средние рейтинги по типам объектов
- География объектов
- Популярные виды объектов

### **Логирование:**
```php
// В контроллере
Logger::info('Guide object created', [
    'object_id' => $objectId,
    'user_id' => $userId,
    'type' => $objectType
]);
```

---

## 🔗 Связанные документы

- [API Overview](../OVERVIEW.md) — общие принципы API
- [Reviews API](REVIEWS.md) — управление отзывами
- [Database Schema](../../DATABASE/SCHEMA.md) — структура БД
- [Authentication](../../AUTHENTICATION/OVERVIEW.md) — авторизация

---

> **Примечание:** Все рейтинги передаются в диапазоне 1-10. Средний рейтинг вычисляется как среднее арифметическое всех трёх оценок (качество, скорость, цена). 
# Events API - Управление событиями 🎉

> **Назначение:** API для управления событиями и мероприятиями клуба CabrioRide

---

## 🎯 Назначение

API Events предоставляет полный функционал для работы с событиями клуба:
- Создание и управление событиями
- Регистрация участников
- Просмотр списка событий
- Управление статусами событий

---

## 🏗️ Архитектура

### **Базовые URL:**
- **Короткий:** `http://localhost/app/api/events`
- **Длинный:** `http://localhost/app/backend/routes/api.php`

### **Файлы событий:**
- **Фото события:** `http://localhost/app/uploads/event/event_456_photo.jpg`
- **Обложка события:** `http://localhost/app/uploads/event/event_456_cover.jpg`

### **JavaScript примеры:**
```javascript
// Получение списка событий
const response = await fetch('http://localhost/app/api/events?status=published');

// Получение события по ID
const event = await fetch('http://localhost/app/api/events/456');

// Создание события
const createEvent = await fetch('http://localhost/app/api/events', {
  method: 'POST',
  body: JSON.stringify(eventData)
});
```

---

## 🔐 Авторизация

### **Требуемые роли:**
- **Просмотр:** `guest`, `user`, `member`, `moderator`, `admin`
- **Создание:** `member`, `moderator`, `admin`
- **Редактирование:** `moderator`, `admin` (или создатель события)
- **Удаление:** `moderator`, `admin`

### **Проверка доступа:**
```php
// В контроллере
$this->requireAccess('events_create'); // Создание события
$this->requireAccess('events_view');   // Просмотр событий
```

---

## 📋 Эндпоинты

### **Основные операции:**
- `GET http://localhost/app/api/events` — получение списка событий
- `POST http://localhost/app/api/events` — создание нового события
- `GET http://localhost/app/api/events/{id}` — получение события по ID
- `PUT http://localhost/app/api/events/{id}` — обновление события
- `DELETE http://localhost/app/api/events/{id}` — удаление события

### **Управление участниками:**
- `POST http://localhost/app/api/events/{id}/join` — присоединиться к событию
- `DELETE http://localhost/app/api/events/{id}/leave` — покинуть событие
- `GET http://localhost/app/api/events/{id}/participants` — список участников

---

## 📝 Примеры запросов

### **1. Получение списка событий**

#### **Запрос:**
```http
GET http://localhost/app/api/events?page=1&per_page=20&status=published&event_type_id=1&city=Москва
```

#### **Ответ:**
```json
{
  "success": true,
  "data": [
    {
      "id": 456,
      "title": "Встреча кабриолетов в Москве",
      "description": "Ежегодная встреча владельцев кабриолетов в центре Москвы",
      "event_date": "2024-06-15",
      "event_time": "14:00:00",
      "location": "Парк Горького, главная аллея",
      "city": "Москва",
      "price": 0.00,
      "max_participants": 50,
      "registration_type": "free",
      "status": {
        "id": 2,
        "code": "published",
        "name": "Опубликовано",
        "color": "#28a745"
      },
      "event_type": {
        "id": 1,
        "code": "meetup",
        "name": "Встреча",
        "color": "#007bff"
      },
      "organizer": {
        "id": 123,
        "first_name": "Иван",
        "last_name": "Иванов",
        "username": "ivan_user"
      },
      "photo": {
        "id": 15,
        "url": "http://localhost/app/uploads/event/event_456_photo.jpg",
        "file_name": "event_456_photo.jpg"
      },
      "participants_count": 25,
      "created_at": "2024-01-15T10:30:00Z",
      "updated_at": "2024-01-15T12:00:00Z"
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

### **2. Создание события**

#### **Запрос:**
```http
POST http://localhost/app/api/events
Content-Type: application/json
Authorization: Bearer {token}

{
  "title": "Поездка на озеро",
  "description": "Выезд на озеро с пикником и купанием",
  "event_date": "2024-07-20",
  "event_time": "10:00:00",
  "location": "Озеро Сенеж, Солнечногорский район",
  "city": "Солнечногорск",
  "price": 500.00,
  "max_participants": 30,
  "registration_type": "confirmation",
  "event_type_id": 2
}
```

#### **Ответ:**
```json
{
  "success": true,
  "data": {
    "id": 457,
    "title": "Поездка на озеро",
    "description": "Выезд на озеро с пикником и купанием",
    "event_date": "2024-07-20",
    "event_time": "10:00:00",
    "location": "Озеро Сенеж, Солнечногорский район",
    "city": "Солнечногорск",
    "price": 500.00,
    "max_participants": 30,
    "registration_type": "confirmation",
    "status": {
      "id": 1,
      "code": "draft",
      "name": "Черновик",
      "color": "#6c757d"
    },
    "event_type": {
      "id": 2,
      "code": "trip",
      "name": "Поездка",
      "color": "#28a745"
    },
    "organizer": {
      "id": 123,
      "first_name": "Иван",
      "last_name": "Иванов",
      "username": "ivan_user"
    },
    "participants_count": 0,
    "created_at": "2024-01-15T14:30:00Z",
    "updated_at": "2024-01-15T14:30:00Z"
  }
}
```

### **3. Получение события по ID**

#### **Запрос:**
```http
GET http://localhost/app/api/events/456
```

#### **Ответ:**
```json
{
  "success": true,
  "data": {
    "id": 456,
    "title": "Встреча кабриолетов в Москве",
    "description": "Ежегодная встреча владельцев кабриолетов в центре Москвы",
    "event_date": "2024-06-15",
    "event_time": "14:00:00",
    "location": "Парк Горького, главная аллея",
    "city": "Москва",
    "price": 0.00,
    "max_participants": 50,
    "registration_type": "free",
    "status": {
      "id": 2,
      "code": "published",
      "name": "Опубликовано",
      "color": "#28a745"
    },
    "event_type": {
      "id": 1,
      "code": "meetup",
      "name": "Встреча",
      "color": "#007bff"
    },
    "organizer": {
      "id": 123,
      "first_name": "Иван",
      "last_name": "Иванов",
      "username": "ivan_user"
    },
    "photo": {
      "id": 15,
      "url": "http://localhost/app/uploads/event/event_456_photo.jpg",
      "file_name": "event_456_photo.jpg"
    },
    "participants": [
      {
        "user": {
          "id": 124,
          "first_name": "Мария",
          "last_name": "Петрова",
          "username": "maria_user"
        },
        "confidence": "yes",
        "plus_one": false,
        "joined_at": "2024-01-15T11:00:00Z"
      }
    ],
    "participants_count": 25,
    "created_at": "2024-01-15T10:30:00Z",
    "updated_at": "2024-01-15T12:00:00Z"
  }
}
```

### **4. Присоединение к событию**

#### **Запрос:**
```http
POST http://localhost/app/api/events/456/join
Content-Type: application/json
Authorization: Bearer {token}

{
  "confidence": "yes",
  "plus_one": true
}
```

#### **Ответ:**
```json
{
  "success": true,
  "data": {
    "event_id": 456,
    "user_id": 125,
    "confidence": "yes",
    "plus_one": true,
    "joined_at": "2024-01-15T15:00:00Z"
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
    "title": "Название события обязательно",
    "event_date": "Дата события должна быть в будущем"
  }
}
```

### **Ошибка доступа:**
```json
{
  "success": false,
  "error": "access_denied",
  "message": "Недостаточно прав для создания события"
}
```

### **Событие не найдено:**
```json
{
  "success": false,
  "error": "not_found",
  "message": "Событие с ID 999 не найдено"
}
```

### **Событие переполнено:**
```json
{
  "success": false,
  "error": "event_full",
  "message": "Событие уже заполнено (максимум 50 участников)"
}
```

---

## 🔐 Права доступа

### **Просмотр событий:**
- **guest** — только опубликованные события
- **user** — все события
- **member** — все события + создание своих
- **moderator** — все события + модерация
- **admin** — полный доступ

### **Создание событий:**
- **member** — создание своих событий
- **moderator** — создание любых событий
- **admin** — создание любых событий

### **Редактирование событий:**
- **Создатель события** — редактирование своего события
- **moderator** — редактирование любых событий
- **admin** — редактирование любых событий

---

## 📊 Структура данных

### **Event (Событие):**
```typescript
interface Event {
  id: number;
  title: string;
  description: string;
  event_date: string;        // YYYY-MM-DD
  event_time: string;        // HH:MM:SS
  location: string;
  city: string;
  price: number;
  max_participants: number;
  registration_type: 'free' | 'invitation' | 'confirmation';
  status: Status;
  event_type: EventType;
  organizer: User;
  photo?: Photo;
  participants_count: number;
  created_at: string;
  updated_at: string;
}
```

### **EventType (Тип события):**
```typescript
interface EventType {
  id: number;
  code: string;              // 'meetup', 'trip', 'competition'
  name: string;              // 'Встреча', 'Поездка', 'Соревнование'
  color: string;             // '#007bff'
}
```

### **EventParticipant (Участник события):**
```typescript
interface EventParticipant {
  event_id: number;
  user: User;
  confidence: 'yes' | 'maybe' | 'no';
  plus_one: boolean;
  joined_at: string;
}
```

---

## 🔗 Интеграция

### **С Telegram Bot:**
```javascript
// Создание события через бота
const eventData = {
  title: "Встреча кабриолетов",
  description: "Ежегодная встреча",
  event_date: "2024-06-15",
  event_time: "14:00:00",
  location: "Парк Горького",
  city: "Москва",
  price: 0,
  max_participants: 50,
  registration_type: "free",
  event_type_id: 1
};

const response = await fetch('http://localhost/app/api/events', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${telegramToken}`
  },
  body: JSON.stringify(eventData)
});
```

### **С Frontend:**
```javascript
// Получение событий для календаря
const events = await fetch('http://localhost/app/api/events?status=published&event_date=2024-06', {
  headers: {
    'Authorization': `Bearer ${userToken}`
  }
});

// Присоединение к событию
const joinEvent = await fetch('http://localhost/app/api/events/456/join', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${userToken}`
  },
  body: JSON.stringify({
    confidence: 'yes',
    plus_one: false
  })
});
```

### **Загрузка фото события:**
```javascript
// Загрузка фото события
const formData = new FormData();
formData.append('photo', file);
formData.append('entity_type', 'event');
formData.append('entity_id', '456');

const response = await fetch('http://localhost/app/api/photos', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${userToken}`
  },
  body: formData
});

const result = await response.json();
console.log(result.data.url); // http://localhost/app/uploads/event/event_456_photo.jpg
```

---

## 📈 Мониторинг

### **Метрики для отслеживания:**
- Количество созданных событий
- Количество участников событий
- Популярные типы событий
- География событий
- Конверсия регистраций

### **Логирование:**
```php
// В контроллере
Logger::info('Event created', [
    'event_id' => $eventId,
    'user_id' => $userId,
    'event_type' => $eventType
]);
```

---

## 🔗 Связанные документы

- [API Overview](../OVERVIEW.md) — общие принципы API
- [Users API](USERS.md) — управление пользователями
- [Database Schema](../../DATABASE/SCHEMA.md) — структура БД
- [Authentication](../../AUTHENTICATION/OVERVIEW.md) — авторизация

---

> **Примечание:** Все даты передаются в формате ISO 8601 (YYYY-MM-DD), время в формате HH:MM:SS. Временная зона — UTC. 
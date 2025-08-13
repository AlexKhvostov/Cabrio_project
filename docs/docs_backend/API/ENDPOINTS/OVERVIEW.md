# 🛣️ API Endpoints - Обзор

> Полный список всех API эндпоинтов CabrioRide

## 📋 Назначение

API эндпоинты обеспечивают взаимодействие между клиентами (Telegram WebApp, Bot) и backend системой. Все эндпоинты используют единую систему авторизации и стандартизированные ответы.

## 🌐 Структура проекта

### URL адресация
```
http://localhost/app/
├── frontend/          # Vue.js приложение
├── backend/           # PHP backend
├── bot/              # Telegram Bot
├── uploads/          # Загруженные файлы
│   ├── car/         # Фото автомобилей
│   ├── user/        # Аватары пользователей
│   └── event/       # Фото событий
└── .htaccess        # URL перезапись
```

### API эндпоинты
- **Короткий URL:** `http://localhost/app/api/cars`
- **Длинный URL:** `http://localhost/app/backend/routes/api.php`

### Файлы
- **Фото автомобиля:** `http://localhost/app/uploads/car/car_505_377.jpg`
- **Аватар пользователя:** `http://localhost/app/uploads/user/user_123_avatar.jpg`
- **Фото события:** `http://localhost/app/uploads/event/event_456_photo.jpg`

## 🏗️ Архитектура

### Принципы API
- **RESTful** — для CRUD операций с сущностями
- **RPC** — для сложных бизнес-операций
- **Единая авторизация** — через AuthMiddleware
- **Стандартизированные ответы** — через ResponseHelper
- **Валидация данных** — через ValidationHelper

### Категории эндпоинтов

#### 🔐 Публичные эндпоинты
- `GET /api/health` — проверка состояния API
- `GET /api/status` — информация о системе

#### 👥 Пользователи
- `GET /api/users` — управление пользователями
- `POST /api/users` — создание пользователей
- `GET /api/users/profile` — профиль текущего пользователя
- `POST /api/users/check-by-telegram` — проверка пользователя по Telegram ID
- `POST /api/users/find-by-telegram` — поиск пользователя по Telegram ID

#### 🚗 Автомобили
- `GET /api/cars` — управление автомобилями
- `POST /api/cars` — создание автомобилей
- `GET /api/cars/{id}` — получение автомобиля по ID

#### 📅 События
- `GET /api/events` — управление событиями
- `POST /api/events` — создание событий

#### 🏪 Гид-объекты
- `GET /api/guide-objects` — управление гид-объектами
- `POST /api/guide-objects` — создание гид-объектов

#### 📇 Визитки
- `GET /api/business-cards` — управление визитками
- `POST /api/business-cards` — создание визиток

#### 📸 Фото
- `GET /api/photos` — управление фотографиями
- `POST /api/photos` — загрузка фотографий

#### ⭐ Отзывы
- `GET /api/reviews` — управление отзывами
- `POST /api/reviews` — создание отзывов

#### ⚙️ Системные эндпоинты
- `POST /api/system/user-sync` — синхронизация пользователей
- `POST /api/system/user-role` — управление ролями
- `POST /api/system/entity-status` — управление статусами

#### 🔧 Actions (L3 операции)
- `POST /api/actions/check-car-in-club` — проверка автомобиля в клубе (OCR)
- `POST /api/actions/leave-business-card` — оставить визитку
- `POST /api/actions/add-car-to-garage` — добавить автомобиль в гараж

## 📊 Структура ответов

### Успешный ответ
```json
{
  "success": true,
  "data": {
    "id": 123,
    "model": "BMW Z4",
    "owner": {
      "id": 456,
      "first_name": "Иван"
    },
    "photos": [
      {
        "id": 10,
        "url": "http://localhost/app/uploads/car/car_123_photo.jpg"
      }
    ]
  },
  "error": null
}
```

### Ответ с ошибкой
```json
{
  "success": false,
  "data": null,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Некорректные данные",
    "details": {
      "field": "email",
      "message": "Неверный формат email"
    }
  }
}
```

### Ответ с пагинацией
```json
{
  "success": true,
  "data": [
    {"id": 1, "model": "BMW Z4"},
    {"id": 2, "model": "Mercedes SLK"}
  ],
  "pagination": {
    "page": 1,
    "per_page": 20,
    "total": 150,
    "pages": 8
  },
  "error": null
}
```

## 🔐 Авторизация

### Типы авторизации
1. **Telegram WebApp** — через initData
2. **Telegram Bot** — через telegram_id в теле запроса
3. **SYSTEM_TOKEN** — для системных операций
4. **DEV-режим** — для разработки

### Заголовки авторизации
```http
# Для Telegram WebApp
X-Telegram-Init-Data: query_id=...&user=...&auth_date=...&hash=...

# Для Telegram Bot
Content-Type: application/json
{
  "telegram_id": 123456789,
  "first_name": "Иван",
  "hash": "abc123..."
}

# Для системных запросов
Authorization: Bearer SYSTEM_TOKEN
```

## 📝 Форматы запросов

### GET запросы
```http
GET http://localhost/app/api/cars?page=1&per_page=20&status=active
Authorization: Bearer <token>
```

### POST запросы
```http
POST http://localhost/app/api/cars
Content-Type: application/json
Authorization: Bearer <token>

{
  "model": "BMW Z4",
  "color": "red",
  "year": 2020,
  "owner_user_id": 123
}
```

### Multipart запросы (для файлов)
```http
POST http://localhost/app/api/photos
Content-Type: multipart/form-data
Authorization: Bearer <token>

photo: [binary file]
entity_type: "car"
entity_id: 123
```

## 🚨 Коды ошибок

### Стандартные коды
- `AUTH_ERROR` — ошибка авторизации
- `VALIDATION_ERROR` — ошибка валидации
- `NOT_FOUND` — ресурс не найден
- `ACCESS_DENIED` — недостаточно прав
- `INTERNAL_ERROR` — внутренняя ошибка сервера

### Бизнес-коды
- `CAR_ALREADY_EXISTS` — автомобиль уже существует
- `USER_NOT_MEMBER` — пользователь не является участником
- `EVENT_FULL` — событие полностью заполнено
- `INVALID_STATUS_TRANSITION` — недопустимый переход статуса

## 📊 Метрики и мониторинг

### Логирование запросов
```php
Logger::info('API request', [
    'endpoint' => '/api/cars',
    'method' => 'POST',
    'user_id' => AppContext::getCurrentUserId(),
    'execution_time' => 0.05
]);
```

### Метрики
- Количество запросов по эндпоинтам
- Время выполнения запросов
- Количество ошибок по типам
- Популярные эндпоинты

## 🔧 Конфигурация

### Переменные окружения
```env
# API настройки
API_VERSION=1.0.0
API_RATE_LIMIT=100
API_TIMEOUT=30

# Авторизация
SYSTEM_TOKEN=your_system_token
DEV_AUTH=true
DEV_USER_ID=123
DEV_ROLE=admin

# Пути к файлам
UPLOADS_PATH=uploads/
CAR_PHOTOS_PATH=uploads/car/
USER_PHOTOS_PATH=uploads/user/
EVENT_PHOTOS_PATH=uploads/event/
```

### Настройки по умолчанию
```php
// Лимиты пагинации
const DEFAULT_PER_PAGE = 20;
const MAX_PER_PAGE = 100;

// Таймауты
const API_TIMEOUT = 30;
const OCR_TIMEOUT = 60;

// Пути к файлам
const UPLOADS_BASE_URL = 'http://localhost/app/uploads/';
const CAR_PHOTOS_URL = UPLOADS_BASE_URL . 'car/';
const USER_PHOTOS_URL = UPLOADS_BASE_URL . 'user/';
const EVENT_PHOTOS_URL = UPLOADS_BASE_URL . 'event/';
```

## 📈 Версионирование

### Текущая версия
- **Версия API:** 1.0.0
- **Статус:** Стабильная
- **Обратная совместимость:** Да

### Планы развития
- **v1.1** — добавление новых эндпоинтов
- **v2.0** — рефакторинг архитектуры
- **v3.0** — GraphQL поддержка

## 🔗 Интеграция

### С клиентами
- **Telegram WebApp** — основной клиент
- **Telegram Bot** — административные функции
- **Мобильное приложение** — планируется

### С внешними сервисами
- **OCR сервис** — для распознавания номеров
- **Telegram API** — для получения данных пользователей
- **Файловое хранилище** — для фотографий

## 📚 Документация по эндпоинтам

### Основные сущности
- **[Users](USERS.md)** — управление пользователями
- **[Cars](CARS.md)** — управление автомобилями
- **[Events](EVENTS.md)** — управление событиями
- **[Guide Objects](GUIDE_OBJECTS.md)** — управление гид-объектами
- **[Business Cards](BUSINESS_CARDS.md)** — управление визитками
- **[Photos](PHOTOS.md)** — управление фотографиями
- **[Reviews](REVIEWS.md)** — управление отзывами

### Системные эндпоинты
- **[System](SYSTEM.md)** — системные операции
- **[Actions](ACTIONS.md)** — сложные бизнес-операции

### Публичные эндпоинты
- **[Health](HEALTH.md)** — проверка состояния системы

## 📝 Примеры использования

### Получение списка автомобилей
```javascript
// Telegram WebApp
const response = await fetch('http://localhost/app/api/cars?status=active', {
  headers: {
    'Authorization': `Bearer ${token}`
  }
});

const data = await response.json();
console.log(data.data); // Список автомобилей
```

### Загрузка фотографии
```javascript
const formData = new FormData();
formData.append('photo', file);
formData.append('entity_type', 'car');
formData.append('entity_id', '123');

const response = await fetch('http://localhost/app/api/photos', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`
  },
  body: formData
});

const result = await response.json();
console.log(result.data.url); // http://localhost/app/uploads/car/car_123_photo.jpg
```

### Проверка автомобиля в клубе
```javascript
const checkData = {
  photo: 'base64_encoded_image',
  location: 'Москва, ул. Тверская',
  notes: 'Встретил на парковке'
};

const response = await fetch('http://localhost/app/api/actions/check-car-in-club', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${token}`
  },
  body: JSON.stringify(checkData)
});

const result = await response.json();
if (result.data.found) {
  console.log('Автомобиль найден:', result.data.car);
}
```

---

**📚 См. также:** [Архитектура API](../OVERVIEW.md), [Авторизация](../../AUTHENTICATION/OVERVIEW.md) 
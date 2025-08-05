# Business Cards API - Визитки 📇

> **Назначение:** API для управления визитками/приглашениями к автомобилям

---

## 🎯 Назначение

API Business Cards предоставляет функционал для работы с визитками:
- Создание визиток для замеченных автомобилей
- Просмотр визиток пользователя
- Управление статусами визиток
- Связь визиток с автомобилями

---

## 🏗️ Архитектура

### **Базовые URL:**
- **Короткий:** `http://localhost/app/api/business-cards`
- **Длинный:** `http://localhost/app/backend/routes/api.php`

### **JavaScript примеры:**
```javascript
// Получение списка визиток
const response = await fetch('http://localhost/app/api/business-cards?user_id=123');

// Получение визитки по ID
const card = await fetch('http://localhost/app/api/business-cards/456');

// Создание визитки
const createCard = await fetch('http://localhost/app/api/business-cards', {
  method: 'POST',
  body: JSON.stringify(cardData)
});
```

---

## 🔐 Авторизация

### **Требуемые роли:**
- **Просмотр:** `guest`, `user`, `member`, `moderator`, `admin`
- **Создание:** `member`, `moderator`, `admin`
- **Редактирование:** `moderator`, `admin` (или создатель визитки)
- **Удаление:** `moderator`, `admin`

### **Проверка доступа:**
```php
// В контроллере
$this->requireAccess('business_cards_create'); // Создание визитки
$this->requireAccess('business_cards_view');   // Просмотр визиток
```

---

## 📋 Эндпоинты

### **Основные операции:**
- `GET http://localhost/app/api/business-cards` — получение списка визиток
- `POST http://localhost/app/api/business-cards` — создание новой визитки
- `GET http://localhost/app/api/business-cards/{id}` — получение визитки по ID
- `PUT http://localhost/app/api/business-cards/{id}` — обновление визитки
- `DELETE http://localhost/app/api/business-cards/{id}` — удаление визитки

### **Специальные операции:**
- `POST http://localhost/app/api/actions/leave-business-card` — оставить визитку (через Actions)

---

## 📝 Примеры запросов

### **1. Получение списка визиток**

#### **Запрос:**
```http
GET http://localhost/app/api/business-cards?page=1&per_page=20&user_id=123&car_id=456
```

#### **Ответ:**
```json
{
  "success": true,
  "data": [
    {
      "id": 789,
      "location": "Москва, ул. Тверская, 15",
      "notes": "Красивый BMW Z4, встретил на парковке",
      "car": {
        "id": 456,
        "model": "BMW Z4",
        "color": "red",
        "year": 2020,
        "plate_number": "A123BC",
        "status": {
          "id": 2,
          "code": "business_card",
          "name": "Визитка",
          "color": "#ffc107"
        },
        "brand": {
          "id": 2,
          "name": "BMW",
          "logo_url": "http://localhost/app/uploads/brand/bmw_logo.png"
        }
      },
      "inviter": {
        "id": 123,
        "first_name": "Иван",
        "last_name": "Иванов",
        "username": "ivan_user"
      },
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

### **2. Создание визитки**

#### **Запрос:**
```http
POST http://localhost/app/api/business-cards
Content-Type: application/json
Authorization: Bearer {token}

{
  "car_id": 456,
  "location": "Москва, ул. Арбат, 25",
  "notes": "Встретил на светофоре, красивый кабриолет"
}
```

#### **Ответ:**
```json
{
  "success": true,
  "data": {
    "id": 790,
    "location": "Москва, ул. Арбат, 25",
    "notes": "Встретил на светофоре, красивый кабриолет",
    "car": {
      "id": 456,
      "model": "BMW Z4",
      "color": "red",
      "year": 2020,
      "plate_number": "A123BC",
      "status": {
        "id": 2,
        "code": "business_card",
        "name": "Визитка",
        "color": "#ffc107"
      },
      "brand": {
        "id": 2,
        "name": "BMW",
        "logo_url": "http://localhost/app/uploads/brand/bmw_logo.png"
      }
    },
    "inviter": {
      "id": 123,
      "first_name": "Иван",
      "last_name": "Иванов",
      "username": "ivan_user"
    },
    "created_at": "2024-01-15T14:30:00Z",
    "updated_at": "2024-01-15T14:30:00Z"
  }
}
```

### **3. Получение визитки по ID**

#### **Запрос:**
```http
GET http://localhost/app/api/business-cards/789
```

#### **Ответ:**
```json
{
  "success": true,
  "data": {
    "id": 789,
    "location": "Москва, ул. Тверская, 15",
    "notes": "Красивый BMW Z4, встретил на парковке",
    "car": {
      "id": 456,
      "model": "BMW Z4",
      "color": "red",
      "year": 2020,
      "plate_number": "A123BC",
      "show_reg_number": true,
      "vin": "WBAJL5C56BE123456",
      "description": "Спортивный кабриолет",
      "status": {
        "id": 2,
        "code": "business_card",
        "name": "Визитка",
        "color": "#ffc107"
      },
      "brand": {
        "id": 2,
        "name": "BMW",
        "logo_url": "http://localhost/app/uploads/brand/bmw_logo.png"
      },
      "owner": {
        "id": 124,
        "first_name": "Мария",
        "last_name": "Петрова",
        "username": "maria_user"
      },
      "photos": [
        {
          "id": 25,
          "url": "http://localhost/app/uploads/car/car_456_photo.jpg",
          "file_name": "car_456_photo.jpg"
        }
      ]
    },
    "inviter": {
      "id": 123,
      "first_name": "Иван",
      "last_name": "Иванов",
      "username": "ivan_user"
    },
    "created_at": "2024-01-15T10:30:00Z",
    "updated_at": "2024-01-15T12:00:00Z"
  }
}
```

### **4. Оставить визитку через Actions**

#### **Запрос:**
```http
POST http://localhost/app/api/actions/leave-business-card
Content-Type: application/json
Authorization: Bearer {token}

{
  "car_id": 456,
  "location": "Москва, ул. Арбат, 25",
  "notes": "Встретил на светофоре, красивый кабриолет"
}
```

#### **Ответ:**
```json
{
  "success": true,
  "data": {
    "business_card_id": 790,
    "car_id": 456,
    "location": "Москва, ул. Арбат, 25",
    "notes": "Встретил на светофоре, красивый кабриолет",
    "inviter_id": 123,
    "created_at": "2024-01-15T14:30:00Z"
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
    "car_id": "ID автомобиля обязателен",
    "location": "Местоположение обязательно"
  }
}
```

### **Ошибка доступа:**
```json
{
  "success": false,
  "error": "access_denied",
  "message": "Недостаточно прав для создания визитки"
}
```

### **Визитка не найдена:**
```json
{
  "success": false,
  "error": "not_found",
  "message": "Визитка с ID 999 не найдена"
}
```

### **Автомобиль не найден:**
```json
{
  "success": false,
  "error": "car_not_found",
  "message": "Автомобиль с ID 999 не найден"
}
```

---

## 🔐 Права доступа

### **Просмотр визиток:**
- **guest** — только свои визитки
- **user** — свои визитки
- **member** — свои визитки + визитки к своим автомобилям
- **moderator** — все визитки
- **admin** — все визитки

### **Создание визиток:**
- **member** — создание визиток к любым автомобилям
- **moderator** — создание любых визиток
- **admin** — создание любых визиток

### **Редактирование визиток:**
- **Создатель визитки** — редактирование своей визитки
- **moderator** — редактирование любых визиток
- **admin** — редактирование любых визиток

---

## 📊 Структура данных

### **BusinessCard (Визитка):**
```typescript
interface BusinessCard {
  id: number;
  car_id: number;
  location: string;
  notes: string;
  car: Car;
  inviter: User;
  created_at: string;
  updated_at: string;
}
```

### **Car (Автомобиль в визитке):**
```typescript
interface Car {
  id: number;
  model: string;
  color: string;
  year: number;
  plate_number: string;
  show_reg_number: boolean;
  vin?: string;
  description?: string;
  status: Status;
  brand: CarBrand;
  owner?: User;
  photos?: Photo[];
}
```

### **CarBrand (Марка автомобиля):**
```typescript
interface CarBrand {
  id: number;
  name: string;
  logo_url?: string;
}
```

---

## 🔗 Интеграция

### **С Telegram Bot:**
```javascript
// Создание визитки через бота
const cardData = {
  car_id: 456,
  location: "Москва, ул. Арбат, 25",
  notes: "Встретил на светофоре, красивый кабриолет"
};

const response = await fetch('http://localhost/app/api/actions/leave-business-card', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${telegramToken}`
  },
  body: JSON.stringify(cardData)
});
```

### **С Frontend:**
```javascript
// Получение визиток пользователя
const cards = await fetch('http://localhost/app/api/business-cards?user_id=123', {
  headers: {
    'Authorization': `Bearer ${userToken}`
  }
});

// Создание визитки
const card = await fetch('http://localhost/app/api/business-cards', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${userToken}`
  },
  body: JSON.stringify({
    car_id: 456,
    location: "Москва, ул. Арбат, 25",
    notes: "Встретил на светофоре"
  })
});
```

### **С Actions API:**
```javascript
// Оставить визитку через Actions
const response = await fetch('http://localhost/app/api/actions/leave-business-card', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${userToken}`
  },
  body: JSON.stringify({
    car_id: 456,
    location: "Москва, ул. Арбат, 25",
    notes: "Встретил на светофоре"
  })
});

const result = await response.json();
console.log(result.data.business_card_id); // 790
```

---

## 📈 Мониторинг

### **Метрики для отслеживания:**
- Количество созданных визиток
- Количество визиток по автомобилям
- География визиток
- Конверсия визиток в активные автомобили
- Популярные места для визиток

### **Логирование:**
```php
// В контроллере
Logger::info('Business card created', [
    'card_id' => $cardId,
    'user_id' => $userId,
    'car_id' => $carId,
    'location' => $location
]);
```

---

## 🔗 Связанные документы

- [API Overview](../OVERVIEW.md) — общие принципы API
- [Cars API](CARS.md) — управление автомобилями
- [Actions API](ACTIONS.md) — специальные действия
- [Database Schema](../../DATABASE/SCHEMA.md) — структура БД
- [Authentication](../../AUTHENTICATION/OVERVIEW.md) — авторизация

---

> **Примечание:** Визитки автоматически связываются с автомобилями и могут быть созданы как через основной API, так и через Actions API для более сложной логики. 
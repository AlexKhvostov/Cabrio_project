# Actions API - Специальные действия 🎯

> **Назначение:** API для сложных операций, требующих интеграции с внешними сервисами

---

## 🎯 Назначение

API Actions предоставляет функционал для сложных операций:
- OCR распознавание номеров автомобилей
- Проверка автомобилей в клубе
- Оставление визиток
- Добавление автомобилей в гараж
- Интеграция с внешними сервисами

---

## 🏗️ Архитектура

### **Базовые URL:**
- **Короткий:** `http://localhost/app/api/actions`
- **Длинный:** `http://localhost/app/backend/routes/api.php`

### **JavaScript примеры:**
```javascript
// Проверка автомобиля в клубе (OCR)
const response = await fetch('http://localhost/app/api/actions/check-car-in-club', {
  method: 'POST',
  body: formData
});

// Оставить визитку
const businessCard = await fetch('http://localhost/app/api/actions/leave-business-card', {
  method: 'POST',
  body: JSON.stringify(cardData)
});
```

---

## 🔐 Авторизация

### **Требуемые роли:**
- **OCR проверка:** `member`, `moderator`, `admin`
- **Визитки:** `member`, `moderator`, `admin`
- **Гараж:** `member`, `moderator`, `admin`

### **Проверка доступа:**
```php
// В контроллере
$this->requireAccess('actions_check_car'); // Проверка автомобиля
$this->requireAccess('actions_business_card'); // Оставление визитки
```

---

## 📋 Эндпоинты

### **Основные операции:**
- `POST http://localhost/app/api/actions/check-car-in-club` — проверка автомобиля в клубе (OCR)
- `POST http://localhost/app/api/actions/leave-business-card` — оставить визитку
- `POST http://localhost/app/api/actions/add-car-to-garage` — добавить автомобиль в гараж

---

## 📝 Примеры запросов

### **1. Проверка автомобиля в клубе (OCR)**

#### **Запрос:**
```http
POST http://localhost/app/api/actions/check-car-in-club
Content-Type: multipart/form-data
Authorization: Bearer {token}

FormData:
- photo: [файл изображения номера]
- location: "Москва, ул. Тверская"
- notes: "Встретил на парковке"
```

#### **Ответ:**
```json
{
  "success": true,
  "data": {
    "action": "check_car_in_club",
    "ocr_result": {
      "plate_number": "A123BC",
      "confidence": 0.95,
      "processing_time": "1.2s"
    },
    "car_search": {
      "found": true,
      "car_id": 456,
      "car": {
        "id": 456,
        "model": "BMW Z4",
        "color": "red",
        "year": 2020,
        "plate_number": "A123BC",
        "status": {
          "id": 7,
          "code": "active",
          "name": "Активен"
        },
        "owner": {
          "id": 124,
          "first_name": "Мария",
          "last_name": "Петрова",
          "username": "maria_user"
        },
        "brand": {
          "id": 2,
          "name": "BMW",
          "logo_url": "http://localhost/app/uploads/brand/bmw_logo.png"
        }
      }
    },
    "business_card_created": {
      "id": 789,
      "location": "Москва, ул. Тверская",
      "notes": "Встретил на парковке",
      "created_at": "2024-01-15T18:30:00Z"
    },
    "processed_at": "2024-01-15T18:30:00Z"
  }
}
```

### **2. Автомобиль не найден в клубе**

#### **Запрос:**
```http
POST http://localhost/app/api/actions/check-car-in-club
Content-Type: multipart/form-data
Authorization: Bearer {token}

FormData:
- photo: [файл изображения номера]
- location: "Москва, ул. Арбат"
- notes: "Новый автомобиль"
```

#### **Ответ:**
```json
{
  "success": true,
  "data": {
    "action": "check_car_in_club",
    "ocr_result": {
      "plate_number": "B456DE",
      "confidence": 0.92,
      "processing_time": "1.1s"
    },
    "car_search": {
      "found": false,
      "message": "Автомобиль не найден в клубе"
    },
    "new_car_created": {
      "id": 457,
      "model": "Неизвестно",
      "plate_number": "B456DE",
      "status": {
        "id": 1,
        "code": "noticed",
        "name": "Замечен"
      }
    },
    "business_card_created": {
      "id": 790,
      "location": "Москва, ул. Арбат",
      "notes": "Новый автомобиль",
      "created_at": "2024-01-15T18:35:00Z"
    },
    "processed_at": "2024-01-15T18:35:00Z"
  }
}
```

### **3. Оставить визитку**

#### **Запрос:**
```http
POST http://localhost/app/api/actions/leave-business-card
Content-Type: application/json
Authorization: Bearer {token}

{
  "car_id": 456,
  "location": "Москва, ул. Тверская, 15",
  "notes": "Красивый BMW Z4, встретил на парковке"
}
```

#### **Ответ:**
```json
{
  "success": true,
  "data": {
    "action": "leave_business_card",
    "business_card_id": 791,
    "car_id": 456,
    "location": "Москва, ул. Тверская, 15",
    "notes": "Красивый BMW Z4, встретил на парковке",
    "inviter_id": 123,
    "car": {
      "id": 456,
      "model": "BMW Z4",
      "color": "red",
      "year": 2020,
      "plate_number": "A123BC",
      "status": {
        "id": 2,
        "code": "business_card",
        "name": "Визитка"
      }
    },
    "created_at": "2024-01-15T18:40:00Z"
  }
}
```

### **4. Добавить автомобиль в гараж**

#### **Запрос:**
```http
POST http://localhost/app/api/actions/add-car-to-garage
Content-Type: application/json
Authorization: Bearer {token}

{
  "car_id": 456,
  "role": "owner"
}
```

#### **Ответ:**
```json
{
  "success": true,
  "data": {
    "action": "add_car_to_garage",
    "user_id": 123,
    "car_id": 456,
    "role": "owner",
    "car": {
      "id": 456,
      "model": "BMW Z4",
      "color": "red",
      "year": 2020,
      "plate_number": "A123BC",
      "status": {
        "id": 7,
        "code": "active",
        "name": "Активен"
      }
    },
    "added_at": "2024-01-15T18:45:00Z"
  }
}
```

---

## 🚨 Обработка ошибок

### **Ошибка OCR:**
```json
{
  "success": false,
  "error": "ocr_failed",
  "message": "Не удалось распознать номер автомобиля",
  "data": {
    "ocr_result": {
      "plate_number": null,
      "confidence": 0.0,
      "error": "Номер не найден на изображении"
    }
  }
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

### **Ошибка доступа:**
```json
{
  "success": false,
  "error": "access_denied",
  "message": "Недостаточно прав для выполнения действия"
}
```

### **Некорректный файл:**
```json
{
  "success": false,
  "error": "invalid_file",
  "message": "Неподдерживаемый формат файла. Разрешены: JPG, PNG"
}
```

### **OCR сервис недоступен:**
```json
{
  "success": false,
  "error": "ocr_service_unavailable",
  "message": "Сервис распознавания временно недоступен"
}
```

---

## 🔐 Права доступа

### **OCR проверка автомобилей:**
- **member** — проверка автомобилей в клубе
- **moderator** — полный доступ к OCR
- **admin** — полный доступ к OCR

### **Оставление визиток:**
- **member** — оставление визиток к любым автомобилям
- **moderator** — оставление любых визиток
- **admin** — оставление любых визиток

### **Управление гаражом:**
- **member** — добавление автомобилей в свой гараж
- **moderator** — управление гаражом пользователей
- **admin** — полное управление гаражом

---

## 📊 Структура данных

### **OCRResult (Результат OCR):**
```typescript
interface OCRResult {
  plate_number: string;
  confidence: number;        // 0.0 - 1.0
  processing_time: string;
  error?: string;
}
```

### **CarSearch (Поиск автомобиля):**
```typescript
interface CarSearch {
  found: boolean;
  car_id?: number;
  car?: Car;
  message?: string;
}
```

### **BusinessCardAction (Действие с визиткой):**
```typescript
interface BusinessCardAction {
  car_id: number;
  location: string;
  notes: string;
}
```

### **GarageAction (Действие с гаражом):**
```typescript
interface GarageAction {
  car_id: number;
  role: 'owner' | 'passenger';
}
```

---

## 🔗 Интеграция

### **С Telegram Bot:**
```javascript
// Проверка автомобиля через бота
const formData = new FormData();
formData.append('photo', file);
formData.append('location', 'Москва, ул. Тверская');
formData.append('notes', 'Встретил на парковке');

const response = await fetch('http://localhost/app/api/actions/check-car-in-club', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${telegramToken}`
  },
  body: formData
});

const result = await response.json();
if (result.data.car_search.found) {
  await bot.sendMessage(chatId, `Найден автомобиль: ${result.data.car_search.car.model}`);
} else {
  await bot.sendMessage(chatId, 'Автомобиль не найден в клубе');
}
```

### **С Frontend:**
```javascript
// Проверка автомобиля через веб-интерфейс
const formData = new FormData();
formData.append('photo', file);
formData.append('location', 'Москва, ул. Тверская');

const response = await fetch('http://localhost/app/api/actions/check-car-in-club', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${userToken}`
  },
  body: formData
});

const result = await response.json();
console.log('OCR результат:', result.data.ocr_result);
console.log('Поиск автомобиля:', result.data.car_search);
```

### **Оставление визитки:**
```javascript
// Оставить визитку
const cardData = {
  car_id: 456,
  location: "Москва, ул. Тверская, 15",
  notes: "Красивый BMW Z4"
};

const response = await fetch('http://localhost/app/api/actions/leave-business-card', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${userToken}`
  },
  body: JSON.stringify(cardData)
});

const result = await response.json();
console.log('Визитка создана:', result.data.business_card_id);
```

### **Добавление в гараж:**
```javascript
// Добавить автомобиль в гараж
const garageData = {
  car_id: 456,
  role: "owner"
};

const response = await fetch('http://localhost/app/api/actions/add-car-to-garage', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${userToken}`
  },
  body: JSON.stringify(garageData)
});

const result = await response.json();
console.log('Автомобиль добавлен в гараж:', result.data.car.model);
```

---

## 📈 Мониторинг

### **Метрики для отслеживания:**
- Количество OCR запросов
- Точность распознавания номеров
- Время обработки OCR
- Количество созданных визиток
- Популярность действий

### **Логирование:**
```php
// В контроллере
Logger::info('OCR check performed', [
    'plate_number' => $plateNumber,
    'confidence' => $confidence,
    'processing_time' => $processingTime,
    'user_id' => $userId
]);

Logger::info('Business card created', [
    'car_id' => $carId,
    'user_id' => $userId,
    'location' => $location
]);
```

---

## 🔧 Конфигурация

### **OCR сервис:**
- **API Endpoint:** Настраивается в .env
- **Таймаут:** 30 секунд
- **Поддерживаемые форматы:** JPG, PNG
- **Максимальный размер файла:** 10 MB

### **Ограничения:**
- **OCR запросы:** 10 в минуту на пользователя
- **Визитки:** 50 в день на пользователя
- **Гараж:** 10 автомобилей на пользователя

---

## 🔗 Связанные документы

- [API Overview](../OVERVIEW.md) — общие принципы API
- [Cars API](CARS.md) — управление автомобилями
- [Business Cards API](BUSINESS_CARDS.md) — управление визитками
- [Photos API](PHOTOS.md) — управление фотографиями
- [Database Schema](../../DATABASE/SCHEMA.md) — структура БД
- [Authentication](../../AUTHENTICATION/OVERVIEW.md) — авторизация

---

> **Примечание:** Actions API предназначен для сложных операций с интеграцией внешних сервисов. OCR распознавание может занять несколько секунд в зависимости от качества изображения. 
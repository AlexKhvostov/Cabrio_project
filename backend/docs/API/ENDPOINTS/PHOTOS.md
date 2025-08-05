# Photos API - Фотографии 📸

> **Назначение:** API для управления фотографиями всех сущностей системы

---

## 🎯 Назначение

API Photos предоставляет универсальный функционал для работы с фотографиями:
- Загрузка фотографий для любых сущностей
- Управление типами фотографий (аватар, обложка, галерея)
- Автоматическое создание превью
- Безопасное хранение файлов

---

## 🏗️ Архитектура

### **Базовые URL:**
- **Короткий:** `http://localhost/app/api/photos`
- **Длинный:** `http://localhost/app/backend/routes/api.php`

### **Пути к файлам:**
- **Автомобили:** `http://localhost/app/uploads/car/car_123_photo.jpg`
- **Пользователи:** `http://localhost/app/uploads/user/user_123_avatar.jpg`
- **События:** `http://localhost/app/uploads/event/event_456_photo.jpg`
- **Гид-объекты:** `http://localhost/app/uploads/guide_object/guide_789_photo.jpg`
- **Отзывы:** `http://localhost/app/uploads/review/review_101_photo.jpg`

### **JavaScript примеры:**
```javascript
// Загрузка фото автомобиля
const formData = new FormData();
formData.append('photo', file);
formData.append('entity_type', 'car');
formData.append('entity_id', '123');

const response = await fetch('http://localhost/app/api/photos', {
  method: 'POST',
  body: formData
});
```

---

## 🔐 Авторизация

### **Требуемые роли:**
- **Просмотр:** `guest`, `user`, `member`, `moderator`, `admin`
- **Загрузка:** `member`, `moderator`, `admin`
- **Удаление:** `moderator`, `admin` (или владелец фото)

### **Проверка доступа:**
```php
// В контроллере
$this->requireAccess('photos_upload'); // Загрузка фото
$this->requireAccess('photos_view');   // Просмотр фото
```

---

## 📋 Эндпоинты

### **Основные операции:**
- `GET http://localhost/app/api/photos` — получение списка фотографий
- `POST http://localhost/app/api/photos` — загрузка новой фотографии
- `GET http://localhost/app/api/photos/{id}` — получение фото по ID
- `DELETE http://localhost/app/api/photos/{id}` — удаление фотографии

### **Фильтрация:**
- `GET http://localhost/app/api/photos?entity_type=car&entity_id=123` — фото автомобиля
- `GET http://localhost/app/api/photos?entity_type=user&entity_id=456` — фото пользователя
- `GET http://localhost/app/api/photos?photo_type=avatar` — только аватары

---

## 📝 Примеры запросов

### **1. Загрузка фотографии**

#### **Запрос:**
```http
POST http://localhost/app/api/photos
Content-Type: multipart/form-data
Authorization: Bearer {token}

FormData:
- photo: [файл изображения]
- entity_type: "car"
- entity_id: "123"
- photo_type: "gallery"
- description: "Фото автомобиля спереди"
```

#### **Ответ:**
```json
{
  "success": true,
  "data": {
    "id": 25,
    "entity_type": "car",
    "entity_id": 123,
    "file_name": "car_123_photo_20240115_143000.jpg",
    "url": "http://localhost/app/uploads/car/car_123_photo_20240115_143000.jpg",
    "photo_type": "gallery",
    "description": "Фото автомобиля спереди",
    "uploaded_by": {
      "id": 123,
      "first_name": "Иван",
      "last_name": "Иванов",
      "username": "ivan_user"
    },
    "file_size": 2048576,
    "mime_type": "image/jpeg",
    "dimensions": {
      "width": 1920,
      "height": 1080
    },
    "uploaded_at": "2024-01-15T14:30:00Z"
  }
}
```

### **2. Получение списка фотографий**

#### **Запрос:**
```http
GET http://localhost/app/api/photos?entity_type=car&entity_id=123&photo_type=gallery
```

#### **Ответ:**
```json
{
  "success": true,
  "data": [
    {
      "id": 25,
      "entity_type": "car",
      "entity_id": 123,
      "file_name": "car_123_photo_20240115_143000.jpg",
      "url": "http://localhost/app/uploads/car/car_123_photo_20240115_143000.jpg",
      "photo_type": "gallery",
      "description": "Фото автомобиля спереди",
      "uploaded_by": {
        "id": 123,
        "first_name": "Иван",
        "last_name": "Иванов",
        "username": "ivan_user"
      },
      "file_size": 2048576,
      "mime_type": "image/jpeg",
      "dimensions": {
        "width": 1920,
        "height": 1080
      },
      "uploaded_at": "2024-01-15T14:30:00Z"
    },
    {
      "id": 26,
      "entity_type": "car",
      "entity_id": 123,
      "file_name": "car_123_photo_20240115_143100.jpg",
      "url": "http://localhost/app/uploads/car/car_123_photo_20240115_143100.jpg",
      "photo_type": "gallery",
      "description": "Фото автомобиля сбоку",
      "uploaded_by": {
        "id": 123,
        "first_name": "Иван",
        "last_name": "Иванов",
        "username": "ivan_user"
      },
      "file_size": 1876543,
      "mime_type": "image/jpeg",
      "dimensions": {
        "width": 1920,
        "height": 1080
      },
      "uploaded_at": "2024-01-15T14:31:00Z"
    }
  ],
  "pagination": {
    "page": 1,
    "per_page": 20,
    "total": 5,
    "pages": 1
  }
}
```

### **3. Получение фото по ID**

#### **Запрос:**
```http
GET http://localhost/app/api/photos/25
```

#### **Ответ:**
```json
{
  "success": true,
  "data": {
    "id": 25,
    "entity_type": "car",
    "entity_id": 123,
    "file_name": "car_123_photo_20240115_143000.jpg",
    "url": "http://localhost/app/uploads/car/car_123_photo_20240115_143000.jpg",
    "photo_type": "gallery",
    "description": "Фото автомобиля спереди",
    "uploaded_by": {
      "id": 123,
      "first_name": "Иван",
      "last_name": "Иванов",
      "username": "ivan_user"
    },
    "file_size": 2048576,
    "mime_type": "image/jpeg",
    "dimensions": {
      "width": 1920,
      "height": 1080
    },
    "uploaded_at": "2024-01-15T14:30:00Z"
  }
}
```

### **4. Загрузка аватара пользователя**

#### **Запрос:**
```http
POST http://localhost/app/api/photos
Content-Type: multipart/form-data
Authorization: Bearer {token}

FormData:
- photo: [файл изображения]
- entity_type: "user"
- entity_id: "123"
- photo_type: "avatar"
- description: "Аватар пользователя"
```

#### **Ответ:**
```json
{
  "success": true,
  "data": {
    "id": 30,
    "entity_type": "user",
    "entity_id": 123,
    "file_name": "user_123_avatar_20240115_150000.jpg",
    "url": "http://localhost/app/uploads/user/user_123_avatar_20240115_150000.jpg",
    "photo_type": "avatar",
    "description": "Аватар пользователя",
    "uploaded_by": {
      "id": 123,
      "first_name": "Иван",
      "last_name": "Иванов",
      "username": "ivan_user"
    },
    "file_size": 512000,
    "mime_type": "image/jpeg",
    "dimensions": {
      "width": 400,
      "height": 400
    },
    "uploaded_at": "2024-01-15T15:00:00Z"
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
    "photo": "Файл обязателен",
    "entity_type": "Тип сущности обязателен",
    "entity_id": "ID сущности обязателен"
  }
}
```

### **Ошибка доступа:**
```json
{
  "success": false,
  "error": "access_denied",
  "message": "Недостаточно прав для загрузки фотографии"
}
```

### **Фото не найдено:**
```json
{
  "success": false,
  "error": "not_found",
  "message": "Фотография с ID 999 не найдена"
}
```

### **Неподдерживаемый формат:**
```json
{
  "success": false,
  "error": "unsupported_format",
  "message": "Неподдерживаемый формат файла. Разрешены: JPG, PNG, GIF"
}
```

### **Файл слишком большой:**
```json
{
  "success": false,
  "error": "file_too_large",
  "message": "Размер файла превышает максимально допустимый (10 MB)"
}
```

---

## 🔐 Права доступа

### **Просмотр фотографий:**
- **guest** — только публичные фото
- **user** — свои фото + фото своих сущностей
- **member** — свои фото + фото своих сущностей
- **moderator** — все фото
- **admin** — все фото

### **Загрузка фотографий:**
- **member** — загрузка фото к своим сущностям
- **moderator** — загрузка любых фото
- **admin** — загрузка любых фото

### **Удаление фотографий:**
- **Владелец фото** — удаление своих фото
- **moderator** — удаление любых фото
- **admin** — удаление любых фото

---

## 📊 Структура данных

### **Photo (Фотография):**
```typescript
interface Photo {
  id: number;
  entity_type: string;       // 'user', 'car', 'event', 'guide_object', 'review'
  entity_id: number;
  file_name: string;
  url: string;
  photo_type?: string;       // 'avatar', 'cover', 'gallery', 'plate'
  description?: string;
  uploaded_by: User;
  file_size: number;
  mime_type: string;
  dimensions: {
    width: number;
    height: number;
  };
  uploaded_at: string;
}
```

### **Entity Types (Типы сущностей):**
```typescript
type EntityType = 'user' | 'car' | 'event' | 'guide_object' | 'review';
```

### **Photo Types (Типы фотографий):**
```typescript
type PhotoType = 'avatar' | 'cover' | 'gallery' | 'plate' | 'logo';
```

---

## 🔗 Интеграция

### **С Telegram Bot:**
```javascript
// Загрузка фото автомобиля через бота
const formData = new FormData();
formData.append('photo', file);
formData.append('entity_type', 'car');
formData.append('entity_id', '123');
formData.append('photo_type', 'gallery');
formData.append('description', 'Фото автомобиля');

const response = await fetch('http://localhost/app/api/photos', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${telegramToken}`
  },
  body: formData
});

const result = await response.json();
console.log(result.data.url); // http://localhost/app/uploads/car/car_123_photo.jpg
```

### **С Frontend:**
```javascript
// Загрузка аватара пользователя
const formData = new FormData();
formData.append('photo', file);
formData.append('entity_type', 'user');
formData.append('entity_id', '123');
formData.append('photo_type', 'avatar');

const response = await fetch('http://localhost/app/api/photos', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${userToken}`
  },
  body: formData
});

const result = await response.json();
console.log(result.data.url); // http://localhost/app/uploads/user/user_123_avatar.jpg
```

### **Получение фото сущности:**
```javascript
// Получение всех фото автомобиля
const photos = await fetch('http://localhost/app/api/photos?entity_type=car&entity_id=123', {
  headers: {
    'Authorization': `Bearer ${userToken}`
  }
});

// Получение только аватаров пользователей
const avatars = await fetch('http://localhost/app/api/photos?entity_type=user&photo_type=avatar', {
  headers: {
    'Authorization': `Bearer ${userToken}`
  }
});
```

### **Удаление фото:**
```javascript
// Удаление фотографии
const response = await fetch('http://localhost/app/api/photos/25', {
  method: 'DELETE',
  headers: {
    'Authorization': `Bearer ${userToken}`
  }
});
```

---

## 📈 Мониторинг

### **Метрики для отслеживания:**
- Количество загруженных фотографий
- Размер хранилища файлов
- Популярные типы фотографий
- Средний размер файлов
- Количество удалений

### **Логирование:**
```php
// В контроллере
Logger::info('Photo uploaded', [
    'photo_id' => $photoId,
    'user_id' => $userId,
    'entity_type' => $entityType,
    'entity_id' => $entityId,
    'file_size' => $fileSize
]);
```

---

## 🔧 Конфигурация

### **Поддерживаемые форматы:**
- **Изображения:** JPG, JPEG, PNG, GIF
- **Максимальный размер:** 10 MB
- **Минимальные размеры:** 100x100 пикселей
- **Максимальные размеры:** 4096x4096 пикселей

### **Автоматическая обработка:**
- **Сжатие:** Автоматическое сжатие больших файлов
- **Превью:** Создание превью для галерей
- **Водяные знаки:** Добавление водяных знаков (опционально)
- **EXIF:** Очистка метаданных для безопасности

---

## 🔗 Связанные документы

- [API Overview](../OVERVIEW.md) — общие принципы API
- [Cars API](CARS.md) — управление автомобилями
- [Users API](USERS.md) — управление пользователями
- [Database Schema](../../DATABASE/SCHEMA.md) — структура БД
- [Authentication](../../AUTHENTICATION/OVERVIEW.md) — авторизация

---

> **Примечание:** Все фотографии автоматически обрабатываются для оптимизации размера и качества. Главное фото сущности определяется как запись с максимальным ID для данной сущности. 
# 🚗 Cars API

> Эндпоинты для управления автомобилями в CabrioRide

## 📋 Назначение

Cars API обеспечивает управление автомобилями участников клуба: создание, получение, обновление, проверка в клубе через OCR и другие операции.

## 🌐 URL адресация

### Базовые URL
- **Короткий:** `http://localhost/app/api/cars`
- **Длинный:** `http://localhost/app/backend/routes/api.php`

### Файлы автомобилей
- **Фото автомобиля:** `http://localhost/app/uploads/car/car_505_377.jpg`
- **Фото номера:** `http://localhost/app/uploads/car/car_505_plate.jpg`

### Примеры запросов
```javascript
// Получение списка автомобилей
const response = await fetch('http://localhost/app/api/cars?status=active');

// Получение автомобиля по ID
const car = await fetch('http://localhost/app/api/cars/123');

// Создание автомобиля
const createCar = await fetch('http://localhost/app/api/cars', {
  method: 'POST',
  body: JSON.stringify(carData)
});
```

## 🏗️ Архитектура

### Основные эндпоинты
- `GET http://localhost/app/api/cars` — получение списка автомобилей
- `POST http://localhost/app/api/cars` — создание нового автомобиля
- `GET http://localhost/app/api/cars/{id}` — получение автомобиля по ID
- `POST http://localhost/app/api/actions/check-car-in-club` — проверка автомобиля в клубе (OCR)
- `POST http://localhost/app/api/actions/leave-business-card` — оставить визитку
- `POST http://localhost/app/api/actions/add-car-to-garage` — добавить автомобиль в гараж

### Авторизация
- **Минимальная роль:** member (для просмотра)
- **Создание автомобилей:** member+
- **OCR операции:** member+

## 📝 Эндпоинты

### GET /api/cars
Получение списка автомобилей с фильтрацией и пагинацией.

#### Параметры запроса
```http
GET http://localhost/app/api/cars?page=1&per_page=20&status=active&owner_id=123
```

| Параметр | Тип | Описание | По умолчанию |
|----------|-----|----------|--------------|
| `page` | int | Номер страницы | 1 |
| `per_page` | int | Количество на странице | 20 |
| `status` | string | Фильтр по статусу | - |
| `owner_id` | int | Фильтр по владельцу | - |
| `brand_id` | int | Фильтр по бренду | - |
| `model` | string | Поиск по модели | - |

#### Пример ответа
```json
{
  "success": true,
  "data": [
    {
      "id": 123,
      "model": "BMW Z4",
      "color": "red",
      "year": 2020,
      "plate_number": "A123BC",
      "status": {
        "id": 7,
        "code": "active",
        "name": "Активен",
        "color": "#28a745"
      },
      "owner": {
        "id": 456,
        "first_name": "Иван",
        "last_name": "Иванов",
        "username": "ivan_user"
      },
      "brand": {
        "id": 2,
        "name": "BMW",
        "logo_url": "http://localhost/app/uploads/brand/bmw_logo.png"
      },
      "creator": {
        "id": 456,
        "first_name": "Иван",
        "last_name": "Иванов"
      },
      "photos": [
        {
          "id": 10,
          "url": "http://localhost/app/uploads/car/car_123_photo.jpg",
          "file_name": "car_123_photo.jpg"
        }
      ],
      "created_at": "2024-01-15T10:30:00Z",
      "updated_at": "2024-01-15T12:00:00Z"
    }
  ],
  "pagination": {
    "page": 1,
    "per_page": 20,
    "total": 150,
    "pages": 8
  }
}
```

### POST /api/cars
Создание нового автомобиля.

#### Тело запроса
```json
{
  "model": "BMW Z4",
  "color": "red",
  "year": 2020,
  "plate_number": "A123BC",
  "brand_id": 2,
  "description": "Мой первый кабриолет",
  "photos": ["base64_encoded_image"]
}
```

#### Обязательные поля
- `model` — модель автомобиля
- `year` — год выпуска

#### Опциональные поля
- `color` — цвет
- `plate_number` — номерной знак
- `brand_id` — ID бренда
- `description` — описание
- `photos` — массив фотографий в base64

#### Пример ответа
```json
{
  "success": true,
  "data": {
    "id": 124,
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
      "id": 456,
      "first_name": "Иван",
      "last_name": "Иванов"
    },
    "brand": {
      "id": 2,
      "name": "BMW"
    },
    "photos": [
      {
        "id": 11,
        "url": "http://localhost/app/uploads/car/car_124_photo.jpg",
        "file_name": "car_124_photo.jpg"
      }
    ],
    "created_at": "2024-01-15T14:30:00Z"
  }
}
```

### GET /api/cars/{id}
Получение автомобиля по ID.

#### Параметры пути
- `id` — ID автомобиля

#### Пример ответа
```json
{
  "success": true,
  "data": {
    "id": 123,
    "model": "BMW Z4",
    "color": "red",
    "year": 2020,
    "plate_number": "A123BC",
    "status": {
      "id": 7,
      "code": "active",
      "name": "Активен",
      "color": "#28a745"
    },
    "owner": {
      "id": 456,
      "first_name": "Иван",
      "last_name": "Иванов",
      "username": "ivan_user"
    },
    "brand": {
      "id": 2,
      "name": "BMW",
      "logo_url": "http://localhost/app/uploads/brand/bmw_logo.png"
    },
    "creator": {
      "id": 456,
      "first_name": "Иван",
      "last_name": "Иванов"
    },
    "photos": [
      {
        "id": 10,
        "url": "http://localhost/app/uploads/car/car_123_photo.jpg",
        "file_name": "car_123_photo.jpg"
      }
    ],
    "business_cards": [
      {
        "id": 5,
        "location": "Москва, ул. Тверская",
        "notes": "Встретил на парковке",
        "created_at": "2024-01-15T10:30:00Z"
      }
    ],
    "created_at": "2024-01-15T10:30:00Z",
    "updated_at": "2024-01-15T12:00:00Z"
  }
}
```

### POST /api/actions/check-car-in-club
Проверка автомобиля в клубе через OCR распознавание номера.

#### Тело запроса
```json
{
  "photo": "base64_encoded_image",
  "location": "Москва, ул. Тверская",
  "notes": "Встретил на парковке"
}
```

#### Обязательные поля
- `photo` — фотография автомобиля в base64

#### Опциональные поля
- `location` — место встречи
- `notes` — заметки

#### Пример ответа
```json
{
  "success": true,
  "data": {
    "found": true,
    "car": {
      "id": 123,
      "model": "BMW Z4",
      "plate_number": "A123BC",
      "owner": {
        "id": 456,
        "first_name": "Иван",
        "last_name": "Иванов"
      },
      "status": {
        "id": 7,
        "code": "active",
        "name": "Активен"
      }
    },
    "ocr_result": {
      "plate_number": "A123BC",
      "confidence": 0.95
    },
    "business_card": {
      "id": 5,
      "location": "Москва, ул. Тверская",
      "notes": "Встретил на парковке",
      "created_at": "2024-01-15T14:30:00Z"
    }
  }
}
```

### POST /api/actions/leave-business-card
Оставить визитку для автомобиля.

#### Тело запроса
```json
{
  "car_id": 123,
  "location": "Москва, ул. Тверская",
  "notes": "Встретил на парковке"
}
```

#### Обязательные поля
- `car_id` — ID автомобиля
- `location` — место встречи

#### Опциональные поля
- `notes` — заметки

#### Пример ответа
```json
{
  "success": true,
  "data": {
    "business_card": {
      "id": 6,
      "car_id": 123,
      "location": "Москва, ул. Тверская",
      "notes": "Встретил на парковке",
      "inviter": {
        "id": 456,
        "first_name": "Иван",
        "last_name": "Иванов"
      },
      "created_at": "2024-01-15T14:30:00Z"
    }
  }
}
```

### POST /api/actions/add-car-to-garage
Добавить автомобиль в гараж (создать новый автомобиль).

#### Тело запроса
```json
{
  "model": "BMW Z4",
  "color": "red",
  "year": 2020,
  "plate_number": "A123BC",
  "brand_id": 2,
  "description": "Мой первый кабриолет",
  "photos": ["base64_encoded_image"]
}
```

#### Обязательные поля
- `model` — модель автомобиля
- `year` — год выпуска

#### Пример ответа
```json
{
  "success": true,
  "data": {
    "car": {
      "id": 125,
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
        "id": 456,
        "first_name": "Иван",
        "last_name": "Иванов"
      },
      "brand": {
        "id": 2,
        "name": "BMW"
      },
      "photos": [
        {
          "id": 12,
          "url": "http://localhost/app/uploads/car/car_125_photo.jpg",
          "file_name": "car_125_photo.jpg"
        }
      ],
      "created_at": "2024-01-15T14:30:00Z"
    }
  }
}
```

## 🚨 Обработка ошибок

### Коды ошибок
- `ACCESS_DENIED` — недостаточно прав для операции
- `VALIDATION_ERROR` — ошибка валидации данных
- `CAR_NOT_FOUND` — автомобиль не найден
- `CAR_ALREADY_EXISTS` — автомобиль уже существует
- `OCR_FAILED` — ошибка распознавания номера
- `INVALID_PLATE_NUMBER` — некорректный номерной знак

### Примеры ошибок
```json
{
  "success": false,
  "error": {
    "code": "CAR_NOT_FOUND",
    "message": "Автомобиль не найден"
  }
}
```

```json
{
  "success": false,
  "error": {
    "code": "OCR_FAILED",
    "message": "Не удалось распознать номерной знак",
    "details": {
      "confidence": 0.3,
      "suggestions": ["A123BC", "A123BС"]
    }
  }
}
```

## 🔐 Права доступа

### Матрица прав
| Эндпоинт | Роль | Описание |
|-----------|------|----------|
| `GET /api/cars` | member+ | Просмотр списка автомобилей |
| `POST /api/cars` | member+ | Создание автомобилей |
| `GET /api/cars/{id}` | member+ | Просмотр автомобиля |
| `POST /api/actions/check-car-in-club` | member+ | Проверка автомобиля |
| `POST /api/actions/leave-business-card` | member+ | Оставить визитку |
| `POST /api/actions/add-car-to-garage` | member+ | Добавить в гараж |

## 📊 Структура данных

### Автомобиль
```json
{
  "id": 123,
  "model": "BMW Z4",
  "color": "red",
  "year": 2020,
  "plate_number": "A123BC",
  "status": {
    "id": 7,
    "code": "active",
    "name": "Активен",
    "color": "#28a745"
  },
  "owner": {
    "id": 456,
    "first_name": "Иван",
    "last_name": "Иванов",
    "username": "ivan_user"
  },
  "brand": {
    "id": 2,
    "name": "BMW",
    "logo_url": "http://localhost/app/uploads/brand/bmw_logo.png"
  },
  "creator": {
    "id": 456,
    "first_name": "Иван",
    "last_name": "Иванов"
  },
  "photos": [...],
  "business_cards": [...],
  "created_at": "2024-01-15T10:30:00Z",
  "updated_at": "2024-01-15T12:00:00Z"
}
```

### Статусы автомобилей
- `noticed` (1) — Замечен
- `business_card` (2) — Визитка
- `deleted` (3) — Удалён
- `archived` (4) — В архиве
- `blocked` (5) — Заблокирован
- `pending` (6) — На модерации
- `active` (7) — Активен

## 📝 Примеры использования

### Получение списка автомобилей
```javascript
// Telegram WebApp
const response = await fetch('http://localhost/app/api/cars?status=active&owner_id=123', {
  headers: {
    'Authorization': `Bearer ${token}`
  }
});

const data = await response.json();
console.log(data.data); // Список автомобилей
```

### Создание автомобиля
```javascript
const carData = {
  model: 'BMW Z4',
  color: 'red',
  year: 2020,
  plate_number: 'A123BC',
  brand_id: 2,
  description: 'Мой первый кабриолет',
  photos: ['base64_encoded_image']
};

const response = await fetch('http://localhost/app/api/cars', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${token}`
  },
  body: JSON.stringify(carData)
});

const result = await response.json();
console.log(result.data); // Созданный автомобиль
```

### Загрузка фото автомобиля
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
} else {
  console.log('Автомобиль не найден в клубе');
}
```

## 🔄 Интеграция

### С OCR сервисом
```php
// В CheckCarInClubAction
$ocrResult = self::recognizePlateNumber($photo);
if ($ocrResult['success']) {
    $plateNumber = $ocrResult['plate_number'];
    $car = Car::findByPlateNumber($plateNumber);
}
```

### С ExpandHelper
```php
// Развертывание связанных данных
$carData = Car::findByIdWithDetails($id);
$expandedCar = ExpandHelper::expandCarData($carData);
```

### С ResponseHelper
```php
// Стандартизированные ответы
echo ResponseHelper::success($cars, $pagination);
echo ResponseHelper::error('CAR_NOT_FOUND', 'Автомобиль не найден');
```

## 📈 Мониторинг

### Логирование
```php
Logger::info('Car check completed', [
    'user_id' => AppContext::getCurrentUserId(),
    'found' => $result['found'],
    'plate_number' => $ocrResult['plate_number'],
    'confidence' => $ocrResult['confidence']
]);
```

### Метрики
- Количество проверок автомобилей
- Успешность OCR распознавания
- Популярные бренды и модели
- Количество оставленных визиток

---

**📚 См. также:** [Обзор API](../OVERVIEW.md), [Actions](../../ACTIONS/L3_ACTIONS.md), [OCR интеграция](../../INTEGRATIONS/OCR.md) 
# 👥 Users API

> Эндпоинты для управления пользователями в CabrioRide

## 📋 Назначение

Users API обеспечивает управление пользователями системы: создание, получение, обновление профилей, поиск по Telegram ID и другие операции.

## 🌐 URL адресация

### Базовые URL
- **Короткий:** `http://localhost/app/api/users`
- **Длинный:** `http://localhost/app/backend/routes/api.php`

### Примеры запросов
```javascript
// Получение списка пользователей
const response = await fetch('http://localhost/app/api/users?role=member');

// Профиль текущего пользователя
const profile = await fetch('http://localhost/app/api/users/profile');

// Создание пользователя
const createUser = await fetch('http://localhost/app/api/users', {
  method: 'POST',
  body: JSON.stringify(userData)
});
```

## 🏗️ Архитектура

### Основные эндпоинты
- `GET http://localhost/app/api/users` — получение списка пользователей
- `POST http://localhost/app/api/users` — создание нового пользователя
- `GET http://localhost/app/api/users/profile` — профиль текущего пользователя
- `POST http://localhost/app/api/users/check-by-telegram` — проверка пользователя по Telegram ID
- `POST http://localhost/app/api/users/find-by-telegram` — поиск пользователя по Telegram ID

### Авторизация
- **Минимальная роль:** member (для просмотра)
- **Создание пользователей:** admin
- **Профиль:** любой авторизованный пользователь

## 📝 Эндпоинты

### GET /api/users
Получение списка пользователей с фильтрацией и пагинацией.

#### Параметры запроса
```http
GET http://localhost/app/api/users?page=1&per_page=20&role=member&city=Москва
```

| Параметр | Тип | Описание | По умолчанию |
|----------|-----|----------|--------------|
| `page` | int | Номер страницы | 1 |
| `per_page` | int | Количество на странице | 20 |
| `role` | string | Фильтр по роли | - |
| `city` | string | Фильтр по городу | - |
| `status` | string | Фильтр по статусу | - |

#### Пример ответа
```json
{
  "success": true,
  "data": [
    {
      "id": 123,
      "first_name": "Иван",
      "last_name": "Иванов",
      "username": "ivan_user",
      "telegram_id": 456789,
      "role": {
        "id": 4,
        "code": "member",
        "name": "Участник",
        "color": "#007bff"
      },
      "city": "Москва",
      "email": "ivan@example.com",
      "photo": {
        "id": 10,
        "url": "http://localhost/app/uploads/user/user_123_avatar.jpg",
        "file_name": "user_123_avatar.jpg"
      },
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

### POST /api/users
Создание нового пользователя (только для администраторов).

#### Тело запроса
```json
{
  "first_name": "Иван",
  "last_name": "Иванов",
  "username": "ivan_user",
  "telegram_id": 456789,
  "email": "ivan@example.com",
  "city": "Москва",
  "role_id": 4,
  "photo_url": "http://localhost/app/uploads/user/user_123_avatar.jpg"
}
```

#### Обязательные поля
- `first_name` — имя пользователя
- `telegram_id` — ID в Telegram

#### Опциональные поля
- `last_name` — фамилия
- `username` — username в Telegram
- `email` — email адрес
- `city` — город
- `role_id` — ID роли (по умолчанию: member)
- `photo_url` — URL аватара

#### Пример ответа
```json
{
  "success": true,
  "data": {
    "id": 124,
    "first_name": "Иван",
    "last_name": "Иванов",
    "username": "ivan_user",
    "telegram_id": 456789,
    "role": {
      "id": 4,
      "code": "member",
      "name": "Участник"
    },
    "city": "Москва",
    "email": "ivan@example.com",
    "photo": {
      "id": 11,
      "url": "http://localhost/app/uploads/user/user_124_avatar.jpg",
      "file_name": "user_124_avatar.jpg"
    },
    "created_at": "2024-01-15T14:30:00Z"
  }
}
```

### GET /api/users/profile
Получение профиля текущего авторизованного пользователя.

#### Заголовки
```http
Authorization: Bearer <token>
```

#### Пример ответа
```json
{
  "success": true,
  "data": {
    "id": 123,
    "first_name": "Иван",
    "last_name": "Иванов",
    "username": "ivan_user",
    "telegram_id": 456789,
    "role": {
      "id": 4,
      "code": "member",
      "name": "Участник",
      "color": "#007bff"
    },
    "city": "Москва",
    "email": "ivan@example.com",
    "photo": {
      "id": 10,
      "url": "http://localhost/app/uploads/user/user_123_avatar.jpg",
      "file_name": "user_123_avatar.jpg"
    },
    "cars": [
      {
        "id": 1,
        "model": "BMW Z4",
        "status": {
          "id": 7,
          "code": "active",
          "name": "Активен"
        }
      }
    ],
    "events": [
      {
        "id": 5,
        "title": "Поездка в Сочи",
        "event_type": {
          "id": 1,
          "code": "trip",
          "name": "Поездка"
        }
      }
    ],
    "created_at": "2024-01-15T10:30:00Z",
    "updated_at": "2024-01-15T12:00:00Z"
  }
}
```

### POST /api/users/check-by-telegram
Проверка существования пользователя по Telegram ID.

#### Тело запроса
```json
{
  "telegram_id": 456789
}
```

#### Пример ответа
```json
{
  "success": true,
  "data": {
    "exists": true,
    "user": {
      "id": 123,
      "first_name": "Иван",
      "last_name": "Иванов",
      "username": "ivan_user",
      "role": {
        "id": 4,
        "code": "member",
        "name": "Участник"
      }
    }
  }
}
```

### POST /api/users/find-by-telegram
Поиск пользователя по Telegram ID с полными данными.

#### Тело запроса
```json
{
  "telegram_id": 456789
}
```

#### Пример ответа
```json
{
  "success": true,
  "data": {
    "id": 123,
    "first_name": "Иван",
    "last_name": "Иванов",
    "username": "ivan_user",
    "telegram_id": 456789,
    "role": {
      "id": 4,
      "code": "member",
      "name": "Участник",
      "color": "#007bff"
    },
    "city": "Москва",
    "email": "ivan@example.com",
    "photo": {
      "id": 10,
      "url": "http://localhost/app/uploads/user/user_123_avatar.jpg",
      "file_name": "user_123_avatar.jpg"
    },
    "cars": [
      {
        "id": 1,
        "model": "BMW Z4",
        "status": {
          "id": 7,
          "code": "active",
          "name": "Активен"
        }
      }
    ],
    "created_at": "2024-01-15T10:30:00Z"
  }
}
```

## 🚨 Обработка ошибок

### Коды ошибок
- `ACCESS_DENIED` — недостаточно прав для операции
- `VALIDATION_ERROR` — ошибка валидации данных
- `USER_NOT_FOUND` — пользователь не найден
- `USER_ALREADY_EXISTS` — пользователь уже существует
- `INVALID_TELEGRAM_ID` — некорректный Telegram ID

### Примеры ошибок
```json
{
  "success": false,
  "error": {
    "code": "ACCESS_DENIED",
    "message": "Недостаточно прав для создания пользователей"
  }
}
```

```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Некорректные данные",
    "details": {
      "field": "telegram_id",
      "message": "Поле telegram_id должно быть целым числом"
    }
  }
}
```

## 🔐 Права доступа

### Матрица прав
| Эндпоинт | Роль | Описание |
|-----------|------|----------|
| `GET /api/users` | member+ | Просмотр списка пользователей |
| `POST /api/users` | admin | Создание пользователей |
| `GET /api/users/profile` | any | Профиль текущего пользователя |
| `POST /api/users/check-by-telegram` | any | Проверка пользователя |
| `POST /api/users/find-by-telegram` | any | Поиск пользователя |

### Проверка прав
```php
// В контроллере
if (!$this->requireAccess('api.users.getList')) {
    return; // Ответ уже отправлен
}
```

## 📊 Структура данных

### Пользователь
```json
{
  "id": 123,
  "first_name": "Иван",
  "last_name": "Иванов",
  "username": "ivan_user",
  "telegram_id": 456789,
  "role": {
    "id": 4,
    "code": "member",
    "name": "Участник",
    "color": "#007bff"
  },
  "city": "Москва",
  "email": "ivan@example.com",
  "photo": {
    "id": 10,
    "url": "http://localhost/app/uploads/user/user_123_avatar.jpg",
    "file_name": "user_123_avatar.jpg"
  },
  "cars": [...],
  "events": [...],
  "created_at": "2024-01-15T10:30:00Z",
  "updated_at": "2024-01-15T12:00:00Z"
}
```

### Роли пользователей
- `external` (1) — Внешний пользователь
- `guest` (2) — Гость
- `user` (3) — Пользователь
- `member` (4) — Участник клуба
- `moderator` (5) — Модератор
- `admin` (6) — Администратор

## 📝 Примеры использования

### Получение списка пользователей
```javascript
// Telegram WebApp
const response = await fetch('http://localhost/app/api/users?role=member&city=Москва', {
  headers: {
    'Authorization': `Bearer ${token}`
  }
});

const data = await response.json();
console.log(data.data); // Список пользователей
```

### Создание пользователя
```javascript
// Только для администраторов
const userData = {
  first_name: 'Иван',
  last_name: 'Иванов',
  telegram_id: 456789,
  email: 'ivan@example.com',
  city: 'Москва',
  role_id: 4
};

const response = await fetch('http://localhost/app/api/users', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${token}`
  },
  body: JSON.stringify(userData)
});

const result = await response.json();
console.log(result.data); // Созданный пользователь
```

### Получение профиля
```javascript
const response = await fetch('http://localhost/app/api/users/profile', {
  headers: {
    'Authorization': `Bearer ${token}`
  }
});

const profile = await response.json();
console.log(profile.data); // Профиль текущего пользователя
```

### Загрузка аватара
```javascript
const formData = new FormData();
formData.append('photo', file);
formData.append('entity_type', 'user');
formData.append('entity_id', '123');

const response = await fetch('http://localhost/app/api/photos', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`
  },
  body: formData
});

const result = await response.json();
console.log(result.data.url); // http://localhost/app/uploads/user/user_123_avatar.jpg
```

## 🔄 Интеграция

### С AuthMiddleware
```php
// Автоматическая авторизация через Telegram
$authResult = AuthMiddleware::authenticate($route, $method);
if ($authResult['success']) {
    $userData = $authResult['data'];
    // Продолжаем обработку
}
```

### С ExpandHelper
```php
// Развертывание связанных данных
$userData = User::findByIdWithDetails($id);
$expandedUser = ExpandHelper::expandUserData($userData);
```

### С ResponseHelper
```php
// Стандартизированные ответы
echo ResponseHelper::success($users, $pagination);
echo ResponseHelper::error('ACCESS_DENIED', 'Недостаточно прав');
```

## 📈 Мониторинг

### Логирование
```php
Logger::info('User list requested', [
    'user_id' => AppContext::getCurrentUserId(),
    'filters' => $filters,
    'count' => count($users)
]);
```

### Метрики
- Количество запросов к эндпоинтам пользователей
- Популярные фильтры поиска
- Время выполнения запросов
- Количество созданных пользователей

---

**📚 См. также:** [Обзор API](../OVERVIEW.md), [Авторизация](../../AUTHENTICATION/OVERVIEW.md), [Модель User](../../MODELS/USER.md) 
# 🛣️ Обзор API бэкенда CabrioRide

> **Назначение:** Принципы и стандарты API бэкенда  
> **Версия:** 1.0.0  
> **Последнее обновление:** 2024-01-01

---

## 🎯 **Принципы API**

### **1. REST для сущностей, RPC для действий**
- **REST эндпоинты** для CRUD операций с сущностями
- **RPC эндпоинты** для сложных бизнес-действий
- **Единообразные** ответы и обработка ошибок

### **2. Централизованная авторизация**
- Все защищённые эндпоинты требуют авторизации
- Ролевая модель доступа
- Единая точка проверки прав

### **3. Стандартизированные ответы**
- Единый формат JSON ответов
- Стандартные коды ошибок
- Метаданные в ответах

### **4. Валидация входных данных**
- Проверка обязательных полей
- Валидация типов данных
- Санитизация входных данных

---

## 🏗️ **Структура API**

### **Базовый URL**
```
https://api.cabrioride.com/api/
```

### **Основные группы эндпоинтов**

#### **1. Пользователи (`/api/users`)**
- `GET /api/users` — список пользователей
- `POST /api/users` — создание пользователя
- `GET /api/users/profile` — профиль текущего пользователя
- `GET /api/users/{id}` — информация о пользователе

#### **2. Автомобили (`/api/cars`)**
- `GET /api/cars` — список автомобилей
- `POST /api/cars` — добавление автомобиля
- `GET /api/cars/{id}` — информация об автомобиле
- `PUT /api/cars/{id}` — обновление автомобиля
- `DELETE /api/cars/{id}` — удаление автомобиля

#### **3. События (`/api/events`)**
- `GET /api/events` — список событий
- `POST /api/events` — создание события
- `GET /api/events/{id}` — информация о событии

#### **4. Гид-объекты (`/api/guide-objects`)**
- `GET /api/guide-objects` — список гид-объектов
- `POST /api/guide-objects` — создание гид-объекта

#### **5. Визитки (`/api/business-cards`)**
- `GET /api/business-cards` — список визиток
- `POST /api/business-cards` — создание визитки

#### **6. Фото (`/api/photos`)**
- `GET /api/photos` — список фото
- `POST /api/photos` — загрузка фото

#### **7. Отзывы (`/api/reviews`)**
- `GET /api/reviews` — список отзывов
- `POST /api/reviews` — создание отзыва

#### **8. Системные (`/api/system`)**
- `POST /api/system/user-sync` — синхронизация пользователя
- `POST /api/system/user-role` — обновление роли
- `POST /api/system/entity-status` — обновление статуса

#### **9. Actions (`/api/actions`)**
- `POST /api/actions/check-car-in-club` — проверка авто в клубе
- `POST /api/actions/leave-business-card` — оставление визитки
- `POST /api/actions/add-car-to-garage` — добавление в гараж

#### **10. Системные (`/api/health`, `/api/status`)**
- `GET /api/health` — проверка здоровья API
- `GET /api/status` — статус системы

---

## 🔐 **Авторизация**

### **Требования авторизации**
- Все эндпоинты кроме `/api/health` требуют авторизации
- Авторизация через `AuthMiddleware`
- Проверка ролей через централизованную конфигурацию

### **Типы авторизации**
1. **Telegram WebApp** — через заголовки Telegram
2. **Telegram Bot** — через JSON данные
3. **SYSTEM_TOKEN** — для системных запросов
4. **DEV-мод** — для разработки

### **Заголовки авторизации**
```http
Authorization: Bearer <token>
Content-Type: application/json
```

---

## 📤 **Формат запросов**

### **HTTP методы**
- **GET** — получение данных
- **POST** — создание данных
- **PUT** — обновление данных
- **DELETE** — удаление данных

### **Заголовки**
```http
Content-Type: application/json
Authorization: Bearer <token>
Accept: application/json
```

### **Тело запроса (JSON)**
```json
{
  "field1": "value1",
  "field2": "value2",
  "nested": {
    "field3": "value3"
  }
}
```

### **Параметры запроса**
```
GET /api/users?page=1&limit=10&role=member
```

---

## 📥 **Формат ответов**

### **Успешный ответ**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Example",
    "created_at": "2024-01-01T12:00:00Z"
  },
  "meta": {
    "pagination": {
      "page": 1,
      "limit": 10,
      "total": 50,
      "pages": 5
    },
    "execution_time": 0.05
  }
}
```

### **Ответ с ошибкой**
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Ошибка валидации",
    "details": {
      "field": "Описание ошибки"
    }
  }
}
```

### **Метаданные**
- `success` — результат операции (boolean)
- `data` — данные ответа (object/array/null)
- `error` — информация об ошибке (object)
- `meta` — метаданные (pagination, timing, etc.)

---

## 🚨 **Обработка ошибок**

### **HTTP статусы**
- **200 OK** — успешный запрос
- **201 Created** — ресурс создан
- **400 Bad Request** — ошибка валидации
- **401 Unauthorized** — ошибка авторизации
- **403 Forbidden** — недостаточно прав
- **404 Not Found** — ресурс не найден
- **500 Internal Server Error** — внутренняя ошибка

### **Коды ошибок**
- `VALIDATION_ERROR` — ошибка валидации
- `UNAUTHORIZED` — ошибка авторизации
- `FORBIDDEN` — недостаточно прав
- `NOT_FOUND` — ресурс не найден
- `INTERNAL_ERROR` — внутренняя ошибка

---

## 📊 **Пагинация**

### **Параметры пагинации**
- `page` — номер страницы (по умолчанию: 1)
- `limit` — количество записей на странице (по умолчанию: 20)

### **Метаданные пагинации**
```json
{
  "pagination": {
    "page": 1,
    "limit": 10,
    "total": 50,
    "pages": 5,
    "has_next": true,
    "has_prev": false
  }
}
```

---

## 🔍 **Фильтрация и поиск**

### **Параметры фильтрации**
```
GET /api/users?role=member&status=active
GET /api/cars?brand=BMW&year=2020
GET /api/events?date_from=2024-01-01&date_to=2024-12-31
```

### **Поиск**
```
GET /api/users?search=ivan
GET /api/cars?search=A123BC
```

### **Сортировка**
```
GET /api/users?sort=name&order=asc
GET /api/events?sort=date&order=desc
```

---

## 📁 **Загрузка файлов**

### **Мультипарт запросы**
```http
POST /api/photos
Content-Type: multipart/form-data
Authorization: Bearer <token>

photo: [binary data]
description: "Описание фото"
```

### **Base64 кодирование**
```json
{
  "photo": "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQ...",
  "description": "Описание фото"
}
```

---

## 🔄 **Версионирование**

### **Текущая версия**
- **API версия:** v1.0.0
- **Стабильность:** Стабильная
- **Обратная совместимость:** Да

### **Планы версионирования**
- Мажорные изменения через новые версии
- Обратная совместимость в рамках мажорной версии
- Документирование изменений

---

## 📚 **Документация по эндпоинтам**

### **Детальные руководства**
- [Пользователи](ENDPOINTS/USERS.md) — `/api/users`
- [Автомобили](ENDPOINTS/CARS.md) — `/api/cars`
- [События](ENDPOINTS/EVENTS.md) — `/api/events`
- [Гид-объекты](ENDPOINTS/GUIDE_OBJECTS.md) — `/api/guide-objects`
- [Визитки](ENDPOINTS/BUSINESS_CARDS.md) — `/api/business-cards`
- [Фото](ENDPOINTS/PHOTOS.md) — `/api/photos`
- [Отзывы](ENDPOINTS/REVIEWS.md) — `/api/reviews`
- [Системные](ENDPOINTS/SYSTEM.md) — `/api/system`

### **Справочники**
- [Стандартные ответы](RESPONSES.md) — формат ответов
- [Коды ошибок](ERRORS.md) — обработка ошибок
- [Примеры](EXAMPLES.md) — примеры использования
- [OpenAPI спецификация](openapi.yaml) — техническая спецификация

---

## 🧪 **Тестирование API**

### **Инструменты**
- **Postman** — для тестирования эндпоинтов
- **curl** — для командной строки
- **Встроенные тесты** — в `_tests/` директории

### **Примеры тестов**
```bash
# Проверка здоровья API
curl -X GET "https://api.cabrioride.com/api/health"

# Получение списка пользователей
curl -X GET "https://api.cabrioride.com/api/users" \
  -H "Authorization: Bearer <token>"
```

---

> **💡 Совет:** Изучите разделы [Стандартные ответы](RESPONSES.md) и [Примеры](EXAMPLES.md) для более детального понимания API. 
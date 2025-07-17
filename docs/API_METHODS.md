# Документация по REST API CabrioRide

> См. также: [Техническое задание](TECHNICAL_SPECIFICATION.md), [Структура БД](DATABASE_SCHEMA.md), [Роли пользователя](USER_ROLES.md)

---

> **Пояснение:** Пользователь считается завершившим регистрацию, если его статус не "new" и не "external". Пользователь с этими статусами — не завершил регистрацию и не имеет доступа к основному функционалу.

## Пример версионирования API
- Все новые методы размещать по пути `/api/v1/...`
- Пример: `GET /api/v1/users`, `POST /api/v1/cars`

## 1. Общие принципы
- API реализован в стиле REST.
- Все эндпоинты используют snake_case.
- Аутентификация через JWT (в заголовке Authorization).
- Ответы в формате JSON.
- Все методы должны быть документированы.

## 2. Примеры эндпоинтов

### Авторизация
```
POST /api/auth/login
POST /api/auth/telegram
```

### Пользователи
```
GET    /api/users                # Список участников
GET    /api/users/{id}           # Профиль участника
POST   /api/users                # Создать участника
PUT    /api/users/{id}           # Обновить участника
DELETE /api/users/{id}           # Удалить участника
```

### Автомобили
```
GET    /api/cars                   # Список автомобилей
GET    /api/cars/{id}              # Карточка автомобиля
POST   /api/cars                   # Добавить автомобиль
PUT    /api/cars/{id}              # Обновить автомобиль
DELETE /api/cars/{id}              # Удалить автомобиль
```

### Мероприятия
```
GET    /api/events                 # Список событий
GET    /api/events/{id}            # Детали события
POST   /api/events                 # Создать событие
PUT    /api/events/{id}            # Обновить событие
DELETE /api/events/{id}            # Удалить событие
```

### Прочее
- Для загрузки файлов использовать отдельные эндпоинты (например, /api/upload/user-photo).
- Для поиска авто по номеру: GET /api/cars?reg_number=XXX

## 3. Структура запросов и ответов

### Пример запроса
```
POST /api/users
Content-Type: application/json
Authorization: Bearer <jwt_token>

{
  "first_name": "Иван",
  "last_name": "Иванов",
  "username": "ivan123",
  ...
}
```

### Пример ответа
```
{
  "success": true,
  "data": {
    "id": "...",
    "first_name": "Иван",
    ...
  },
  "error": null
}
```

### Ошибка
```
{
  "success": false,
  "data": null,
  "error": {
    "code": 400,
    "message": "Некорректные данные"
  }
}
```

## 4. Рекомендации по оформлению
- Все методы должны быть описаны в формате: метод, путь, описание, параметры, пример запроса/ответа.
- Для сложных методов — отдельные подпункты с примерами.
- Документировать все возможные ошибки и коды ответов.

## 5. Примечания
- API должен быть расширяемым и поддерживать версионирование (например, /api/v1/...).
- Все изменения в API должны быть отражены в этом файле. 

## Работа с координатами (Карта)

### POST /api/map/location
- **Описание:** Обновить координаты пользователя
- **Параметры запроса:**
    - `latitude` (float, required)
    - `longitude` (float, required)
- **Ответ:**
    - 200 OK, { success: true }
    - 400 Bad Request (если невалидные данные)
    - 401 Unauthorized (если не авторизован)

### GET /api/map/locations
- **Описание:** Получить список участников с актуальными координатами
- **Параметры запроса:**
    - `city` (string, optional) — фильтр по городу
- **Ответ:**
    - 200 OK, массив объектов:
        - `user_id` (int)
        - `latitude` (float)
        - `longitude` (float)
        - `updated_at` (datetime)
        - `status` (online/recent/away)
        - `avatar_url` (string)
        - `name` (string)
    - Только пользователи с координатами, обновлёнными не более 20 минут назад 

## Голосовые сообщения (Рация)

### POST /api/map/voice
- **Описание:** Загрузка голосового сообщения (multipart/form-data, поле `voice`)
- **Параметры запроса:**
    - `voice` (audio/webm, required)
- **Ответ:**
    - 200 OK, { success: true, voice_id: int, url: string }
    - 400 Bad Request (если невалидные данные)
    - 401 Unauthorized (если не авторизован)

### GET /api/map/voices?since=...
- **Описание:** Получение последних голосовых сообщений (например, за последний час)
- **Параметры запроса:**
    - `since` (datetime, optional) — получить только новые сообщения
- **Ответ:**
    - 200 OK, массив объектов:
        - `voice_id` (int)
        - `user_id` (int)
        - `url` (string)
        - `created_at` (datetime)
        - `name` (string)
        - `avatar_url` (string)
    - Только сообщения, созданные не более 1 часа назад
- **Удаление:** сервер автоматически удаляет аудиофайлы старше 1 часа. 

## Подсказки на карте (Hints)

### POST /api/map/hint
- **Описание:** Добавить новую подсказку на карте
- **Параметры запроса:**
    - `type` (string, required) — тип подсказки ("ГАИ", "ремонт", "пробка", "авария" и др.)
    - `latitude` (float, required)
    - `longitude` (float, required)
- **Ответ:**
    - 200 OK, { success: true, hint_id: int }
    - 400 Bad Request (если невалидные данные)
    - 401 Unauthorized (если не авторизован)

### GET /api/map/hints
- **Описание:** Получить все активные подсказки на карте
- **Ответ:**
    - 200 OK, массив объектов:
        - `hint_id` (int)
        - `type` (string)
        - `latitude` (float)
        - `longitude` (float)
        - `created_at` (datetime)
        - `user_id` (int)
        - `active` (boolean)
    - Только подсказки, созданные не более 1 часа назад и не удалённые вручную

### DELETE /api/map/hint/:id
- **Описание:** Удалить подсказку (может автор или модератор/админ/root)
- **Ответ:**
    - 200 OK, { success: true }
    - 403 Forbidden (если нет прав)
    - 404 Not Found (если подсказка не найдена) 

---

### Распознавание номера по фото

**POST /api/ocr**

- **Описание:**
  Принимает изображение автомобиля, распознаёт номер через внешний сервис, ищет этот номер в базе клуба и возвращает результат.

- **Запрос:**
  - Content-Type: multipart/form-data
  - upload: файл изображения (до 3 МБ, jpg/png)

- **Ответ (JSON):**
  ```json
  {
    "success": true,
    "plate": "0070MX7",
    "region": "BY",
    "confidence": 0.98,
    "vehicle_type": "Sedan",
    "club_status": {
      "found": true,
      "status": "active", // или "not_found", "pending", "blocked" и т.д.
      "car_id": 123,
      "owner": {
        "id": 45,
        "name": "Иван Иванов"
      }
    },
    "raw": { /* исходный ответ platerecognizer.com */ }
  }
  ```
  - Если номер не найден в базе:
    ```json
    {
      "success": true,
      "plate": "0070MX7",
      "region": "BY",
      "confidence": 0.98,
      "vehicle_type": "Sedan",
      "club_status": {
        "found": false
      },
      "raw": { /* ... */ }
    }
    ```
  - В случае ошибки:
    ```json
    {
      "success": false,
      "error": { "code": 400, "message": "Ошибка загрузки файла" }
    }
    ```

- **Пример запроса:**
  ```bash
  curl -X POST -F "upload=@car.jpg" http://localhost/api/ocr
  ```

- **Примечания:**
  - Токен для внешнего API хранится в .env
  - Размер файла — до 3 МБ
  - Если распознано несколько номеров — возвращается первый с максимальной уверенностью
  - В поле club_status возвращается информация о статусе авто в клубе, если найдено совпадение по номеру 

---

### Поиск автомобиля по номеру (текстовый запрос)

**POST /api/plate-search**

- **Описание:**
  Поиск автомобиля по номеру (или его части) в базе клуба. Используется для текстовых запросов (например, если пользователь прислал номер текстом). В ответе не возвращаются найденные номера, только факт наличия совпадения.

- **Запрос:**
  ```json
  {
    "query": "0070MX7"
  }
  ```

- **Ответ (JSON):**
  ```json
  {
    "success": true,
    "found": true
  }
  ```
  - Если не найдено:
    ```json
    {
      "success": true,
      "found": false
    }
    ```
  - В случае ошибки:
    ```json
    {
      "success": false,
      "error": { "code": 400, "message": "Некорректный запрос" }
    }
    ```
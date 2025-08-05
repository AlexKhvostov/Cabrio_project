[⬅️ К архитектуре и структуре backend (README.md)](README.md)

# Эндпоинты API CabrioRide

---

## Примеры запросов

### Создание пользователя
```http
POST /api/users
Content-Type: application/json
Authorization: Bearer <token>

{
  "first_name": "Иван",
  "last_name": "Иванов",
  "role": { "id": 3 },
  "email": "ivan@example.com",
  "phone": "+79991234567"
}
```

### Обновление автомобиля
```http
PUT /api/cars/7
Content-Type: application/json
Authorization: Bearer <token>

{
  "model": "BMW Z4",
  "color": "red",
  "year": 2020
}
```

### Получение списка пользователей с фильтрацией
```http
GET /api/users?role=member&city=Москва
Authorization: Bearer <token>
```

---

## Пример успешного ответа для пользователя
```json
{
  "success": true,
  "data": {
    "id": 123,
    "first_name": "Иван",
    "last_name": "Иванов",
    "role": {
      "id": 3,
      "code": "member",
      "name": "Участник"
    },
    "status": {
      "id": 1,
      "code": "active",
      "name": "Активен"
    },
    "city": "Москва",
    "photos": [
      { "id": 1, "url": "https://example.com/photo1.jpg" }
    ],
    "created_at": "2024-05-01T12:00:00Z"
  },
  "error": null
}
```

---

## Пример успешного ответа для списка
```json
{
  "success": true,
  "data": [
    { "id": 1, "name": "...", "role": { "id": 3, "code": "member" } },
    { "id": 2, "name": "...", "role": { "id": 4, "code": "moderator" } }
  ],
  "pagination": {
    "page": 1,
    "per_page": 20,
    "total": 135
  },
  "error": null
}
```

---

## 1. Users (Пользователи)
- `GET    /api/users`                — список пользователей
- `GET    /api/users/{id}`           — получить пользователя по id
- `POST   /api/users`                — создать пользователя
- `PUT    /api/users/{id}`           — обновить пользователя
- `DELETE /api/users/{id}`           — удалить пользователя (admin)

### Дополнительно:
- `GET    /api/users/{id}/cars`      — список авто пользователя
- `GET    /api/users/{id}/events`    — события пользователя

---

## 2. Cars (Автомобили)
- `GET    /api/cars`                 — список авто
- `GET    /api/cars/{id}`            — получить авто по id
- `POST   /api/cars`                 — добавить авто
- `PUT    /api/cars/{id}`            — обновить авто
- `DELETE /api/cars/{id}`            — удалить авто (владелец/admin)

### Дополнительно:
- `GET    /api/cars/{id}/photos`     — фото автомобиля

---

## 3. Events (События)
- `GET    /api/events`               — список событий
- `GET    /api/events/{id}`          — получить событие по id
- `POST   /api/events`               — создать событие
- `PUT    /api/events/{id}`          — обновить событие
- `DELETE /api/events/{id}`          — удалить событие (организатор/admin)

### Дополнительно:
- `GET    /api/events/{id}/participants` — участники события
- `POST   /api/events/{id}/join`         — записаться на событие
- `POST   /api/events/{id}/leave`        — выйти из события

---

## 4. Business Cards (Визитки)
- `GET    /api/business-cards`           — список визиток
- `GET    /api/business-cards/{id}`      — получить визитку по id
- `POST   /api/business-cards`           — создать визитку
- `DELETE /api/business-cards/{id}`      — удалить визитку (создатель/admin)

---

## 5. Guide Objects (Гид-объекты)
- `GET    /api/guide-objects`            — список гид-объектов
- `GET    /api/guide-objects/{id}`       — получить гид-объект по id
- `POST   /api/guide-objects`            — добавить гид-объект
- `PUT    /api/guide-objects/{id}`       — обновить гид-объект
- `DELETE /api/guide-objects/{id}`       — удалить гид-объект (автор/admin)

---

## 6. Reviews (Отзывы)
- `GET    /api/reviews`                  — список отзывов
- `GET    /api/reviews/{id}`             — получить отзыв по id
- `POST   /api/reviews`                  — добавить отзыв
- `PUT    /api/reviews/{id}`             — обновить отзыв
- `DELETE /api/reviews/{id}`             — удалить отзыв (автор/admin)

---

## 7. Map Hints (Подсказки на карте)
- `GET    /api/map-hints`                — список подсказок
- `POST   /api/map-hints`                — добавить подсказку
- `DELETE /api/map-hints/{id}`           — удалить подсказку (автор/moderator/admin)

---

## 8. Photos (Фото)
- `GET    /api/photos/{entity_type}/{entity_id}` — фото для сущности
- `POST   /api/photos/{entity_type}/{entity_id}` — загрузить фото
- `DELETE /api/photos/{id}`                      — удалить фото (автор/admin)

---

## 9. Справочники (Reference)
- `GET    /api/ref/roles`                — список ролей
- `GET    /api/ref/statuses`             — список статусов
- `GET    /api/ref/event-types`          — типы событий
- `GET    /api/ref/guide-object-types`   — типы гид-объектов
- `GET    /api/ref/guide-object-kinds`   — виды гид-объектов
- `GET    /api/ref/car-brands`           — марки авто

---

## 10. Сессии и авторизация
- `POST   /api/auth/telegram`            — авторизация через Telegram
- `POST   /api/auth/logout`              — выход из системы

---

## 11. RPC-действия (Actions)
- `POST   /api/users/{id}/approve`           — подтвердить пользователя (moderator/admin)
- `POST   /api/users/{id}/block`             — заблокировать пользователя (moderator/admin)
- `POST   /api/users/{id}/unblock`           — разблокировать пользователя (moderator/admin)
- `POST   /api/cars/{id}/transferOwnership`  — передать авто другому участнику (владелец)
- `POST   /api/events/{id}/invite`           — пригласить пользователя на событие (организатор/moderator)
- `POST   /api/events/{id}/confirm`          — подтвердить участие пользователя (организатор/moderator)
- `POST   /api/guide-objects/{id}/approve`   — одобрить гид-объект (moderator/admin)
- `POST   /api/reviews/{id}/approve`         — одобрить отзыв (moderator/admin)

---

## 12. Прочее
- `GET    /api/me`                          — информация о текущем пользователе
- `GET    /api/activity`                    — активность пользователя
- `POST   /api/activity/give`               — выдать активность другому пользователю

---

> Для каждого эндпоинта будет добавлено подробное описание параметров, структуры запроса и ответа, а также ролей доступа. Файл дополняется и актуализируется по мере развития проекта. 
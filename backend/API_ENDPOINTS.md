# 📡 API Endpoints CabrioRide

## 🔐 Авторизация
| Метод | Endpoint | Описание | Роли доступа |
|-------|----------|----------|--------------|
| POST | /api/auth/login | Авторизация через Telegram | all |
| POST | /api/auth/check | Проверка авторизации | all |
| POST | /api/auth/logout | Выход | all |

## 👤 Пользователи
| Метод | Endpoint | Описание | Роли доступа |
|-------|----------|----------|--------------|
| GET | /api/users/profile | Получить свой профиль | all |
| PUT | /api/users/profile | Обновить свой профиль | registered+ |
| GET | /api/users/{id} | Получить профиль пользователя | member+ |
| GET | /api/users/list | Список пользователей | member+ |
| POST | /api/users/activity | Поставить активность | member+ |
| GET | /api/users/check-role | Проверить роль пользователя | all |

## 🚗 Автомобили
| Метод | Endpoint | Описание | Роли доступа |
|-------|----------|----------|--------------|
| GET | /api/cars/list | Список автомобилей | member+ |
| GET | /api/cars/{id} | Получить информацию об авто | member+ |
| POST | /api/cars/add | Добавить автомобиль | registered+ |
| PUT | /api/cars/{id} | Обновить информацию об авто | owner,admin |
| DELETE | /api/cars/{id} | Удалить автомобиль | owner,admin |
| POST | /api/cars/check | Проверить авто по номеру | all |

## 📸 OCR API
| Метод | Endpoint | Описание | Роли доступа |
|-------|----------|----------|--------------|
| POST | /api/ocr/recognize | Распознать номер по фото | all |
| POST | /api/ocr/check | Проверить номер в базе | all |

## 📅 События
| Метод | Endpoint | Описание | Роли доступа |
|-------|----------|----------|--------------|
| GET | /api/events/list | Список событий | member+ |
| GET | /api/events/{id} | Информация о событии | member+ |
| POST | /api/events/add | Создать событие | member+ |
| PUT | /api/events/{id} | Обновить событие | organizer,admin |
| DELETE | /api/events/{id} | Удалить событие | organizer,admin |
| POST | /api/events/{id}/join | Присоединиться к событию | member+ |
| POST | /api/events/{id}/leave | Покинуть событие | member+ |

## 🎯 Гид-объекты
| Метод | Endpoint | Описание | Роли доступа |
|-------|----------|----------|--------------|
| GET | /api/guide/list | Список гид-объектов | member+ |
| GET | /api/guide/{id} | Информация о гид-объекте | member+ |
| POST | /api/guide/add | Добавить гид-объект | member+ |
| PUT | /api/guide/{id} | Обновить гид-объект | author,admin |
| DELETE | /api/guide/{id} | Удалить гид-объект | author,admin |
| POST | /api/guide/{id}/review | Добавить отзыв | member+ |

## 💼 Визитки
| Метод | Endpoint | Описание | Роли доступа |
|-------|----------|----------|--------------|
| POST | /api/cards/add | Оставить визитку | member+ |
| GET | /api/cards/list | Список своих визиток | member+ |
| GET | /api/cards/stats | Статистика по визиткам | member+ |

## 🗺 Карта
| Метод | Endpoint | Описание | Роли доступа |
|-------|----------|----------|--------------|
| POST | /api/map/location | Обновить локацию | member+ |
| GET | /api/map/users | Получить участников на карте | member+ |
| POST | /api/map/hint | Добавить метку на карту | member+ |
| DELETE | /api/map/hint/{id} | Удалить метку | author,admin |

## 👑 Админ
| Метод | Endpoint | Описание | Роли доступа |
|-------|----------|----------|--------------|
| GET | /api/admin/users | Управление пользователями | admin |
| PUT | /api/admin/users/{id}/role | Изменить роль пользователя | admin |
| GET | /api/admin/logs | Просмотр логов | admin |
| GET | /api/admin/stats | Статистика системы | admin |

## 📨 Уведомления
| Метод | Endpoint | Описание | Роли доступа |
|-------|----------|----------|--------------|
| GET | /api/notifications/list | Список уведомлений | all |
| POST | /api/notifications/read | Отметить прочитанным | all |
| GET | /api/notifications/settings | Настройки уведомлений | member+ |

## Роли доступа
- all: доступно всем
- registered+: зарегистрированным и выше
- member+: членам клуба и выше
- admin: только администраторам
- owner: владельцу ресурса
- author: автору ресурса
- organizer: организатору события

## Формат ответа API
```json
// Успешный ответ
{
    "success": true,
    "data": {},
    "message": "OK"
}

// Ошибка
{
    "success": false,
    "error": "Описание ошибки",
    "code": 400
}
```

## Коды ошибок
- 400: Неверный запрос
- 401: Не авторизован
- 403: Нет прав доступа
- 404: Ресурс не найден
- 422: Ошибка валидации
- 429: Превышен лимит запросов
- 500: Внутренняя ошибка сервера 
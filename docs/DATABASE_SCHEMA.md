> ℹ️ Актуальная ER-диаграмма структуры базы данных находится в [docs/DATABASE_RELATIONS.md](DATABASE_RELATIONS.md)

> **Пояснение:** Запись о пользователе создаётся в базе при первом обращении к боту. Пользователь с незавершённой регистрацией — это статус "new" или "external". Только пользователи со статусом, отличным от "new" и "external", считаются завершившими регистрацию и получают доступ к основному функционалу.

> См. также: [API](API_METHODS.md), [Роли пользователя](USER_ROLES.md), [Структура проекта](PROJECT_STRUCTURE.md)

# Схема базы данных CabrioRide (актуальная)

---

## 📋 Список таблиц

| Таблица                  | Назначение/Описание                                      |
|--------------------------|----------------------------------------------------------|
| ref_roles                | Справочник ролей пользователей                           |
| ref_statuses             | Справочник статусов (универсальный для всех сущностей)   |
| ref_event_types          | Справочник типов событий                                 |
| ref_guide_object_types   | Справочник типов объектов гида                           |
| ref_guide_object_kinds   | Справочник видов гид-объектов (привязан к типу)         |
| ref_car_brands           | Справочник марок автомобилей                           |
| users                    | Пользователи платформы                                   |
| cars                     | Автомобили участников                                    |
| events                   | События/мероприятия                                      |
| business_cards           | Визитки/приглашения                                      |
| guide_objects            | Объекты гида (GuideObject)                               |
| reviews                  | Отзывы о GuideObject                                     |
| photos                   | Фото, связанные с любыми сущностями                      |
| link_user_cars           | Связь пользователей и автомобилей (владелец, пассажир)   |
| link_event_participants  | Связь участников и событий                               |
| moderation_logs          | История действий модераторов с профилями пользователей   |
| activity_logs            | История выдачи активности между пользователями              |
| sessions                 | Сессии пользователей (авторизация, хранение токенов)   |

---

## Справочники

### ref_roles
| Поле         | Тип                | Описание                        |
|--------------|--------------------|---------------------------------|
| id           | BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY | Уникальный идентификатор роли   |
| code         | VARCHAR(32)        | Код роли (admin, moderator...)  |
| name         | VARCHAR(64)        | Название роли                   |
| description  | TEXT               | Описание                        |
| color        | VARCHAR(16)        | Цвет для UI (опционально)       |

### ref_statuses
| Поле         | Тип                | Описание                        |
|--------------|--------------------|---------------------------------|
| id           | BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY | Уникальный идентификатор статуса|
| code         | VARCHAR(32)        | Код статуса (active, pending...)|
| name         | VARCHAR(64)        | Название статуса                |
| description  | TEXT               | Описание                        |
| color        | VARCHAR(16)        | Цвет для UI (опционально)       |
| entity_type  | VARCHAR(32)        | Для какой сущности (user, car, event) |

### ref_event_types
| Поле         | Тип                | Описание                        |
|--------------|--------------------|---------------------------------|
| id           | BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY | Уникальный идентификатор типа   |
| code         | VARCHAR(32)        | Код типа (trip, meetup...)      |
| name         | VARCHAR(64)        | Название типа                   |
| description  | TEXT               | Описание                        |
| color        | VARCHAR(16)        | Цвет для UI (опционально)       |

### ref_guide_object_types
| Поле         | Тип                | Описание                        |
|--------------|--------------------|---------------------------------|
| id           | BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY | Уникальный идентификатор типа   |
| code         | VARCHAR(32)        | Код типа (service, cafe...)     |
| name         | VARCHAR(64)        | Название типа                   |
| description  | TEXT               | Описание                        |
| color        | VARCHAR(16)        | Цвет для UI (опционально)       |

### ref_guide_object_kinds
| Поле        | Тип                | Описание                        |
|-------------|--------------------|---------------------------------|
| id          | BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY | Уникальный идентификатор        |
| type_id     | BIGINT UNSIGNED    | FK на ref_guide_object_types.id (тип) |
| code        | VARCHAR(64)        | Код вида (например, "breakfast")      |
| name        | VARCHAR(64)        | Название вида (например, "Завтрак")   |
| description | TEXT               | Описание (опционально)          |

- type_id и name вместе формируют уникальную пару (один вид не может повторяться в рамках одного типа).
- Для быстрого поиска рекомендуется индексировать поле type_id.

---

### ref_car_brands
| Поле         | Тип                | Описание                        |
|--------------|--------------------|---------------------------------|
| id           | BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY | Уникальный идентификатор марки |
| brand        | VARCHAR(100)       | Марка автомобиля                |

---

## Пользователи (users)
| Поле            | Тип                | Описание                        |
|-----------------|--------------------|---------------------------------|
| id              | BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY | Уникальный идентификатор        |
| created_at      | TIMESTAMP          | Дата и время создания записи    |
| updated_at      | TIMESTAMP          | Дата и время обновления записи  |
| join_date       | DATE               | Дата вступления в клуб          |
| left_date       | DATE               | Дата выхода из клуба            |
| telegram_id     | BIGINT             | Telegram ID (уникальный)        |
| username        | VARCHAR(100)       | Username в Telegram             |
| first_name_tg   | VARCHAR(100)       | Имя из телеграмма               |
| last_name_tg    | VARCHAR(100)       | Фамилия из телеграмма           |
| first_name_app  | VARCHAR(100)       | Имя в приложении                |
| last_name_app   | VARCHAR(100)       | Фамилия в приложении            |
| birth_date      | DATE               | Дата рождения                   |
| city            | VARCHAR(100)       | Город проживания                |
| country         | VARCHAR(100)       | Страна проживания               |
| email           | VARCHAR(255)       | Email адрес                     |
| phone           | VARCHAR(20)        | Номер телефона                  |
| about           | TEXT               | О себе                          |
| notes           | TEXT               | Заметки (для администрации)     |
| role_id         | BIGINT UNSIGNED    | FK на ref_roles.id              |
| activity        | INTEGER            | Активность пользователя (default: 0, без максимума). Баллы начисляются за действия пользователя |
| weight          | INTEGER            | лайки от других пользователей   |
| messages_count  | INTEGER            | Количество сообщений в Telegram чате |
| last_activity   | TIMESTAMP          | Время последней активности пользователя (обновляется при каждом действии) |
| telegram_photo_url  | VARCHAR(255)       | Ссылка на аватар пользователя из Telegram                                 |
| host_user_id    | BIGINT UNSIGNED    | Основной гость                  |
| referrer_id     | BIGINT UNSIGNED    | Реферер                         |

> Фото пользователя (аватар) определяется как запись с максимальным id в таблице photos с entity_type = 'user' и entity_id = users.id

---

### user_locations
| Поле        | Тип                | Описание                        |
|-------------|--------------------|---------------------------------|
| id          | BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY | Уникальный идентификатор        |
| user_id     | BIGINT UNSIGNED    | FK на users.id                  |
| latitude    | DECIMAL(9,6)       | Широта                          |
| longitude   | DECIMAL(9,6)       | Долгота                         |
| updated_at  | TIMESTAMP          | Время последнего обновления     |

- При каждом обновлении координат пользователя строка обновляется.
- Для отображения на карте используются только записи, обновлённые не более 60 минут назад.

---

## Автомобили (cars)
| Поле              | Тип                | Описание                        |
|-------------------|--------------------|---------------------------------|
| id                | BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY | Уникальный идентификатор        |
| created_at        | TIMESTAMP          | Дата и время создания записи    |
| updated_at        | TIMESTAMP          | Дата и время обновления записи  |
| car_brand_id      | BIGINT UNSIGNED    | FK на ref_car_brands.id         |
| model             | VARCHAR(100)       | Модель автомобиля (ручной ввод) |
| engine_power      | INTEGER            | Мощность двигателя (л.с.)       |
| engine_volume     | DECIMAL(3,1)       | Объём двигателя (л)             |
| color             | VARCHAR(50)        | Цвет автомобиля                 |
| year              | INTEGER            | Год выпуска                     |
| reg_number        | VARCHAR(20)        | Регистрационный номер           |
| show_reg_number   | BOOLEAN            | Показывать ли номер публично    |
| vin               | VARCHAR(17)        | VIN номер                       |
| description       | TEXT               | Описание автомобиля             |
| create_user_id    | BIGINT UNSIGNED    | Кто создал запись               |
| owner_user_id     | BIGINT UNSIGNED    | Текущий владелец                |
| roof_type         | VARCHAR(50)        | Тип крыши                       |
| notes             | TEXT               | Заметки (для администрации)     |
| status_id         | BIGINT UNSIGNED    | FK на ref_statuses.id           |

> Фото автомобиля определяется как запись с максимальным id в таблице photos с entity_type = 'car' и entity_id = cars.id

---

## События (events)
| Поле         | Тип                | Описание                        |
|--------------|--------------------|---------------------------------|
| id           | BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY | Уникальный идентификатор        |
| created_at   | TIMESTAMP          | Дата и время создания записи    |
| updated_at   | TIMESTAMP          | Дата и время обновления записи  |
| event_date   | DATE               | Дата проведения                 |
| event_time   | TIME               | Время проведения                |
| event_type_id| BIGINT UNSIGNED    | FK на ref_event_types.id        |
| title        | VARCHAR(255)       | Название события                |
| description  | TEXT               | Описание события                |
| location     | TEXT               | Место проведения                |
| city         | VARCHAR(100)       | Город проведения                |
| price        | DECIMAL(10,2)      | Стоимость участия               |
| max_participants | INTEGER         | Максимальное количество участников |
| org_user_id| BIGINT UNSIGNED    | Организатор события             |
| registration_type | VARCHAR(20)    | Тип регистрации (свободная/по приглашению/с подтверждением) |
| status_id    | BIGINT UNSIGNED    | FK на ref_statuses.id           |

> Фото события определяется как запись с максимальным id в таблице photos с entity_type = 'event' и entity_id = events.id

---

## Визитки/приглашения (business_cards)
| Поле              | Тип                | Описание                        |
|-------------------|--------------------|---------------------------------|
| id                | BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY | Уникальный идентификатор        |
| created_at        | TIMESTAMP          | Дата и время создания записи    |
| updated_at        | TIMESTAMP          | Дата и время обновления записи  |
| car_id            | BIGINT UNSIGNED    | Внешний ключ на замеченный автомобиль |
| location          | TEXT               | Где была оставлена визитка      |
| notes             | TEXT               | Заметки о машине/ситуации       |
| inviter_user_id | BIGINT UNSIGNED    | Кто оставил визитку             |

---

## GuideObject (guide_objects)
| Поле         | Тип                | Описание                        |
|--------------|--------------------|---------------------------------|
| id           | BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY | Уникальный идентификатор        |
| created_at   | TIMESTAMP          | Дата и время создания записи    |
| updated_at   | TIMESTAMP          | Дата и время обновления записи  |
| guide_object_type_id | BIGINT UNSIGNED  | FK на ref_guide_object_types.id|
| guide_object_kind_id | BIGINT UNSIGNED    | FK на ref_guide_object_kinds.id |
| name         | VARCHAR(255)       | Название                        |
| city         | VARCHAR(100)       | Город                           |
| address      | TEXT               | Адрес                           |
| website      | VARCHAR(255)       | Веб-сайт                        |
| phone        | VARCHAR(20)        | Телефон                         |
| Instagram    | VARCHAR(255)       | Ссылка на Instagram             |
| Telegram     | VARCHAR(255)       | Ссылка на Telegram              |
| Viber        | VARCHAR(255)       | Номер Viber                     |
| WhatsApp     | VARCHAR(255)       | Номер WhatsApp                  |
| description  | TEXT               | Описание                        |
| service_list | VARCHAR[]          | Список предоставляемых услуг    |
| price        | DECIMAL(10,2)      | Цена (если применимо)           |
| brand        | VARCHAR(100)       | Бренд (если применимо)          |
| add_user_id  | BIGINT UNSIGNED    | Кто добавил                     |
| status_id    | BIGINT UNSIGNED    | FK на ref_statuses.id           |

> Фото GuideObject определяется как запись с максимальным id в таблице photos с entity_type = 'guide_object' и entity_id = guide_objects.id

---

## Отзывы (reviews)
| Поле           | Тип                | Описание                        |
|----------------|--------------------|---------------------------------|
| id             | BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY | Уникальный идентификатор        |
| created_at     | TIMESTAMP          | Дата и время создания записи    |
| updated_at     | TIMESTAMP          | Дата и время обновления записи  |
| guide_object_id| BIGINT UNSIGNED    | Внешний ключ на guide_objects.id|
| rating         | INTEGER            | Оценка (1-5, устаревшее поле, для обратной совместимости) |
| quality_rating | TINYINT            | Оценка качества (1–10)          |
| speed_rating   | TINYINT            | Оценка скорости (1–10)          |
| price_rating   | TINYINT            | Оценка цены (1–10)              |
| feedback       | TEXT               | Текст отзыва                    |
| author_user_id | BIGINT UNSIGNED    | Автор отзыва                    |
| status_id      | BIGINT UNSIGNED    | FK на ref_statuses.id           |

> Фото к отзыву определяется как все записи в таблице photos с entity_type = 'review' и entity_id = reviews.id

---

## Фото (photos)
| Поле         | Тип                | Описание                                  |
|--------------|--------------------|-------------------------------------------|
| id           | BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY | Уникальный идентификатор фото             |
| entity_type  | VARCHAR(32)        | Тип сущности (user, car, event, review...)|
| entity_id    | BIGINT UNSIGNED    | ID сущности, к которой привязано фото     |
| file_name    | VARCHAR(255)       | Имя файла                                 |
| url          | VARCHAR(255)       | Путь/URL к файлу                          |
| description  | TEXT               | Описание/подпись (опционально)            |
| uploaded_at  | TIMESTAMP          | Дата загрузки                             |
| uploaded_by  | BIGINT UNSIGNED    | Кто загрузил (user_id)                    |

> Главное фото определяется как запись с максимальным id для данной сущности (entity_type, entity_id)

---


### map_hints
| Поле        | Тип                | Описание                                 |
|-------------|--------------------|------------------------------------------|
| id          | BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY | Уникальный идентификатор метки           |
| user_id     | BIGINT UNSIGNED    | FK на users.id (кто поставил)            |
| type        | VARCHAR(32)        | Тип подсказки ("ГАИ", "ремонт", "пробка", "авария") |
| latitude    | DECIMAL(9,6)       | Широта                                   |
| longitude   | DECIMAL(9,6)       | Долгота                                  |
| created_at  | TIMESTAMP          | Время создания                           |
| expires_at  | TIMESTAMP          | Время автоматического удаления           |
| active      | BOOLEAN            | Активна ли метка (true/false)            |
| removed_by  | BIGINT UNSIGNED    | FK на users.id (кто удалил, если удалено вручную) |
| removed_at  | TIMESTAMP          | Время удаления (если удалено вручную)    |

>  Метка видна всем на карте в течение 1 часа после создания, затем автоматически становится неактивной.
>  Автор метки или модератор/админ/root может удалить метку вручную.
>  В базе сохраняется информация о том, кто поставил и кто удалил метку. 


---

## moderation_logs

**Назначение:**
Таблица для хранения истории всех действий модераторов с профилями пользователей (активация, отклонение, блокировка и т.д.). Обеспечивает прозрачность, аудит и анализ работы модерации.

**Поля:**
| Поле         | Тип            | Описание                                              |
|--------------|----------------|-------------------------------------------------------|
| id           | BIGINT, PK     | Уникальный идентификатор записи                       |
| user_id      | BIGINT, FK     | ID пользователя, чей профиль модерируется             |
| moderator_id | BIGINT, FK     | ID модератора, выполнившего действие                  |
| action       | ENUM           | Тип действия: 'activate', 'decline', 'block', 'unblock', 'edit' |
| reason       | VARCHAR(255)   | Причина (если применимо, например, при отклонении)    |
| created_at   | DATETIME       | Дата и время действия                                 |

**Связи:**
- user_id — внешний ключ на users(id)
- moderator_id — внешний ключ на users(id)

**Индексы:**
- idx_user_id (user_id)
- idx_moderator_id (moderator_id)
- idx_action (action)
- idx_created_at (created_at)

**Особенности:**
- Все действия фиксируются для истории и аудита
- reason обязателен для отклонения/блокировки
- Таблица расширяема (можно добавить новые типы действий)

---

## activity_logs

**Назначение:**
Таблица для хранения истории выдачи активности между пользователями. Используется для контроля лимитов (1 раз в сутки одному человеку, максимум N активностей в день) и аудита.

**Поля:**
| Поле         | Тип                | Описание                                 |
|--------------|--------------------|------------------------------------------|
| id           | BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY | Уникальный идентификатор записи          |
| from_user_id | BIGINT UNSIGNED    | FK на users.id (кто поставил активность)    |
| to_user_id   | BIGINT UNSIGNED    | FK на users.id (кому поставлена активность)  |
| date         | DATE               | Дата постановки активности (без времени)    |
| created_at   | DATETIME           | Дата и время постановки активности          |

**Связи:**
- from_user_id — внешний ключ на users(id)
- to_user_id — внешний ключ на users(id)

**Индексы:**
- idx_from_user_id (from_user_id)
- idx_to_user_id (to_user_id)
- idx_date (date)
- idx_created_at (created_at)
- UNIQUE (from_user_id, to_user_id, date) — чтобы нельзя было поставить активность одному человеку более 1 раза в сутки

**Особенности:**
- Используется для контроля лимитов и истории
- Позволяет быстро проверить, ставил ли пользователь активность сегодня и сколько всего активностей раздал за сутки
- created_at хранит точное время, date — только дату для уникальности

---

### sessions
| Поле           | Тип                | Описание                                              |
|----------------|--------------------|-------------------------------------------------------|
| id             | BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY | Уникальный идентификатор сессии                      |
| user_id        | BIGINT UNSIGNED    | FK на users.id — пользователь, которому принадлежит сессия |
| session_token  | VARCHAR(255)       | Уникальный токен сессии (используется для авторизации)|
| telegram_data  | JSON               | Данные Telegram (если авторизация через Telegram)     |
| created_at     | TIMESTAMP          | Дата и время создания сессии                          |
| expires_at     | TIMESTAMP          | Дата и время истечения сессии (обычно +30 минут)      |
| is_active      | TINYINT(1)         | Активна ли сессия (1 — да, 0 — нет)                   |

**Индексы:**
- PRIMARY KEY (id)
- INDEX user_id (user_id)
- INDEX session_token (session_token)
- INDEX expires_at (expires_at)
- INDEX is_active (is_active)

**Пояснения:**
- session_token — используется для проверки авторизации пользователя в каждом защищённом эндпоинте.
- expires_at — по истечении этого времени сессия считается недействительной.
- is_active — позволяет деактивировать сессию вручную (например, при выходе пользователя).
- telegram_data — хранит дополнительные данные Telegram, если сессия создана через Telegram WebApp.

---

## Таблицы связей

### link_user_cars
| Поле    | Тип                | Описание                        |
|---------|--------------------|---------------------------------|
| user_id | BIGINT UNSIGNED    | FK на users.id                  |
| car_id  | BIGINT UNSIGNED    | FK на cars.id                   |
| role_id | BIGINT UNSIGNED    | FK на ref_roles.id              |
| PK      | (user_id, car_id, role_id) | Композитный ключ           |

### link_event_participants
| Поле        | Тип                | Описание                                  |
|-------------|--------------------|-------------------------------------------|
| event_id    | BIGINT UNSIGNED    | FK на events.id                           |
| user_id     | BIGINT UNSIGNED    | FK на users.id                            |
| confidence  | VARCHAR(20)        | Уверенность участия: 'yes', 'maybe', 'no' |
| plus_one    | BOOLEAN            | Будет ли +1 (например, жена/друг)         |
| created_at  | TIMESTAMP          | Дата добавления в участники               |
| PK          | (event_id, user_id)| Композитный ключ                          | 

---

## Внешние ключи и связи между таблицами

### ref_roles
- Не содержит внешних ключей

### ref_statuses
- Не содержит внешних ключей

### ref_event_types
- Не содержит внешних ключей

### ref_guide_object_types
- Не содержит внешних ключей

### ref_car_brands
- PK: id
- INDEX: brand

### users
| Поле      | Связь                        |
|-----------|------------------------------|
| role_id   | FK → ref_roles.id            |

### cars
| Поле            | Связь                        |
|-----------------|------------------------------|
| car_brand_id    | FK → ref_car_brands.id       |
| create_user_id  | FK → users.id                |
| owner_user_id   | FK → users.id                |
| status_id       | FK → ref_statuses.id         |

### events
| Поле            | Связь                        |
|-----------------|------------------------------|
| event_type_id   | FK → ref_event_types.id      |
| org_user_id   | FK → users.id                |
| status_id       | FK → ref_statuses.id         |

### business_cards
| Поле              | Связь                        |
|-------------------|------------------------------|
| car_id            | FK → cars.id                 |
| inviter_user_id | FK → users.id                |

### guide_objects
| Поле            | Связь                        |
|-----------------|------------------------------|
| guide_object_type_id | FK → ref_guide_object_types.id |
| guide_object_kind_id | FK → ref_guide_object_kinds.id|
| add_user_id     | FK → users.id                |
| status_id       | FK → ref_statuses.id         |

### reviews
| Поле            | Связь                        |
|-----------------|------------------------------|
| guide_object_id | FK → guide_objects.id        |
| author_user_id  | FK → users.id                |
| status_id       | FK → ref_statuses.id         |

### photos
| Поле         | Связь                        |
|--------------|------------------------------|
| entity_type  | (логическая связь, не FK)    |
| entity_id    | (логическая связь, не FK)    |
| uploaded_by  | FK → users.id                | 

### user_locations
| Поле      | Связь                        |
|-----------|------------------------------|
| user_id   | FK → users.id                |

### map_hints
| Поле        | Связь                        |
|-------------|------------------------------|
| user_id     | FK → users.id                |
| removed_by  | FK → users.id (опционально)  |

### moderation_logs
| Поле        | Связь                        |
|-------------|------------------------------|
| user_id     | FK → users.id                |
| moderator_id| FK → users.id                |

### activity_logs
| Поле         | Связь                        |
|--------------|------------------------------|
| from_user_id | FK → users.id                |
| to_user_id   | FK → users.id                |

### ref_guide_object_kinds
| Поле    | Связь                        |
|---------|------------------------------|
| type_id | FK → ref_guide_object_types.id|

---

## Индексы и уникальные ограничения

### ref_roles
- PK: id
- UNIQUE: code

### ref_statuses
- PK: id
- UNIQUE: code, (entity_type, code)

### ref_event_types
- PK: id
- UNIQUE: code

### ref_guide_object_types
- PK: id
- UNIQUE: code

### ref_car_brands
- PK: id
- INDEX: brand

### users
- PK: id
- UNIQUE: telegram_id, email, phone
- INDEX: role_id

### cars
- PK: id
- INDEX: car_brand_id, create_user_id, owner_user_id, status_id
- UNIQUE: vin, reg_number

### events
- PK: id
- INDEX: event_type_id, org_user_id, status_id, event_date

### business_cards
- PK: id
- INDEX: car_id, inviter_user_id

### guide_objects
- PK: id
- INDEX: guide_object_type_id, guide_object_kind_id, add_user_id, status_id, city
- UNIQUE: (name, city)

### reviews
- PK: id
- INDEX: guide_object_id, author_user_id, status_id
- UNIQUE: (guide_object_id, author_user_id)

### photos
- PK: id
- INDEX: (entity_type, entity_id), uploaded_by

### link_user_cars
- PK: (user_id, car_id, role_id)
- INDEX: user_id, car_id, role_id

### link_event_participants
- PK: (event_id, user_id)
- INDEX: event_id, user_id 

### user_locations
- PK: id
- INDEX: user_id, updated_at

### map_hints
- PK: id
- INDEX: user_id, type, created_at, expires_at, active
- INDEX: removed_by

### moderation_logs
- PK: id
- INDEX: user_id, moderator_id, action, created_at

### activity_logs
- PK: id
- INDEX: from_user_id, to_user_id, created_at
- UNIQUE: (from_user_id, to_user_id, date)

### ref_guide_object_kinds
- PK: id
- UNIQUE: (type_id, name)
- INDEX: type_id

---

## Каталоги (catalogs)

В проекте используются отдельные справочные таблицы (каталоги), которые обеспечивают единые списки для выбора, фильтрации и валидации данных в основных сущностях:

- **car_brands** — справочник марок автомобилей
- **event_types** — типы мероприятий
- **guide_object_kinds** — виды гид-объектов
- **guide_object_types** — типы гид-объектов
- **roles** — роли пользователей
- **statuses** — статусы сущностей

> Каталоги связаны с основными таблицами через внешние ключи (см. DATABASE_RELATIONS.md). Используются для выпадающих списков, фильтрации, валидации и отображения данных во всех модулях системы.

## ER-диаграмма структуры базы данных

> Визуализация актуальной структуры теперь находится в docs/DATABASE_RELATIONS.md (Mermaid-диаграмма). 

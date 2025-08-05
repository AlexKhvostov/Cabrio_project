# 📦 Справочник по моделям backend CabrioRide

В этом файле собраны описания всех моделей, используемых в backend. Для каждой модели указано назначение, ключевые поля, связи с другими сущностями и примеры использования.

---

## User
- **Назначение:** Пользователь платформы CabrioRide (авторизация, профиль, связи с авто, событиями, фото и т.д.)
- **Ключевые поля:** id, telegram_id, username, role_id, city, email, created_at, updated_at
- **Связи:** Role, Cars (link_user_cars), Events (link_event_participants), Photos, Sessions
- **Пример:** `$user = User::findById(1);`

## Car
- **Назначение:** Автомобиль участника клуба
- **Ключевые поля:** id, car_brand_id, model, color, year, owner_user_id, status_id, created_at, updated_at
- **Связи:** CarBrand, Owner (User), Status, Users (link_user_cars), Photos
- **Пример:** `$car = Car::findById(1);`

## Photo
- **Назначение:** Фото, связанное с любой сущностью (user, car, event, review и т.д.)
- **Ключевые поля:** id, entity_type, entity_id, file_name, url, photo_type, description, uploaded_at, uploaded_by
- **Связи:** User, Car, Event, Review и др. (логическая связь), UploadedBy (User)
- **Пример:** `$photo = Photo::findById(1);`

## Event
- **Назначение:** Событие/мероприятие клуба
- **Ключевые поля:** id, event_type_id, title, description, event_date, event_time, city, org_user_id, status_id, created_at, updated_at
- **Связи:** EventType, Organizer (User), Status, Participants (link_event_participants), Photos
- **Пример:** `$event = Event::findById(1);`

## BusinessCard
- **Назначение:** Визитка/приглашение, оставленная участником
- **Ключевые поля:** id, car_id, location, notes, inviter_user_id, created_at, updated_at
- **Связи:** Car, Inviter (User)
- **Пример:** `$card = BusinessCard::findById(1);`

## GuideObject
- **Назначение:** Гид-объект (место, сервис, точка интереса)
- **Ключевые поля:** id, guide_object_type_id, guide_object_kind_id, name, city, address, website, phone, description, add_user_id, status_id, created_at, updated_at
- **Связи:** Type, Kind, Author (User), Status, Photos, Reviews
- **Пример:** `$obj = GuideObject::findById(1);`

## Review
- **Назначение:** Отзыв пользователя о гид-объекте
- **Ключевые поля:** id, guide_object_id, quality_rating, speed_rating, price_rating, feedback, author_user_id, status_id, created_at, updated_at
- **Связи:** GuideObject, Author (User), Status, Photos
- **Пример:** `$review = Review::findById(1);`

## MapHint
- **Назначение:** Подсказка/метка на карте (ГАИ, ремонт, пробка и т.д.)
- **Ключевые поля:** id, user_id, type, latitude, longitude, created_at, expires_at, active, removed_by, removed_at
- **Связи:** User, RemovedBy (User)
- **Пример:** `$hint = MapHint::findById(1);`

## ActivityLog
- **Назначение:** Запись о выдаче активности между пользователями
- **Ключевые поля:** id, from_user_id, to_user_id, date, created_at
- **Связи:** FromUser (User), ToUser (User)
- **Пример:** `$log = ActivityLog::findById(1);`

## Session
- **Назначение:** Сессия пользователя (авторизация, хранение токена)
- **Ключевые поля:** id, user_id, session_token, telegram_data, created_at, expires_at, is_active
- **Связи:** User
- **Пример:** `$session = Session::findById(1);`

## LinkUserCar
- **Назначение:** Связь пользователя и автомобиля (владелец, пассажир и т.д.)
- **Ключевые поля:** user_id, car_id, role_id
- **Связи:** User, Car, Role
- **Пример:** `$link = LinkUserCar::find($user_id, $car_id);`

## LinkEventParticipant
- **Назначение:** Связь участника и события (статус участия, +1 и т.д.)
- **Ключевые поля:** event_id, user_id, confidence, plus_one, created_at
- **Связи:** Event, User
- **Пример:** `$link = LinkEventParticipant::find($event_id, $user_id);`

## ModerationLog
- **Назначение:** История действий модераторов
- **Ключевые поля:** id, user_id, moderator_id, action, reason, created_at
- **Связи:** User, Moderator (User)
- **Пример:** `$log = ModerationLog::findById(1);`

## Role
- **Назначение:** Роль пользователя (member, moderator, admin и т.д.)
- **Ключевые поля:** id, code, name, description, color
- **Связи:** Users, LinkUserCar
- **Пример:** `$role = Role::findById(1);`

## Status
- **Назначение:** Статус сущности (active, pending, blocked и т.д.)
- **Ключевые поля:** id, code, name, description, color, entity_type
- **Связи:** Cars, Events, GuideObjects, Reviews
- **Пример:** `$status = Status::findById(1);`

## EventType
- **Назначение:** Тип события (trip, meetup и т.д.)
- **Ключевые поля:** id, code, name, description, color
- **Связи:** Events
- **Пример:** `$type = EventType::findById(1);`

## GuideObjectType
- **Назначение:** Тип гид-объекта (service, cafe и т.д.)
- **Ключевые поля:** id, code, name, description, color
- **Связи:** GuideObjects, GuideObjectKinds
- **Пример:** `$type = GuideObjectType::findById(1);`

## GuideObjectKind
- **Назначение:** Вид гид-объекта (например, "breakfast" для кафе)
- **Ключевые поля:** id, type_id, code, name, description
- **Связи:** GuideObjectType, GuideObjects
- **Пример:** `$kind = GuideObjectKind::findById(1);`

## CarBrand
- **Назначение:** Марка автомобиля (BMW, Mercedes и т.д.)
- **Ключевые поля:** id, brand
- **Связи:** Cars
- **Пример:** `$brand = CarBrand::findById(1);` 
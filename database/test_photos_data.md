# 📸 Тестовые данные для таблицы photos

## 📋 Описание таблицы photos

Согласно схеме базы данных, таблица `photos` имеет следующую структуру:

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

## 🎯 Правила для тестовых данных

### Типы сущностей (entity_type):
- `user` - аватары пользователей
- `car` - фото автомобилей
- `event` - фото событий
- `guide_object` - фото гид-объектов
- `review` - фото к отзывам

### Главное фото:
- Определяется как запись с максимальным id для данной сущности (entity_type, entity_id)
- Для каждой сущности может быть только одно главное фото

## 👥 Аватары пользователей (entity_type = 'user')

| ID | entity_id | file_name | url | description | uploaded_by |
|----|-----------|-----------|-----|-------------|-------------|
| 1 | 1 | lex_avatar.jpg | /uploads/avatars/lex_avatar.jpg | Аватар Lex - основателя клуба | 1 |
| 2 | 2 | ivan_avatar.jpg | /uploads/avatars/ivan_avatar.jpg | Аватар Ивана - модератора | 2 |
| 3 | 3 | maria_avatar.jpg | /uploads/avatars/maria_avatar.jpg | Аватар Марии | 3 |
| 4 | 4 | dmitry_avatar.jpg | /uploads/avatars/dmitry_avatar.jpg | Аватар Дмитрия | 4 |
| 5 | 5 | anna_avatar.jpg | /uploads/avatars/anna_avatar.jpg | Аватар Анны | 5 |
| 6 | 6 | sergey_avatar.jpg | /uploads/avatars/sergey_avatar.jpg | Аватар Сергея | 6 |
| 7 | 7 | elena_avatar.jpg | /uploads/avatars/elena_avatar.jpg | Аватар Елены | 7 |
| 8 | 8 | pavel_avatar.jpg | /uploads/avatars/pavel_avatar.jpg | Аватар Павла | 8 |

## 🚗 Фото автомобилей (entity_type = 'car')

| ID | entity_id | file_name | url | description | uploaded_by |
|----|-----------|-----------|-----|-------------|-------------|
| 9 | 1 | bmw_3series_front.jpg | /uploads/cars/bmw_3series_front.jpg | BMW 3 Series - вид спереди | 1 |
| 10 | 1 | bmw_3series_side.jpg | /uploads/cars/bmw_3series_side.jpg | BMW 3 Series - вид сбоку | 1 |
| 11 | 1 | bmw_3series_interior.jpg | /uploads/cars/bmw_3series_interior.jpg | BMW 3 Series - салон | 1 |
| 12 | 2 | mercedes_slk_front.jpg | /uploads/cars/mercedes_slk_front.jpg | Mercedes SLK - вид спереди | 2 |
| 13 | 2 | mercedes_slk_top.jpg | /uploads/cars/mercedes_slk_top.jpg | Mercedes SLK - с открытой крышей | 2 |
| 14 | 3 | audi_a3_front.jpg | /uploads/cars/audi_a3_front.jpg | Audi A3 - вид спереди | 3 |
| 15 | 3 | audi_a3_side.jpg | /uploads/cars/audi_a3_side.jpg | Audi A3 - вид сбоку | 3 |
| 16 | 4 | porsche_boxster_front.jpg | /uploads/cars/porsche_boxster_front.jpg | Porsche Boxster - вид спереди | 4 |
| 17 | 4 | porsche_boxster_side.jpg | /uploads/cars/porsche_boxster_side.jpg | Porsche Boxster - вид сбоку | 4 |
| 18 | 5 | mazda_mx5_front.jpg | /uploads/cars/mazda_mx5_front.jpg | Mazda MX-5 - вид спереди | 5 |
| 19 | 5 | mazda_mx5_top.jpg | /uploads/cars/mazda_mx5_top.jpg | Mazda MX-5 - с открытой крышей | 5 |
| 20 | 6 | vw_beetle_front.jpg | /uploads/cars/vw_beetle_front.jpg | Volkswagen Beetle - вид спереди | 6 |
| 21 | 6 | vw_beetle_side.jpg | /uploads/cars/vw_beetle_side.jpg | Volkswagen Beetle - вид сбоку | 6 |

## 📅 Фото событий (entity_type = 'event')

| ID | entity_id | file_name | url | description | uploaded_by |
|----|-----------|-----------|-----|-------------|-------------|
| 22 | 1 | spring_meet_2024.jpg | /uploads/events/spring_meet_2024.jpg | Весенняя встреча 2024 - общий вид | 1 |
| 23 | 1 | spring_meet_cars.jpg | /uploads/events/spring_meet_cars.jpg | Весенняя встреча - кабриолеты | 1 |
| 24 | 2 | summer_picnic_2024.jpg | /uploads/events/summer_picnic_2024.jpg | Летний пикник - место проведения | 2 |
| 25 | 2 | summer_picnic_bbq.jpg | /uploads/events/summer_picnic_bbq.jpg | Летний пикник - барбекю | 2 |
| 26 | 3 | autumn_tour_start.jpg | /uploads/events/autumn_tour_start.jpg | Осенний тур - старт | 3 |
| 27 | 4 | winter_meet_2024.jpg | /uploads/events/winter_meet_2024.jpg | Зимняя встреча - помещение | 1 |
| 28 | 5 | photosession_2024.jpg | /uploads/events/photosession_2024.jpg | Фотосессия - процесс съемки | 5 |

## 🏪 Фото гид-объектов (entity_type = 'guide_object')

| ID | entity_id | file_name | url | description | uploaded_by |
|----|-----------|-----------|-----|-------------|-------------|
| 29 | 1 | cabrio_service_exterior.jpg | /uploads/guide_objects/cabrio_service_exterior.jpg | Автосервис "Кабриолет" - фасад | 1 |
| 30 | 1 | cabrio_service_interior.jpg | /uploads/guide_objects/cabrio_service_interior.jpg | Автосервис "Кабриолет" - мастерская | 1 |
| 31 | 2 | wind_cafe_exterior.jpg | /uploads/guide_objects/wind_cafe_exterior.jpg | Кафе "Ветер в волосах" - фасад | 2 |
| 32 | 2 | wind_cafe_interior.jpg | /uploads/guide_objects/wind_cafe_interior.jpg | Кафе "Ветер в волосах" - интерьер | 2 |
| 33 | 3 | blisk_wash_exterior.jpg | /uploads/guide_objects/blisk_wash_exterior.jpg | Автомойка "Блеск" - фасад | 3 |
| 34 | 4 | cabrio_hotel_exterior.jpg | /uploads/guide_objects/cabrio_hotel_exterior.jpg | Отель "Кабриолет" - фасад | 4 |
| 35 | 4 | cabrio_hotel_room.jpg | /uploads/guide_objects/cabrio_hotel_room.jpg | Отель "Кабриолет" - номер | 4 |
| 36 | 5 | express_fuel_station.jpg | /uploads/guide_objects/express_fuel_station.jpg | Заправка "Экспресс" - АЗС | 5 |

## ⭐ Фото к отзывам (entity_type = 'review')

| ID | entity_id | file_name | url | description | uploaded_by |
|----|-----------|-----------|-----|-------------|-------------|
| 37 | 1 | review_cabrio_service_work.jpg | /uploads/reviews/review_cabrio_service_work.jpg | Работа автосервиса "Кабриолет" | 1 |
| 38 | 1 | review_cabrio_service_result.jpg | /uploads/reviews/review_cabrio_service_result.jpg | Результат ремонта крыши | 1 |
| 39 | 2 | review_wind_cafe_food.jpg | /uploads/reviews/review_wind_cafe_food.jpg | Блюда в кафе "Ветер в волосах" | 3 |
| 40 | 2 | review_wind_cafe_parking.jpg | /uploads/reviews/review_wind_cafe_parking.jpg | Парковка кабриолетов у кафе | 3 |
| 41 | 3 | review_blisk_wash_process.jpg | /uploads/reviews/review_blisk_wash_process.jpg | Процесс мойки кабриолета | 6 |
| 42 | 3 | review_blisk_wash_result.jpg | /uploads/reviews/review_blisk_wash_result.jpg | Результат мойки | 6 |

---

## 🚀 SQL запросы для добавления фото

### 1. Аватары пользователей

```sql
INSERT INTO photos (entity_type, entity_id, file_name, url, description, uploaded_by, uploaded_at) VALUES
('user', 1, 'lex_avatar.jpg', '/uploads/avatars/lex_avatar.jpg', 'Аватар Lex - основателя клуба', 1, NOW()),
('user', 2, 'ivan_avatar.jpg', '/uploads/avatars/ivan_avatar.jpg', 'Аватар Ивана - модератора', 2, NOW()),
('user', 3, 'maria_avatar.jpg', '/uploads/avatars/maria_avatar.jpg', 'Аватар Марии', 3, NOW()),
('user', 4, 'dmitry_avatar.jpg', '/uploads/avatars/dmitry_avatar.jpg', 'Аватар Дмитрия', 4, NOW()),
('user', 5, 'anna_avatar.jpg', '/uploads/avatars/anna_avatar.jpg', 'Аватар Анны', 5, NOW()),
('user', 6, 'sergey_avatar.jpg', '/uploads/avatars/sergey_avatar.jpg', 'Аватар Сергея', 6, NOW()),
('user', 7, 'elena_avatar.jpg', '/uploads/avatars/elena_avatar.jpg', 'Аватар Елены', 7, NOW()),
('user', 8, 'pavel_avatar.jpg', '/uploads/avatars/pavel_avatar.jpg', 'Аватар Павла', 8, NOW());
```

### 2. Фото автомобилей

```sql
INSERT INTO photos (entity_type, entity_id, file_name, url, description, uploaded_by, uploaded_at) VALUES
('car', 1, 'bmw_3series_front.jpg', '/uploads/cars/bmw_3series_front.jpg', 'BMW 3 Series - вид спереди', 1, NOW()),
('car', 1, 'bmw_3series_side.jpg', '/uploads/cars/bmw_3series_side.jpg', 'BMW 3 Series - вид сбоку', 1, NOW()),
('car', 1, 'bmw_3series_interior.jpg', '/uploads/cars/bmw_3series_interior.jpg', 'BMW 3 Series - салон', 1, NOW()),
('car', 2, 'mercedes_slk_front.jpg', '/uploads/cars/mercedes_slk_front.jpg', 'Mercedes SLK - вид спереди', 2, NOW()),
('car', 2, 'mercedes_slk_top.jpg', '/uploads/cars/mercedes_slk_top.jpg', 'Mercedes SLK - с открытой крышей', 2, NOW()),
('car', 3, 'audi_a3_front.jpg', '/uploads/cars/audi_a3_front.jpg', 'Audi A3 - вид спереди', 3, NOW()),
('car', 3, 'audi_a3_side.jpg', '/uploads/cars/audi_a3_side.jpg', 'Audi A3 - вид сбоку', 3, NOW()),
('car', 4, 'porsche_boxster_front.jpg', '/uploads/cars/porsche_boxster_front.jpg', 'Porsche Boxster - вид спереди', 4, NOW()),
('car', 4, 'porsche_boxster_side.jpg', '/uploads/cars/porsche_boxster_side.jpg', 'Porsche Boxster - вид сбоку', 4, NOW()),
('car', 5, 'mazda_mx5_front.jpg', '/uploads/cars/mazda_mx5_front.jpg', 'Mazda MX-5 - вид спереди', 5, NOW()),
('car', 5, 'mazda_mx5_top.jpg', '/uploads/cars/mazda_mx5_top.jpg', 'Mazda MX-5 - с открытой крышей', 5, NOW()),
('car', 6, 'vw_beetle_front.jpg', '/uploads/cars/vw_beetle_front.jpg', 'Volkswagen Beetle - вид спереди', 6, NOW()),
('car', 6, 'vw_beetle_side.jpg', '/uploads/cars/vw_beetle_side.jpg', 'Volkswagen Beetle - вид сбоку', 6, NOW());
```

### 3. Фото событий

```sql
INSERT INTO photos (entity_type, entity_id, file_name, url, description, uploaded_by, uploaded_at) VALUES
('event', 1, 'spring_meet_2024.jpg', '/uploads/events/spring_meet_2024.jpg', 'Весенняя встреча 2024 - общий вид', 1, NOW()),
('event', 1, 'spring_meet_cars.jpg', '/uploads/events/spring_meet_cars.jpg', 'Весенняя встреча - кабриолеты', 1, NOW()),
('event', 2, 'summer_picnic_2024.jpg', '/uploads/events/summer_picnic_2024.jpg', 'Летний пикник - место проведения', 2, NOW()),
('event', 2, 'summer_picnic_bbq.jpg', '/uploads/events/summer_picnic_bbq.jpg', 'Летний пикник - барбекю', 2, NOW()),
('event', 3, 'autumn_tour_start.jpg', '/uploads/events/autumn_tour_start.jpg', 'Осенний тур - старт', 3, NOW()),
('event', 4, 'winter_meet_2024.jpg', '/uploads/events/winter_meet_2024.jpg', 'Зимняя встреча - помещение', 1, NOW()),
('event', 5, 'photosession_2024.jpg', '/uploads/events/photosession_2024.jpg', 'Фотосессия - процесс съемки', 5, NOW());
```

### 4. Фото гид-объектов

```sql
INSERT INTO photos (entity_type, entity_id, file_name, url, description, uploaded_by, uploaded_at) VALUES
('guide_object', 1, 'cabrio_service_exterior.jpg', '/uploads/guide_objects/cabrio_service_exterior.jpg', 'Автосервис "Кабриолет" - фасад', 1, NOW()),
('guide_object', 1, 'cabrio_service_interior.jpg', '/uploads/guide_objects/cabrio_service_interior.jpg', 'Автосервис "Кабриолет" - мастерская', 1, NOW()),
('guide_object', 2, 'wind_cafe_exterior.jpg', '/uploads/guide_objects/wind_cafe_exterior.jpg', 'Кафе "Ветер в волосах" - фасад', 2, NOW()),
('guide_object', 2, 'wind_cafe_interior.jpg', '/uploads/guide_objects/wind_cafe_interior.jpg', 'Кафе "Ветер в волосах" - интерьер', 2, NOW()),
('guide_object', 3, 'blisk_wash_exterior.jpg', '/uploads/guide_objects/blisk_wash_exterior.jpg', 'Автомойка "Блеск" - фасад', 3, NOW()),
('guide_object', 4, 'cabrio_hotel_exterior.jpg', '/uploads/guide_objects/cabrio_hotel_exterior.jpg', 'Отель "Кабриолет" - фасад', 4, NOW()),
('guide_object', 4, 'cabrio_hotel_room.jpg', '/uploads/guide_objects/cabrio_hotel_room.jpg', 'Отель "Кабриолет" - номер', 4, NOW()),
('guide_object', 5, 'express_fuel_station.jpg', '/uploads/guide_objects/express_fuel_station.jpg', 'Заправка "Экспресс" - АЗС', 5, NOW());
```

### 5. Фото к отзывам

```sql
INSERT INTO photos (entity_type, entity_id, file_name, url, description, uploaded_by, uploaded_at) VALUES
('review', 1, 'review_cabrio_service_work.jpg', '/uploads/reviews/review_cabrio_service_work.jpg', 'Работа автосервиса "Кабриолет"', 1, NOW()),
('review', 1, 'review_cabrio_service_result.jpg', '/uploads/reviews/review_cabrio_service_result.jpg', 'Результат ремонта крыши', 1, NOW()),
('review', 2, 'review_wind_cafe_food.jpg', '/uploads/reviews/review_wind_cafe_food.jpg', 'Блюда в кафе "Ветер в волосах"', 3, NOW()),
('review', 2, 'review_wind_cafe_parking.jpg', '/uploads/reviews/review_wind_cafe_parking.jpg', 'Парковка кабриолетов у кафе', 3, NOW()),
('review', 3, 'review_blisk_wash_process.jpg', '/uploads/reviews/review_blisk_wash_process.jpg', 'Процесс мойки кабриолета', 6, NOW()),
('review', 3, 'review_blisk_wash_result.jpg', '/uploads/reviews/review_blisk_wash_result.jpg', 'Результат мойки', 6, NOW());
```

---

## 📊 Статистика фото

| Тип сущности | Количество фото | Описание |
|--------------|-----------------|----------|
| user | 8 | Аватары всех пользователей |
| car | 13 | Фото автомобилей (2-3 фото на машину) |
| event | 7 | Фото событий (1-2 фото на событие) |
| guide_object | 8 | Фото гид-объектов (1-2 фото на объект) |
| review | 6 | Фото к отзывам (2 фото на отзыв) |
| **Всего** | **42** | **Общее количество фото** |

## 🎯 Главные фото (максимальный ID для каждой сущности)

### Пользователи:
- **Lex (ID 1):** `lex_avatar.jpg` (ID 1)
- **Иван (ID 2):** `ivan_avatar.jpg` (ID 2)
- **Мария (ID 3):** `maria_avatar.jpg` (ID 3)
- **Дмитрий (ID 4):** `dmitry_avatar.jpg` (ID 4)
- **Анна (ID 5):** `anna_avatar.jpg` (ID 5)
- **Сергей (ID 6):** `sergey_avatar.jpg` (ID 6)
- **Елена (ID 7):** `elena_avatar.jpg` (ID 7)
- **Павел (ID 8):** `pavel_avatar.jpg` (ID 8)

### Автомобили:
- **BMW 3 Series (ID 1):** `bmw_3series_interior.jpg` (ID 11)
- **Mercedes SLK (ID 2):** `mercedes_slk_top.jpg` (ID 13)
- **Audi A3 (ID 3):** `audi_a3_side.jpg` (ID 15)
- **Porsche Boxster (ID 4):** `porsche_boxster_side.jpg` (ID 17)
- **Mazda MX-5 (ID 5):** `mazda_mx5_top.jpg` (ID 19)
- **Volkswagen Beetle (ID 6):** `vw_beetle_side.jpg` (ID 21)

### События:
- **Весенняя встреча (ID 1):** `spring_meet_cars.jpg` (ID 23)
- **Летний пикник (ID 2):** `summer_picnic_bbq.jpg` (ID 25)
- **Осенний тур (ID 3):** `autumn_tour_start.jpg` (ID 26)
- **Зимняя встреча (ID 4):** `winter_meet_2024.jpg` (ID 27)
- **Фотосессия (ID 5):** `photosession_2024.jpg` (ID 28)

### Гид-объекты:
- **Автосервис "Кабриолет" (ID 1):** `cabrio_service_interior.jpg` (ID 30)
- **Кафе "Ветер в волосах" (ID 2):** `wind_cafe_interior.jpg` (ID 32)
- **Автомойка "Блеск" (ID 3):** `blisk_wash_exterior.jpg` (ID 33)
- **Отель "Кабриолет" (ID 4):** `cabrio_hotel_room.jpg` (ID 35)
- **Заправка "Экспресс" (ID 5):** `express_fuel_station.jpg` (ID 36)

### Отзывы:
- **Отзыв о автосервисе (ID 1):** `review_cabrio_service_result.jpg` (ID 38)
- **Отзыв о кафе (ID 2):** `review_wind_cafe_parking.jpg` (ID 40)
- **Отзыв об автомойке (ID 3):** `review_blisk_wash_result.jpg` (ID 42)

---

## 📁 Структура папок для файлов

```
uploads/
├── avatars/           # Аватары пользователей
│   ├── lex_avatar.jpg
│   ├── ivan_avatar.jpg
│   └── ...
├── cars/              # Фото автомобилей
│   ├── bmw_3series_front.jpg
│   ├── mercedes_slk_front.jpg
│   └── ...
├── events/            # Фото событий
│   ├── spring_meet_2024.jpg
│   ├── summer_picnic_2024.jpg
│   └── ...
├── guide_objects/     # Фото гид-объектов
│   ├── cabrio_service_exterior.jpg
│   ├── wind_cafe_exterior.jpg
│   └── ...
└── reviews/           # Фото к отзывам
    ├── review_cabrio_service_work.jpg
    ├── review_wind_cafe_food.jpg
    └── ...
```

## ⚠️ Важные замечания

1. **Порядок выполнения:** Сначала добавьте основные данные (пользователи, автомобили, события, гид-объекты, отзывы), затем фото
2. **Главные фото:** Определяются как записи с максимальным ID для каждой сущности
3. **Пути к файлам:** Используются относительные пути от корня сайта
4. **Загрузчики:** Все фото загружены их владельцами (uploaded_by = entity_id для пользователей)
5. **Описания:** Содержат краткую информацию о содержимом фото 
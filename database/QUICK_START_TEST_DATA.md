# 🚀 Быстрый старт: Тестовые данные CabrioRide

## 📋 Что создано

### 📁 Файлы с тестовыми данными:
1. **`database/test_data_tables.md`** - Таблицы с данными и SQL запросы
2. **`database/scripts/004_insert_test_data.sql`** - Готовый SQL скрипт для основных данных
3. **`database/scripts/005_insert_test_photos.sql`** - Готовый SQL скрипт для фото
4. **`database/test_photos_data.md`** - Детальное описание тестовых фото
5. **`database/test_data.md`** - Детальное описание данных
6. **`database/README_TEST_DATA.md`** - Полная документация

## ⚡ Быстрое добавление данных

### Способ 1: Готовые SQL скрипты
```bash
# 1. Сначала основные данные
mysql -u your_user -p cabrioride < database/scripts/004_insert_test_data.sql

# 2. Затем фото
mysql -u your_user -p cabrioride < database/scripts/005_insert_test_photos.sql
```

### Способ 2: Копирование запросов
Скопируйте SQL запросы из `database/test_data_tables.md` и `database/test_photos_data.md` и выполните их в MySQL.

## 🎯 Ключевые данные для тестирования

### Автомобили для OCR:
| Номер | Марка | Владелец | Telegram ID | Главное фото |
|-------|-------|----------|-------------|--------------|
| 9588MI1 | BMW | Lex | 287536885 | bmw_3series_interior.jpg |
| 1234AB7 | Mercedes | Иван | 123456789 | mercedes_slk_top.jpg |
| 5678CD7 | Audi | Мария | 987654321 | audi_a3_side.jpg |
| 9999EF7 | Porsche | Дмитрий | 555666777 | porsche_boxster_side.jpg |
| 1111GH7 | Mazda | Анна | 111222333 | mazda_mx5_top.jpg |
| 2222IJ7 | Volkswagen | Сергей | 444555666 | vw_beetle_side.jpg |

### Пользователи по ролям:
- **Администратор:** Lex (287536885) - аватар: lex_avatar.jpg
- **Модератор:** Иван (123456789) - аватар: ivan_avatar.jpg
- **Участники:** Мария, Дмитрий, Анна, Сергей, Елена, Павел

### События с фото:
- **Весенняя встреча** (15.04.2024) - 4 участника, 2 фото
- **Летний пикник** (20.07.2024) - 3 участника, 2 фото
- **Осенний тур** (28.09.2024) - без участников, 1 фото
- **Зимняя встреча** (15.12.2024) - без участников, 1 фото
- **Фотосессия** (08.06.2024) - без участников, 1 фото

## 🧪 Тестирование API

### 1. Plate Search API
```bash
curl -X POST http://localhost/api/plate-search \
  -H "Content-Type: application/json" \
  -d '{"plate": "9588MI1"}'
```

**Ожидаемый результат:**
```json
{
  "success": true,
  "found": true,
  "status": "active"
}
```

### 2. OCR API
```bash
curl -X POST http://localhost/api/ocr \
  -F "image=@test_image.jpg"
```

**Ожидаемый результат:**
```json
{
  "success": true,
  "plate": "9588MI1",
  "region": "BY",
  "confidence": 0.95,
  "club_status": {
    "found": true,
    "status": "active"
  }
}
```

### 3. Photos API
```bash
# Получить главное фото пользователя
curl -X GET http://localhost/api/users/1/photo

# Получить главное фото автомобиля
curl -X GET http://localhost/api/cars/1/photo

# Получить все фото события
curl -X GET http://localhost/api/events/1/photos
```

## 📊 Статистика данных

| Таблица | Записей | Описание |
|---------|---------|----------|
| users | 8 | Пользователи с разными ролями |
| cars | 6 | Автомобили для тестирования OCR |
| events | 5 | События разных типов |
| guide_objects | 5 | Гид-объекты разных категорий |
| reviews | 3 | Отзывы с разными оценками |
| user_locations | 6 | Координаты пользователей |
| link_event_participants | 7 | Участие в событиях |
| link_user_cars | 6 | Связи пользователей и автомобилей |
| **photos** | **42** | **Тестовые фото всех типов** |

### Детализация фото:
| Тип сущности | Количество фото | Описание |
|--------------|-----------------|----------|
| user | 8 | Аватары всех пользователей |
| car | 13 | Фото автомобилей (2-3 фото на машину) |
| event | 7 | Фото событий (1-2 фото на событие) |
| guide_object | 8 | Фото гид-объектов (1-2 фото на объект) |
| review | 6 | Фото к отзывам (2 фото на отзыв) |

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

1. **Порядок выполнения:** 
   - Сначала `001_create_all_tables.sql` и `002_fill_catalogs.sql`
   - Затем `004_insert_test_data.sql` (основные данные)
   - Потом `005_insert_test_photos.sql` (фото)

2. **Telegram ID:** Lex имеет реальный ID `287536885`

3. **Номера автомобилей:** Все соответствуют белорусскому формату

4. **Статусы:** Все автомобили имеют статус "active"

5. **Главные фото:** Определяются как записи с максимальным ID для каждой сущности

6. **Пути к файлам:** Используются относительные пути от корня сайта

## 🔧 Проверка данных

После добавления данных выполните:
```sql
SELECT COUNT(*) as users_count FROM users;
SELECT COUNT(*) as cars_count FROM cars;
SELECT COUNT(*) as events_count FROM events;
SELECT COUNT(*) as photos_count FROM photos;
SELECT entity_type, COUNT(*) as count FROM photos GROUP BY entity_type;
```

## 🎉 Готово!

После выполнения скриптов у вас будет полный набор тестовых данных для:
- ✅ Тестирования Telegram бота
- ✅ Тестирования OCR API
- ✅ Тестирования Plate Search API
- ✅ Тестирования Photos API
- ✅ Тестирования всех функций системы
- ✅ Тестирования отображения фото в интерфейсе 
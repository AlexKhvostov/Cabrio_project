# 📸 Загрузка тестовых изображений

## 🎯 Что делает скрипт

Скрипт `download_test_images.php` автоматически:
1. Создает структуру папок `uploads/`
2. Скачивает 42 тестовых изображения с placeholder сервиса
3. Сохраняет их в соответствующие папки
4. Показывает прогресс и результат

## 🚀 Как запустить

### Способ 1: Через браузер
```bash
# Откройте в браузере:
http://localhost/app/database/scripts/download_test_images.php
```

### Способ 2: Через командную строку
```bash
# Перейдите в директорию проекта
cd C:\xampp\htdocs\app

# Запустите скрипт
php database/scripts/download_test_images.php
```

### Способ 3: Через PowerShell
```powershell
# Перейдите в директорию проекта
Set-Location C:\xampp\htdocs\app

# Запустите скрипт
php database/scripts/download_test_images.php
```

## 📁 Структура создаваемых папок

```
uploads/
├── avatars/           # 8 аватаров пользователей
│   ├── lex_avatar.jpg
│   ├── ivan_avatar.jpg
│   ├── maria_avatar.jpg
│   ├── dmitry_avatar.jpg
│   ├── anna_avatar.jpg
│   ├── sergey_avatar.jpg
│   ├── elena_avatar.jpg
│   └── pavel_avatar.jpg
├── cars/              # 13 фото автомобилей
│   ├── bmw_3series_front.jpg
│   ├── bmw_3series_side.jpg
│   ├── bmw_3series_interior.jpg
│   ├── mercedes_slk_front.jpg
│   ├── mercedes_slk_top.jpg
│   ├── audi_a3_front.jpg
│   ├── audi_a3_side.jpg
│   ├── porsche_boxster_front.jpg
│   ├── porsche_boxster_side.jpg
│   ├── mazda_mx5_front.jpg
│   ├── mazda_mx5_top.jpg
│   ├── vw_beetle_front.jpg
│   └── vw_beetle_side.jpg
├── events/            # 7 фото событий
│   ├── spring_meet_2024.jpg
│   ├── spring_meet_cars.jpg
│   ├── summer_picnic_2024.jpg
│   ├── summer_picnic_bbq.jpg
│   ├── autumn_tour_start.jpg
│   ├── winter_meet_2024.jpg
│   └── photosession_2024.jpg
├── guide_objects/     # 8 фото гид-объектов
│   ├── cabrio_service_exterior.jpg
│   ├── cabrio_service_interior.jpg
│   ├── wind_cafe_exterior.jpg
│   ├── wind_cafe_interior.jpg
│   ├── blisk_wash_exterior.jpg
│   ├── cabrio_hotel_exterior.jpg
│   ├── cabrio_hotel_room.jpg
│   └── express_fuel_station.jpg
└── reviews/           # 6 фото к отзывам
    ├── review_cabrio_service_work.jpg
    ├── review_cabrio_service_result.jpg
    ├── review_wind_cafe_food.jpg
    ├── review_wind_cafe_parking.jpg
    ├── review_blisk_wash_process.jpg
    └── review_blisk_wash_result.jpg
```

## 🎨 Характеристики изображений

### Аватары пользователей (200x200px)
- **Lex:** Синий фон с белым текстом
- **Иван:** Зеленый фон с белым текстом
- **Мария:** Оранжевый фон с белым текстом
- **Дмитрий:** Фиолетовый фон с белым текстом
- **Анна:** Бирюзовый фон с белым текстом
- **Сергей:** Красный фон с белым текстом
- **Елена:** Пурпурный фон с белым текстом
- **Павел:** Темно-зеленый фон с белым текстом

### Фото автомобилей (400x300px)
- **BMW:** Черный фон с белым текстом
- **Mercedes:** Серый фон с черным текстом
- **Audi:** Белый фон с черным текстом
- **Porsche:** Красный фон с белым текстом
- **Mazda:** Синий фон с белым текстом
- **Volkswagen:** Желтый фон с черным текстом

### Фото событий (600x400px)
- **Весенняя встреча:** Зеленый фон
- **Летний пикник:** Оранжевый фон
- **Осенний тур:** Коричневый фон
- **Зимняя встреча:** Серо-голубой фон
- **Фотосессия:** Фиолетовый фон

### Фото гид-объектов (500x300px)
- **Автосервис:** Синий фон
- **Кафе:** Оранжевый фон
- **Автомойка:** Голубой фон
- **Отель:** Зеленый фон
- **Заправка:** Желтый фон с черным текстом

### Фото к отзывам (400x300px)
- Соответствуют цветам гид-объектов

## ⚠️ Требования

1. **PHP:** Должен быть установлен и доступен
2. **Интернет:** Требуется подключение к интернету
3. **Права записи:** PHP должен иметь права на создание папок и файлов
4. **XAMPP:** Должен быть запущен Apache

## 🔧 Устранение проблем

### Ошибка "Permission denied"
```bash
# Дайте права на запись в папку проекта
chmod -R 755 uploads/
```

### Ошибка "Connection timeout"
- Проверьте подключение к интернету
- Попробуйте запустить скрипт позже

### Ошибка "PHP not found"
```bash
# Установите PHP или добавьте в PATH
# Для Windows: скачайте с https://windows.php.net/
```

## 📊 Ожидаемый результат

При успешном выполнении вы увидите:
```
🚀 Начинаем загрузку тестовых изображений...

✅ Создана директория: C:\xampp\htdocs\app\uploads
✅ Создана директория: C:\xampp\htdocs\app\uploads\avatars
✅ Загружено: avatars/lex_avatar.jpg
✅ Загружено: avatars/ivan_avatar.jpg
...
✅ Загружено: reviews/review_blisk_wash_result.jpg

📊 Результат загрузки:
✅ Успешно загружено: 42 из 42
❌ Ошибок: 0

🎉 Все изображения успешно загружены!
📁 Файлы сохранены в: C:\xampp\htdocs\app\uploads
```

## 🎯 После загрузки

1. **Проверьте файлы:**
```bash
# Посчитайте количество файлов
dir uploads /s | find "File(s)"
```

2. **Добавьте данные в БД:**
```bash
# Сначала основные данные
mysql -u your_user -p cabrioride < database/scripts/004_insert_test_data.sql

# Затем фото
mysql -u your_user -p cabrioride < database/scripts/005_insert_test_photos.sql
```

3. **Протестируйте API:**
```bash
# Проверьте отображение фото
curl -X GET http://localhost/api/users/1/photo
```

## 🎉 Готово!

После выполнения всех шагов у вас будет:
- ✅ 42 тестовых изображения в папке `uploads/`
- ✅ Данные в базе данных
- ✅ Готовность к тестированию всех функций с фото 
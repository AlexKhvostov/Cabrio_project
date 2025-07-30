# 🔍 OCR API - Распознавание номеров автомобилей

## 📋 Обзор

OCR API предоставляет функциональность для распознавания номеров автомобилей по фотографиям и их проверки в базе данных клуба CabrioRide.

## 🚀 Эндпоинты

### 1. `/api/ocr/recognize.php` - Распознавание номера
**Метод:** `POST`  
**Content-Type:** `multipart/form-data`  
**Роли доступа:** `all`

#### Параметры запроса:
- `image` (file) - Изображение автомобиля (JPG, PNG)

#### Пример запроса:
```bash
curl -X POST \
  -F "image=@car_photo.jpg" \
  http://localhost/app/backend/api/ocr/recognize.php
```

#### Ответ при успехе:
```json
{
  "success": true,
  "results": [
    {
      "plate": "0070MX7",
      "score": 0.95,
      "region": {
        "code": "by",
        "score": 0.884
      },
      "candidates": [
        {"score": 1, "plate": "0070MX7"},
        {"score": 0.864, "plate": "007omx7"}
      ]
    }
  ]
}
```

#### Ответ при ошибке:
```json
{
  "success": false,
  "error": "Описание ошибки"
}
```

---

### 2. `/api/ocr/check.php` - Проверка номера в БД
**Метод:** `POST`  
**Content-Type:** `application/x-www-form-urlencoded`  
**Роли доступа:** `all`

#### Параметры запроса:
- `plate` (string) - Номер автомобиля для проверки

#### Пример запроса:
```bash
curl -X POST \
  -d "plate=0070MX7" \
  http://localhost/app/backend/api/ocr/check.php
```

#### Ответ при найденном автомобиле:
```json
{
  "success": true,
  "found": true,
  "plate": "0070MX7",
  "status": "Активный",
  "message": "Автомобиль найден в базе клуба",
  "can_leave_card": true
}
```

#### Ответ при ненайденном автомобиле:
```json
{
  "success": true,
  "found": false,
  "plate": "0070MX7",
  "message": "Автомобиль с таким номером не найден в базе клуба",
  "can_leave_card": false
}
```

---

### 3. `/api/ocr/process.php` - Объединенный эндпоинт ⭐
**Метод:** `POST`  
**Content-Type:** `multipart/form-data`  
**Роли доступа:** `all`

#### Параметры запроса:
- `image` (file) - Изображение автомобиля (JPG, PNG)

#### Пример запроса:
```bash
curl -X POST \
  -F "image=@car_photo.jpg" \
  http://localhost/app/backend/api/ocr/process.php
```

#### Ответ при найденном автомобиле:
```json
{
  "success": true,
  "ocr_success": true,
  "found": true,
  "plate": "0070MX7",
  "confidence": 0.95,
  "status": "Активный",
  "message": "Автомобиль найден в базе клуба",
  "can_leave_card": true
}
```

#### Ответ при ненайденном автомобиле:
```json
{
  "success": true,
  "ocr_success": true,
  "found": false,
  "plate": "0070MX7",
  "confidence": 0.95,
  "message": "Автомобиль с таким номером не найден в базе клуба",
  "can_leave_card": false
}
```

#### Ответ при неудачном распознавании:
```json
{
  "success": true,
  "ocr_success": false,
  "message": "Не удалось распознать номер на изображении",
  "can_leave_card": false
}
```

## 🔧 Технические детали

### Внешние зависимости:
- **platerecognizer.com API** - для распознавания номеров
- **MySQL Database** - для проверки номеров в базе

### Конфигурация:
Все параметры берутся из `.env` файла:
```ini
# OCR API
OCR_TOKEN=your_platerecognizer_token

# Database
DB_HOST=localhost
DB_PORT=3306
DB_NAME=cabrio
DB_USER=root
DB_PASSWORD=
```

### Безопасность:
- ✅ **Безопасные ответы** - не раскрываются личные данные владельцев
- ✅ **Валидация файлов** - проверка типа и размера изображений
- ✅ **Обработка ошибок** - детальное логирование проблем
- ✅ **Защита от SQL-инъекций** - использование подготовленных запросов

## 🎯 Рекомендации по использованию

### Для Telegram бота:
```javascript
// Рекомендуется использовать объединенный эндпоинт
const response = await fetch('/api/ocr/process.php', {
  method: 'POST',
  body: formData
});

const result = await response.json();

if (result.success && result.ocr_success && result.found) {
  // Автомобиль найден - предложить оставить визитку
  bot.sendMessage(chatId, `✅ Найден автомобиль ${result.plate} (${result.status})`);
} else if (result.success && result.ocr_success && !result.found) {
  // Автомобиль не найден
  bot.sendMessage(chatId, `🔍 Автомобиль ${result.plate} не найден в базе`);
} else {
  // Ошибка распознавания
  bot.sendMessage(chatId, '❌ Не удалось распознать номер. Попробуйте еще раз.');
}
```

### Для веб-приложения:
```javascript
// Можно использовать отдельные эндпоинты для гибкости
const ocrResponse = await fetch('/api/ocr/recognize.php', {
  method: 'POST',
  body: formData
});

const ocrResult = await ocrResponse.json();

if (ocrResult.success && ocrResult.results.length > 0) {
  const plate = ocrResult.results[0].plate;
  
  const checkResponse = await fetch('/api/ocr/check.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `plate=${encodeURIComponent(plate)}`
  });
  
  const checkResult = await checkResponse.json();
  // Обработка результата...
}
```

## 🧪 Тестирование

### Тестовые страницы:
- `backend/_test/ocr/test.html` - тест отдельных эндпоинтов
- `backend/_test/ocr/process.html` - тест объединенного эндпоинта

### Тестовые изображения:
- `backend/0070mx7.jpg` - автомобиль с номером 0070MX7
- Другие изображения в папке `uploads/cars/`

## 📊 Логирование

Все эндпоинты ведут подробное логирование:
```php
error_log("=== OCR Process API ===");
error_log("Распознан номер: " . $recognizedPlate);
error_log("Автомобиль найден в БД: " . json_encode($response));
```

## 🔄 Схема работы

### Объединенный эндпоинт (`process.php`):
1. **Загрузка изображения** → валидация файла
2. **OCR распознавание** → отправка в platerecognizer.com
3. **Проверка в БД** → поиск номера в таблице `cars`
4. **Формирование ответа** → безопасный результат для пользователя

### Отдельные эндпоинты:
- `recognize.php` → только шаги 1-2
- `check.php` → только шаг 3

## ⚠️ Ограничения

- **Размер файла:** до 3MB
- **Формат:** JPG, PNG
- **Разрешение:** рекомендуется 1024×768
- **Ориентация:** портретная
- **Площадь автомобиля:** минимум 15% от изображения
- **Читаемость номера:** должна быть читаема человеком

## 🚀 Производительность

- **Время распознавания:** ~2-5 секунд
- **Время проверки в БД:** ~100-500ms
- **Общее время:** ~3-6 секунд для объединенного эндпоинта

## 🔗 Связанные документы

- [API Endpoints](../API_ENDPOINTS.md)
- [Database Schema](../../../docs/DATABASE_SCHEMA.md)
- [Telegram Bot Integration](../../../bot/README.md)
- [Environment Configuration](../../../docs/ENVIRONMENT.md) 
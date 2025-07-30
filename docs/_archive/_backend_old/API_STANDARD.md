# 📡 API Standard CabrioRide

## 🎯 Общий принцип
Все API endpoints используют единый формат запроса и ответа для обеспечения консистентности и простоты разработки.

## 🚫 Принцип "НЕ УСЛОЖНЕНИЯ"
> **КРИТИЧЕСКИ ВАЖНО:** В endpoints добавляем ТОЛЬКО то, что НАДО! Никаких лишних функций, валидаций или проверок без необходимости.

### Правила разработки endpoints:
- ✅ **Минимальная логика** - только основная бизнес-логика
- ✅ **Обязательные поля** - валидируем только критически важные
- ✅ **Простые ответы** - только нужные данные
- ✅ **Стандартные ошибки** - без избыточной детализации
- ❌ **НЕ добавляем** - лишние проверки, сложную валидацию, дополнительные поля
- ❌ **НЕ усложняем** - логику, ответы, обработку ошибок

### Примеры правильного подхода:
```php
// ✅ ПРАВИЛЬНО - только нужная логика
protected function process() {
    $regNumber = $this->requireField('reg_number');
    $regNumber = $this->validateRegNumber($regNumber);
    
    // Создаём авто
    $carId = $this->createCar($regNumber);
    
    return $this->success(['car_id' => $carId]);
}

// ❌ НЕПРАВИЛЬНО - лишние проверки
protected function process() {
    // Лишние проверки
    $this->validateUserPermissions();
    $this->checkRateLimits();
    $this->validateCarBrand();
    $this->checkInsurance();
    
    // Сложная логика
    $regNumber = $this->requireField('reg_number');
    $regNumber = $this->validateRegNumber($regNumber);
    $regNumber = $this->normalizeRegNumber($regNumber);
    $regNumber = $this->formatRegNumber($regNumber);
    
    // Избыточные данные в ответе
    return $this->success([
        'car_id' => $carId,
        'reg_number' => $regNumber,
        'created_at' => $timestamp,
        'user_info' => $userData,
        'car_details' => $carDetails,
        'insurance_status' => $insurance,
        'warranty_info' => $warranty
    ]);
}
```

## 🔐 Шаблон запроса

### Структура запроса:
```json
{
  "auth": {
    // Данные об авторе запроса (постоянные)
    "user_id": 123,
    "telegram_id": 287536885,
    "role": "member",
    "token": "abc123...",
    "session_id": "xyz789..."
  },
  "data": {
    // Специфичные данные для конкретного endpoint
    "reg_number": "А123БВ77",
    "model": "MX-5",
    "year": 2020
  }
}
```

### Поля auth (постоянные):
- `user_id` (int) - ID пользователя в нашей БД
- `telegram_id` (int) - ID пользователя в Telegram
- `role` (string) - роль пользователя (guest, member, admin)
- `token` (string, опционально) - токен сессии
- `session_id` (string, опционально) - ID сессии

### Поля data (специфичные):
Зависят от конкретного endpoint. Например:
- Для добавления авто: `reg_number`, `model`, `year`, `color`
- Для регистрации: `telegram_id`, `username`, `first_name`
- Для профиля: `user_id`

### Передача Telegram-профиля пользователя
> Если для endpoint требуется профиль Telegram, он всегда передаётся как объект `telegram_requestor_profile` внутри `data`.

#### Пример:
```json
{
  "auth": {
    "user_id": 123,
    "role": "member"
  },
  "data": {
    "telegram_requestor_profile": {
      "telegram_id": 287536885,
      "username": "lex",
      "first_name": "Lex",
      "last_name": "Smith",
      "telegram_photo_id": "AgACAgIAAxkBAA...",
      "language_code": "ru"
    }
  }
}
```
- **telegram_requestor_profile** — профиль Telegram-пользователя, который инициирует запрос (actor). Используется для всех операций, где требуется Telegram-контекст инициатора.

## ✅ Шаблон ответа

### Успешный ответ:
```json
{
  "success": true,
  "timestamp": "2024-01-15T10:30:00Z",
  "request_id": "req_123456",
  
  "auth": {
    "user_id": 123,
    "role": "member"
  },
  
  "result": {
    "message": "Автомобиль успешно добавлен",
    "data": {
      "car_id": 456,
      "reg_number": "А123БВ77"
    }
  },
  
  "error": null
}
```

### Ответ с ошибкой:
```json
{
  "success": false,
  "timestamp": "2024-01-15T10:30:00Z",
  "request_id": "req_123456",
  
  "auth": {
    "user_id": 123,
    "role": "member"
  },
  
  "result": null,
  
  "error": {
    "code": 400,
    "type": "VALIDATION_ERROR",
    "message": "Номер автомобиля обязателен",
    "details": {
      "field": "reg_number",
      "rule": "required"
    }
  }
}
```

## 📋 Общие поля ответа

### Обязательные поля:
- `success` (boolean) - результат операции
- `timestamp` (string) - время ответа в ISO 8601
- `request_id` (string) - уникальный ID запроса для трейсинга
- `auth` (object) - отражение авторизации
- `result` (object|null) - данные успешного ответа
- `error` (object|null) - данные ошибки

### Поля result (при успехе):
- `message` (string) - текстовое сообщение
- `data` (object) - данные ответа

### Поля error (при ошибке):
- `code` (int) - HTTP код ошибки
- `type` (string) - тип ошибки (VALIDATION_ERROR, AUTH_ERROR, etc.)
- `message` (string) - описание ошибки
- `details` (object, опционально) - детали ошибки

## 🔄 HTTP коды

- `200` - Успешный GET запрос
- `201` - Успешный POST запрос (создание)
- `400` - Ошибка валидации
- `401` - Не авторизован
- `403` - Нет прав доступа
- `404` - Ресурс не найден
- `422` - Ошибка бизнес-логики
- `500` - Внутренняя ошибка сервера

## 🎯 Примеры использования

### Добавление автомобиля:
```json
// Запрос
{
  "auth": {
    "user_id": 123,
    "role": "member"
  },
  "data": {
    "reg_number": "А123БВ77",
    "model": "MX-5",
    "year": 2020
  }
}

// Ответ
{
  "success": true,
  "timestamp": "2024-01-15T10:30:00Z",
  "request_id": "req_123456",
  "auth": {"user_id": 123, "role": "member"},
  "result": {
    "message": "Автомобиль успешно добавлен",
    "data": {
      "car_id": 456,
      "reg_number": "А123БВ77"
    }
  },
  "error": null
}
```

### Получение профиля:
```json
// Запрос
{
  "auth": {
    "user_id": 123,
    "role": "member"
  },
  "data": {
    "user_id": 456
  }
}

// Ответ
{
  "success": true,
  "timestamp": "2024-01-15T10:30:00Z",
  "request_id": "req_123457",
  "auth": {"user_id": 123, "role": "member"},
  "result": {
    "message": "Профиль получен",
    "data": {
      "id": 456,
      "username": "lex",
      "first_name": "Lex",
      "role": "member"
    }
  },
  "error": null
}
```

## 🛠️ Обработка на клиенте

```javascript
// Пример обработки ответа
fetch('/api/endpoint', {
  method: 'POST',
  headers: {'Content-Type': 'application/json'},
  body: JSON.stringify(requestData)
})
.then(response => response.json())
.then(data => {
  if (data.success) {
    // Обрабатываем успешный ответ
    console.log(data.result.message);
    console.log(data.result.data);
  } else {
    // Обрабатываем ошибку
    console.error(data.error.message);
    console.error(data.error.details);
  }
});
```

## 📝 Примечания

1. **Расширяемость**: Можно добавлять новые поля в auth и data
2. **Обратная совместимость**: Старые клиенты продолжают работать
3. **Трейсинг**: request_id для отслеживания запросов
4. **Временные метки**: timestamp для логирования
5. **Единообразие**: Все endpoints используют один формат
6. **Простота**: Только необходимая логика, без усложнений 

## 🧩 Оркестраторы (Orchestrator Endpoints)

### Что такое оркестратор?
- Оркестратор — это endpoint, который агрегирует несколько действий/endpoint-ов в одну бизнес-операцию.
- Пример: создание визитки с одновременным созданием машины, если её нет (add_full.php); объединённый OCR (process.php).

### Как реализовать оркестратор:
- Класс-наследник ApiHandler, как и обычный endpoint.
- Внутри process() вызываются другие endpoint-ы через HTTP (curl) с передачей auth/data.
- Все внутренние вызовы логируются (payload, ответ).
- Ответ оркестратора агрегирует результаты всех внутренних вызовов.
- Ошибки внутренних endpoint-ов корректно обрабатываются и возвращаются в едином формате.

### Пример (визиточный оркестратор):
```php
// ...
$carResult = $this->callInternalEndpoint('/backend/api/cars/add.php', [...]);
if (!$carResult['success']) { ... }
$cardResult = $this->callInternalEndpoint('/backend/api/business-cards/add_to_car.php', [...]);
if (!$cardResult['success']) { ... }
return $this->success([
  'car_created' => $carCreated,
  'car_result' => $carResult,
  'business_card' => $cardResult['result']['data'] ?? null
]);
```

### Пример (OCR оркестратор):
```php
$recognizeResult = $this->callInternalEndpoint('/backend/api/ocr/recognize.php', [...]);
if (!$recognizeResult['success']) { ... }
$checkResult = $this->callInternalEndpoint('/backend/api/ocr/check.php', [...]);
return $this->success([
  'ocr' => $recognizeResult,
  'check' => $checkResult
]);
```

### Требования к оркестраторам:
- Всегда отдельная тестовая страница (пример: backend/_test/ocr/process.html)
- Подробный комментарий в начале файла (что агрегирует, какие endpoint-ы вызывает)
- Логирование всех внутренних вызовов (payload, ответ)
- Корректная обработка ошибок (если внутренний endpoint не отвечает — возвращать ошибку с пояснением)
- В ответе — агрегированные данные всех шагов

### Чек-лист для оркестратора:
- [ ] Подключён config.php, Database.php, ApiHandler.php
- [ ] Наследование от ApiHandler
- [ ] Вызовы внутренних endpoint-ов только через HTTP (curl)
- [ ] Логирование всех внутренних вызовов
- [ ] Корректная обработка ошибок
- [ ] Актуальная тестовая страница
- [ ] Документация и структура соответствуют стандарту 
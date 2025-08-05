# 🔐 Telegram авторизация

> **Назначение:** Подробное руководство по работе с Telegram авторизацией  
> **Версия:** 1.0.0  
> **Последнее обновление:** 2024-01-01

---

## 🎯 **Обзор Telegram авторизации**

### **Принципы работы**
- Извлечение данных из различных источников (headers, JSON, FormData)
- Валидация подписи Telegram для безопасности
- Синхронизация пользователя с базой данных
- Создание/обновление сессии пользователя

### **Компоненты системы**
- **AuthMiddleware** — централизованная обработка
- **AuthHelper** — извлечение и валидация данных
- **SessionHelper** — управление сессиями
- **AppContext** — глобальный контекст

---

## 📤 **Источники данных Telegram**

### **1. Заголовки HTTP (Telegram WebApp)**
```http
X-Telegram-User-Id: 123456789
X-Telegram-First-Name: Иван
X-Telegram-Last-Name: Иванов
X-Telegram-Username: ivan
X-Telegram-Photo-URL: https://t.me/i/userpic/320/ivan.jpg
X-Telegram-Auth-Date: 1640995200
X-Telegram-Hash: abc123def456...
```

### **2. JSON тело запроса (Telegram Bot)**
```json
{
  "message": {
    "from": {
      "id": 123456789,
      "first_name": "Иван",
      "last_name": "Иванов",
      "username": "ivan",
      "photo_url": "https://t.me/i/userpic/320/ivan.jpg"
    }
  },
  "hash": "abc123def456..."
}
```

### **3. FormData (Telegram Bot)**
```http
POST /api/endpoint
Content-Type: multipart/form-data

telegram_id: 123456789
first_name: Иван
last_name: Иванов
username: ivan
photo_url: https://t.me/i/userpic/320/ivan.jpg
auth_date: 1640995200
hash: abc123def456...
```

### **4. GET параметры (для тестирования)**
```
GET /api/endpoint?telegram_id=123456789&first_name=Иван&username=ivan&hash=abc123...
```

---

## 🔍 **Извлечение данных (AuthHelper::extractTelegramData)**

### **Алгоритм извлечения**
1. **Проверка заголовков** — поиск Telegram данных в HTTP заголовках
2. **Проверка JSON тела** — извлечение из JSON запроса
3. **Проверка FormData** — извлечение из POST данных
4. **Проверка GET параметров** — извлечение из URL параметров

### **Код извлечения**
```php
public static function extractTelegramData()
{
    // 1. Пробуем извлечь из заголовков (Telegram WebApp)
    $telegramData = self::extractFromHeaders();
    if ($telegramData) {
        return $telegramData;
    }
    
    // 2. Пробуем извлечь из JSON тела запроса (Telegram Bot)
    $telegramData = self::extractFromJsonBody();
    if ($telegramData) {
        return $telegramData;
    }
    
    // 3. Пробуем извлечь из FormData (Telegram Bot)
    $telegramData = self::extractFromFormData();
    if ($telegramData) {
        return $telegramData;
    }
    
    // 4. Пробуем извлечь из GET параметров (для тестирования)
    $telegramData = self::extractFromGetParams();
    if ($telegramData) {
        return $telegramData;
    }
    
    return null;
}
```

### **Извлечение из заголовков**
```php
private static function extractFromHeaders()
{
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    
    $telegramData = [];
    $fields = [
        'X-Telegram-User-Id' => 'telegram_id',
        'X-Telegram-First-Name' => 'first_name',
        'X-Telegram-Last-Name' => 'last_name',
        'X-Telegram-Username' => 'username',
        'X-Telegram-Photo-URL' => 'photo_url',
        'X-Telegram-Auth-Date' => 'auth_date',
        'X-Telegram-Hash' => 'hash'
    ];
    
    foreach ($fields as $header => $field) {
        $value = $headers[$header] ?? null;
        
        if ($value === null) {
            $serverKey = 'HTTP_' . strtoupper(str_replace('-', '_', $header));
            $value = $_SERVER[$serverKey] ?? null;
        }
        
        if ($value !== null) {
            $telegramData[$field] = $value;
        }
    }
    
    if (!empty($telegramData['telegram_id'])) {
        return $telegramData;
    }
    
    return null;
}
```

---

## ✅ **Валидация данных**

### **Обязательные поля**
- `telegram_id` — ID пользователя в Telegram (обязательно)
- `first_name` — имя пользователя (обязательно)

### **Опциональные поля**
- `last_name` — фамилия пользователя
- `username` — username в Telegram
- `photo_url` — URL аватара
- `auth_date` — дата авторизации
- `hash` — подпись для валидации

### **Валидация типов данных**
```php
public static function validateTelegramData($telegramData)
{
    // Проверка обязательных полей
    if (empty($telegramData['telegram_id'])) {
        return [
            'success' => false,
            'error' => [
                'code' => 'MISSING_TELEGRAM_ID',
                'message' => 'Отсутствует telegram_id'
            ]
        ];
    }
    
    if (empty($telegramData['first_name'])) {
        return [
            'success' => false,
            'error' => [
                'code' => 'MISSING_FIRST_NAME',
                'message' => 'Отсутствует first_name'
            ]
        ];
    }
    
    // Проверка типов данных
    if (!is_numeric($telegramData['telegram_id'])) {
        return [
            'success' => false,
            'error' => [
                'code' => 'INVALID_TELEGRAM_ID',
                'message' => 'telegram_id должен быть числом'
            ]
        ];
    }
    
    // Проверка длины строк
    if (strlen($telegramData['first_name']) > 64) {
        return [
            'success' => false,
            'error' => [
                'code' => 'INVALID_FIRST_NAME',
                'message' => 'first_name слишком длинный'
            ]
        ];
    }
    
    // Проверка подписи
    if (!self::isHashValid($telegramData)) {
        return [
            'success' => false,
            'error' => [
                'code' => 'INVALID_HASH',
                'message' => 'Подпись Telegram недействительна'
            ]
        ];
    }
    
    return [
        'success' => true,
        'message' => 'Данные Telegram валидны'
    ];
}
```

---

## 🔐 **Проверка подписи Telegram**

### **Алгоритм проверки**
Согласно [документации Telegram](https://core.telegram.org/bots/webapps#validating-data-received-via-the-mini-app):

```php
private static function isHashValid(array $data): bool
{
    if (empty($data['hash'])) {
        return false;
    }
    
    $recvHash = $data['hash'];
    unset($data['hash']);

    // Формируем data_check_string
    ksort($data, SORT_STRING);
    $pairs = [];
    foreach ($data as $k => $v) {
        $pairs[] = $k . '=' . $v;
    }
    $dataCheckString = implode("\n", $pairs);

    $botToken = getenv('BOT_TOKEN');
    if (!$botToken) return false;
    
    $secretKey = hash('sha256', $botToken, true); // binary
    $calculatedHash = hash_hmac('sha256', $dataCheckString, $secretKey);

    return hash_equals($calculatedHash, $recvHash);
}
```

### **Пример проверки**
```php
// Входные данные
$data = [
    'telegram_id' => '123456789',
    'first_name' => 'Иван',
    'username' => 'ivan',
    'auth_date' => '1640995200',
    'hash' => 'abc123def456...'
];

// Проверка подписи
$isValid = AuthHelper::isHashValid($data);
```

---

## 🔄 **Синхронизация пользователя**

### **Процесс синхронизации**
1. **Проверка существования** — поиск пользователя по telegram_id
2. **Создание/обновление** — создание нового или обновление существующего
3. **Сохранение фото** — обработка аватара пользователя
4. **Возврат данных** — полная информация о пользователе

### **Используемые Actions**
- `_CheckUserByTelegramIdAction` — проверка существования
- `_CreateUserAction` — создание пользователя
- `_UpdateUserAction` — обновление данных
- `_CreatePhotoAction` — сохранение фото

### **Пример результата синхронизации**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "telegram_id": 123456789,
    "first_name": "Иван",
    "last_name": "Иванов",
    "username": "ivan",
    "role": {
      "id": 2,
      "code": "guest",
      "name": "Гость"
    },
    "created_at": "2024-01-01T12:00:00Z",
    "updated_at": "2024-01-01T12:00:00Z"
  },
  "meta": {
    "action": "created",
    "message": "Пользователь создан"
  }
}
```

---

## 🛡️ **Безопасность**

### **Защита от подделки**
- **Проверка подписи** — валидация hash от Telegram
- **Временные метки** — проверка актуальности данных
- **Валидация полей** — проверка корректности данных

### **Рекомендации по безопасности**
1. **Всегда проверяйте подпись** — не доверяйте данным без валидации
2. **Используйте HTTPS** — защищайте передачу данных
3. **Логируйте попытки** — отслеживайте подозрительную активность
4. **Ограничивайте доступ** — используйте ролевую модель

### **Примеры атак и защита**
```php
// Защита от подделки данных
if (!AuthHelper::isHashValid($telegramData)) {
    Logger::warning('Invalid Telegram hash detected', [
        'telegram_id' => $telegramData['telegram_id'] ?? 'unknown',
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ]);
    return ['success' => false, 'error' => ['code' => 'INVALID_HASH']];
}
```

---

## 🧪 **Тестирование**

### **Тестовые данные**
```php
// Валидные тестовые данные
$testData = [
    'telegram_id' => '123456789',
    'first_name' => 'Test User',
    'username' => 'testuser',
    'auth_date' => time(),
    'hash' => 'valid_hash_here'
];
```

### **Примеры тестов**
```php
// Тест извлечения данных
$telegramData = AuthHelper::extractTelegramData();
assert($telegramData !== null, 'Telegram data should be extracted');

// Тест валидации
$validationResult = AuthHelper::validateTelegramData($telegramData);
assert($validationResult['success'], 'Telegram data should be valid');
```

---

## 📊 **Мониторинг и логирование**

### **Логируемые события**
- Извлечение Telegram данных
- Результаты валидации
- Ошибки авторизации
- Подозрительная активность

### **Примеры логов**
```php
Logger::info('Telegram data extracted', [
    'telegram_id' => $telegramData['telegram_id'] ?? 'unknown'
]);

Logger::warning('Invalid Telegram hash detected', [
    'telegram_id' => $telegramData['telegram_id'] ?? 'unknown',
    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
]);
```

---

## 🚨 **Обработка ошибок**

### **Типы ошибок**
- `NO_TELEGRAM_DATA` — данные не найдены
- `TELEGRAM_VALIDATION_ERROR` — ошибка валидации
- `INVALID_HASH` — недействительная подпись
- `MISSING_TELEGRAM_ID` — отсутствует ID
- `MISSING_FIRST_NAME` — отсутствует имя

### **HTTP статусы**
- **401 Unauthorized** — ошибки авторизации
- **400 Bad Request** — ошибки валидации

---

## 📚 **Связанная документация**

- [Обзор авторизации](OVERVIEW.md) — общая система авторизации
- [DEV-режим](DEV_MODE.md) — тестирование без Telegram
- [Сессии](SESSIONS.md) — управление сессиями
- [Безопасность](SECURITY.md) — принципы безопасности

---

> **💡 Совет:** Всегда тестируйте Telegram авторизацию с реальными данными в DEV-режиме перед продакшеном. 
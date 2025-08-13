# 🔐 AuthHelper

> Утилита для извлечения и валидации данных авторизации из различных источников

## 📋 Назначение

`AuthHelper` — центральная утилита для работы с авторизацией в CabrioRide. Обеспечивает:

- Извлечение Telegram данных из различных источников
- Криптографическую валидацию подписи Telegram
- Обработку legacy JWT токенов
- Проверку ролей пользователей

## 🏗️ Архитектура

### Основные методы

#### `extractTelegramData()`
Извлекает данные Telegram из различных источников в порядке приоритета:

1. **Заголовки** (Telegram WebApp)
2. **JSON тело** (Telegram Bot)
3. **FormData** (Telegram Bot)
4. **GET параметры** (для тестирования)

```php
$telegramData = AuthHelper::extractTelegramData();
if ($telegramData) {
    // Данные найдены
}
```

#### `validateTelegramData($telegramData)`
Валидирует данные Telegram, включая криптографическую проверку подписи:

```php
$result = AuthHelper::validateTelegramData($telegramData);
if ($result['success']) {
    // Данные валидны
}
```

#### `isHashValid($data)`
Выполняет криптографическую проверку подписи Telegram:

```php
$isValid = AuthHelper::isHashValid($telegramData);
```

## 🔍 Источники данных

### 1. Заголовки (Telegram WebApp)
```php
// Извлечение из заголовков
$telegramData = AuthHelper::extractFromHeaders();
```

### 2. JSON тело (Telegram Bot)
```php
// Извлечение из JSON тела запроса
$telegramData = AuthHelper::extractFromJsonBody();
```

### 3. FormData (Telegram Bot)
```php
// Извлечение из FormData
$telegramData = AuthHelper::extractFromFormData();
```

### 4. GET параметры (тестирование)
```php
// Извлечение из GET параметров для тестирования
$telegramData = AuthHelper::extractFromGetParams();
```

## 🔐 Валидация подписи

### Принцип работы
1. Извлекает `hash` из данных Telegram
2. Строит строку для проверки из всех полей кроме `hash`
3. Вычисляет HMAC-SHA256 с секретным ключом
4. Сравнивает с полученным хешем

### Пример валидации
```php
$telegramData = [
    'telegram_id' => 123456789,
    'first_name' => 'Иван',
    'auth_date' => 1640995200,
    'hash' => 'abc123...'
];

$isValid = AuthHelper::isHashValid($telegramData);
```

## 🏛️ Legacy JWT поддержка

### `decodeJWT($jwt, $secret)`
Декодирует JWT токен без использования сторонних библиотек:

```php
$payload = AuthHelper::decodeJWT($token, $secret);
```

### `getUserFromToken()`
Извлекает пользователя из JWT токена:

```php
$user = AuthHelper::getUserFromToken();
```

### `requireRole($role)`
Проверяет роль пользователя:

```php
AuthHelper::requireRole('admin');
```

## 🛡️ Безопасность

### Криптографическая валидация
- Использует HMAC-SHA256 для проверки подписи
- Секретный ключ берется из `BOT_TOKEN`
- Проверка временных меток для предотвращения replay-атак

### Защита от подделки
- Все данные Telegram подписываются Telegram
- Невозможно подделать данные без знания секретного ключа
- Автоматическая проверка на каждом запросе

## 🔧 Конфигурация

### Переменные окружения
```env
BOT_TOKEN=your_telegram_bot_token
JWT_SECRET=your_jwt_secret  # legacy
```

### Настройка валидации
```php
// Включение/отключение валидации для разработки
$skipValidation = getenv('SKIP_TELEGRAM_VALIDATION') === 'true';
```

## 📊 Структура данных

### Telegram данные
```php
[
    'telegram_id' => 123456789,
    'first_name' => 'Иван',
    'last_name' => 'Иванов',
    'username' => 'ivan_user',
    'photo_url' => 'https://t.me/i/userpic/320/...',
    'auth_date' => 1640995200,
    'hash' => 'abc123def456...'
]
```

### Результат валидации
```php
[
    'success' => true,
    'message' => 'Данные Telegram валидны'
]
```

## 🚨 Обработка ошибок

### Типы ошибок
- `NO_TELEGRAM_DATA` — данные не найдены
- `INVALID_HASH` — неверная подпись
- `EXPIRED_DATA` — данные устарели
- `INVALID_DATA` — некорректные данные

### Пример обработки
```php
$result = AuthHelper::validateTelegramData($telegramData);
if (!$result['success']) {
    $errorCode = $result['error']['code'];
    $errorMessage = $result['error']['message'];
    // Обработка ошибки
}
```

## 🔄 Интеграция

### С AuthMiddleware
```php
// AuthMiddleware использует AuthHelper
$telegramData = AuthHelper::extractTelegramData();
$validationResult = AuthHelper::validateTelegramData($telegramData);
```

### С контроллерами
```php
// Проверка ролей в контроллерах
AuthHelper::requireRole('admin');
```

## 📝 Примеры использования

### Полная валидация
```php
// 1. Извлечение данных
$telegramData = AuthHelper::extractTelegramData();
if (!$telegramData) {
    throw new Exception('Telegram данные не найдены');
}

// 2. Валидация
$validationResult = AuthHelper::validateTelegramData($telegramData);
if (!$validationResult['success']) {
    throw new Exception($validationResult['error']['message']);
}

// 3. Использование данных
$telegramId = $telegramData['telegram_id'];
```

### Проверка ролей
```php
// Проверка минимальной роли
AuthHelper::requireRole('moderator');

// Проверка конкретной роли
if (AppContext::getCurrentUserRole() === 'admin') {
    // Специальные права администратора
}
```

## 🔗 Связанные компоненты

- **AuthMiddleware** — использует AuthHelper для централизованной авторизации
- **AppContext** — получает данные пользователя после валидации
- **SessionHelper** — создает сессии на основе валидных данных
- **BaseController** — использует requireRole для проверки доступа

---

**📚 См. также:** [AuthMiddleware](../AUTHENTICATION/AUTH_MIDDLEWARE.md), [Telegram Auth](../AUTHENTICATION/TELEGRAM_AUTH.md) 
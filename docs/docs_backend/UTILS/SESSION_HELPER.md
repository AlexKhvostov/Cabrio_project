# 🎫 SessionHelper

> Утилита для управления сессиями пользователей и системными сессиями

## 📋 Назначение

`SessionHelper` — утилита для работы с сессиями пользователей в CabrioRide. Обеспечивает:

- Создание и обновление сессий пользователей
- Валидацию сессионных токенов
- Управление системными сессиями
- Очистку устаревших сессий

## 🏗️ Архитектура

### Основные методы

#### `createOrUpdateSession($userId, $options = [])`
Создает новую сессию или обновляет существующую:

```php
$sessionResult = SessionHelper::createOrUpdateSession($userId, [
    'telegram_data' => $telegramData,
    'expires_in' => 86400 // 24 часа
]);
```

#### `maybeCreateSession($userId, $options = [])`
Создает сессию с особым handling для системных пользователей:

```php
$sessionResult = SessionHelper::maybeCreateSession($userId);
// Для user_id = 0 возвращает системную сессию
```

#### `validateSession($sessionToken)`
Валидирует сессионный токен:

```php
$validationResult = SessionHelper::validateSession($sessionToken);
if ($validationResult['success']) {
    $sessionData = $validationResult['data'];
}
```

#### `destroySession($sessionToken)`
Деактивирует сессию:

```php
SessionHelper::destroySession($sessionToken);
```

## 🔧 Типы сессий

### 1. Обычные сессии пользователей
```php
[
    'success' => true,
    'session_id' => 123,
    'session_token' => 'abc123def456...',
    'expires_at' => '2024-01-15 12:00:00',
    'action' => 'created' // или 'updated'
]
```

### 2. Системные сессии
```php
[
    'success' => true,
    'session_id' => 'system',
    'expires_at' => null,
    'action' => 'system'
]
```

## 📊 Структура данных

### Параметры создания сессии
```php
$options = [
    'telegram_data' => $telegramData,    // Данные Telegram
    'expires_in' => 86400,              // Время жизни в секундах
    'ip_address' => $_SERVER['REMOTE_ADDR'], // IP адрес
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] // User Agent
];
```

### Результат создания сессии
```php
[
    'success' => true,
    'session_id' => 123,
    'session_token' => 'abc123def456...',
    'expires_at' => '2024-01-15 12:00:00',
    'action' => 'created' // created, updated, system
]
```

### Результат валидации
```php
[
    'success' => true,
    'data' => [
        'session_id' => 123,
        'user_id' => 456,
        'telegram_data' => [...],
        'created_at' => '2024-01-15 10:00:00',
        'expires_at' => '2024-01-15 12:00:00',
        'is_active' => true
    ]
]
```

## 🔄 Жизненный цикл сессии

### 1. Создание сессии
```php
// При первом входе пользователя
$sessionResult = SessionHelper::createOrUpdateSession($userId, [
    'telegram_data' => $telegramData
]);
```

### 2. Обновление сессии
```php
// При повторном входе - обновляется существующая сессия
$sessionResult = SessionHelper::createOrUpdateSession($userId, [
    'telegram_data' => $telegramData
]);
```

### 3. Валидация сессии
```php
// При каждом запросе
$validationResult = SessionHelper::validateSession($sessionToken);
```

### 4. Уничтожение сессии
```php
// При выходе пользователя
SessionHelper::destroySession($sessionToken);
```

## 🛡️ Безопасность

### Валидация токенов
- Проверка существования сессии в базе данных
- Проверка активности сессии (`is_active = true`)
- Проверка срока действия (`expires_at`)
- Проверка соответствия пользователя

### Защита от переиспользования
- Сессии деактивируются при выходе
- Автоматическая очистка устаревших сессий
- Уникальные токены для каждой сессии

## 🔧 Конфигурация

### Переменные окружения
```env
SESSION_LIFETIME=86400  # Время жизни сессии в секундах (24 часа)
SESSION_CLEANUP_INTERVAL=3600  # Интервал очистки в секундах
```

### Настройки по умолчанию
```php
// Время жизни сессии
$defaultExpiresIn = 86400; // 24 часа

// Максимальное время жизни
$maxExpiresIn = 604800; // 7 дней
```

## 🧹 Очистка сессий

### `cleanupExpiredSessions()`
Удаляет устаревшие сессии из базы данных:

```php
$deletedCount = SessionHelper::cleanupExpiredSessions();
echo "Удалено {$deletedCount} устаревших сессий";
```

### Автоматическая очистка
- Вызывается периодически через cron
- Удаляет сессии с истекшим сроком действия
- Удаляет деактивированные сессии

## 🚨 Обработка ошибок

### Типы ошибок
- `SESSION_NOT_FOUND` — сессия не найдена
- `SESSION_EXPIRED` — сессия истекла
- `SESSION_INACTIVE` — сессия деактивирована
- `INVALID_TOKEN` — неверный токен

### Пример обработки
```php
$result = SessionHelper::validateSession($sessionToken);
if (!$result['success']) {
    $errorCode = $result['error']['code'];
    $errorMessage = $result['error']['message'];
    // Обработка ошибки
}
```

## 📝 Примеры использования

### Создание сессии для нового пользователя
```php
$telegramData = AuthHelper::extractTelegramData();
$userId = 123;

$sessionResult = SessionHelper::createOrUpdateSession($userId, [
    'telegram_data' => $telegramData,
    'expires_in' => 86400
]);

if ($sessionResult['success']) {
    $sessionToken = $sessionResult['session_token'];
    // Возвращаем токен клиенту
}
```

### Валидация сессии в middleware
```php
$sessionToken = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$sessionToken = str_replace('Bearer ', '', $sessionToken);

$validationResult = SessionHelper::validateSession($sessionToken);
if ($validationResult['success']) {
    $sessionData = $validationResult['data'];
    $userId = $sessionData['user_id'];
    // Продолжаем обработку запроса
}
```

### Системная сессия
```php
// Для системных запросов (user_id = 0)
$sessionResult = SessionHelper::maybeCreateSession(0);
// Возвращает системную сессию без обращения к БД
```

### Выход пользователя
```php
$sessionToken = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$sessionToken = str_replace('Bearer ', '', $sessionToken);

SessionHelper::destroySession($sessionToken);
// Сессия деактивирована
```

## 🔄 Интеграция

### С AuthMiddleware
```php
// AuthMiddleware использует SessionHelper
$sessionResult = SessionHelper::maybeCreateSession($userId, [
    'telegram_data' => $telegramData
]);
```

### С AppContext
```php
// AppContext получает данные сессии
$sessionData = AppContext::getSessionData();
$sessionId = $sessionData['session_id'];
```

### С контроллерами
```php
// Контроллеры могут проверять сессию
$currentSession = AppContext::getCurrentSession();
if ($currentSession['user_id'] === $targetUserId) {
    // Пользователь может редактировать свои данные
}
```

## 📊 Мониторинг

### Метрики сессий
- Количество активных сессий
- Время жизни сессий
- Частота создания/обновления
- Количество устаревших сессий

### Логирование
```php
Logger::info('Session created', [
    'user_id' => $userId,
    'session_id' => $sessionId,
    'action' => 'created'
]);
```

## 🔗 Связанные компоненты

- **AuthMiddleware** — использует SessionHelper для создания сессий
- **AppContext** — хранит данные текущей сессии
- **Database** — для работы с таблицей sessions
- **Logger** — для логирования операций с сессиями

---

**📚 См. также:** [AuthMiddleware](../AUTHENTICATION/AUTH_MIDDLEWARE.md), [AppContext](APP_CONTEXT.md) 
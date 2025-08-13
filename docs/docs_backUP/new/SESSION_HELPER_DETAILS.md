# 🔐 SessionHelper - Управление сессиями

## 📋 Обзор

`SessionHelper` — это утилита для управления сессиями пользователей в CabrioRide. Она обеспечивает создание, валидацию, обновление и уничтожение сессий.

## 🏗️ Структура таблицы sessions

```sql
CREATE TABLE sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_id VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_session_id (session_id),
    INDEX idx_user_id (user_id),
    INDEX idx_expires_at (expires_at)
);
```

## 🔧 Основные методы

### 1️⃣ `createOrUpdateSession($userId, $options = [])`

**Назначение:** Создает новую сессию или обновляет существующую

**Параметры:**
- `$userId` (int) — ID пользователя
- `$options` (array) — дополнительные опции:
  - `'lifetime'` — время жизни сессии в секундах (по умолчанию 3600)
  - `'ip_address'` — IP адрес клиента
  - `'user_agent'` — User-Agent браузера

**Логика работы:**
1. Проверяет существующие активные сессии пользователя
2. Если сессий больше лимита — удаляет старые
3. Генерирует уникальный `session_id`
4. Создает запись в БД с временем истечения
5. Возвращает данные сессии

**Возвращает:**
```php
[
    'success' => true,
    'session_id' => 'abc123def456...',
    'expires_at' => '2024-01-15 11:30:00',
    'user_id' => 123
]
```

### 2️⃣ `validateSession($sessionId)`

**Назначение:** Проверяет валидность сессии

**Параметры:**
- `$sessionId` (string) — идентификатор сессии

**Логика работы:**
1. Ищет сессию в БД по `session_id`
2. Проверяет, что сессия активна (`is_active = TRUE`)
3. Проверяет, что сессия не истекла (`expires_at > NOW()`)
4. Обновляет `last_activity`
5. Возвращает данные пользователя

**Возвращает:**
```php
[
    'success' => true,
    'user' => [
        'id' => 123,
        'telegram_id' => 123456789,
        'first_name_tg' => 'Иван',
        'role' => 'member'
    ]
]
```

### 3️⃣ `getSessionUser($sessionId)`

**Назначение:** Получает пользователя по сессии

**Параметры:**
- `$sessionId` (string) — идентификатор сессии

**Логика работы:**
1. Вызывает `validateSession()`
2. Если сессия валидна — получает пользователя из БД
3. Возвращает полные данные пользователя

**Возвращает:**
```php
[
    'success' => true,
    'user' => User::findById($sessionData['user_id'])->toArray()
]
```

### 4️⃣ `destroySession($sessionId)`

**Назначение:** Уничтожает сессию

**Параметры:**
- `$sessionId` (string) — идентификатор сессии

**Логика работы:**
1. Находит сессию в БД
2. Устанавливает `is_active = FALSE`
3. Или полностью удаляет запись (в зависимости от настроек)

**Возвращает:**
```php
[
    'success' => true,
    'message' => 'Сессия успешно уничтожена'
]
```

### 5️⃣ `cleanupExpiredSessions()`

**Назначение:** Очищает истекшие сессии

**Логика работы:**
1. Находит все сессии с `expires_at < NOW()`
2. Удаляет их из БД
3. Логирует количество удаленных сессий

**Возвращает:**
```php
[
    'success' => true,
    'deleted_count' => 15,
    'message' => 'Удалено 15 истекших сессий'
]
```

### 6️⃣ `getUserSessions($userId)`

**Назначение:** Получает все активные сессии пользователя

**Параметры:**
- `$userId` (int) — ID пользователя

**Возвращает:**
```php
[
    'success' => true,
    'sessions' => [
        [
            'session_id' => 'abc123...',
            'created_at' => '2024-01-15 10:30:00',
            'expires_at' => '2024-01-15 11:30:00',
            'ip_address' => '192.168.1.1'
        ]
    ]
]
```

## 🔒 Безопасность

### Генерация session_id

```php
private static function generateSessionId() {
    // Криптографически стойкая генерация
    $randomBytes = random_bytes(32);
    return bin2hex($randomBytes);
}
```

### Проверка уникальности

```php
private static function isSessionIdUnique($sessionId) {
    $stmt = Database::getInstance()->prepare(
        "SELECT COUNT(*) FROM sessions WHERE session_id = ?"
    );
    $stmt->execute([$sessionId]);
    return $stmt->fetchColumn() == 0;
}
```

### Защита от перехвата

1. **HTTPS только** — сессии работают только по HTTPS
2. **HttpOnly cookies** — если используются cookies
3. **Secure flag** — для production
4. **SameSite** — защита от CSRF

## 📊 Мониторинг

### Логирование операций

```php
Logger::info("Session created", [
    'user_id' => $userId,
    'session_id' => $sessionId,
    'ip_address' => $ipAddress,
    'expires_at' => $expiresAt
]);
```

### Метрики

- Количество активных сессий
- Время жизни сессий
- Частота создания/уничтожения
- Подозрительная активность

## 🔄 Интеграция с Telegram Bot

### Особенности для Bot

1. **Короткое время жизни** — 1 час вместо 24 часов
2. **Не сохраняется** — бот не сохраняет `session_id`
3. **Каждый запрос** — бот отправляет Telegram данные при каждом запросе
4. **Автоочистка** — сессии бота очищаются чаще

### Код для Bot

```php
// В AuthMiddleware для Bot запросов
if ($isBotRequest) {
    // Создаем временную сессию
    $sessionResult = SessionHelper::createOrUpdateSession($userId, [
        'lifetime' => 3600, // 1 час
        'ip_address' => $_SERVER['REMOTE_ADDR'],
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
    ]);
    
    // НЕ возвращаем session_id боту
    // Бот будет отправлять Telegram данные при каждом запросе
}
```

## 🧪 Тестирование

### Тестовые методы

```php
// Создание тестовой сессии
$sessionData = SessionHelper::createOrUpdateSession(123);

// Проверка валидности
$isValid = SessionHelper::validateSession($sessionData['session_id']);

// Уничтожение сессии
SessionHelper::destroySession($sessionData['session_id']);
```

### Моки для тестов

```php
class MockSessionHelper {
    public static function createTestSession($userId) {
        return [
            'session_id' => 'test_session_' . $userId,
            'user_id' => $userId,
            'expires_at' => date('Y-m-d H:i:s', time() + 3600)
        ];
    }
}
```

## 📋 Конфигурация

### Настройки сессий

```php
// config/session.php
return [
    'lifetime' => 3600, // 1 час
    'cleanup_interval' => 1800, // очистка каждые 30 минут
    'max_sessions_per_user' => 3,
    'secure_cookies' => true,
    'http_only' => true,
    'same_site' => 'Strict',
    'bot_session_lifetime' => 3600, // 1 час для бота
    'webapp_session_lifetime' => 3600, // 1 час для WebApp
];
```

## 🎯 Преимущества

1. **Безопасность** — криптографически стойкие session_id
2. **Производительность** — индексы на ключевых полях
3. **Мониторинг** — подробное логирование
4. **Гибкость** — настройка времени жизни
5. **Очистка** — автоматическое удаление истекших сессий

Эта система обеспечивает надежное управление сессиями с учетом особенностей Telegram Bot и WebApp интеграции. 
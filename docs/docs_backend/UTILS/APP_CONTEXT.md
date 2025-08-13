# 🌐 AppContext — глобальный контекст приложения

> **Назначение:** Централизованное хранилище данных для текущего запроса  
> **Версия:** 1.0.0  
> **Последнее обновление:** 2024-01-01

---

## 🎯 **Обзор AppContext**

### **Назначение**
AppContext предоставляет централизованное хранилище для данных текущего запроса, включая пользователя, сессию, Telegram данные и метаданные запроса.

### **Принципы работы**
- **Статические методы** — глобальный доступ к данным
- **Потокобезопасность** — данные изолированы для каждого запроса
- **Автоматическая очистка** — данные очищаются после завершения запроса
- **Логирование** — все операции логируются для отладки

---

## 📊 **Структура данных**

### **Хранимые данные**
```php
class AppContext {
    // Пользователь
    private static $currentUser = null;      // Данные текущего пользователя
    
    // Сессия
    private static $sessionId = null;        // ID сессии
    
    // Telegram данные
    private static $telegramData = null;     // Данные из Telegram
    
    // Аватар пользователя
    private static $userAvatar = null;       // Аватар пользователя
    
    // Метаданные запроса
    private static $requestId = null;        // Уникальный ID запроса
    private static $startTime = null;        // Время начала запроса
}
```

### **Типы данных**
- **Пользователь** — массив с данными пользователя
- **Сессия** — строка с ID сессии
- **Telegram данные** — массив с данными из Telegram
- **Аватар** — массив с данными аватара
- **Метаданные** — строки и числа для отслеживания

---

## 👤 **Управление пользователем**

### **setCurrentUser($user)**
Установить текущего пользователя.

```php
$userData = [
    'id' => 1,
    'telegram_id' => 123456789,
    'first_name' => 'Иван',
    'last_name' => 'Иванов',
    'username' => 'ivan',
    'role' => [
        'id' => 4,
        'code' => 'member',
        'name' => 'Участник'
    ]
];

AppContext::setCurrentUser($userData);
```

### **getCurrentUser()**
Получить данные текущего пользователя.

```php
$user = AppContext::getCurrentUser();
if ($user) {
    echo $user['first_name']; // Иван
    echo $user['role']['name']; // Участник
}
```

### **hasCurrentUser()**
Проверить наличие текущего пользователя.

```php
if (AppContext::hasCurrentUser()) {
    echo "Пользователь авторизован";
} else {
    echo "Пользователь не авторизован";
}
```

### **clearCurrentUser()**
Очистить данные пользователя.

```php
AppContext::clearCurrentUser();
```

---

## 🖼️ **Управление аватаром**

### **setUserAvatar($avatar)**
Установить аватар пользователя.

```php
$avatarData = [
    'id' => 1,
    'url' => 'https://example.com/photos/user_1.jpg',
    'filename' => 'user_1.jpg',
    'size' => 1024
];

AppContext::setUserAvatar($avatarData);
```

### **getUserAvatar()**
Получить данные аватара пользователя.

```php
$avatar = AppContext::getUserAvatar();
if ($avatar) {
    echo $avatar['url']; // URL аватара
    echo $avatar['filename']; // Имя файла
}
```

### **hasUserAvatar()**
Проверить наличие аватара.

```php
if (AppContext::hasUserAvatar()) {
    echo "Аватар установлен";
} else {
    echo "Аватар не установлен";
}
```

### **clearUserAvatar()**
Очистить данные аватара.

```php
AppContext::clearUserAvatar();
```

---

## 🔐 **Управление сессией**

### **setSessionId($sessionId)**
Установить ID сессии.

```php
AppContext::setSessionId('session_abc123def456');
```

### **getSessionId()**
Получить ID сессии.

```php
$sessionId = AppContext::getSessionId();
if ($sessionId) {
    echo "ID сессии: " . $sessionId;
}
```

### **hasSession()**
Проверить наличие сессии.

```php
if (AppContext::hasSession()) {
    echo "Сессия активна";
} else {
    echo "Сессия не активна";
}
```

### **clearSession()**
Очистить данные сессии.

```php
AppContext::clearSession();
```

---

## 📱 **Управление Telegram данными**

### **setTelegramData($telegramData)**
Установить данные из Telegram.

```php
$telegramData = [
    'telegram_id' => 123456789,
    'first_name' => 'Иван',
    'last_name' => 'Иванов',
    'username' => 'ivan',
    'photo_url' => 'https://t.me/i/userpic/320/ivan.jpg',
    'auth_date' => 1640995200,
    'hash' => 'abc123def456...'
];

AppContext::setTelegramData($telegramData);
```

### **getTelegramData()**
Получить данные из Telegram.

```php
$telegramData = AppContext::getTelegramData();
if ($telegramData) {
    echo $telegramData['first_name']; // Иван
    echo $telegramData['username']; // ivan
}
```

### **hasTelegramData()**
Проверить наличие Telegram данных.

```php
if (AppContext::hasTelegramData()) {
    echo "Telegram данные доступны";
} else {
    echo "Telegram данные недоступны";
}
```

### **clearTelegramData()**
Очистить Telegram данные.

```php
AppContext::clearTelegramData();
```

---

## 📊 **Управление метаданными**

### **setRequestId($requestId)**
Установить уникальный ID запроса.

```php
AppContext::setRequestId('req_abc123def456');
```

### **getRequestId()**
Получить ID запроса.

```php
$requestId = AppContext::getRequestId();
if ($requestId) {
    echo "ID запроса: " . $requestId;
}
```

### **setStartTime($startTime)**
Установить время начала запроса.

```php
AppContext::setStartTime(microtime(true));
```

### **getStartTime()**
Получить время начала запроса.

```php
$startTime = AppContext::getStartTime();
if ($startTime) {
    echo "Время начала: " . $startTime;
}
```

### **getExecutionTime()**
Получить время выполнения запроса.

```php
$executionTime = AppContext::getExecutionTime();
if ($executionTime !== null) {
    echo "Время выполнения: " . $executionTime . " сек";
}
```

---

## 🔍 **Информационные методы**

### **getContextInfo()**
Получить полную информацию о контексте.

```php
$contextInfo = AppContext::getContextInfo();
echo json_encode($contextInfo, JSON_PRETTY_PRINT);
```

**Пример вывода:**
```json
{
  "has_user": true,
  "user_id": 1,
  "user_role": "member",
  "has_session": true,
  "session_id": "session_abc123",
  "has_telegram_data": true,
  "telegram_id": 123456789,
  "has_avatar": true,
  "request_id": "req_abc123def456",
  "execution_time": 0.05
}
```

### **isInitialized()**
Проверить инициализацию контекста.

```php
if (AppContext::isInitialized()) {
    echo "Контекст инициализирован";
} else {
    echo "Контекст не инициализирован";
}
```

### **getLogInfo()**
Получить информацию для логирования.

```php
$logInfo = AppContext::getLogInfo();
Logger::info('Request processed', $logInfo);
```

---

## 🧹 **Очистка данных**

### **clear()**
Полная очистка всех данных контекста.

```php
AppContext::clear();
```

**Что очищается:**
- Данные пользователя
- ID сессии
- Telegram данные
- Аватар пользователя
- ID запроса
- Время начала запроса

---

## 📊 **Примеры использования**

### **Инициализация контекста**
```php
// В начале запроса
AppContext::setRequestId('req_' . uniqid());
AppContext::setStartTime(microtime(true));

// После авторизации
AppContext::setCurrentUser($userData);
AppContext::setSessionId($sessionId);
AppContext::setTelegramData($telegramData);

if ($userAvatar) {
    AppContext::setUserAvatar($userAvatar);
}
```

### **Получение данных в коде**
```php
// В контроллере или Action
$user = AppContext::getCurrentUser();
if ($user) {
    $userId = $user['id'];
    $userRole = $user['role']['code'];
    
    // Использование данных пользователя
    $result = SomeAction::handle(['user_id' => $userId]);
}
```

### **Логирование контекста**
```php
// В конце запроса
$contextInfo = AppContext::getContextInfo();
$executionTime = AppContext::getExecutionTime();

Logger::info('Request completed', [
    'context' => $contextInfo,
    'execution_time' => $executionTime
]);
```

### **Проверка авторизации**
```php
// Проверка авторизации в коде
if (!AppContext::hasCurrentUser()) {
    return [
        'success' => false,
        'error' => ['code' => 'UNAUTHORIZED']
    ];
}

$user = AppContext::getCurrentUser();
if ($user['role']['code'] !== 'admin') {
    return [
        'success' => false,
        'error' => ['code' => 'FORBIDDEN']
    ];
}
```

---

## 🚨 **Обработка ошибок**

### **Типичные ошибки**
- **Отсутствие пользователя** — пользователь не авторизован
- **Отсутствие сессии** — сессия не активна
- **Отсутствие Telegram данных** — данные Telegram недоступны

### **Проверки безопасности**
```php
// Проверка наличия всех необходимых данных
if (!AppContext::hasCurrentUser()) {
    Logger::warning('No current user in context');
    return false;
}

if (!AppContext::hasSession()) {
    Logger::warning('No session in context');
    return false;
}
```

---

## 📊 **Производительность**

### **Оптимизации**
- **Статические переменные** — быстрый доступ к данным
- **Ленивая загрузка** — данные загружаются по требованию
- **Минимальное использование памяти** — только необходимые данные

### **Мониторинг**
- **Время выполнения** — отслеживание производительности
- **Использование памяти** — мониторинг потребления ресурсов
- **Количество операций** — статистика использования

---

## 🧪 **Тестирование**

### **Unit тесты**
```php
public function testAppContext()
{
    // Тест установки пользователя
    $userData = ['id' => 1, 'first_name' => 'Иван'];
    AppContext::setCurrentUser($userData);
    
    $user = AppContext::getCurrentUser();
    assert($user['id'] === 1);
    assert($user['first_name'] === 'Иван');
    
    // Тест очистки
    AppContext::clear();
    assert(AppContext::getCurrentUser() === null);
}
```

### **Интеграционные тесты**
```php
public function testAppContextInRequest()
{
    // Симуляция полного запроса
    AppContext::setRequestId('test_request');
    AppContext::setStartTime(microtime(true));
    AppContext::setCurrentUser($userData);
    AppContext::setSessionId('test_session');
    
    $contextInfo = AppContext::getContextInfo();
    assert($contextInfo['has_user'] === true);
    assert($contextInfo['has_session'] === true);
}
```

---

## 📚 **Связанная документация**

- [AuthHelper](AUTH_HELPER.md) — авторизация и валидация
- [SessionHelper](SESSION_HELPER.md) — управление сессиями
- [Logger](LOGGER.md) — логирование операций
- [Авторизация](../../AUTHENTICATION/OVERVIEW.md) — система безопасности

---

> **💡 Совет:** Используйте AppContext для централизованного доступа к данным запроса. Это упрощает код и улучшает производительность. 
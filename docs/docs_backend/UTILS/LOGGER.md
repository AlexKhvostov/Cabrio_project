# 📝 Logger

> Простой класс для логирования событий и ошибок в CabrioRide

## 📋 Назначение

`Logger` — утилита для централизованного логирования в CabrioRide. Обеспечивает:

- Запись информационных сообщений
- Логирование предупреждений
- Запись ошибок в отдельный файл
- Контекстную информацию для каждого лога

## 🏗️ Архитектура

### Основные методы

#### `info($message, $context = [])`
Записывает информационное сообщение:

```php
Logger::info('User logged in', [
    'user_id' => 123,
    'telegram_id' => 456789,
    'ip' => '192.168.1.1'
]);
```

#### `warning($message, $context = [])`
Записывает предупреждение:

```php
Logger::warning('Invalid login attempt', [
    'telegram_id' => 456789,
    'ip' => '192.168.1.1',
    'reason' => 'Invalid hash'
]);
```

#### `error($message, $context = [])`
Записывает ошибку в отдельный файл:

```php
Logger::error('Database connection failed', [
    'host' => 'localhost',
    'database' => 'cabrioride',
    'error' => $e->getMessage()
]);
```

## 📁 Файлы логов

### app.log
Основной файл для информационных сообщений и предупреждений:
```
2024-01-15T10:30:00+03:00 [INFO] User logged in | Context: {"user_id":123,"telegram_id":456789}
2024-01-15T10:31:00+03:00 [WARNING] Invalid login attempt | Context: {"telegram_id":456789,"reason":"Invalid hash"}
```

### error.log
Файл для ошибок:
```
2024-01-15T10:32:00+03:00 [ERROR] Database connection failed | Context: {"host":"localhost","error":"Access denied"}
```

## 📝 Примеры использования

### В контроллерах
```php
// Логирование успешных операций
public function createCar($data) {
    Logger::info('Car created', [
        'user_id' => AppContext::getCurrentUserId(),
        'car_model' => $data['model'],
        'car_id' => $carId
    ]);
    
    echo ResponseHelper::success($car);
}

// Логирование ошибок
public function updateCar($id) {
    try {
        $car = Car::update($id, $data);
        Logger::info('Car updated', ['car_id' => $id]);
        echo ResponseHelper::success($car);
    } catch (Exception $e) {
        Logger::error('Car update failed', [
            'car_id' => $id,
            'error' => $e->getMessage()
        ]);
        echo ResponseHelper::error('UPDATE_FAILED', 'Ошибка обновления');
    }
}
```

### В Actions
```php
// Логирование бизнес-операций
public static function handle($data) {
    Logger::info('Action started', [
        'action' => 'CreateCarAction',
        'user_id' => AppContext::getCurrentUserId()
    ]);
    
    $result = self::process($data);
    
    if ($result['success']) {
        Logger::info('Action completed', [
            'action' => 'CreateCarAction',
            'car_id' => $result['data']['id']
        ]);
    } else {
        Logger::warning('Action failed', [
            'action' => 'CreateCarAction',
            'error' => $result['error']['message']
        ]);
    }
    
    return $result;
}
```

### В middleware
```php
// Логирование авторизации
if (!$authResult['success']) {
    Logger::warning('Authentication failed', [
        'route' => $route,
        'method' => $method,
        'error' => $authResult['error']['message']
    ]);
    
    echo ResponseHelper::error('AUTH_ERROR', $authResult['error']['message']);
    exit;
}
```

### В Database
```php
// Логирование ошибок подключения
try {
    self::$instance = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    Logger::error('Database connection failed', [
        'host' => getenv('DB_HOST'),
        'database' => getenv('DB_NAME'),
        'error' => $e->getMessage()
    ]);
    throw $e;
}
```

## 🔧 Конфигурация

### Пути к файлам логов
```php
// Автоматически определяется
$appLogPath = __DIR__ . '/../logs/app.log';
$errorLogPath = __DIR__ . '/../logs/error.log';
```

### Формат времени
```php
// Использует ISO 8601 формат
date('c') // 2024-01-15T10:30:00+03:00
```

### Кодировка контекста
```php
// Поддержка русских символов
json_encode($context, JSON_UNESCAPED_UNICODE)
```

## 📊 Структура логов

### Информационное сообщение
```
2024-01-15T10:30:00+03:00 [INFO] User logged in | Context: {"user_id":123,"telegram_id":456789}
```

### Предупреждение
```
2024-01-15T10:31:00+03:00 [WARNING] Invalid login attempt | Context: {"telegram_id":456789,"reason":"Invalid hash"}
```

### Ошибка
```
2024-01-15T10:32:00+03:00 [ERROR] Database connection failed | Context: {"host":"localhost","error":"Access denied"}
```

## 🔄 Интеграция

### С AuthMiddleware
```php
// Логирование процесса авторизации
Logger::info('AuthMiddleware: Starting authentication process');
Logger::info('AuthMiddleware: Telegram data extracted', [
    'telegram_id' => $telegramData['telegram_id'] ?? 'unknown'
]);
```

### С контроллерами
```php
// Логирование API запросов
Logger::info('API request received', [
    'endpoint' => '/api/cars',
    'method' => 'POST',
    'user_id' => AppContext::getCurrentUserId()
]);
```

### С Actions
```php
// Логирование бизнес-операций
Logger::info('Business action executed', [
    'action' => 'CreateCarAction',
    'duration' => $executionTime,
    'success' => $result['success']
]);
```

## 📈 Мониторинг

### Метрики логирования
- Количество сообщений по уровням
- Размер файлов логов
- Частота ошибок
- Популярные контексты

### Ротация логов
```bash
# Автоматическая ротация (через cron)
mv backend/logs/app.log backend/logs/app.log.$(date +%Y%m%d)
mv backend/logs/error.log backend/logs/error.log.$(date +%Y%m%d)
```

### Анализ логов
```bash
# Поиск ошибок
grep "ERROR" backend/logs/error.log

# Поиск по пользователю
grep "user_id\":123" backend/logs/app.log

# Статистика по уровням
grep -c "INFO" backend/logs/app.log
grep -c "WARNING" backend/logs/app.log
grep -c "ERROR" backend/logs/error.log
```

## 🚨 Обработка ошибок

### Проблемы с записью
```php
// Проверка прав на запись
if (!is_writable(__DIR__ . '/../logs/')) {
    // Fallback - запись в системный лог
    error_log("Logger: Cannot write to log files");
}
```

### Переполнение диска
```php
// Проверка размера файлов
$logSize = filesize(__DIR__ . '/../logs/app.log');
if ($logSize > 100 * 1024 * 1024) { // 100MB
    // Ротация логов
    rename(__DIR__ . '/../logs/app.log', __DIR__ . '/../logs/app.log.old');
}
```

## 🔗 Связанные компоненты

- **AuthMiddleware** — логирует процесс авторизации
- **Controllers** — логируют API запросы
- **Actions** — логируют бизнес-операции
- **Database** — логирует ошибки подключения

---

**📚 См. также:** [AuthMiddleware](../AUTHENTICATION/AUTH_MIDDLEWARE.md), [Controllers](../CONTROLLERS/OVERVIEW.md) 
# Инструкция по использованию класса Logger

**Назначение:**
Класс для логирования событий, ошибок, действий в системе. Записывает логи в файл.

**Когда использовать:**
Для логирования любых событий, ошибок, действий, которые нужно отследить.

**Как подключать:**
```php
require_once __DIR__ . '/../../utils/Logger.php';
```

**Публичные методы:**
- `debug($msg, $ctx)` — лог debug
- `info($msg, $ctx)` — лог info
- `warning($msg, $ctx)` — лог warning
- `error($msg, $ctx)` — лог error
- `critical($msg, $ctx)` — лог critical

**Пример:**
```php
$logger = new Logger();
$logger->info('Пользователь вошёл', ['user_id' => $userId]);
$logger->error('Ошибка БД', ['exception' => $e->getMessage()]);
```

**Best practices:**
- Для логирования ошибок используйте error() или critical().
- Для бизнес-событий — info().
- Не храните чувствительные данные в логах.
- Очищайте старые логи через cleanOldLogs(). 
# Инструкция по использованию класса Database

**Назначение:**
Singleton для подключения к MySQL через PDO. Централизует работу с БД.

**Когда использовать:**
Всегда для любых операций с БД.

**Как подключать:**
```php
require_once __DIR__ . '/../../utils/Database.php';
```

**Публичные методы:**
- `Database::getInstance()` — получить singleton-экземпляр
- `getConnection()` — получить PDO-подключение

**Пример:**
```php
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$userId]);
```

**Best practices:**
- Не создавайте PDO напрямую, только через Database.
- Все параметры подключения берутся из .env.
- Используйте только подготовленные запросы (prepare/execute). 
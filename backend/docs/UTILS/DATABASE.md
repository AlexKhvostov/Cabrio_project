# 🗄️ Database

> Singleton для подключения к MySQL через PDO в CabrioRide

## 📋 Назначение

`Database` — утилита для работы с базой данных MySQL. Обеспечивает:

- Singleton паттерн для единственного подключения к БД
- Автоматическую загрузку параметров из .env
- Безопасное подключение через PDO
- Централизованную обработку ошибок подключения

## 🏗️ Архитектура

### Singleton паттерн
```php
// Получение единственного экземпляра PDO
$pdo = Database::getInstance();

// Всегда возвращает один и тот же объект
$pdo2 = Database::getInstance();
// $pdo === $pdo2 // true
```

### Основные методы

#### `getInstance()`
Возвращает единственный экземпляр PDO подключения:

```php
$pdo = Database::getInstance();
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$id]);
$user = $stmt->fetch();
```

## 🔧 Конфигурация

### Переменные окружения
```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=cabrioride
DB_USER=root
DB_PASSWORD=password
```

### Параметры подключения
```php
// Автоматически загружаются из .env
$dsn = 'mysql:host=' . getenv('DB_HOST') . 
       ';port=' . getenv('DB_PORT') . 
       ';dbname=' . getenv('DB_NAME') . 
       ';charset=utf8mb4';
```

### Настройки PDO
```php
[
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]
```

## 📝 Примеры использования

### Базовые операции
```php
// Получение подключения
$pdo = Database::getInstance();

// Простой запрос
$stmt = $pdo->query('SELECT COUNT(*) FROM users');
$count = $stmt->fetchColumn();

// Подготовленный запрос
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([123]);
$user = $stmt->fetch();

// Вставка данных
$stmt = $pdo->prepare('INSERT INTO users (first_name, last_name) VALUES (?, ?)');
$stmt->execute(['Иван', 'Иванов']);
$userId = $pdo->lastInsertId();
```

### В моделях
```php
class User {
    public static function findById($id) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public static function create($data) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('
            INSERT INTO users (first_name, last_name, telegram_id) 
            VALUES (?, ?, ?)
        ');
        $stmt->execute([
            $data['first_name'],
            $data['last_name'],
            $data['telegram_id']
        ]);
        return $pdo->lastInsertId();
    }
}
```

### Транзакции
```php
$pdo = Database::getInstance();

try {
    $pdo->beginTransaction();
    
    // Первая операция
    $stmt = $pdo->prepare('INSERT INTO cars (model, owner_id) VALUES (?, ?)');
    $stmt->execute(['BMW Z4', $userId]);
    $carId = $pdo->lastInsertId();
    
    // Вторая операция
    $stmt = $pdo->prepare('INSERT INTO photos (car_id, url) VALUES (?, ?)');
    $stmt->execute([$carId, $photoUrl]);
    
    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    throw $e;
}
```

## 🛡️ Безопасность

### Подготовленные запросы
```php
// ✅ Безопасно - использует подготовленные запросы
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$userId]);

// ❌ Опасно - прямая конкатенация
$stmt = $pdo->query("SELECT * FROM users WHERE id = $userId");
```

### Обработка ошибок
```php
try {
    $pdo = Database::getInstance();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch();
} catch (PDOException $e) {
    Logger::error('Database error: ' . $e->getMessage());
    throw new Exception('Ошибка базы данных');
}
```

## 🔄 Интеграция

### С моделями
```php
// Все модели используют Database::getInstance()
class Car {
    public static function findAll() {
        $pdo = Database::getInstance();
        $stmt = $pdo->query('SELECT * FROM cars');
        return $stmt->fetchAll();
    }
}
```

### С Actions
```php
// Actions используют Database для сложных операций
class CreateCarAction {
    public static function handle($data) {
        $pdo = Database::getInstance();
        
        // Сложная бизнес-логика с БД
        $pdo->beginTransaction();
        // ... операции
        $pdo->commit();
    }
}
```

### С Logger
```php
// Автоматическое логирование ошибок подключения
if (class_exists('Logger')) {
    Logger::error('DB connection failed: ' . $e->getMessage());
}
```

## 📊 Производительность

### Singleton преимущества
- Одно подключение на весь запрос
- Экономия ресурсов
- Быстрые операции

### Настройки для производительности
```php
[
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false, // Нативные подготовленные запросы
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
]
```

## 🚨 Обработка ошибок

### Типы ошибок
- `PDOException` — ошибки подключения и запросов
- `DatabaseConnectionException` — ошибки подключения
- `DatabaseQueryException` — ошибки выполнения запросов

### Пример обработки
```php
try {
    $pdo = Database::getInstance();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch();
} catch (PDOException $e) {
    // Логирование ошибки
    Logger::error('Database query failed', [
        'query' => 'SELECT * FROM users WHERE id = ?',
        'params' => [$id],
        'error' => $e->getMessage()
    ]);
    
    // Возврат ошибки
    return [
        'success' => false,
        'error' => [
            'code' => 'DATABASE_ERROR',
            'message' => 'Ошибка базы данных'
        ]
    ];
}
```

## 🔧 Конфигурация

### Настройка подключения
```php
// В .env файле
DB_HOST=localhost
DB_PORT=3306
DB_NAME=cabrioride
DB_USER=root
DB_PASSWORD=password

// Дополнительные настройки
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci
```

### Настройка для разработки
```env
# Локальная разработка
DB_HOST=localhost
DB_PORT=3306
DB_NAME=cabrioride_dev

# Продакшн
DB_HOST=production-db.example.com
DB_PORT=3306
DB_NAME=cabrioride_prod
```

## 📈 Мониторинг

### Метрики подключения
- Количество подключений
- Время выполнения запросов
- Количество ошибок
- Размер пула соединений

### Логирование
```php
Logger::info('Database connection established', [
    'host' => getenv('DB_HOST'),
    'database' => getenv('DB_NAME'),
    'connection_time' => $connectionTime
]);
```

## 🔗 Связанные компоненты

- **Models** — используют Database для CRUD операций
- **Actions** — используют для сложных бизнес-операций
- **Logger** — для логирования ошибок подключения
- **load_env.php** — для загрузки переменных окружения

---

**📚 См. также:** [Models](../MODELS/OVERVIEW.md), [Actions](../ACTIONS/OVERVIEW.md), [Logger](LOGGER.md) 
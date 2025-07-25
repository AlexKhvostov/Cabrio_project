# Оптимизация производительности backend CabrioRide

> Рекомендации и стратегии для обеспечения высокой производительности backend API.
> 
> **Важно:** Производительность критична для пользовательского опыта!

---

## 🗄️ Оптимизация базы данных

### Индексы для часто используемых полей
```sql
-- Основные индексы для таблицы users
CREATE INDEX idx_users_telegram_id ON users(telegram_id);
CREATE INDEX idx_users_role_id ON users(role_id);
CREATE INDEX idx_users_status_id ON users(status_id);
CREATE INDEX idx_users_created_at ON users(created_at);

-- Индексы для таблицы cars
CREATE INDEX idx_cars_owner_id ON cars(owner_id);
CREATE INDEX idx_cars_brand_id ON cars(brand_id);
CREATE INDEX idx_cars_status_id ON cars(status_id);

-- Индексы для таблицы events
CREATE INDEX idx_events_organizer_id ON events(organizer_id);
CREATE INDEX idx_events_event_type_id ON events(event_type_id);
CREATE INDEX idx_events_date ON events(date);

-- Составные индексы для сложных запросов
CREATE INDEX idx_users_role_status ON users(role_id, status_id);
CREATE INDEX idx_events_type_date ON events(event_type_id, date);
```

### Оптимизация запросов
```php
// ❌ Неэффективно — N+1 проблема
$users = User::getAll();
foreach ($users as $user) {
    $user['cars'] = Car::getByOwnerId($user['id']); // Дополнительный запрос для каждого пользователя
}

// ✅ Эффективно — один запрос с JOIN
$users = User::getAllWithCars(); // Один запрос с LEFT JOIN
```

### Кэширование справочников
```php
// Кэширование ролей (меняются редко)
class Role {
    private static $cache = null;
    
    public static function getAll() {
        if (self::$cache === null) {
            $pdo = Database::getInstance();
            $stmt = $pdo->query('SELECT * FROM ref_roles ORDER BY id');
            self::$cache = $stmt->fetchAll();
        }
        return self::$cache;
    }
    
    public static function clearCache() {
        self::$cache = null;
    }
}
```

---

## 🚀 Оптимизация API

### Пагинация для больших списков
```php
// В контроллере
public function getList() {
    $page = (int)($_GET['page'] ?? 1);
    $perPage = min((int)($_GET['per_page'] ?? 20), 100); // Максимум 100
    $offset = ($page - 1) * $perPage;
    
    $users = User::getAllPaginated($offset, $perPage);
    $total = User::getCount();
    
    echo ResponseHelper::success($users, [
        'page' => $page,
        'per_page' => $perPage,
        'total' => $total,
        'total_pages' => ceil($total / $perPage)
    ]);
}
```

### Ленивая загрузка связанных данных
```php
// В модели User
public static function getById($id, $expand = []) {
    $user = self::findById($id);
    
    // Загружаем связанные данные только по запросу
    if (in_array('cars', $expand)) {
        $user['cars'] = Car::getByOwnerId($id);
    }
    
    if (in_array('events', $expand)) {
        $user['events'] = Event::getByParticipantId($id);
    }
    
    return $user;
}

// Использование
$user = User::getById(123, ['cars', 'events']);
```

### Сжатие ответов
```php
// В ResponseHelper
public static function success($data = null, $pagination = null) {
    $response = [
        'success' => true,
        'data' => $data,
        'error' => null
    ];
    
    if ($pagination !== null) {
        $response['pagination'] = $pagination;
    }
    
    header('Content-Type: application/json');
    header('Content-Encoding: gzip'); // Включаем сжатие
    
    return gzencode(json_encode($response, JSON_UNESCAPED_UNICODE));
}
```

---

## 📊 Мониторинг производительности

### Логирование времени выполнения
```php
// В контроллерах
public function getList() {
    $startTime = microtime(true);
    
    // Выполнение запроса
    $users = User::getAll();
    
    $executionTime = microtime(true) - $startTime;
    Logger::info("API Performance: GET /api/users executed in {$executionTime}s");
    
    echo ResponseHelper::success($users);
}
```

### Метрики для отслеживания
```php
// В Database.php
class Database {
    private static $queryCount = 0;
    private static $totalQueryTime = 0;
    
    public static function getInstance() {
        $startTime = microtime(true);
        $instance = self::$instance ?? new self();
        self::$queryCount++;
        self::$totalQueryTime += microtime(true) - $startTime;
        return $instance;
    }
    
    public static function getMetrics() {
        return [
            'query_count' => self::$queryCount,
            'total_time' => self::$totalQueryTime,
            'avg_time' => self::$queryCount > 0 ? self::$totalQueryTime / self::$queryCount : 0
        ];
    }
}
```

### Алерты при медленных запросах
```php
// В ResponseHelper
public static function success($data = null, $pagination = null) {
    $executionTime = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
    
    if ($executionTime > 1.0) { // Больше 1 секунды
        Logger::warning("Slow API response: {$executionTime}s for " . $_SERVER['REQUEST_URI']);
    }
    
    // ... остальной код
}
```

---

## 💾 Кэширование

### Кэширование в памяти
```php
// Простой кэш в памяти
class Cache {
    private static $data = [];
    
    public static function get($key) {
        return self::$data[$key] ?? null;
    }
    
    public static function set($key, $value, $ttl = 3600) {
        self::$data[$key] = [
            'value' => $value,
            'expires' => time() + $ttl
        ];
    }
    
    public static function has($key) {
        if (!isset(self::$data[$key])) return false;
        return self::$data[$key]['expires'] > time();
    }
}

// Использование
if (!Cache::has('roles')) {
    Cache::set('roles', Role::getAll(), 3600); // Кэш на 1 час
}
$roles = Cache::get('roles');
```

### Кэширование файлов
```php
// Кэширование статических данных
class StaticCache {
    public static function get($key) {
        $file = __DIR__ . "/../cache/{$key}.json";
        if (file_exists($file) && (time() - filemtime($file)) < 3600) {
            return json_decode(file_get_contents($file), true);
        }
        return null;
    }
    
    public static function set($key, $data) {
        $file = __DIR__ . "/../cache/{$key}.json";
        file_put_contents($file, json_encode($data));
    }
}
```

---

## 🔧 Оптимизация PHP

### Настройки php.ini
```ini
; Оптимизация памяти
memory_limit = 256M
max_execution_time = 30

; Оптимизация OPcache
opcache.enable = 1
opcache.memory_consumption = 128
opcache.interned_strings_buffer = 8
opcache.max_accelerated_files = 4000
opcache.revalidate_freq = 2
opcache.fast_shutdown = 1

; Оптимизация сессий
session.gc_maxlifetime = 1440
session.gc_probability = 1
session.gc_divisor = 100
```

### Оптимизация автозагрузки
```php
// composer.json
{
    "autoload": {
        "psr-4": {
            "App\\": "src/"
        },
        "classmap": [
            "utils/",
            "models/"
        ]
    },
    "autoload-dev": {
        "psr-4": {
            "Tests\\": "tests/"
        }
    }
}
```

---

## 📈 Нагрузочное тестирование

### Инструменты для тестирования
```bash
# Apache Bench (ab)
ab -n 1000 -c 10 http://localhost/api/users

# wrk (более современный)
wrk -t12 -c400 -d30s http://localhost/api/users

# Artillery (для сложных сценариев)
npm install -g artillery
artillery run load-test.yml
```

### Пример конфигурации Artillery
```yaml
# load-test.yml
config:
  target: 'http://localhost'
  phases:
    - duration: 60
      arrivalRate: 10
  defaults:
    headers:
      Content-Type: 'application/json'

scenarios:
  - name: "API Load Test"
    requests:
      - get:
          url: "/api/users"
      - post:
          url: "/api/users"
          json:
            first_name: "Test"
            last_name: "User"
            email: "test@example.com"
```

---

## 🎯 Целевые показатели производительности

### Время ответа API
- **GET запросы:** < 200ms
- **POST/PUT запросы:** < 500ms
- **Сложные запросы:** < 1000ms

### Пропускная способность
- **Одновременных пользователей:** 100+
- **Запросов в секунду:** 50+
- **Использование памяти:** < 256MB

### База данных
- **Время выполнения запросов:** < 100ms
- **Количество запросов на страницу:** < 10
- **Размер индексов:** < 50% от размера таблиц

---

## 🚨 Оптимизация критических путей

### Главная страница (список пользователей)
```php
// Оптимизированный запрос
public static function getListOptimized($limit = 20) {
    $pdo = Database::getInstance();
    $stmt = $pdo->prepare("
        SELECT u.id, u.first_name, u.last_name, u.telegram_id,
               r.code as role_code, r.name as role_name,
               p.url as photo_url
        FROM users u
        LEFT JOIN ref_roles r ON u.role_id = r.id
        LEFT JOIN photos p ON p.id = (
            SELECT id FROM photos 
            WHERE entity_type = 'user' AND entity_id = u.id 
            ORDER BY id DESC LIMIT 1
        )
        WHERE u.status_id = 1
        ORDER BY u.created_at DESC
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}
```

### Поиск и фильтрация
```php
// Эффективный поиск с индексами
public static function search($query, $filters = []) {
    $sql = "SELECT u.*, r.code as role_code 
            FROM users u 
            LEFT JOIN ref_roles r ON u.role_id = r.id 
            WHERE 1=1";
    $params = [];
    
    if (!empty($query)) {
        $sql .= " AND (u.first_name LIKE ? OR u.last_name LIKE ?)";
        $params[] = "%{$query}%";
        $params[] = "%{$query}%";
    }
    
    if (!empty($filters['role'])) {
        $sql .= " AND r.code = ?";
        $params[] = $filters['role'];
    }
    
    $sql .= " ORDER BY u.created_at DESC LIMIT 50";
    
    $stmt = Database::getInstance()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}
```

---

## 📋 Чек-лист оптимизации

### База данных
- [ ] Созданы индексы для часто используемых полей
- [ ] Оптимизированы медленные запросы
- [ ] Настроено кэширование справочников
- [ ] Мониторинг времени выполнения запросов

### API
- [ ] Реализована пагинация для больших списков
- [ ] Настроено сжатие ответов
- [ ] Оптимизирована загрузка связанных данных
- [ ] Логирование времени выполнения

### Сервер
- [ ] Настроен OPcache
- [ ] Оптимизированы настройки PHP
- [ ] Настроен кэш в памяти
- [ ] Мониторинг использования ресурсов

### Мониторинг
- [ ] Настроены метрики производительности
- [ ] Созданы алерты для медленных запросов
- [ ] Регулярное нагрузочное тестирование
- [ ] Отслеживание трендов производительности

---

> **Дата последнего обновления:** 2024-12-19  
> **Версия:** 1.0.0 
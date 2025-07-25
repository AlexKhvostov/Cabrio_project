# Безопасность backend CabrioRide

> Подробные инструкции по обеспечению безопасности backend API.
> 
> **Важно:** Безопасность — критически важный аспект для любого API!

---

## 🔐 Аутентификация и авторизация

### JWT токены
- **Алгоритм:** HMAC-SHA256
- **Срок действия:** 24 часа (настраивается)
- **Секрет:** хранится только в .env (JWT_SECRET)

```php
// Пример создания JWT токена
$payload = [
    'user_id' => $user['id'],
    'role' => $user['role']['code'],
    'exp' => time() + (24 * 60 * 60) // 24 часа
];
$token = JWT::encode($payload, getenv('JWT_SECRET'), 'HS256');
```

### Telegram WebApp интеграция
- **Проверка подписи:** обязательна для всех запросов
- **Валидация initData:** проверка хеша от Telegram
- **Временные метки:** проверка актуальности данных

```php
// Проверка подписи Telegram WebApp
function validateTelegramWebApp($initData) {
    $data = parse_str($initData, $params);
    $hash = $params['hash'] ?? '';
    unset($params['hash']);
    
    $dataCheckString = http_build_query($params);
    $secretKey = hash_hmac('sha256', getenv('BOT_TOKEN'), 'WebAppData', true);
    $calculatedHash = bin2hex(hash_hmac('sha256', $dataCheckString, $secretKey, true));
    
    return hash_equals($calculatedHash, $hash);
}
```

### Системные токены
- **SYSTEM_TOKEN:** для автоматических операций
- **Использование:** только для внутренних системных вызовов
- **Проверка:** строгое сравнение с .env

---

## 🛡️ Ролевая модель доступа

### Уровни ролей (от низшей к высшей)
1. **external** — внешние пользователи
2. **guest** — гости
3. **new** — новые участники
4. **registered** — зарегистрированные
5. **member** — члены клуба
6. **moderator** — модераторы
7. **admin** — администраторы

### Проверка прав
```php
// В контроллерах
AuthHelper::requireRole('moderator'); // Минимальная роль для действия

// В actions
if ($user['role']['code'] === 'admin') {
    // Специальные права администратора
}
```

### Централизованная проверка
- Все проверки прав в `AuthHelper`
- Конфигурация ролей в `config/sectionGroups.php`
- Логирование всех проверок доступа

---

## 🔒 Защита данных

### Валидация входных данных
```php
// Обязательная валидация всех входных данных
ValidationHelper::requireFields($data, ['email', 'password']);
ValidationHelper::validateEmail($data['email']);
ValidationHelper::validateInt($data['id'], 'id');
```

### SQL-инъекции
- **Использование:** только подготовленные запросы (PDO)
- **Запрещено:** прямая конкатенация строк в SQL

```php
// ✅ Правильно
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$id]);

// ❌ Запрещено
$query = "SELECT * FROM users WHERE id = $id";
```

### XSS защита
- **Выходные данные:** всегда экранирование HTML
- **JSON ответы:** автоматическое экранирование через `json_encode()`
- **Заголовки:** правильная установка Content-Type

```php
// Экранирование HTML
echo htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8');

// JSON ответы (автоматически безопасны)
echo json_encode($data, JSON_UNESCAPED_UNICODE);
```

---

## 🌐 CORS и заголовки безопасности

### CORS настройки (для будущего)
```php
// Заголовки безопасности
header('Access-Control-Allow-Origin: https://cabrioride.by');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Max-Age: 86400');

// Дополнительные заголовки безопасности
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
```

### CSP (Content Security Policy)
```php
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline';");
```

---

## 📁 Безопасность файлов

### Загрузка файлов
```php
// Проверка типа файла
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
if (!in_array($_FILES['photo']['type'], $allowedTypes)) {
    throw new Exception('Неподдерживаемый тип файла');
}

// Проверка размера
$maxSize = 5 * 1024 * 1024; // 5 MB
if ($_FILES['photo']['size'] > $maxSize) {
    throw new Exception('Файл слишком большой');
}

// Безопасное имя файла
$filename = sanitizeFilename($_FILES['photo']['name']);
```

### Структура папок
```
uploads/
├── users/          # Только для пользователей
├── cars/           # Только для автомобилей
└── events/         # Только для событий
```

### Права доступа к файлам
```bash
# Устанавливаем правильные права
chmod 755 uploads/
chmod 644 uploads/*/*.jpg
chown www-data:www-data uploads/
```

---

## 🔍 Логирование безопасности

### События для логирования
- Все попытки авторизации (успешные и неуспешные)
- Все проверки доступа (разрешённые и запрещённые)
- Все загрузки файлов
- Все изменения критических данных

```php
// Логирование событий безопасности
Logger::info("Security: User {$userId} accessed {$endpoint}");
Logger::error("Security: Failed login attempt for user {$email}");
Logger::warning("Security: Unauthorized access attempt to {$resource}");
```

### Аудит действий
- **Кто:** ID пользователя
- **Что:** выполняемое действие
- **Когда:** точное время
- **Где:** IP адрес (если доступен)

---

## 🚨 Обработка ошибок

### Не раскрывать внутреннюю информацию
```php
// ❌ Неправильно (раскрывает структуру БД)
echo "Error: Table 'users' doesn't exist";

// ✅ Правильно (общая ошибка)
echo ResponseHelper::error('INTERNAL_ERROR', 'Произошла внутренняя ошибка');
```

### Логирование ошибок
```php
try {
    // Код, который может вызвать ошибку
} catch (Exception $e) {
    Logger::error('Application error: ' . $e->getMessage());
    echo ResponseHelper::error('INTERNAL_ERROR', 'Произошла ошибка');
}
```

---

## 🔄 Ротация и обновление

### Регулярное обновление
- **JWT_SECRET:** каждые 3 месяца
- **SYSTEM_TOKEN:** каждые 6 месяцев
- **Пароли БД:** каждые 12 месяцев

### Мониторинг безопасности
```bash
# Проверка подозрительной активности
grep "Security:" backend/logs/app.log | tail -100

# Проверка неуспешных попыток входа
grep "Failed login" backend/logs/error.log
```

---

## 📋 Чек-лист безопасности

### Настройка
- [ ] JWT_SECRET установлен и достаточно длинный
- [ ] SYSTEM_TOKEN установлен и уникален
- [ ] Все пароли БД сложные
- [ ] SSL сертификат установлен
- [ ] Права доступа к файлам настроены

### Мониторинг
- [ ] Логирование всех событий безопасности
- [ ] Регулярная проверка логов
- [ ] Мониторинг подозрительной активности
- [ ] Резервное копирование логов

### Обновления
- [ ] Регулярное обновление токенов
- [ ] Обновление зависимостей
- [ ] Проверка уязвимостей
- [ ] Тестирование безопасности

---

## 🛠️ Инструменты безопасности

### Рекомендуемые инструменты
- **OWASP ZAP:** для тестирования уязвимостей
- **PHP Security Checker:** для проверки зависимостей
- **Fail2ban:** для блокировки подозрительных IP
- **ModSecurity:** для WAF (Web Application Firewall)

### Настройка Fail2ban
```ini
# /etc/fail2ban/jail.local
[cabrioride]
enabled = true
port = http,https
filter = cabrioride
logpath = /var/log/apache2/cabrioride_error.log
maxretry = 5
bantime = 3600
```

---

> **Дата последнего обновления:** 2024-12-19  
> **Версия:** 1.0.0 
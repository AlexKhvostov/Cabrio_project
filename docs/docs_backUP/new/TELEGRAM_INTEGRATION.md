# 🤖 Интеграция с Telegram Bot и WebApp

## 📱 Telegram WebApp интеграция

### Как работает WebApp

**Telegram WebApp** — это веб-приложение, которое запускается внутри Telegram и имеет доступ к данным пользователя через специальные API.

### Передача данных от WebApp

**Источник данных:** Telegram автоматически передает данные пользователя в заголовках HTTP запросов:

```
X-Telegram-ID: 123456789
X-Telegram-First-Name: Иван
X-Telegram-Last-Name: Иванов
X-Telegram-Username: ivan_user
X-Telegram-Photo-URL: https://t.me/i/userpic/320/photo.jpg
X-Telegram-Auth-Date: 1640995200
X-Telegram-Hash: abc123def456...
```

### Обработка на бэкенде

**Где:** `backend/utils/AuthHelper.php` → `extractTelegramData()`

**Логика:**
1. Проверяем наличие заголовков `X-Telegram-*`
2. Извлекаем данные пользователя
3. Валидируем подпись запроса (если включена)
4. Возвращаем структурированные данные

**Код обработки:**
```php
private static function extractFromHeaders() {
    $data = [];
    
    // Обязательные поля
    if (isset($_SERVER['HTTP_X_TELEGRAM_ID'])) {
        $data['telegram_id'] = (int)$_SERVER['HTTP_X_TELEGRAM_ID'];
    }
    if (isset($_SERVER['HTTP_X_TELEGRAM_FIRST_NAME'])) {
        $data['first_name'] = $_SERVER['HTTP_X_TELEGRAM_FIRST_NAME'];
    }
    
    // Опциональные поля
    if (isset($_SERVER['HTTP_X_TELEGRAM_LAST_NAME'])) {
        $data['last_name'] = $_SERVER['HTTP_X_TELEGRAM_LAST_NAME'];
    }
    if (isset($_SERVER['HTTP_X_TELEGRAM_USERNAME'])) {
        $data['username'] = $_SERVER['HTTP_X_TELEGRAM_USERNAME'];
    }
    if (isset($_SERVER['HTTP_X_TELEGRAM_PHOTO_URL'])) {
        $data['photo_url'] = $_SERVER['HTTP_X_TELEGRAM_PHOTO_URL'];
    }
    
    return $data;
}
```

## 🤖 Telegram Bot интеграция

### Как работает Bot

**Telegram Bot** отправляет запросы к нашему API от имени пользователя. В отличие от WebApp, бот должен явно передавать данные пользователя.

### Передача данных от Bot

**Структура запроса:**
```json
{
    "telegram_id": 123456789,
    "first_name": "Иван",
    "last_name": "Иванов",
    "username": "ivan_user",
    "photo_url": "https://t.me/i/userpic/320/photo.jpg",
    "action": "sync_user",
    "data": {
        // дополнительные данные для действия
    }
}
```

### Обработка на бэкенде

**Где:** `backend/utils/AuthHelper.php` → `extractTelegramData()`

**Логика:**
1. Читаем JSON тело запроса
2. Извлекаем Telegram данные из корня JSON
3. Валидируем обязательные поля
4. Возвращаем структурированные данные

## 🔄 Унифицированная обработка

### Единый интерфейс

Независимо от источника данных (WebApp или Bot), система работает через единый интерфейс:

**Вход:** Telegram данные в любом формате
**Выход:** Структурированные данные пользователя

### Приоритет источников данных

1. **HTTP заголовки** (WebApp)
2. **JSON тело запроса** (Bot)
3. **FormData поля** (тестирование)
4. **GET параметры** (отладка)

### Валидация данных

**Где:** `backend/utils/ValidationHelper.php`

**Проверки:**
- `telegram_id` — обязательное число
- `first_name` — опциональная строка
- `last_name` — опциональная строка
- `username` — опциональная строка
- `photo_url` — опциональный URL

## 🛡️ Безопасность

### Валидация подписи WebApp

**Для WebApp запросов:**
1. Получаем `X-Telegram-Hash` из заголовка
2. Собираем данные для проверки
3. Вычисляем HMAC-SHA256 с секретным ключом
4. Сравниваем с полученной подписью

**Код проверки:**
```php
private static function validateWebAppSignature($data, $hash) {
    $secret = getenv('TELEGRAM_BOT_TOKEN');
    $checkString = implode("\n", $data);
    $expectedHash = hash_hmac('sha256', $checkString, $secret);
    
    return hash_equals($expectedHash, $hash);
}
```

### Защита от подмены данных

1. **Валидация формата** — проверка типов данных
2. **Проверка подписи** — для WebApp запросов
3. **Логирование** — все попытки авторизации
4. **Ограничения** — максимальное количество запросов

## 📊 Схема работы

### WebApp сценарий

```
1. Пользователь открывает WebApp в Telegram
2. Telegram автоматически добавляет заголовки с данными
3. WebApp отправляет запрос к нашему API
4. AuthHelper извлекает данные из заголовков
5. Система создает/обновляет пользователя
6. Создается сессия
7. Выполняется бизнес-логика
8. Возвращается результат
```

### Bot сценарий

```
1. Пользователь отправляет команду боту
2. Bot собирает данные пользователя
3. Bot отправляет запрос к нашему API с данными в JSON
4. AuthHelper извлекает данные из JSON
5. Система создает/обновляет пользователя
6. Создается временная сессия (1 час)
7. Выполняется бизнес-логика
8. Bot получает результат (БЕЗ session_id)
9. При следующем запросе бот снова отправляет Telegram данные
```

## 🔧 Технические детали

### Структура данных пользователя

```php
$telegramData = [
    'telegram_id' => 123456789,      // обязательное
    'first_name' => 'Иван',          // обязательное
    'last_name' => 'Иванов',         // опциональное
    'username' => 'ivan_user',       // опциональное
    'photo_url' => 'https://...',    // опциональное
    'auth_date' => 1640995200,       // для WebApp
    'hash' => 'abc123...'            // для WebApp
];
```

### Обработка ошибок

**Типы ошибок:**
- `MISSING_TELEGRAM_DATA` — отсутствуют данные пользователя
- `INVALID_TELEGRAM_ID` — неверный формат Telegram ID
- `INVALID_SIGNATURE` — неверная подпись WebApp
- `EXPIRED_REQUEST` — устаревший запрос

### Логирование

**Что логируется:**
- Источник данных (WebApp/Bot)
- Успешные авторизации
- Ошибки валидации
- Подозрительная активность

## 🧪 Тестирование

### Тестовые данные WebApp

```php
$_SERVER['HTTP_X_TELEGRAM_ID'] = '123456789';
$_SERVER['HTTP_X_TELEGRAM_FIRST_NAME'] = 'Тест';
$_SERVER['HTTP_X_TELEGRAM_LAST_NAME'] = 'Пользователь';
$_SERVER['HTTP_X_TELEGRAM_USERNAME'] = 'test_user';
```

### Тестовые данные Bot

```json
{
    "telegram_id": 123456789,
    "first_name": "Тест",
    "last_name": "Пользователь",
    "username": "test_user",
    "action": "sync_user"
}
```

### Тестовые хелперы

**`TestTelegramDataHelper.php`:**
- Генерация тестовых данных
- Создание подписей для WebApp
- Валидация тестовых запросов

## 📋 Конфигурация

### Настройки Telegram

```php
// config/telegram.php
return [
    'bot_token' => getenv('TELEGRAM_BOT_TOKEN'),
    'webapp_secret' => getenv('TELEGRAM_WEBAPP_SECRET'),
    'validate_signature' => true,
    'max_request_age' => 3600, // 1 час
];
```

### Переменные окружения

```env
TELEGRAM_BOT_TOKEN=1234567890:ABCdefGHIjklMNOpqrsTUVwxyz
TELEGRAM_WEBAPP_SECRET=your_webapp_secret_here
SESSION_LIFETIME=3600
BOT_SESSION_LIFETIME=3600
```

## 🎯 Преимущества интеграции

1. **Единообразие** — одинаковая обработка для WebApp и Bot
2. **Безопасность** — валидация подписей и данных
3. **Гибкость** — поддержка различных форматов данных
4. **Надежность** — подробное логирование и обработка ошибок
5. **Масштабируемость** — легко добавлять новые источники данных

Эта система обеспечивает надежную и безопасную интеграцию с Telegram, позволяя как WebApp, так и Bot работать с единым API через унифицированный интерфейс авторизации. 
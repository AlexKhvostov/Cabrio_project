# 🛠️ UTILS - Обзор утилит

> Централизованные утилиты для backend CabrioRide

## 📋 Назначение

UTILS — это набор утилит, обеспечивающих базовую функциональность для всего backend. Каждая утилита решает конкретную задачу и может использоваться во всех компонентах системы.

## 🏗️ Архитектура

### Принципы UTILS
- **Единая ответственность** — каждая утилита решает одну задачу
- **Переиспользование** — утилиты используются во всех компонентах
- **Консистентность** — единый подход к решению задач
- **Простота** — минимум зависимостей, максимум функциональности

## 📚 Список утилит

### 🔐 Аутентификация и авторизация
- **[AuthHelper](AUTH_HELPER.md)** — извлечение и валидация Telegram данных
- **[SessionHelper](SESSION_HELPER.md)** — управление сессиями пользователей

### 🌐 Глобальный контекст
- **[AppContext](APP_CONTEXT.md)** — глобальный контекст запроса

### 📊 Работа с данными
- **[ExpandHelper](EXPAND_HELPER.md)** — развертывание связанных данных
- **[ReferenceData](REFERENCE_DATA.md)** — справочные данные и константы

### 🗄️ База данных
- **[Database](DATABASE.md)** — Singleton для подключения к MySQL

### 📤 API и валидация
- **[ResponseHelper](RESPONSE_HELPER.md)** — стандартные API ответы
- **[ValidationHelper](VALIDATION_HELPER.md)** — валидация входных данных

### 📝 Логирование
- **[Logger](LOGGER.md)** — централизованное логирование

### ⚙️ Системные утилиты
- **[load_env.php](LOAD_ENV.md)** — загрузка переменных окружения
- **[ValidationException.php](VALIDATION_EXCEPTION.md)** — исключения валидации

## 🔄 Взаимодействие утилит

### Типичный поток обработки запроса
```php
// 1. Загрузка окружения
require_once 'load_env.php';

// 2. Извлечение Telegram данных
$telegramData = AuthHelper::extractTelegramData();

// 3. Валидация данных
$validationResult = AuthHelper::validateTelegramData($telegramData);

// 4. Создание сессии
$sessionResult = SessionHelper::maybeCreateSession($userId);

// 5. Установка глобального контекста
AppContext::setCurrentUser($userData);

// 6. Валидация входных данных
ValidationHelper::requireFields($data, ['field1', 'field2']);

// 7. Работа с базой данных
$pdo = Database::getInstance();

// 8. Развертывание данных для ответа
$expandedData = ExpandHelper::expandCarData($carData);

// 9. Формирование ответа
echo ResponseHelper::success($expandedData);

// 10. Логирование
Logger::info('Request processed successfully');
```

## 📊 Структура зависимостей

```
AuthHelper
├── load_env.php
└── Logger

SessionHelper
├── Database
└── Logger

AppContext
└── (независим)

ExpandHelper
├── ReferenceData
└── Database

ReferenceData
└── (независим)

Database
├── load_env.php
└── Logger

ResponseHelper
└── (независим)

ValidationHelper
└── (независим)

Logger
└── (независим)
```

## 🔧 Конфигурация

### Переменные окружения
```env
# База данных
DB_HOST=localhost
DB_PORT=3306
DB_NAME=cabrioride
DB_USER=root
DB_PASSWORD=password

# Telegram
BOT_TOKEN=your_bot_token

# Логирование
LOG_LEVEL=info
LOG_PATH=backend/logs/

# Валидация
VALIDATION_STRICT_MODE=true
```

### Настройки по умолчанию
```php
// Время жизни сессии (24 часа)
const SESSION_LIFETIME = 86400;

// Максимальное время жизни сессии (7 дней)
const MAX_SESSION_LIFETIME = 604800;

// Путь к логам
const LOG_PATH = __DIR__ . '/../logs/';
```

## 📝 Примеры использования

### Создание пользователя
```php
// 1. Валидация входных данных
ValidationHelper::requireFields($data, ['first_name', 'telegram_id']);
ValidationHelper::validateInt($data['telegram_id'], 'telegram_id');

// 2. Создание в базе данных
$pdo = Database::getInstance();
$userId = User::create($data);

// 3. Создание сессии
$sessionResult = SessionHelper::createOrUpdateSession($userId, [
    'telegram_data' => $telegramData
]);

// 4. Развертывание данных для ответа
$userData = User::findByIdWithDetails($userId);
$expandedUser = ExpandHelper::expandUserData($userData);

// 5. Возврат ответа
echo ResponseHelper::success($expandedUser);

// 6. Логирование
Logger::info('User created', ['user_id' => $userId]);
```

### Проверка автомобиля в клубе
```php
// 1. Валидация входных данных
ValidationHelper::requireFields($data, ['photo_url']);

// 2. Выполнение бизнес-логики
$result = CheckCarInClubAction::handle($data);

// 3. Развертывание результата
if ($result['success']) {
    $expandedCar = ExpandHelper::expandCarData($result['data']);
    echo ResponseHelper::success($expandedCar);
} else {
    echo ResponseHelper::error($result['error']['code'], $result['error']['message']);
}

// 4. Логирование
Logger::info('Car check completed', [
    'found' => $result['success'],
    'user_id' => AppContext::getCurrentUserId()
]);
```

## 🚨 Обработка ошибок

### Централизованная обработка
```php
try {
    // Использование утилит
    $result = someUtility::process($data);
    
} catch (ValidationException $e) {
    // Ошибки валидации
    echo ResponseHelper::error('VALIDATION_ERROR', $e->getMessage());
    
} catch (PDOException $e) {
    // Ошибки базы данных
    Logger::error('Database error', ['error' => $e->getMessage()]);
    echo ResponseHelper::error('DATABASE_ERROR', 'Ошибка базы данных');
    
} catch (Exception $e) {
    // Общие ошибки
    Logger::error('Unexpected error', ['error' => $e->getMessage()]);
    echo ResponseHelper::error('INTERNAL_ERROR', 'Внутренняя ошибка сервера');
}
```

## 📈 Производительность

### Оптимизации
- **Singleton для Database** — одно подключение на запрос
- **Статические массивы в ReferenceData** — быстрый доступ к справочникам
- **Кэширование в ExpandHelper** — избежание повторных запросов
- **Асинхронное логирование** — не блокирует основной поток

### Метрики
- Время выполнения утилит
- Количество обращений к каждой утилите
- Размер данных, обрабатываемых утилитами
- Частота ошибок в утилитах

## 🔗 Интеграция с компонентами

### Controllers
```php
// Все контроллеры используют утилиты
class CarController extends BaseController {
    public function create($data) {
        // Валидация
        ValidationHelper::requireFields($data, ['model']);
        
        // Создание
        $car = Car::create($data);
        
        // Развертывание
        $expandedCar = ExpandHelper::expandCarData($car);
        
        // Ответ
        echo ResponseHelper::success($expandedCar);
    }
}
```

### Actions
```php
// Actions используют утилиты для бизнес-логики
class CreateCarAction {
    public static function handle($data) {
        // Валидация
        ValidationHelper::requireFields($data, ['model', 'owner_id']);
        
        // Бизнес-логика
        $result = self::process($data);
        
        // Логирование
        Logger::info('Car created', ['car_id' => $result['id']]);
        
        return $result;
    }
}
```

### Models
```php
// Модели используют утилиты для работы с данными
class User {
    public static function findByIdWithDetails($id) {
        $user = self::findById($id);
        return ExpandHelper::expandUserData($user);
    }
}
```

## 🔧 Расширение утилит

### Добавление новой утилиты
```php
// 1. Создать файл utils/NewUtility.php
class NewUtility {
    public static function process($data) {
        // Логика утилиты
        return $result;
    }
}

// 2. Добавить в документацию
// 3. Обновить зависимости
// 4. Добавить тесты
```

### Модификация существующих утилит
```php
// 1. Сохранить обратную совместимость
// 2. Добавить новые методы
// 3. Обновить документацию
// 4. Протестировать изменения
```

---

**📚 См. также:** [Архитектура](../ARCHITECTURE/OVERVIEW.md), [Controllers](../CONTROLLERS/OVERVIEW.md), [Actions](../ACTIONS/OVERVIEW.md) 
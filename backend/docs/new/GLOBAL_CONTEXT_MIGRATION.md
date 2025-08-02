# 🌐 Переход на глобальный контекст в CabrioRide

## 📋 Обзор

Данный документ описывает переход от передачи объекта пользователя между функциями к использованию глобального контекста приложения. Это ключевое архитектурное решение, которое упростит код, повысит производительность и обеспечит единообразие во всем приложении.

---

## 🎯 Причины перехода на глобальный контекст

### **1. Проблемы текущего подхода**

#### **Многословность и дублирование**
```php
// Текущий подход - много параметров
function action1($data, $user) {
    // логика
}

function action2($data, $user) {
    // логика
}

// Вызов - дублирование
$result1 = action1($data, $user);
$result2 = action2($data, $user);
```

#### **Легко забыть передать пользователя**
```php
// Ошибка - забыли передать пользователя
$result = action1($data); // Без $user - ошибка!
```

#### **Сложность в цепочках вызовов**
```php
// Сложная цепочка с передачей пользователя
$result1 = action1($data, $user);
$result2 = action2($result1, $user);
$result3 = action3($result2, $user);
```

### **2. Преимущества глобального контекста**

#### **Простота использования**
```php
// Новый подход - просто и чисто
function action1($data) {
    $user = AppContext::getCurrentUser();
    // логика
}

function action2($data) {
    $user = AppContext::getCurrentUser();
    // логика
}

// Вызов - без дублирования
$result1 = action1($data);
$result2 = action2($data);
```

#### **Явные зависимости**
```php
// Видно, что функция использует пользователя
function someAction($data) {
    $user = AppContext::getCurrentUser();
    if (!$user) {
        return ['success' => false, 'error' => 'NO_USER'];
    }
    // логика
}
```

#### **Производительность**
- Пользователь загружается один раз и кэшируется
- Нет лишних запросов к БД
- Нет дублирования данных в памяти

---

## 🏗️ Структура глобального контекста

### **1. Основные компоненты**

#### **AppContext — главный класс контекста**
```php
class AppContext {
    private static $currentUser = null;
    private static $sessionId = null;
    private static $telegramData = null;
    private static $requestId = null;
    private static $startTime = null;
    
    // Методы управления пользователем
    public static function setCurrentUser($user)
    public static function getCurrentUser()
    public static function clearCurrentUser()
    
    // Методы управления сессией
    public static function setSessionId($sessionId)
    public static function getSessionId()
    
    // Методы управления Telegram данными
    public static function setTelegramData($data)
    public static function getTelegramData()
    
    // Методы для отладки и мониторинга
    public static function setRequestId($requestId)
    public static function getRequestId()
    public static function getStartTime()
    public static function clear()
}
```

#### **Данные, хранящиеся в контексте**

**1. Пользователь (currentUser)**
```php
$user = [
    'id' => 123,
    'telegram_id' => 123456789,
    'first_name_tg' => 'Иван',
    'last_name_tg' => 'Иванов',
    'username' => 'ivan_user',
    'role' => 'member',
    'role_id' => 3,
    'city' => 'Москва',
    'email' => 'ivan@example.com',
    'created_at' => '2024-01-15 10:30:00',
    'updated_at' => '2024-01-15 10:30:00'
];
```

**2. Сессия (sessionId)**
```php
$sessionId = 'abc123def456ghi789jkl012mno345pqr678stu901vwx234yz567';
```

**3. Telegram данные (telegramData)**
```php
$telegramData = [
    'telegram_id' => 123456789,
    'first_name' => 'Иван',
    'last_name' => 'Иванов',
    'username' => 'ivan_user',
    'photo_url' => 'https://t.me/i/userpic/320/photo.jpg',
    'auth_date' => 1640995200,
    'hash' => 'abc123def456...'
];
```

**4. Метаданные запроса (requestId, startTime)**
```php
$requestId = 'req_20240115_103000_123456';
$startTime = 1705312200.123456;
```

### **2. Жизненный цикл контекста**

#### **Инициализация (в AuthMiddleware)**
```php
// 1. Извлекаем Telegram данные
$telegramData = AuthHelper::extractTelegramData();

// 2. Синхронизируем пользователя
$userResult = __SyncUserDataAction::handle($telegramData);

// 3. Создаем сессию
$sessionResult = SessionHelper::createOrUpdateSession($userResult['data']['id']);

// 4. Устанавливаем контекст
AppContext::setTelegramData($telegramData);
AppContext::setCurrentUser($userResult['data']);
AppContext::setSessionId($sessionResult['session_id']);
AppContext::setRequestId(generateRequestId());
AppContext::setStartTime(microtime(true));
```

#### **Использование (в Actions и контроллерах)**
```php
// В Action
class __AddCarToUserAction {
    public static function handle($data) {
        $user = AppContext::getCurrentUser();
        if (!$user) {
            return ['success' => false, 'error' => 'NO_USER'];
        }
        
        $data['user_id'] = $user['id'];
        // остальная логика...
    }
}

// В контроллере
class UserController {
    public function getProfile() {
        $user = AppContext::getCurrentUser();
        if (!$user) {
            $this->json(['success' => false, 'error' => 'NO_USER'], 401);
            return;
        }
        
        $this->json(['success' => true, 'data' => $user]);
    }
}
```

#### **Очистка (в конце запроса)**
```php
// В api.php после обработки запроса
AppContext::clear();
```

---

## 🔄 План перехода на глобальный контекст

### **Этап 1: Создание инфраструктуры (День 1)**

#### **1.1 Создать AppContext.php**
```php
// backend/utils/AppContext.php
class AppContext {
    // Полная реализация с методами управления контекстом
}
```

#### **1.2 Создать SessionHelper.php**
```php
// backend/utils/SessionHelper.php
class SessionHelper {
    // Методы управления сессиями
}
```

#### **1.3 Создать AuthMiddleware.php**
```php
// backend/middleware/AuthMiddleware.php
class AuthMiddleware {
    // Централизованная обработка авторизации
}
```

#### **1.4 Обновить AuthHelper.php**
```php
// Добавить методы для работы с Telegram данными
class AuthHelper {
    // Существующие методы остаются
    // Добавляем новые методы
    public static function extractTelegramData()
    public static function validateTelegramData()
}
```

### **Этап 2: Переписывание Actions (День 2)**

#### **2.1 Обновить L1 Actions**
```php
// Было:
class _CreateUserAction {
    public static function handle($data, $user) {
        // логика
    }
}

// Стало:
class _CreateUserAction {
    public static function handle($data) {
        $user = AppContext::getCurrentUser();
        // логика
    }
}
```

**Список L1 Actions для обновления:**
- `_CreateUserAction.php`
- `_CreateCarAction.php`
- `_CreatePhotoAction.php`
- `_CheckCarInDbAction.php`
- `_UpdateOwnerToCarAction.php`
- `_CreateBusinessCardAction.php`
- `_UpdateStatusAction.php`
- `_UpdateUserAction.php`
- `_CheckUserByTelegramIdAction.php`
- `_UpdateRoleUserAction.php`

#### **2.2 Обновить L2 Actions**
```php
// Было:
class __AddCarToUserAction {
    public static function handle($data, $user) {
        // логика
    }
}

// Стало:
class __AddCarToUserAction {
    public static function handle($data) {
        $user = AppContext::getCurrentUser();
        // логика
    }
}
```

**Список L2 Actions для обновления:**
- `__AddCarToUserAction.php`
- `__SearchCarAction.php`
- `__SyncUserDataAction.php`
- `__DropBusinessCardAction.php`

### **Этап 3: Переписывание контроллеров (День 3)**

#### **3.1 Обновить BaseController**
```php
// Добавить методы для работы с контекстом
class BaseController {
    protected function getCurrentUser() {
        return AppContext::getCurrentUser();
    }
    
    protected function requireUser() {
        $user = $this->getCurrentUser();
        if (!$user) {
            $this->json(['success' => false, 'error' => 'NO_USER'], 401);
            exit;
        }
        return $user;
    }
}
```

#### **3.2 Обновить все контроллеры**
```php
// Было:
class UserController {
    public function getProfile($user) {
        $this->json(['success' => true, 'data' => $user]);
    }
}

// Стало:
class UserController {
    public function getProfile() {
        $user = $this->requireUser();
        $this->json(['success' => true, 'data' => $user]);
    }
}
```

**Список контроллеров для обновления:**
- `UserController.php`
- `CarController.php`
- `EventController.php`
- `GuideObjectController.php`
- `ReviewController.php`
- `BusinessCardController.php`
- `PhotoController.php`

### **Этап 4: Обновление роутера (День 3)**

#### **4.1 Интегрировать AuthMiddleware**
```php
// backend/routes/api.php
try {
    // Обрабатываем авторизацию для всех запросов
    $authResult = AuthMiddleware::process();
    if (!$authResult['success']) {
        echo ResponseHelper::error('AUTH_ERROR', $authResult['message']);
        exit;
    }
    
    // Существующая логика роутинга
    if ($route === '/api/users' && $method === 'GET') {
        (new UserController())->getList();
    }
    // ...
    
} finally {
    // Очищаем контекст в конце запроса
    AppContext::clear();
}
```

### **Этап 5: Тестирование (День 4)**

#### **5.1 Создать тесты для новых компонентов**
```php
// backend/_tests/context/
- AppContextTest.php
- SessionHelperTest.php
- AuthMiddlewareTest.php
```

#### **5.2 Обновить тесты Actions**
```php
// Обновить все тесты Actions для работы с контекстом
// Убрать передачу пользователя в тестах
```

#### **5.3 Интеграционное тестирование**
```php
// Протестировать все эндпоинты
// Проверить производительность
// Проверить безопасность
```

---

## 🛡️ Безопасность и производительность

### **1. Безопасность**

#### **Изоляция контекста**
```php
// Каждый запрос имеет свой контекст
// Контекст очищается после каждого запроса
// Нет утечек данных между запросами
```

#### **Валидация данных**
```php
// Все данные в контексте валидируются
// Telegram данные проверяются на подлинность
// Сессии имеют ограниченное время жизни
```

### **2. Производительность**

#### **Кэширование пользователя**
```php
// Пользователь загружается один раз за запрос
// Нет повторных запросов к БД
// Данные кэшируются в памяти
```

#### **Оптимизация запросов**
```php
// Минимальное количество запросов к БД
// Эффективное использование индексов
// Подготовленные запросы
```

---

## 📊 Метрики успешности перехода

### **1. Количественные метрики**
- ✅ Уменьшение количества параметров в функциях на 50%
- ✅ Сокращение дублирования кода на 30%
- ✅ Увеличение производительности на 20%
- ✅ Сокращение времени разработки на 25%

### **2. Качественные метрики**
- ✅ Упрощение кода
- ✅ Улучшение читаемости
- ✅ Снижение количества ошибок
- ✅ Упрощение тестирования

---

## 🚨 Риски и их минимизация

### **1. Риск: Потеря данных пользователя**

**Минимизация:**
```php
// Проверка на каждом этапе
$user = AppContext::getCurrentUser();
if (!$user) {
    // Обработка ошибки
    return ['success' => false, 'error' => 'NO_USER'];
}
```

### **2. Риск: Утечка памяти**

**Минимизация:**
```php
// Обязательная очистка контекста
try {
    // Обработка запроса
} finally {
    AppContext::clear();
}
```

### **3. Риск: Сложность отладки**

**Минимизация:**
```php
// Подробное логирование
Logger::info('Context updated', [
    'user_id' => $user['id'],
    'session_id' => $sessionId,
    'request_id' => $requestId
]);
```

---

## ✅ Чек-лист завершения перехода

### **Инфраструктура**
- [ ] AppContext.php создан и протестирован
- [ ] SessionHelper.php создан и протестирован
- [ ] AuthMiddleware.php создан и протестирован
- [ ] AuthHelper.php обновлен

### **Actions**
- [ ] Все L1 Actions обновлены
- [ ] Все L2 Actions обновлены
- [ ] Тесты Actions обновлены

### **Контроллеры**
- [ ] BaseController обновлен
- [ ] Все контроллеры обновлены
- [ ] Роутер интегрирован с AuthMiddleware

### **Тестирование**
- [ ] Все новые компоненты протестированы
- [ ] Все эндпоинты протестированы
- [ ] Производительность проверена
- [ ] Безопасность проверена

### **Документация**
- [ ] Обновлена документация по архитектуре
- [ ] Созданы примеры использования
- [ ] Обновлены комментарии в коде

---

> **Важно:** Переход на глобальный контекст — это фундаментальное архитектурное решение, которое обеспечит чистоту, производительность и масштабируемость кода CabrioRide на долгие годы. 
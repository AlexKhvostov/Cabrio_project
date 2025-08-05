# 🔐 План внедрения авторизации и сессий в CabrioRide

## 📋 Обзор

Данный документ описывает пошаговый план внедрения централизованной системы авторизации с поддержкой Telegram WebApp и Bot, управления сессиями и интеграции с глобальным контекстом.

---

## 🎯 Цели внедрения

### **1. Централизованная авторизация**
- Единая точка входа для всех запросов
- Поддержка Telegram WebApp и Bot
- Автоматическая синхронизация пользователей
- Проверка прав доступа по ролям

### **2. Система сессий**
- Безопасное управление сессиями
- Автоматическая очистка истекших сессий
- Разные стратегии для WebApp и Bot
- Криптографически стойкие session_id

### **3. Интеграция с глобальным контекстом**
- Единый доступ к данным пользователя
- Кэширование для производительности
- Изоляция контекста между запросами

---

## 🏗️ Архитектура системы авторизации

### **Схема работы**
```
HTTP Request → AuthMiddleware → SessionHelper → AppContext → Controller → Action
                ↓              ↓              ↓
            Telegram Data   Session Mgmt   Global Context
```

### **Компоненты системы**

#### **1. AuthMiddleware — центральная точка**
```php
class AuthMiddleware {
    public static function process() {
        // 1. Извлечение Telegram данных
        // 2. Синхронизация пользователя
        // 3. Создание/обновление сессии
        // 4. Установка глобального контекста
        // 5. Проверка прав доступа
    }
}
```

#### **2. SessionHelper — управление сессиями**
```php
class SessionHelper {
    public static function createOrUpdateSession($userId, $options = [])
    public static function validateSession($sessionId)
    public static function getSessionUser($sessionId)
    public static function destroySession($sessionId)
    public static function cleanupExpiredSessions()
}
```

#### **3. AppContext — глобальный контекст**
```php
class AppContext {
    public static function setCurrentUser($user)
    public static function getCurrentUser()
    public static function setSessionId($sessionId)
    public static function getSessionId()
    public static function clear()
}
```

---

## 📅 Пошаговый план внедрения

### **Этап 1: Базовая инфраструктура (День 1)**

#### **1.1 Создать AppContext.php**
```php
// backend/utils/AppContext.php
<?php
/**
 * AppContext — глобальный контекст приложения
 * 
 * Назначение: Централизованное хранение данных пользователя, сессии и метаданных запроса
 * Использование: AppContext::getCurrentUser(), AppContext::setCurrentUser($user)
 */
class AppContext {
    private static $currentUser = null;
    private static $sessionId = null;
    private static $telegramData = null;
    private static $requestId = null;
    private static $startTime = null;
    
    // Методы управления пользователем
    public static function setCurrentUser($user) {
        self::$currentUser = $user;
        Logger::info('AppContext: user set', ['user_id' => $user['id'] ?? null]);
    }
    
    public static function getCurrentUser() {
        return self::$currentUser;
    }
    
    public static function clearCurrentUser() {
        self::$currentUser = null;
    }
    
    // Методы управления сессией
    public static function setSessionId($sessionId) {
        self::$sessionId = $sessionId;
    }
    
    public static function getSessionId() {
        return self::$sessionId;
    }
    
    // Методы управления Telegram данными
    public static function setTelegramData($data) {
        self::$telegramData = $data;
    }
    
    public static function getTelegramData() {
        return self::$telegramData;
    }
    
    // Методы для отладки и мониторинга
    public static function setRequestId($requestId) {
        self::$requestId = $requestId;
    }
    
    public static function getRequestId() {
        return self::$requestId;
    }
    
    public static function setStartTime($startTime) {
        self::$startTime = $startTime;
    }
    
    public static function getStartTime() {
        return self::$startTime;
    }
    
    public static function clear() {
        self::$currentUser = null;
        self::$sessionId = null;
        self::$telegramData = null;
        self::$requestId = null;
        self::$startTime = null;
        Logger::info('AppContext: cleared');
    }
}
```

#### **1.2 Создать SessionHelper.php**
```php
// backend/utils/SessionHelper.php
<?php
/**
 * SessionHelper — управление сессиями пользователей
 * 
 * Назначение: Создание, валидация, обновление и уничтожение сессий
 * Использование: SessionHelper::createOrUpdateSession($userId)
 */
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Logger.php';
require_once __DIR__ . '/../models/User.php';

class SessionHelper {
    
    /**
     * Создает новую сессию или обновляет существующую
     */
    public static function createOrUpdateSession($userId, $options = []) {
        try {
            $pdo = Database::getInstance();
            
            // Настройки по умолчанию
            $lifetime = $options['lifetime'] ?? 3600; // 1 час
            $ipAddress = $options['ip_address'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
            $userAgent = $options['user_agent'] ?? $_SERVER['HTTP_USER_AGENT'] ?? '';
            
            // Проверяем существующие активные сессии пользователя
            $stmt = $pdo->prepare("
                SELECT COUNT(*) FROM sessions 
                WHERE user_id = ? AND is_active = 1 AND expires_at > NOW()
            ");
            $stmt->execute([$userId]);
            $activeSessions = $stmt->fetchColumn();
            
            // Если активных сессий больше лимита - удаляем старые
            $maxSessions = $options['max_sessions_per_user'] ?? 3;
            if ($activeSessions >= $maxSessions) {
                $stmt = $pdo->prepare("
                    DELETE FROM sessions 
                    WHERE user_id = ? AND is_active = 1 
                    ORDER BY created_at ASC 
                    LIMIT ?
                ");
                $stmt->execute([$userId, $activeSessions - $maxSessions + 1]);
            }
            
            // Генерируем уникальный session_id
            $sessionId = self::generateSessionId();
            
            // Создаем запись в БД
            $stmt = $pdo->prepare("
                INSERT INTO sessions (user_id, session_token, created_at, expires_at, is_active, ip_address, user_agent)
                VALUES (?, ?, NOW(), DATE_ADD(NOW(), INTERVAL ? SECOND), 1, ?, ?)
            ");
            $stmt->execute([$userId, $sessionId, $lifetime, $ipAddress, $userAgent]);
            
            $sessionId = $pdo->lastInsertId();
            
            Logger::info('Session created', [
                'user_id' => $userId,
                'session_id' => $sessionId,
                'lifetime' => $lifetime
            ]);
            
            return [
                'success' => true,
                'session_id' => $sessionId,
                'expires_at' => date('Y-m-d H:i:s', time() + $lifetime),
                'user_id' => $userId
            ];
            
        } catch (Exception $e) {
            Logger::error('SessionHelper::createOrUpdateSession failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => [
                    'code' => 'SESSION_CREATION_ERROR',
                    'message' => 'Ошибка создания сессии: ' . $e->getMessage()
                ]
            ];
        }
    }
    
    /**
     * Проверяет валидность сессии
     */
    public static function validateSession($sessionId) {
        try {
            $pdo = Database::getInstance();
            
            $stmt = $pdo->prepare("
                SELECT s.*, u.* FROM sessions s
                JOIN users u ON s.user_id = u.id
                WHERE s.session_token = ? AND s.is_active = 1 AND s.expires_at > NOW()
            ");
            $stmt->execute([$sessionId]);
            $sessionData = $stmt->fetch();
            
            if (!$sessionData) {
                return ['success' => false, 'error' => 'INVALID_SESSION'];
            }
            
            // Обновляем last_activity
            $stmt = $pdo->prepare("
                UPDATE sessions SET last_activity = NOW() WHERE session_token = ?
            ");
            $stmt->execute([$sessionId]);
            
            return [
                'success' => true,
                'user' => [
                    'id' => $sessionData['user_id'],
                    'telegram_id' => $sessionData['telegram_id'],
                    'first_name_tg' => $sessionData['first_name_tg'],
                    'last_name_tg' => $sessionData['last_name_tg'],
                    'username' => $sessionData['username'],
                    'role_id' => $sessionData['role_id'],
                    'city' => $sessionData['city'],
                    'email' => $sessionData['email']
                ]
            ];
            
        } catch (Exception $e) {
            Logger::error('SessionHelper::validateSession failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => [
                    'code' => 'SESSION_VALIDATION_ERROR',
                    'message' => 'Ошибка валидации сессии: ' . $e->getMessage()
                ]
            ];
        }
    }
    
    /**
     * Уничтожает сессию
     */
    public static function destroySession($sessionId) {
        try {
            $pdo = Database::getInstance();
            
            $stmt = $pdo->prepare("
                UPDATE sessions SET is_active = 0 WHERE session_token = ?
            ");
            $stmt->execute([$sessionId]);
            
            Logger::info('Session destroyed', ['session_id' => $sessionId]);
            
            return [
                'success' => true,
                'message' => 'Сессия успешно уничтожена'
            ];
            
        } catch (Exception $e) {
            Logger::error('SessionHelper::destroySession failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => [
                    'code' => 'SESSION_DESTROY_ERROR',
                    'message' => 'Ошибка уничтожения сессии: ' . $e->getMessage()
                ]
            ];
        }
    }
    
    /**
     * Очищает истекшие сессии
     */
    public static function cleanupExpiredSessions() {
        try {
            $pdo = Database::getInstance();
            
            $stmt = $pdo->prepare("
                DELETE FROM sessions WHERE expires_at < NOW()
            ");
            $stmt->execute();
            $deletedCount = $stmt->rowCount();
            
            Logger::info('Expired sessions cleaned up', ['deleted_count' => $deletedCount]);
            
            return [
                'success' => true,
                'deleted_count' => $deletedCount,
                'message' => "Удалено $deletedCount истекших сессий"
            ];
            
        } catch (Exception $e) {
            Logger::error('SessionHelper::cleanupExpiredSessions failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => [
                    'code' => 'SESSION_CLEANUP_ERROR',
                    'message' => 'Ошибка очистки сессий: ' . $e->getMessage()
                ]
            ];
        }
    }
    
    /**
     * Генерирует криптографически стойкий session_id
     */
    private static function generateSessionId() {
        $randomBytes = random_bytes(32);
        return bin2hex($randomBytes);
    }
}
```

#### **1.3 Обновить AuthHelper.php**
```php
// Добавить методы для работы с Telegram данными
class AuthHelper {
    // Существующие методы остаются без изменений
    
    /**
     * Извлекает Telegram данные из запроса
     */
    public static function extractTelegramData() {
        $data = [];
        
        // Приоритет 1: HTTP заголовки (WebApp)
        if (isset($_SERVER['HTTP_X_TELEGRAM_ID'])) {
            $data['telegram_id'] = (int)$_SERVER['HTTP_X_TELEGRAM_ID'];
        }
        if (isset($_SERVER['HTTP_X_TELEGRAM_FIRST_NAME'])) {
            $data['first_name'] = $_SERVER['HTTP_X_TELEGRAM_FIRST_NAME'];
        }
        if (isset($_SERVER['HTTP_X_TELEGRAM_LAST_NAME'])) {
            $data['last_name'] = $_SERVER['HTTP_X_TELEGRAM_LAST_NAME'];
        }
        if (isset($_SERVER['HTTP_X_TELEGRAM_USERNAME'])) {
            $data['username'] = $_SERVER['HTTP_X_TELEGRAM_USERNAME'];
        }
        if (isset($_SERVER['HTTP_X_TELEGRAM_PHOTO_URL'])) {
            $data['photo_url'] = $_SERVER['HTTP_X_TELEGRAM_PHOTO_URL'];
        }
        
        // Приоритет 2: JSON тело запроса (Bot)
        if (empty($data['telegram_id'])) {
            $input = file_get_contents('php://input');
            $jsonData = json_decode($input, true);
            
            if ($jsonData && isset($jsonData['telegram_id'])) {
                $data['telegram_id'] = (int)$jsonData['telegram_id'];
                $data['first_name'] = $jsonData['first_name'] ?? null;
                $data['last_name'] = $jsonData['last_name'] ?? null;
                $data['username'] = $jsonData['username'] ?? null;
                $data['photo_url'] = $jsonData['photo_url'] ?? null;
            }
        }
        
        // Приоритет 3: FormData (тестирование)
        if (empty($data['telegram_id']) && isset($_POST['telegram_id'])) {
            $data['telegram_id'] = (int)$_POST['telegram_id'];
            $data['first_name'] = $_POST['first_name'] ?? null;
            $data['last_name'] = $_POST['last_name'] ?? null;
            $data['username'] = $_POST['username'] ?? null;
        }
        
        // Приоритет 4: GET параметры (отладка)
        if (empty($data['telegram_id']) && isset($_GET['telegram_id'])) {
            $data['telegram_id'] = (int)$_GET['telegram_id'];
            $data['first_name'] = $_GET['first_name'] ?? null;
            $data['last_name'] = $_GET['last_name'] ?? null;
            $data['username'] = $_GET['username'] ?? null;
        }
        
        return $data;
    }
    
    /**
     * Валидирует Telegram данные
     */
    public static function validateTelegramData($data) {
        if (empty($data['telegram_id'])) {
            return [
                'success' => false,
                'error' => [
                    'code' => 'MISSING_TELEGRAM_ID',
                    'message' => 'Отсутствует telegram_id'
                ]
            ];
        }
        
        if (!is_numeric($data['telegram_id']) || $data['telegram_id'] <= 0) {
            return [
                'success' => false,
                'error' => [
                    'code' => 'INVALID_TELEGRAM_ID',
                    'message' => 'Некорректный telegram_id'
                ]
            ];
        }
        
        return ['success' => true];
    }
}
```

### **Этап 2: Создание AuthMiddleware (День 1)**

#### **2.1 Создать AuthMiddleware.php**
```php
// backend/middleware/AuthMiddleware.php
<?php
/**
 * AuthMiddleware — централизованная обработка авторизации
 * 
 * Назначение: Извлечение Telegram данных, синхронизация пользователя, создание сессии
 * Использование: AuthMiddleware::process()
 */
require_once __DIR__ . '/../utils/AuthHelper.php';
require_once __DIR__ . '/../utils/SessionHelper.php';
require_once __DIR__ . '/../utils/AppContext.php';
require_once __DIR__ . '/../utils/Logger.php';
require_once __DIR__ . '/../actions/level2/__SyncUserDataAction.php';

class AuthMiddleware {
    
    /**
     * Основной метод обработки авторизации
     */
    public static function process() {
        try {
            // 1. Извлекаем Telegram данные
            $telegramData = AuthHelper::extractTelegramData();
            
            // 2. Валидируем данные
            $validationResult = AuthHelper::validateTelegramData($telegramData);
            if (!$validationResult['success']) {
                return $validationResult;
            }
            
            // 3. Синхронизируем пользователя
            $userResult = __SyncUserDataAction::handle($telegramData);
            if (!$userResult['success']) {
                return $userResult;
            }
            
            // 4. Создаем сессию
            $sessionResult = SessionHelper::createOrUpdateSession($userResult['data']['id'], [
                'lifetime' => 3600, // 1 час
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
            
            if (!$sessionResult['success']) {
                return $sessionResult;
            }
            
            // 5. Устанавливаем глобальный контекст
            AppContext::setTelegramData($telegramData);
            AppContext::setCurrentUser($userResult['data']);
            AppContext::setSessionId($sessionResult['session_id']);
            AppContext::setRequestId(self::generateRequestId());
            AppContext::setStartTime(microtime(true));
            
            Logger::info('AuthMiddleware: user authorized', [
                'user_id' => $userResult['data']['id'],
                'telegram_id' => $telegramData['telegram_id'],
                'session_id' => $sessionResult['session_id']
            ]);
            
            return [
                'success' => true,
                'user' => $userResult['data'],
                'session_id' => $sessionResult['session_id'],
                'action' => $userResult['action'] ?? 'authorized'
            ];
            
        } catch (Exception $e) {
            Logger::error('AuthMiddleware::process failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => [
                    'code' => 'AUTH_ERROR',
                    'message' => 'Ошибка авторизации: ' . $e->getMessage()
                ]
            ];
        }
    }
    
    /**
     * Генерирует уникальный ID запроса
     */
    private static function generateRequestId() {
        return 'req_' . date('Ymd_His') . '_' . substr(md5(uniqid()), 0, 6);
    }
}
```

### **Этап 3: Интеграция с роутером (День 2)**

#### **3.1 Обновить api.php**
```php
// backend/routes/api.php
<?php
// Точка входа для всех API-запросов CabrioRide
require_once __DIR__ . '/../utils/load_env.php';
require_once __DIR__ . '/../utils/ResponseHelper.php';
require_once __DIR__ . '/../utils/Logger.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../utils/AppContext.php';

try {
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $method = $_SERVER['REQUEST_METHOD'];
    $route = $_GET['route'] ?? null;
    
    // Логируем запрос
    Logger::info("API Request", [
        'uri' => $uri,
        'method' => $method,
        'route' => $route,
        'timestamp' => date('c')
    ]);
    
    // Обрабатываем авторизацию для всех запросов
    $authResult = AuthMiddleware::process();
    if (!$authResult['success']) {
        http_response_code(401);
        echo ResponseHelper::error('AUTH_ERROR', $authResult['error']['message']);
        exit;
    }
    
    // Простейший роутер (MVP)
    if ($route === '/api/users' && $method === 'GET') {
        require_once __DIR__ . '/../controllers/UserController.php';
        (new UserController())->getList();
    } elseif ($route === '/api/users' && $method === 'POST') {
        require_once __DIR__ . '/../controllers/UserController.php';
        (new UserController())->create();
    }
    // Маршруты для автомобилей
    elseif ($route === '/api/cars' && $method === 'GET') {
        require_once __DIR__ . '/../controllers/CarController.php';
        (new CarController())->getList();
    } elseif ($route === '/api/cars' && $method === 'POST') {
        require_once __DIR__ . '/../controllers/CarController.php';
        (new CarController())->create();
    }
    // Маршруты для событий
    elseif ($route === '/api/events' && $method === 'GET') {
        require_once __DIR__ . '/../controllers/EventController.php';
        (new EventController())->getList();
    } elseif ($route === '/api/events' && $method === 'POST') {
        require_once __DIR__ . '/../controllers/EventController.php';
        (new EventController())->create();
    }
    // Маршруты для гид-объектов
    elseif ($route === '/api/guide-objects' && $method === 'GET') {
        require_once __DIR__ . '/../controllers/GuideObjectController.php';
        (new GuideObjectController())->getList();
    } elseif ($route === '/api/guide-objects' && $method === 'POST') {
        require_once __DIR__ . '/../controllers/GuideObjectController.php';
        (new GuideObjectController())->create();
    }
    // ...добавляйте остальные маршруты по аналогии

    else {
        http_response_code(404);
        echo ResponseHelper::error('NOT_FOUND', 'Маршрут не найден');
    }
    
} catch (Throwable $e) {
    http_response_code(500);
    echo ResponseHelper::error('INTERNAL_ERROR', $e->getMessage());
} finally {
    // Очищаем контекст в конце запроса
    AppContext::clear();
}
```

### **Этап 4: Обновление контроллеров (День 2)**

#### **4.1 Обновить BaseController.php**
```php
// backend/controllers/BaseController.php
<?php
/**
 * BaseController — базовый класс для всех контроллеров CabrioRide.
 * Здесь можно реализовать общие методы: формирование ответа, авторизация, валидация и т.д.
 */
require_once __DIR__ . '/../utils/AppContext.php';

class BaseController
{
    /**
     * Быстро вернуть JSON-ответ с нужным статусом.
     */
    protected function json($data, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Получить текущего пользователя из контекста
     */
    protected function getCurrentUser() {
        return AppContext::getCurrentUser();
    }
    
    /**
     * Требовать наличие пользователя (с проверкой)
     */
    protected function requireUser() {
        $user = $this->getCurrentUser();
        if (!$user) {
            $this->json([
                'success' => false,
                'error' => [
                    'code' => 'NO_USER',
                    'message' => 'Пользователь не найден'
                ]
            ], 401);
            exit;
        }
        return $user;
    }
    
    /**
     * Проверить роль пользователя
     */
    protected function requireRole($requiredRole) {
        $user = $this->requireUser();
        
        if ($user['role'] !== $requiredRole) {
            $this->json([
                'success' => false,
                'error' => [
                    'code' => 'INSUFFICIENT_PERMISSIONS',
                    'message' => 'Недостаточно прав'
                ]
            ], 403);
            exit;
        }
        
        return $user;
    }
}
```

#### **4.2 Обновить UserController.php**
```php
// backend/controllers/UserController.php
<?php
/**
 * UserController — контроллер для работы с пользователями (users).
 */
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/User.php';

class UserController extends BaseController
{
    /**
     * Получить список пользователей
     */
    public function getList()
    {
        try {
            // Проверяем права доступа
            $this->requireRole('admin');
            
            $users = User::getAll();
            $this->json(['success' => true, 'data' => $users]);
        } catch (Throwable $e) {
            $this->json([
                'success' => false,
                'error' => [
                    'code' => 'DB_ERROR',
                    'message' => $e->getMessage()
                ]
            ], 500);
        }
    }

    /**
     * Получить профиль текущего пользователя
     */
    public function getProfile()
    {
        try {
            $user = $this->requireUser();
            $this->json(['success' => true, 'data' => $user]);
        } catch (Throwable $e) {
            $this->json([
                'success' => false,
                'error' => [
                    'code' => 'PROFILE_ERROR',
                    'message' => $e->getMessage()
                ]
            ], 500);
        }
    }

    /**
     * Создать пользователя (для администраторов)
     */
    public function create()
    {
        try {
            // Проверяем права доступа
            $this->requireRole('admin');
            
            // Получаем данные из запроса
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Создаем пользователя через Action
            require_once __DIR__ . '/../actions/level1/_CreateUserAction.php';
            $result = _CreateUserAction::handle($input);
            
            if ($result['success']) {
                $this->json(['success' => true, 'data' => $result['data']], 201);
            } else {
                $this->json([
                    'success' => false,
                    'error' => $result['error']
                ], 400);
            }
        } catch (Throwable $e) {
            $this->json([
                'success' => false,
                'error' => [
                    'code' => 'CREATE_USER_ERROR',
                    'message' => $e->getMessage()
                ]
            ], 500);
        }
    }
}
```

### **Этап 5: Тестирование (День 3)**

#### **5.1 Создать тесты для новых компонентов**
```php
// backend/_tests/auth/AppContextTest.php
<?php
require_once __DIR__ . '/../../utils/AppContext.php';

class AppContextTest {
    public function testSetAndGetCurrentUser() {
        $testUser = ['id' => 123, 'name' => 'Test User'];
        
        AppContext::setCurrentUser($testUser);
        $user = AppContext::getCurrentUser();
        
        assert($user === $testUser, 'User should be set and retrieved correctly');
        
        AppContext::clear();
        echo "✓ AppContext user management test passed\n";
    }
    
    public function testClear() {
        $testUser = ['id' => 123];
        AppContext::setCurrentUser($testUser);
        AppContext::setSessionId('test_session');
        
        AppContext::clear();
        
        assert(AppContext::getCurrentUser() === null, 'User should be cleared');
        assert(AppContext::getSessionId() === null, 'Session should be cleared');
        
        echo "✓ AppContext clear test passed\n";
    }
}

// Запуск тестов
$test = new AppContextTest();
$test->testSetAndGetCurrentUser();
$test->testClear();
```

#### **5.2 Создать тесты для SessionHelper**
```php
// backend/_tests/auth/SessionHelperTest.php
<?php
require_once __DIR__ . '/../../utils/SessionHelper.php';

class SessionHelperTest {
    public function testCreateSession() {
        $result = SessionHelper::createOrUpdateSession(123);
        
        assert($result['success'] === true, 'Session should be created successfully');
        assert(isset($result['session_id']), 'Session ID should be returned');
        assert(isset($result['user_id']), 'User ID should be returned');
        
        echo "✓ SessionHelper create session test passed\n";
    }
    
    public function testValidateSession() {
        // Сначала создаем сессию
        $createResult = SessionHelper::createOrUpdateSession(123);
        
        // Затем валидируем
        $validateResult = SessionHelper::validateSession($createResult['session_id']);
        
        assert($validateResult['success'] === true, 'Session should be valid');
        assert(isset($validateResult['user']), 'User data should be returned');
        
        echo "✓ SessionHelper validate session test passed\n";
    }
}

// Запуск тестов
$test = new SessionHelperTest();
$test->testCreateSession();
$test->testValidateSession();
```

### **Этап 6: Интеграционное тестирование (День 3)**

#### **6.1 Тестирование эндпоинтов**
```php
// backend/_tests/integration/AuthIntegrationTest.php
<?php
/**
 * Интеграционные тесты авторизации
 */

class AuthIntegrationTest {
    
    public function testWebAppAuth() {
        // Симулируем WebApp запрос
        $_SERVER['HTTP_X_TELEGRAM_ID'] = '123456789';
        $_SERVER['HTTP_X_TELEGRAM_FIRST_NAME'] = 'Test';
        $_SERVER['HTTP_X_TELEGRAM_LAST_NAME'] = 'User';
        
        // Вызываем AuthMiddleware
        require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
        $result = AuthMiddleware::process();
        
        assert($result['success'] === true, 'WebApp auth should succeed');
        assert(isset($result['user']), 'User should be returned');
        assert(isset($result['session_id']), 'Session ID should be returned');
        
        echo "✓ WebApp authentication test passed\n";
    }
    
    public function testBotAuth() {
        // Симулируем Bot запрос
        $botData = [
            'telegram_id' => 987654321,
            'first_name' => 'Bot',
            'last_name' => 'User'
        ];
        
        // Устанавливаем JSON данные
        file_put_contents('php://input', json_encode($botData));
        
        // Вызываем AuthMiddleware
        require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
        $result = AuthMiddleware::process();
        
        assert($result['success'] === true, 'Bot auth should succeed');
        assert(isset($result['user']), 'User should be returned');
        
        echo "✓ Bot authentication test passed\n";
    }
}

// Запуск интеграционных тестов
$test = new AuthIntegrationTest();
$test->testWebAppAuth();
$test->testBotAuth();
```

---

## 🛡️ Безопасность и производительность

### **1. Безопасность**

#### **Криптографически стойкие session_id**
```php
// Генерация через random_bytes()
$randomBytes = random_bytes(32);
$sessionId = bin2hex($randomBytes);
```

#### **Валидация Telegram данных**
```php
// Проверка подписи для WebApp
// Валидация формата данных
// Проверка времени запроса
```

#### **Ограничение сессий**
```php
// Максимум 3 активные сессии на пользователя
// Автоматическая очистка истекших сессий
// Логирование подозрительной активности
```

### **2. Производительность**

#### **Кэширование пользователя**
```php
// Пользователь загружается один раз за запрос
// Данные кэшируются в AppContext
// Нет повторных запросов к БД
```

#### **Оптимизация запросов**
```php
// Индексы на ключевых полях sessions
// Подготовленные запросы
// Минимальное количество обращений к БД
```

---

## 📊 Метрики успешности внедрения

### **1. Количественные метрики**
- ✅ Время авторизации < 100ms
- ✅ Успешность авторизации > 99%
- ✅ Количество активных сессий < 1000
- ✅ Время отклика API < 200ms

### **2. Качественные метрики**
- ✅ Безопасность авторизации
- ✅ Стабильность работы
- ✅ Простота использования
- ✅ Легкость отладки

---

## ✅ Чек-лист завершения внедрения

### **Инфраструктура**
- [ ] AppContext.php создан и протестирован
- [ ] SessionHelper.php создан и протестирован
- [ ] AuthMiddleware.php создан и протестирован
- [ ] AuthHelper.php обновлен

### **Интеграция**
- [ ] api.php интегрирован с AuthMiddleware
- [ ] BaseController обновлен
- [ ] UserController обновлен
- [ ] Все контроллеры обновлены

### **Тестирование**
- [ ] Unit тесты созданы и пройдены
- [ ] Интеграционные тесты созданы и пройдены
- [ ] Производительность проверена
- [ ] Безопасность проверена

### **Документация**
- [ ] Обновлена документация по авторизации
- [ ] Созданы примеры использования
- [ ] Обновлены комментарии в коде

---

## 🚨 Риски и их минимизация

### **1. Риск: Проблемы с производительностью**

**Минимизация:**
- Кэширование пользователя в AppContext
- Оптимизация SQL запросов
- Индексы на ключевых полях

### **2. Риск: Проблемы с безопасностью**

**Минимизация:**
- Криптографически стойкие session_id
- Валидация всех входных данных
- Логирование подозрительной активности

### **3. Риск: Проблемы совместимости**

**Минимизация:**
- Fallback на старую логику
- Постепенное внедрение
- Подробное тестирование

---

> **Важно:** Внедрение системы авторизации и сессий — это критически важный этап развития CabrioRide, который обеспечит безопасность, производительность и масштабируемость системы. 
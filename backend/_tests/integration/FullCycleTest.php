<?php

require_once __DIR__ . '/../../utils/AppContext.php';
require_once __DIR__ . '/../../utils/Logger.php';
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../controllers/BaseController.php';
require_once __DIR__ . '/../../controllers/UserController.php';

/**
 * 🧪 Интеграционный тест полного цикла работы с глобальным контекстом
 * 
 * Проверяет:
 * - Работу AuthMiddleware
 * - Установку глобального контекста
 * - Использование контекста в контроллерах
 * - Очистку контекста
 */
class FullCycleTest
{
    /**
     * Запустить все тесты
     */
    public static function runAllTests()
    {
        echo "🧪 Запуск интеграционных тестов полного цикла...\n\n";
        
        $tests = [
            'testAuthMiddlewareProcess',
            'testAuthMiddlewarePublic',
            'testBaseControllerMethods',
            'testUserControllerWithContext',
            'testContextCleanup'
        ];
        
        $passed = 0;
        $failed = 0;
        
        foreach ($tests as $test) {
            try {
                self::$test();
                echo "✅ {$test}: PASSED\n";
                $passed++;
            } catch (Exception $e) {
                echo "❌ {$test}: FAILED - {$e->getMessage()}\n";
                $failed++;
            }
        }
        
        echo "\n📊 Результаты интеграционных тестов:\n";
        echo "✅ Успешно: {$passed}\n";
        echo "❌ Ошибок: {$failed}\n";
        echo "📈 Всего: " . ($passed + $failed) . "\n";
        
        return $failed === 0;
    }

    /**
     * Тест AuthMiddleware::process() с валидными данными
     */
    private static function testAuthMiddlewareProcess()
    {
        echo "\n🔍 DEBUG: testAuthMiddlewareProcess\n";
        echo "📤 Запрос: AuthMiddleware::process()\n";
        echo "📋 Метод: POST (симулируется)\n";
        echo "🔧 Заголовки:\n";
        echo "   - X-Telegram-Bot-Api-Secret-Token: test_token\n";
        echo "   - X-Telegram-User-Id: 123456789\n";
        echo "   - X-Telegram-First-Name: Тест\n";
        echo "   - X-Telegram-Last-Name: Пользователь\n";
        echo "   - X-Telegram-Username: test_user\n";
        
        // Очищаем контекст
        AppContext::clear();
        
        // Симулируем Telegram данные в заголовках
        $_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN'] = 'test_token';
        $_SERVER['HTTP_X_TELEGRAM_USER_ID'] = '123456789';
        $_SERVER['HTTP_X_TELEGRAM_FIRST_NAME'] = 'Тест';
        $_SERVER['HTTP_X_TELEGRAM_LAST_NAME'] = 'Пользователь';
        $_SERVER['HTTP_X_TELEGRAM_USERNAME'] = 'test_user';
        
        // Вызываем AuthMiddleware
        $result = AuthMiddleware::process();
        
        echo "📥 Ответ:\n";
        echo "   " . json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        
        // Проверяем результат
        if (!$result['success']) {
            throw new Exception('AuthMiddleware должен успешно обработать валидные данные');
        }
        
        // Проверяем, что контекст установлен
        if (!AppContext::hasCurrentUser()) {
            throw new Exception('Пользователь должен быть установлен в контексте');
        }
        
        $user = AppContext::getCurrentUser();
        if ($user['telegram_id'] != 123456789) {
            throw new Exception('Неверный telegram_id в контексте');
        }
        
        echo "✅ AuthMiddleware успешно обработал валидные данные\n";
    }

    /**
     * Тест AuthMiddleware::processPublic() для публичных эндпоинтов
     */
    private static function testAuthMiddlewarePublic()
    {
        echo "\n🔍 DEBUG: testAuthMiddlewarePublic\n";
        echo "📤 Запрос: AuthMiddleware::processPublic()\n";
        echo "📋 Метод: GET (публичный)\n";
        echo "🔧 Заголовки: (отсутствуют - публичный запрос)\n";
        
        // Очищаем контекст
        AppContext::clear();
        
        // Вызываем processPublic
        $result = AuthMiddleware::processPublic();
        
        echo "📥 Ответ:\n";
        echo "   " . json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        
        // Проверяем результат
        if (!$result['success']) {
            throw new Exception('AuthMiddleware должен успешно обработать публичный запрос');
        }
        
        // Проверяем, что базовый контекст установлен
        if (!AppContext::getRequestId()) {
            throw new Exception('Request ID должен быть установлен в контексте');
        }
        
        // Проверяем, что пользователь НЕ установлен (публичный запрос)
        if (AppContext::hasCurrentUser()) {
            throw new Exception('Пользователь не должен быть установлен для публичного запроса');
        }
        
        echo "✅ AuthMiddleware успешно обработал публичный запрос\n";
    }

    /**
     * Тест методов BaseController
     */
    private static function testBaseControllerMethods()
    {
        // Очищаем контекст
        AppContext::clear();
        
        // Создаем тестового пользователя
        $testUser = [
            'id' => 123,
            'telegram_id' => 456789,
            'first_name_tg' => 'Тест',
            'last_name_tg' => 'Пользователь',
            'username' => 'test_user',
            'role' => 'member',
            'role_id' => 3
        ];
        
        AppContext::setCurrentUser($testUser);
        AppContext::setRequestId('test_request_123');
        AppContext::setStartTime(microtime(true));
        
        // Создаем экземпляр BaseController
        $controller = new BaseController();
        
        // Тестируем методы
        $user = $controller->getCurrentUser();
        if (!$user || $user['id'] != 123) {
            throw new Exception('getCurrentUser должен вернуть правильного пользователя');
        }
        
        $userId = $controller->getCurrentUserId();
        if ($userId != 123) {
            throw new Exception('getCurrentUserId должен вернуть правильный ID');
        }
        
        $role = $controller->getCurrentUserRole();
        if ($role != 'member') {
            throw new Exception('getCurrentUserRole должен вернуть правильную роль');
        }
        
        if (!$controller->isAuthenticated()) {
            throw new Exception('isAuthenticated должен вернуть true');
        }
        
        if (!$controller->hasRole('member')) {
            throw new Exception('hasRole должен вернуть true для правильной роли');
        }
        
        if ($controller->isAdmin()) {
            throw new Exception('isAdmin должен вернуть false для member');
        }
        
        $requestInfo = $controller->getRequestInfo();
        if (!isset($requestInfo['user_id']) || $requestInfo['user_id'] != 123) {
            throw new Exception('getRequestInfo должен содержать правильную информацию');
        }
        
        echo "  - BaseController методы работают корректно\n";
    }

    /**
     * Тест UserController с установленным контекстом
     */
    private static function testUserControllerWithContext()
    {
        // Очищаем контекст
        AppContext::clear();
        
        // Создаем тестового пользователя с ролью admin
        $testUser = [
            'id' => 456,
            'telegram_id' => 789123,
            'first_name_tg' => 'Админ',
            'last_name_tg' => 'Тест',
            'username' => 'admin_test',
            'role' => 'admin',
            'role_id' => 6
        ];
        
        AppContext::setCurrentUser($testUser);
        AppContext::setRequestId('test_request_456');
        AppContext::setStartTime(microtime(true));
        
        // Создаем экземпляр UserController
        $controller = new UserController();
        
        // Тестируем проверки авторизации
        if (!$controller->isAuthenticated()) {
            throw new Exception('UserController должен видеть авторизованного пользователя');
        }
        
        if (!$controller->isAdmin()) {
            throw new Exception('UserController должен правильно определять роль admin');
        }
        
        if (!$controller->isModerator()) {
            throw new Exception('UserController должен правильно определять модератора');
        }
        
        // Тестируем requireUser
        $user = $controller->requireUser();
        if ($user['id'] != 456) {
            throw new Exception('requireUser должен вернуть правильного пользователя');
        }
        
        echo "  - UserController корректно работает с контекстом\n";
    }

    /**
     * Тест очистки контекста
     */
    private static function testContextCleanup()
    {
        // Устанавливаем данные в контекст
        $testUser = [
            'id' => 999,
            'telegram_id' => 999999,
            'first_name_tg' => 'Тест',
            'last_name_tg' => 'Очистка',
            'username' => 'cleanup_test',
            'role' => 'member'
        ];
        
        AppContext::setCurrentUser($testUser);
        AppContext::setRequestId('test_cleanup_999');
        AppContext::setStartTime(microtime(true));
        
        // Проверяем, что данные установлены
        if (!AppContext::hasCurrentUser()) {
            throw new Exception('Пользователь должен быть установлен перед очисткой');
        }
        
        // Очищаем контекст
        AppContext::clear();
        
        // Проверяем, что данные очищены
        if (AppContext::hasCurrentUser()) {
            throw new Exception('Пользователь должен быть очищен');
        }
        
        if (AppContext::getRequestId()) {
            throw new Exception('Request ID должен быть очищен');
        }
        
        if (AppContext::getSessionId()) {
            throw new Exception('Session ID должен быть очищен');
        }
        
        echo "  - Очистка контекста работает корректно\n";
    }
}

// Запуск тестов, если файл вызван напрямую
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    FullCycleTest::runAllTests();
} 
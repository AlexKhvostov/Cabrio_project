<?php

require_once __DIR__ . '/../../utils/AppContext.php';
require_once __DIR__ . '/../../utils/Logger.php';
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../utils/AuthHelper.php';

/**
 * 🔍 Детальный тест отладки AuthMiddleware
 * 
 * Проверяет все этапы работы AuthMiddleware с подробным выводом
 */
class AuthMiddlewareDebugTest
{
    /**
     * Запустить все тесты отладки
     */
    public static function runAllTests()
    {
        echo "🔍 Запуск детальных тестов отладки AuthMiddleware...\n\n";
        
        $tests = [
            'testExtractTelegramData',
            'testValidateTelegramData',
            'testProcessWithValidData',
            'testProcessWithInvalidData',
            'testProcessPublic',
            'testRequiresAuth'
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
        
        echo "\n📊 Результаты отладочных тестов:\n";
        echo "✅ Успешно: {$passed}\n";
        echo "❌ Ошибок: {$failed}\n";
        echo "📈 Всего: " . ($passed + $failed) . "\n";
        
        return $failed === 0;
    }

    /**
     * Тест извлечения Telegram данных
     */
    private static function testExtractTelegramData()
    {
        echo "\n🔍 DEBUG: testExtractTelegramData\n";
        
        // Симулируем различные источники данных
        $_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN'] = 'test_token_123';
        $_SERVER['HTTP_X_TELEGRAM_USER_ID'] = '987654321';
        $_SERVER['HTTP_X_TELEGRAM_FIRST_NAME'] = 'Отладка';
        $_SERVER['HTTP_X_TELEGRAM_LAST_NAME'] = 'Тест';
        $_SERVER['HTTP_X_TELEGRAM_USERNAME'] = 'debug_test';
        
        echo "📤 Исходные данные:\n";
        echo "   - HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN: " . ($_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN'] ?? 'null') . "\n";
        echo "   - HTTP_X_TELEGRAM_USER_ID: " . ($_SERVER['HTTP_X_TELEGRAM_USER_ID'] ?? 'null') . "\n";
        echo "   - HTTP_X_TELEGRAM_FIRST_NAME: " . ($_SERVER['HTTP_X_TELEGRAM_FIRST_NAME'] ?? 'null') . "\n";
        echo "   - HTTP_X_TELEGRAM_LAST_NAME: " . ($_SERVER['HTTP_X_TELEGRAM_LAST_NAME'] ?? 'null') . "\n";
        echo "   - HTTP_X_TELEGRAM_USERNAME: " . ($_SERVER['HTTP_X_TELEGRAM_USERNAME'] ?? 'null') . "\n";
        
        $telegramData = AuthHelper::extractTelegramData();
        
        echo "📥 Извлеченные данные:\n";
        echo "   " . json_encode($telegramData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        
        if (!$telegramData) {
            throw new Exception('Не удалось извлечь Telegram данные');
        }
        
        if ($telegramData['telegram_id'] != 987654321) {
            throw new Exception('Неверный telegram_id в извлеченных данных');
        }
        
        echo "✅ Извлечение Telegram данных работает корректно\n";
    }

    /**
     * Тест валидации Telegram данных
     */
    private static function testValidateTelegramData()
    {
        echo "\n🔍 DEBUG: testValidateTelegramData\n";
        
        $validData = [
            'telegram_id' => 123456789,
            'first_name' => 'Валидный',
            'last_name' => 'Пользователь',
            'username' => 'valid_user'
        ];
        
        echo "📤 Валидные данные:\n";
        echo "   " . json_encode($validData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        
        $validationResult = AuthHelper::validateTelegramData($validData);
        
        echo "📥 Результат валидации:\n";
        echo "   " . json_encode($validationResult, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        
        if (!$validationResult['success']) {
            throw new Exception('Валидные данные должны пройти проверку');
        }
        
        // Тест невалидных данных
        $invalidData = [
            'telegram_id' => 'invalid_id',
            'first_name' => '',
            'last_name' => null
        ];
        
        echo "📤 Невалидные данные:\n";
        echo "   " . json_encode($invalidData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        
        $invalidValidationResult = AuthHelper::validateTelegramData($invalidData);
        
        echo "📥 Результат валидации невалидных данных:\n";
        echo "   " . json_encode($invalidValidationResult, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        
        if ($invalidValidationResult['success']) {
            throw new Exception('Невалидные данные не должны проходить проверку');
        }
        
        echo "✅ Валидация Telegram данных работает корректно\n";
    }

    /**
     * Тест AuthMiddleware::process() с валидными данными
     */
    private static function testProcessWithValidData()
    {
        echo "\n🔍 DEBUG: testProcessWithValidData\n";
        
        // Очищаем контекст
        AppContext::clear();
        
        // Симулируем валидные Telegram данные
        $_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN'] = 'valid_token_456';
        $_SERVER['HTTP_X_TELEGRAM_USER_ID'] = '456789123';
        $_SERVER['HTTP_X_TELEGRAM_FIRST_NAME'] = 'Процесс';
        $_SERVER['HTTP_X_TELEGRAM_LAST_NAME'] = 'Тест';
        $_SERVER['HTTP_X_TELEGRAM_USERNAME'] = 'process_test';
        
        echo "📤 Исходные данные:\n";
        echo "   - HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN: " . ($_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN'] ?? 'null') . "\n";
        echo "   - HTTP_X_TELEGRAM_USER_ID: " . ($_SERVER['HTTP_X_TELEGRAM_USER_ID'] ?? 'null') . "\n";
        echo "   - HTTP_X_TELEGRAM_FIRST_NAME: " . ($_SERVER['HTTP_X_TELEGRAM_FIRST_NAME'] ?? 'null') . "\n";
        echo "   - HTTP_X_TELEGRAM_LAST_NAME: " . ($_SERVER['HTTP_X_TELEGRAM_LAST_NAME'] ?? 'null') . "\n";
        echo "   - HTTP_X_TELEGRAM_USERNAME: " . ($_SERVER['HTTP_X_TELEGRAM_USERNAME'] ?? 'null') . "\n";
        
        $result = AuthMiddleware::process();
        
        echo "📥 Результат AuthMiddleware::process():\n";
        echo "   " . json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        
        if (!$result['success']) {
            throw new Exception('AuthMiddleware должен успешно обработать валидные данные');
        }
        
        // Проверяем контекст
        if (!AppContext::hasCurrentUser()) {
            throw new Exception('Пользователь должен быть установлен в контексте');
        }
        
        $user = AppContext::getCurrentUser();
        echo "📋 Пользователь в контексте:\n";
        echo "   " . json_encode($user, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        
        if ($user['telegram_id'] != 456789123) {
            throw new Exception('Неверный telegram_id в контексте');
        }
        
        echo "✅ AuthMiddleware::process() с валидными данными работает корректно\n";
    }

    /**
     * Тест AuthMiddleware::process() с невалидными данными
     */
    private static function testProcessWithInvalidData()
    {
        echo "\n🔍 DEBUG: testProcessWithInvalidData\n";
        
        // Очищаем контекст
        AppContext::clear();
        
        // Симулируем невалидные данные
        $_SERVER['HTTP_X_TELEGRAM_USER_ID'] = 'invalid_user_id';
        $_SERVER['HTTP_X_TELEGRAM_FIRST_NAME'] = '';
        
        echo "📤 Невалидные данные:\n";
        echo "   - HTTP_X_TELEGRAM_USER_ID: " . ($_SERVER['HTTP_X_TELEGRAM_USER_ID'] ?? 'null') . "\n";
        echo "   - HTTP_X_TELEGRAM_FIRST_NAME: " . ($_SERVER['HTTP_X_TELEGRAM_FIRST_NAME'] ?? 'null') . "\n";
        
        $result = AuthMiddleware::process();
        
        echo "📥 Результат AuthMiddleware::process():\n";
        echo "   " . json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        
        if ($result['success']) {
            throw new Exception('AuthMiddleware не должен успешно обработать невалидные данные');
        }
        
        // Проверяем, что контекст не установлен
        if (AppContext::hasCurrentUser()) {
            throw new Exception('Пользователь не должен быть установлен в контексте при невалидных данных');
        }
        
        echo "✅ AuthMiddleware::process() с невалидными данными работает корректно\n";
    }

    /**
     * Тест AuthMiddleware::processPublic()
     */
    private static function testProcessPublic()
    {
        echo "\n🔍 DEBUG: testProcessPublic\n";
        
        // Очищаем контекст
        AppContext::clear();
        
        echo "📤 Публичный запрос (без Telegram данных)\n";
        
        $result = AuthMiddleware::processPublic();
        
        echo "📥 Результат AuthMiddleware::processPublic():\n";
        echo "   " . json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        
        if (!$result['success']) {
            throw new Exception('AuthMiddleware должен успешно обработать публичный запрос');
        }
        
        // Проверяем базовый контекст
        if (!AppContext::getRequestId()) {
            throw new Exception('Request ID должен быть установлен в контексте');
        }
        
        // Проверяем, что пользователь НЕ установлен
        if (AppContext::hasCurrentUser()) {
            throw new Exception('Пользователь не должен быть установлен для публичного запроса');
        }
        
        echo "📋 Контекст после публичного запроса:\n";
        echo "   - Request ID: " . (AppContext::getRequestId() ?? 'null') . "\n";
        echo "   - Has User: " . (AppContext::hasCurrentUser() ? 'true' : 'false') . "\n";
        
        echo "✅ AuthMiddleware::processPublic() работает корректно\n";
    }

    /**
     * Тест AuthMiddleware::requiresAuth()
     */
    private static function testRequiresAuth()
    {
        echo "\n🔍 DEBUG: testRequiresAuth\n";
        
        $testCases = [
            ['route' => '/api/users', 'method' => 'GET', 'expected' => true],
            ['route' => '/api/cars', 'method' => 'POST', 'expected' => true],
            ['route' => '/api/health', 'method' => 'GET', 'expected' => false],
            ['route' => '/api/status', 'method' => 'GET', 'expected' => false],
            ['route' => '/api/nonexistent', 'method' => 'GET', 'expected' => true]
        ];
        
        foreach ($testCases as $testCase) {
            $route = $testCase['route'];
            $method = $testCase['method'];
            $expected = $testCase['expected'];
            
            $result = AuthMiddleware::requiresAuth($route, $method);
            
            echo "📤 Тест: {$method} {$route}\n";
            echo "📥 Результат: " . ($result ? 'true' : 'false') . " (ожидалось: " . ($expected ? 'true' : 'false') . ")\n";
            
            if ($result !== $expected) {
                throw new Exception("Неверный результат для {$method} {$route}: получено " . ($result ? 'true' : 'false') . ", ожидалось " . ($expected ? 'true' : 'false'));
            }
        }
        
        echo "✅ AuthMiddleware::requiresAuth() работает корректно\n";
    }
}

// Запуск тестов, если файл вызван напрямую
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    AuthMiddlewareDebugTest::runAllTests();
} 
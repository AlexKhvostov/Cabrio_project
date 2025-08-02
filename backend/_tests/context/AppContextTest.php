<?php

require_once __DIR__ . '/../../utils/AppContext.php';

/**
 * 🧪 Тест для AppContext
 * 
 * Проверяет основные методы работы с глобальным контекстом:
 * - Установка и получение данных пользователя
 * - Управление сессией
 * - Работа с Telegram данными
 * - Метаданные запроса
 * - Очистка контекста
 */
class AppContextTest
{
    /**
     * Запустить все тесты
     */
    public static function runAllTests()
    {
        echo "🧪 Запуск тестов AppContext...\n\n";
        
        $tests = [
            'testUserManagement',
            'testSessionManagement', 
            'testTelegramDataManagement',
            'testRequestMetadata',
            'testContextInfo',
            'testClearContext'
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
        
        echo "\n📊 Результаты тестов:\n";
        echo "✅ Успешно: {$passed}\n";
        echo "❌ Ошибок: {$failed}\n";
        echo "📈 Всего: " . ($passed + $failed) . "\n";
        
        return $failed === 0;
    }

    /**
     * Тест управления пользователем
     */
    private static function testUserManagement()
    {
        // Очищаем контекст перед тестом
        AppContext::clear();
        
        // Проверяем начальное состояние
        if (AppContext::getCurrentUser() !== null) {
            throw new Exception('Пользователь должен быть null в начале');
        }
        
        if (AppContext::hasCurrentUser()) {
            throw new Exception('hasCurrentUser должен возвращать false');
        }
        
        // Устанавливаем пользователя
        $testUser = [
            'id' => 123,
            'telegram_id' => 456789,
            'first_name_tg' => 'Иван',
            'last_name_tg' => 'Иванов',
            'username' => 'ivan_user',
            'role' => 'member'
        ];
        
        AppContext::setCurrentUser($testUser);
        
        // Проверяем установку
        $user = AppContext::getCurrentUser();
        if ($user !== $testUser) {
            throw new Exception('Пользователь не установлен корректно');
        }
        
        if (!AppContext::hasCurrentUser()) {
            throw new Exception('hasCurrentUser должен возвращать true');
        }
        
        // Очищаем пользователя
        AppContext::clearCurrentUser();
        
        if (AppContext::getCurrentUser() !== null) {
            throw new Exception('Пользователь не очищен');
        }
    }

    /**
     * Тест управления сессией
     */
    private static function testSessionManagement()
    {
        // Очищаем контекст перед тестом
        AppContext::clear();
        
        // Проверяем начальное состояние
        if (AppContext::getSessionId() !== null) {
            throw new Exception('Session ID должен быть null в начале');
        }
        
        if (AppContext::hasSession()) {
            throw new Exception('hasSession должен возвращать false');
        }
        
        // Устанавливаем сессию
        $testSessionId = 'abc123def456ghi789jkl012mno345pqr678stu901vwx234yz567';
        AppContext::setSessionId($testSessionId);
        
        // Проверяем установку
        $sessionId = AppContext::getSessionId();
        if ($sessionId !== $testSessionId) {
            throw new Exception('Session ID не установлен корректно');
        }
        
        if (!AppContext::hasSession()) {
            throw new Exception('hasSession должен возвращать true');
        }
    }

    /**
     * Тест управления Telegram данными
     */
    private static function testTelegramDataManagement()
    {
        // Очищаем контекст перед тестом
        AppContext::clear();
        
        // Проверяем начальное состояние
        if (AppContext::getTelegramData() !== null) {
            throw new Exception('Telegram данные должны быть null в начале');
        }
        
        if (AppContext::hasTelegramData()) {
            throw new Exception('hasTelegramData должен возвращать false');
        }
        
        // Устанавливаем Telegram данные
        $testTelegramData = [
            'telegram_id' => 123456789,
            'first_name' => 'Иван',
            'last_name' => 'Иванов',
            'username' => 'ivan_user',
            'photo_url' => 'https://t.me/i/userpic/320/photo.jpg',
            'auth_date' => 1640995200,
            'hash' => 'abc123def456ghi789jkl012mno345pqr678stu901vwx234yz567'
        ];
        
        AppContext::setTelegramData($testTelegramData);
        
        // Проверяем установку
        $telegramData = AppContext::getTelegramData();
        if ($telegramData !== $testTelegramData) {
            throw new Exception('Telegram данные не установлены корректно');
        }
        
        if (!AppContext::hasTelegramData()) {
            throw new Exception('hasTelegramData должен возвращать true');
        }
    }

    /**
     * Тест метаданных запроса
     */
    private static function testRequestMetadata()
    {
        // Очищаем контекст перед тестом
        AppContext::clear();
        
        // Проверяем начальное состояние
        if (AppContext::getRequestId() !== null) {
            throw new Exception('Request ID должен быть null в начале');
        }
        
        if (AppContext::getStartTime() !== null) {
            throw new Exception('Start time должен быть null в начале');
        }
        
        // Устанавливаем метаданные
        $testRequestId = 'req_20240115_103000_123456';
        $testStartTime = microtime(true);
        
        AppContext::setRequestId($testRequestId);
        AppContext::setStartTime($testStartTime);
        
        // Проверяем установку
        $requestId = AppContext::getRequestId();
        if ($requestId !== $testRequestId) {
            throw new Exception('Request ID не установлен корректно');
        }
        
        $startTime = AppContext::getStartTime();
        if ($startTime !== $testStartTime) {
            throw new Exception('Start time не установлен корректно');
        }
        
        // Проверяем время выполнения
        $executionTime = AppContext::getExecutionTime();
        if ($executionTime === null) {
            throw new Exception('Execution time должен быть не null');
        }
        
        if ($executionTime < 0) {
            throw new Exception('Execution time должен быть положительным');
        }
    }

    /**
     * Тест информации о контексте
     */
    private static function testContextInfo()
    {
        // Очищаем контекст перед тестом
        AppContext::clear();
        
        // Проверяем начальное состояние
        $contextInfo = AppContext::getContextInfo();
        $expectedInitial = [
            'user_id' => null,
            'session_id' => null,
            'telegram_id' => null,
            'request_id' => null,
            'start_time' => null,
            'execution_time' => null
        ];
        
        if ($contextInfo !== $expectedInitial) {
            throw new Exception('Начальная информация о контексте некорректна');
        }
        
        if (AppContext::isInitialized()) {
            throw new Exception('Контекст не должен быть инициализирован в начале');
        }
        
        // Устанавливаем данные
        $testUser = ['id' => 123, 'telegram_id' => 456789];
        $testSessionId = 'session123';
        $testTelegramData = ['telegram_id' => 456789];
        $testRequestId = 'req_123';
        $testStartTime = microtime(true);
        
        AppContext::setCurrentUser($testUser);
        AppContext::setSessionId($testSessionId);
        AppContext::setTelegramData($testTelegramData);
        AppContext::setRequestId($testRequestId);
        AppContext::setStartTime($testStartTime);
        
        // Проверяем инициализацию
        if (!AppContext::isInitialized()) {
            throw new Exception('Контекст должен быть инициализирован');
        }
        
        // Проверяем информацию о контексте
        $contextInfo = AppContext::getContextInfo();
        if ($contextInfo['user_id'] !== 123) {
            throw new Exception('user_id в информации о контексте некорректен');
        }
        
        if ($contextInfo['session_id'] !== $testSessionId) {
            throw new Exception('session_id в информации о контексте некорректен');
        }
        
        if ($contextInfo['telegram_id'] !== 456789) {
            throw new Exception('telegram_id в информации о контексте некорректен');
        }
        
        if ($contextInfo['request_id'] !== $testRequestId) {
            throw new Exception('request_id в информации о контексте некорректен');
        }
        
        // Проверяем информацию для логирования
        $logInfo = AppContext::getLogInfo();
        if ($logInfo['user_id'] !== 123) {
            throw new Exception('user_id в лог-информации некорректен');
        }
        
        if (strpos($logInfo['session_id'], 'session123') !== 0) {
            throw new Exception('session_id в лог-информации некорректен');
        }
    }

    /**
     * Тест очистки контекста
     */
    private static function testClearContext()
    {
        // Устанавливаем все данные
        $testUser = ['id' => 123];
        $testSessionId = 'session123';
        $testTelegramData = ['telegram_id' => 456789];
        $testRequestId = 'req_123';
        $testStartTime = microtime(true);
        
        AppContext::setCurrentUser($testUser);
        AppContext::setSessionId($testSessionId);
        AppContext::setTelegramData($testTelegramData);
        AppContext::setRequestId($testRequestId);
        AppContext::setStartTime($testStartTime);
        
        // Проверяем, что данные установлены
        if (!AppContext::hasCurrentUser()) {
            throw new Exception('Пользователь должен быть установлен');
        }
        
        // Очищаем контекст
        AppContext::clear();
        
        // Проверяем очистку
        if (AppContext::getCurrentUser() !== null) {
            throw new Exception('Пользователь не очищен');
        }
        
        if (AppContext::getSessionId() !== null) {
            throw new Exception('Session ID не очищен');
        }
        
        if (AppContext::getTelegramData() !== null) {
            throw new Exception('Telegram данные не очищены');
        }
        
        if (AppContext::getRequestId() !== null) {
            throw new Exception('Request ID не очищен');
        }
        
        if (AppContext::getStartTime() !== null) {
            throw new Exception('Start time не очищен');
        }
        
        if (AppContext::isInitialized()) {
            throw new Exception('Контекст не должен быть инициализирован после очистки');
        }
    }
}

// Запуск тестов, если файл вызван напрямую
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    AppContextTest::runAllTests();
} 
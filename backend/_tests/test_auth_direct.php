<?php
/**
 * Прямой тест авторизации
 */

require_once __DIR__ . '/../utils/load_env.php';
require_once __DIR__ . '/../utils/Logger.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../utils/AuthHelper.php';

echo "🔍 Прямой тест авторизации\n";
echo "=" . str_repeat("=", 40) . "\n";

// Симулируем Telegram данные в заголовках
$_SERVER['HTTP_X_TELEGRAM_USER_ID'] = '123456789';
$_SERVER['HTTP_X_TELEGRAM_USERNAME'] = 'test_user';
$_SERVER['HTTP_X_TELEGRAM_FIRST_NAME'] = 'Test';
$_SERVER['HTTP_X_TELEGRAM_LAST_NAME'] = 'User';
$_SERVER['HTTP_X_TELEGRAM_AUTH_DATE'] = time();
$_SERVER['HTTP_X_TELEGRAM_HASH'] = md5('123456789' . time());

echo "📦 Симулированные заголовки:\n";
echo "- X-Telegram-User-Id: {$_SERVER['HTTP_X_TELEGRAM_USER_ID']}\n";
echo "- X-Telegram-Username: {$_SERVER['HTTP_X_TELEGRAM_USERNAME']}\n";
echo "- X-Telegram-First-Name: {$_SERVER['HTTP_X_TELEGRAM_FIRST_NAME']}\n";
echo "- X-Telegram-Last-Name: {$_SERVER['HTTP_X_TELEGRAM_LAST_NAME']}\n";
echo "- X-Telegram-Auth-Date: {$_SERVER['HTTP_X_TELEGRAM_AUTH_DATE']}\n";
echo "- X-Telegram-Hash: {$_SERVER['HTTP_X_TELEGRAM_HASH']}\n\n";

try {
    // Тестируем извлечение Telegram данных
    echo "🔍 Тестируем извлечение Telegram данных...\n";
    $telegramData = AuthHelper::extractTelegramData();
    echo "✅ Telegram данные: " . json_encode($telegramData, JSON_UNESCAPED_UNICODE) . "\n\n";
    
    // Тестируем валидацию Telegram данных
    echo "🔍 Тестируем валидацию Telegram данных...\n";
    $validationResult = AuthHelper::validateTelegramData($telegramData);
    echo "✅ Результат валидации: " . json_encode($validationResult, JSON_UNESCAPED_UNICODE) . "\n\n";
    
    // Тестируем полный процесс авторизации
    echo "🔍 Тестируем полный процесс авторизации...\n";
    $authResult = AuthMiddleware::process();
    echo "✅ Результат авторизации: " . json_encode($authResult, JSON_UNESCAPED_UNICODE) . "\n\n";
    
} catch (Exception $e) {
    echo "❌ Ошибка: " . $e->getMessage() . "\n";
    echo "📍 Файл: " . $e->getFile() . "\n";
    echo "📍 Строка: " . $e->getLine() . "\n";
    echo "📋 Трейс:\n" . $e->getTraceAsString() . "\n";
} 
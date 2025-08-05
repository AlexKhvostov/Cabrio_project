<?php
/**
 * test_api_call.php
 * 
 * Тест для проверки работы бота с новыми заголовками
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/services/BotService.php';

// Имитируем данные пользователя от Telegram
$userData = [
    'id' => 123456789,
    'username' => 'testuser',
    'first_name' => 'Test',
    'last_name' => 'User',
    'auth_date' => time(),
    'hash' => '' // Пустой хеш для бота
];

// Создаем экземпляр BotService
$botService = new BotService();

// Тестовые данные для API
$apiData = [
    'test' => 'data',
    'user_id' => $userData['id']
];

echo "🧪 Тестируем вызов API с новыми заголовками...\n";
echo "📋 Данные пользователя:\n";
echo "- ID: " . $userData['id'] . "\n";
echo "- Username: " . $userData['username'] . "\n";
echo "- Auth Date: " . $userData['auth_date'] . "\n";
echo "- Hash: " . ($userData['hash'] ?: 'пустой (для бота)') . "\n\n";

// Вызываем API с правильным эндпоинтом
$result = $botService->callBackendApi('/api/health', $apiData, $userData);

echo "📡 Результат вызова API:\n";
echo "- Success: " . ($result['success'] ? 'true' : 'false') . "\n";
echo "- HTTP Code: " . ($result['http_code'] ?? 'unknown') . "\n";
echo "- URL: " . ($_ENV['BACKEND_API_URL'] ?? 'http://localhost/app/backend') . "/routes/api.php?route=/api/health\n";

if (isset($result['data'])) {
    echo "- Response: " . json_encode($result['data'], JSON_UNESCAPED_UNICODE) . "\n";
}

if (isset($result['error'])) {
    echo "- Error: " . $result['error'] . "\n";
}

echo "\n✅ Тест завершен!\n"; 
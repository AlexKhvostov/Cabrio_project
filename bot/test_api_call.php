<?php
/**
 * test_api_call.php
 * 
 * Тест для проверки работы бота с локальными запросами к backend
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/services/BotService.php';

// Имитируем данные пользователя от Telegram
$userData = [
    'id' => 123456789,
    'username' => 'testuser',
    'first_name' => 'Test',
    'last_name' => 'User',
    'auth_date' => time()
];

// Создаем экземпляр BotService
$botService = new BotService();

// Тестовые данные для API
$apiData = [
    'test' => 'data',
    'user_id' => $userData['id']
];

echo "🧪 Тестируем локальные запросы к backend API...\n";
echo "📋 Данные пользователя:\n";
echo "- ID: " . $userData['id'] . "\n";
echo "- Username: " . $userData['username'] . "\n";
echo "- Auth Date: " . $userData['auth_date'] . "\n";
echo "- SYSTEM_TOKEN: " . (substr($_ENV['SYSTEM_TOKEN'] ?? '', 0, 10) . '...') . "\n";
echo "- Local URL: http://localhost/app/backend/routes/api.php\n\n";

// Тестируем разные эндпоинты
$tests = [
    ['/api/health', 'GET', []],
    ['/api/users', 'GET', []],
    ['/api/cars', 'GET', []],
    ['/api/events', 'GET', []]
];

foreach ($tests as $test) {
    list($endpoint, $method, $data) = $test;
    echo "🔗 Тестируем эндпоинт: $endpoint ($method)\n";
    
    // Вызываем API
    $result = $botService->callBackendApi($endpoint, $data, $userData, $method);
    
    echo "📡 Результат:\n";
    echo "- Success: " . ($result['success'] ? 'true' : 'false') . "\n";
    echo "- HTTP Code: " . ($result['http_code'] ?? 'unknown') . "\n";
    
    if (isset($result['data'])) {
        if (isset($result['data']['success'])) {
            echo "- API Success: " . ($result['data']['success'] ? 'true' : 'false') . "\n";
        }
        if (isset($result['data']['error'])) {
            echo "- API Error: " . $result['data']['error']['message'] . "\n";
        }
    }
    
    if (isset($result['error'])) {
        echo "- Error: " . $result['error'] . "\n";
    }
    
    echo "\n";
}

echo "✅ Тест завершен!\n"; 
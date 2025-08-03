<?php
/**
 * Простой тест авторизации
 */

require_once __DIR__ . '/../utils/load_env.php';
require_once __DIR__ . '/../utils/Logger.php';

// URL для тестирования
$baseUrl = 'http://localhost/app/backend/routes/api.php';

echo "🔍 Простой тест авторизации\n";
echo "=" . str_repeat("=", 40) . "\n";

// Тестовые данные пользователя
$testUserData = [
    'telegram_id' => 123456789,
    'username' => 'test_user',
    'first_name' => 'Test',
    'last_name' => 'User'
];

// Тестируем защищенный эндпоинт с авторизацией
$headers = [
    'Content-Type: application/json',
    'X-Telegram-User-Id: ' . $testUserData['telegram_id'],
    'X-Telegram-Username: ' . $testUserData['username'],
    'X-Telegram-First-Name: ' . $testUserData['first_name'],
    'X-Telegram-Last-Name: ' . $testUserData['last_name'],
    'X-Telegram-Auth-Date: ' . time(),
    'X-Telegram-Hash: ' . md5($testUserData['telegram_id'] . time())
];

$url = $baseUrl . '?route=/api/cars';

echo "🔍 Тестируем: /api/cars с авторизацией\n";
echo "📍 Маршрут: /api/cars\n";
echo "👤 Пользователь: {$testUserData['username']}\n";
echo "📦 Заголовки: " . json_encode($headers, JSON_UNESCAPED_UNICODE) . "\n";

$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => implode("\r\n", $headers),
        'timeout' => 30
    ]
]);

$response = file_get_contents($url, false, $context);
$httpCode = $http_response_header[0] ?? 'Unknown';

echo "🌐 URL: $url\n";
echo "📡 HTTP код: $httpCode\n";
echo "📄 Ответ: $response\n";

if (strpos($httpCode, '200') !== false) {
    echo "✅ Эндпоинт работает с авторизацией!\n";
} else {
    echo "❌ Проблема с авторизацией\n";
} 
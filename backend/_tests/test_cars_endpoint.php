<?php
/**
 * Тест защищенного эндпоинта /api/cars
 */

require_once __DIR__ . '/../utils/load_env.php';

// URL для тестирования
$baseUrl = 'http://localhost/app/backend/routes/api.php';

echo "🔍 Тестируем защищенный эндпоинт /api/cars\n";
echo "=" . str_repeat("=", 50) . "\n";

$url = $baseUrl . '?route=/api/cars';

// Тест без авторизации
echo "📡 Тест БЕЗ авторизации:\n";
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => 'Content-Type: application/json'
    ]
]);

$response = file_get_contents($url, false, $context);
$httpCode = $http_response_header[0] ?? 'Unknown';

echo "🌐 URL: $url\n";
echo "📡 HTTP код: $httpCode\n";
echo "📄 Ответ: $response\n\n";

// Тест с Telegram данными
echo "📡 Тест С Telegram данными:\n";
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => [
            'Content-Type: application/json',
            'X-Telegram-User-Id: 123456789',
            'X-Telegram-Username: test_user',
            'X-Telegram-First-Name: Test',
            'X-Telegram-Last-Name: User'
        ]
    ]
]);

$response = file_get_contents($url, false, $context);
$httpCode = $http_response_header[0] ?? 'Unknown';

echo "🌐 URL: $url\n";
echo "📡 HTTP код: $httpCode\n";
echo "📄 Ответ: $response\n"; 
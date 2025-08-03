<?php
/**
 * Тест публичного эндпоинта /api/health
 */

require_once __DIR__ . '/../utils/load_env.php';

// URL для тестирования
$baseUrl = 'http://localhost/app/backend/routes/api.php';

echo "🔍 Тестируем публичный эндпоинт /api/health\n";
echo "=" . str_repeat("=", 50) . "\n";

$url = $baseUrl . '?route=/api/health';

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
echo "📄 Ответ: $response\n";

if (strpos($httpCode, '200') !== false) {
    echo "✅ Эндпоинт работает!\n";
} else {
    echo "❌ Проблема с эндпоинтом\n";
} 
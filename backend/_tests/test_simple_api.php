<?php
/**
 * Простой тест API для диагностики
 */

echo "🔍 Простой тест API\n";
echo "=" . str_repeat("=", 30) . "\n";

// Тестируем простой GET запрос
$url = 'http://localhost/app/backend/routes/api.php?route=/api/health';

echo "🔍 Тестируем: /api/health\n";
echo "🌐 URL: $url\n";

$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'timeout' => 10
    ]
]);

$response = file_get_contents($url, false, $context);
$httpCode = $http_response_header[0] ?? 'Unknown';

echo "📡 HTTP код: $httpCode\n";
echo "📄 Ответ: " . substr($response, 0, 200) . "\n\n";

if ($response === false) {
    echo "❌ Ошибка получения ответа\n";
} else {
    echo "✅ Запрос выполнен\n";
}

echo "✅ Тестирование завершено!\n"; 
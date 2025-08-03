<?php
/**
 * test_api_fix.php
 * 
 * Тестирует исправленный API URL
 */

require_once __DIR__ . '/config.php';

echo "🧪 Тест исправленного API URL...\n\n";

// Формируем URL как в исправленном обработчике
$backendApiUrl = $_ENV['BACKEND_API_URL'] ?? 'http://localhost/app/backend';
$apiUrl = $backendApiUrl . '/routes/api.php';

echo "🔗 Сформированный URL:\n";
echo "backendApiUrl: $backendApiUrl\n";
echo "apiUrl: $apiUrl\n\n";

// Проверяем доступность
echo "🌐 Проверяем доступность...\n";

$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'timeout' => 5
    ]
]);

$response = file_get_contents($apiUrl, false, $context);

if ($response === false) {
    echo "❌ URL недоступен\n";
} else {
    echo "✅ URL доступен\n";
    echo "Ответ: " . substr($response, 0, 200) . "...\n";
}

// Тестируем системный эндпоинт
echo "\n🧪 Тестируем системный эндпоинт...\n";

$systemToken = $_ENV['SYSTEM_TOKEN'] ?? '';
echo "🔑 SYSTEM_TOKEN: " . substr($systemToken, 0, 10) . "...\n";

$testData = [
    'telegram_id' => 123456789,
    'first_name' => 'Test',
    'last_name' => 'User',
    'username' => 'testuser',
    'role_id' => 2
];

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => [
            'Authorization: Bearer ' . $systemToken,
            'Content-Type: application/json'
        ],
        'content' => json_encode($testData)
    ]
]);

$response = file_get_contents($apiUrl . '?route=/api/system/user-sync', false, $context);

if ($response === false) {
    echo "❌ Системный эндпоинт недоступен\n";
} else {
    echo "✅ Системный эндпоинт работает\n";
    $result = json_decode($response, true);
    echo "Ответ: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";
} 
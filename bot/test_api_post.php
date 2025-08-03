<?php
/**
 * test_api_post.php
 * 
 * Тестирует API через POST запрос
 */

echo "🧪 Тест API через POST запрос...\n\n";

$apiUrl = 'http://localhost/app/backend/routes/api.php';
$systemToken = '7762476:AAHongs-versigi098ap5766:AO6';

echo "🔗 API URL: $apiUrl\n";
echo "🔑 Token: " . substr($systemToken, 0, 10) . "...\n\n";

// Тестируем системный эндпоинт через POST
$testData = [
    'telegram_id' => 123456789,
    'first_name' => 'Test',
    'last_name' => 'User',
    'username' => 'testuser',
    'role_id' => 2
];

$postData = http_build_query([
    'route' => '/api/system/user-sync'
]);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => [
            'Authorization: Bearer ' . $systemToken,
            'Content-Type: application/x-www-form-urlencoded'
        ],
        'content' => $postData . '&' . http_build_query($testData)
    ]
]);

echo "📤 Отправляем POST запрос...\n";
$response = file_get_contents($apiUrl, false, $context);

if ($response === false) {
    echo "❌ Запрос не удался\n";
} else {
    echo "✅ Запрос выполнен\n";
    echo "Ответ: " . $response . "\n";
} 
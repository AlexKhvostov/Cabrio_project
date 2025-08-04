<?php
/**
 * Тест для проверки API пользователей и аватарок
 */

require_once __DIR__ . '/backend/utils/load_env.php';
require_once __DIR__ . '/backend/utils/Logger.php';

// Тестируем API endpoint
$url = 'https://virtually-initially-wool-runtime.trycloudflare.com/app/backend/routes/api.php?route=/api/users';

$headers = [
    'Authorization: Bearer 123456789asd',
    'Content-Type: application/json',
    'X-Telegram-User-Id: 287536885',
    'X-Telegram-First-Name: Test',
    'X-Telegram-Username: test_user'
];

$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => implode("\r\n", $headers),
        'timeout' => 30
    ]
]);

echo "🔍 Тестируем API endpoint: $url\n\n";

$response = file_get_contents($url, false, $context);

if ($response === false) {
    echo "❌ Ошибка при запросе к API\n";
    exit(1);
}

$data = json_decode($response, true);

if (!$data) {
    echo "❌ Ошибка парсинга JSON\n";
    echo "Ответ: $response\n";
    exit(1);
}

echo "✅ API ответ получен\n";
echo "Статус: " . ($data['success'] ? 'success' : 'error') . "\n";

if (isset($data['data']) && is_array($data['data'])) {
    echo "Количество пользователей: " . count($data['data']) . "\n\n";
    
    foreach ($data['data'] as $user) {
        echo "👤 Пользователь ID: {$user['id']}\n";
        echo "   Имя: {$user['first_name']}\n";
        echo "   Telegram ID: {$user['telegram_id']}\n";
        
        if (isset($user['photo']) && $user['photo']) {
            echo "   📸 Фото: {$user['photo']['url']}\n";
            echo "   📸 ID фото: {$user['photo']['id']}\n";
        } else {
            echo "   📸 Фото: нет\n";
        }
        
        if (isset($user['telegram_photo_url'])) {
            echo "   📸 Telegram URL: {$user['telegram_photo_url']}\n";
        }
        
        echo "\n";
    }
} else {
    echo "❌ Нет данных пользователей\n";
    echo "Ответ: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
} 
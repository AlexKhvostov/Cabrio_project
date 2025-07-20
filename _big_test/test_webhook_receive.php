<?php
/**
 * test_webhook_receive.php
 * 
 * Тест получения сообщений webhook
 */

// Включаем отображение ошибок
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "🔍 Тест получения сообщений webhook\n\n";

// Симулируем сообщение от Telegram
$testMessage = [
    'update_id' => 999999999,
    'message' => [
        'message_id' => 999,
        'from' => [
            'id' => 123456789,
            'is_bot' => false,
            'first_name' => 'Test',
            'username' => 'testuser'
        ],
        'chat' => [
            'id' => -1002873258290,
            'type' => 'supergroup',
            'title' => 'Test Group'
        ],
        'date' => time(),
        'text' => 'Тестовое сообщение',
        'photo' => [
            [
                'file_id' => 'test_file_id',
                'file_unique_id' => 'test_unique_id',
                'width' => 100,
                'height' => 100
            ]
        ]
    ]
];

echo "📤 Симулируем отправку сообщения в webhook:\n";
echo "URL: https://physicians-reasons-monetary-disks.trycloudflare.com/app/bot/webhook.php\n";
echo "Message: " . json_encode($testMessage, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

// Отправляем тестовое сообщение в webhook
$webhookUrl = "https://physicians-reasons-monetary-disks.trycloudflare.com/app/bot/webhook.php";
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => json_encode($testMessage)
    ]
]);

$response = file_get_contents($webhookUrl, false, $context);

echo "📥 Ответ webhook:\n";
if ($response !== false) {
    echo "✅ Webhook ответил\n";
    echo "Response: " . $response . "\n";
} else {
    echo "❌ Webhook не ответил\n";
}

echo "\n🎯 Проверьте логи:\n";
echo "1. Посмотрите bot/webhook.log на новые записи\n";
echo "2. Если записей нет, проблема в логировании\n";
echo "3. Если записи есть, проблема в обработке\n";
?> 
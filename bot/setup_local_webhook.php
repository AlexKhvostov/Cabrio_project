<?php
/**
 * setup_local_webhook.php
 * 
 * Настраивает webhook на локальный сервер для тестирования
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/utils/Logger.php';

$botToken = $_ENV['BOT_TOKEN'] ?? '';

if (empty($botToken)) {
    echo "❌ BOT_TOKEN не настроен\n";
    exit(1);
}

echo "🔧 Настраиваем webhook на локальный сервер...\n";

// URL для локального webhook
$webhookUrl = "http://localhost/app/bot/webhook.php";

// Проверяем, доступен ли локальный сервер
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'timeout' => 5
    ]
]);

$response = file_get_contents($webhookUrl, false, $context);

if ($response === false) {
    echo "❌ Локальный сервер недоступен\n";
    echo "Убедитесь, что XAMPP запущен и Apache работает\n";
    exit(1);
}

echo "✅ Локальный сервер доступен\n";

// Настраиваем webhook
$webhookData = [
    'url' => $webhookUrl,
    'allowed_updates' => ['message', 'callback_query', 'chat_member']
];

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => json_encode($webhookData)
    ]
]);

$response = file_get_contents("https://api.telegram.org/bot{$botToken}/setWebhook", false, $context);

if ($response === false) {
    echo "❌ Не удалось настроить webhook\n";
    exit(1);
}

$result = json_decode($response, true);

if ($result['ok'] ?? false) {
    echo "✅ Webhook успешно настроен на локальный сервер\n";
    echo "URL: $webhookUrl\n";
    echo "Разрешенные обновления: message, callback_query, chat_member\n";
} else {
    echo "❌ Ошибка настройки webhook: " . ($result['description'] ?? 'неизвестная ошибка') . "\n";
}

echo "\n⚠️ ВАЖНО: Локальный webhook работает только для тестирования!\n";
echo "Для продакшена нужно настроить Cloudflare туннель.\n"; 
<?php
/**
 * test_webhook_url.php
 * 
 * Тест и установка webhook URL
 */

// Включаем отображение ошибок
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "🔍 Тест и установка webhook URL\n\n";

// Загружаем конфигурацию
require_once __DIR__ . '/../bot/config.php';

$botToken = getConfig('bot_token');

echo "📋 Конфигурация:\n";
echo "Bot Token: " . substr($botToken, 0, 10) . "...\n\n";

// Текущий webhook URL
$currentWebhookUrl = "https://physicians-reasons-monetary-disks.trycloudflare.com/app/bot/webhook.php";

echo "🔗 Текущий webhook URL:\n";
echo $currentWebhookUrl . "\n\n";

// Проверяем доступность webhook URL
echo "🔍 Проверка доступности webhook:\n";
$webhookTest = file_get_contents($currentWebhookUrl);
if ($webhookTest !== false) {
    echo "✅ Webhook доступен\n";
} else {
    echo "❌ Webhook недоступен\n";
}

// Устанавливаем webhook заново
echo "\n📤 Установка webhook:\n";
$setWebhookUrl = "https://api.telegram.org/bot{$botToken}/setWebhook";
$webhookData = [
    'url' => $currentWebhookUrl,
    'allowed_updates' => ['message', 'callback_query', 'my_chat_member'],
    'drop_pending_updates' => true
];

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => json_encode($webhookData)
    ]
]);

$response = file_get_contents($setWebhookUrl, false, $context);
$result = json_decode($response, true);

if ($result['ok']) {
    echo "✅ Webhook установлен успешно\n";
    echo "Result: " . ($result['result'] ? 'true' : 'false') . "\n";
} else {
    echo "❌ Ошибка установки webhook: " . $result['description'] . "\n";
}

// Проверяем webhook info после установки
echo "\n🔍 Информация о webhook после установки:\n";
$webhookInfoUrl = "https://api.telegram.org/bot{$botToken}/getWebhookInfo";
$webhookInfo = file_get_contents($webhookInfoUrl);
$webhookData = json_decode($webhookInfo, true);

if ($webhookData['ok']) {
    $info = $webhookData['result'];
    echo "URL: " . ($info['url'] ?? 'Не установлен') . "\n";
    echo "Pending updates: " . ($info['pending_update_count'] ?? 0) . "\n";
    echo "Last error: " . ($info['last_error_message'] ?? 'Нет ошибок') . "\n";
} else {
    echo "❌ Ошибка получения информации о webhook\n";
}

echo "\n🎯 Рекомендации:\n";
echo "1. Отправьте сообщение в группу\n";
echo "2. Проверьте логи в bot/webhook.log\n";
echo "3. Если логи пустые, проверьте доступность webhook URL\n";
echo "4. Убедитесь, что Cloudflare Tunnel работает\n";
?> 
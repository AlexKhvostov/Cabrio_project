<?php
/**
 * test_webhook_status.php
 * 
 * Тест статуса webhook
 */

// Включаем отображение ошибок
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "🔍 Тест статуса webhook\n\n";

// Загружаем конфигурацию
require_once __DIR__ . '/../bot/config.php';

$botToken = getConfig('bot_token');
$mainChatId = getConfig('main_chat_id');

echo "📋 Конфигурация:\n";
echo "Bot Token: " . substr($botToken, 0, 10) . "...\n";
echo "Main Chat ID: " . $mainChatId . "\n\n";

// Проверяем текущий webhook
$webhookInfoUrl = "https://api.telegram.org/bot{$botToken}/getWebhookInfo";
$webhookInfo = file_get_contents($webhookInfoUrl);
$webhookData = json_decode($webhookInfo, true);

echo "🔗 Webhook Info:\n";
if ($webhookData['ok']) {
    $info = $webhookData['result'];
    echo "URL: " . ($info['url'] ?? 'Не установлен') . "\n";
    echo "Has custom certificate: " . ($info['has_custom_certificate'] ? 'Да' : 'Нет') . "\n";
    echo "Pending update count: " . ($info['pending_update_count'] ?? 0) . "\n";
    echo "Last error date: " . ($info['last_error_date'] ?? 'Нет ошибок') . "\n";
    echo "Last error message: " . ($info['last_error_message'] ?? 'Нет ошибок') . "\n";
    echo "Max connections: " . ($info['max_connections'] ?? 'Не указано') . "\n";
} else {
    echo "❌ Ошибка получения информации о webhook\n";
}

echo "\n🎯 Проверка групповых сообщений:\n";
echo "1. Убедитесь, что бот добавлен в группу\n";
echo "2. Проверьте, что webhook URL правильный\n";
echo "3. Отправьте фото с '?' в группу\n";
echo "4. Проверьте логи в bot/webhook.log\n\n";

echo "💡 Возможные проблемы:\n";
echo "   • Бот не добавлен в группу\n";
echo "   • Неправильный webhook URL\n";
echo "   • Блокировка webhook на сервере\n";
echo "   • Ошибки в обработке сообщений\n";
?> 
<?php
/**
 * check_webhook.php
 * 
 * Проверяет состояние webhook бота
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/utils/Logger.php';

$botToken = $_ENV['BOT_TOKEN'] ?? '';

if (empty($botToken)) {
    echo "❌ BOT_TOKEN не настроен\n";
    exit(1);
}

echo "🔍 Проверяем состояние webhook...\n";

// Получаем информацию о webhook
$webhookInfo = file_get_contents("https://api.telegram.org/bot{$botToken}/getWebhookInfo");

if ($webhookInfo === false) {
    echo "❌ Не удалось получить информацию о webhook\n";
    exit(1);
}

$info = json_decode($webhookInfo, true);

echo "📊 Информация о webhook:\n";
echo "URL: " . ($info['result']['url'] ?? 'не установлен') . "\n";
echo "Активен: " . (($info['ok'] ?? false) ? 'да' : 'нет') . "\n";
echo "Ошибки: " . ($info['result']['last_error_message'] ?? 'нет') . "\n";
echo "Последняя ошибка: " . ($info['result']['last_error_date'] ?? 'нет') . "\n";

if (isset($info['result']['allowed_updates'])) {
    echo "Разрешенные обновления: " . implode(', ', $info['result']['allowed_updates']) . "\n";
}

echo "\n✅ Проверка завершена\n"; 
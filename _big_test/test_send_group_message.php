<?php
/**
 * test_send_group_message.php
 * 
 * Тест отправки сообщения в группу
 */

// Включаем отображение ошибок
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "🔍 Тест отправки сообщения в группу\n\n";

// Загружаем конфигурацию
require_once __DIR__ . '/../bot/config.php';

$botToken = getConfig('bot_token');
$clubChatId = getConfig('club_chat_id');

echo "📋 Конфигурация:\n";
echo "Bot Token: " . substr($botToken, 0, 10) . "...\n";
echo "Club Chat ID: " . $clubChatId . "\n\n";

// Отправляем тестовое сообщение в группу
$sendMessageUrl = "https://api.telegram.org/bot{$botToken}/sendMessage";
$messageData = [
    'chat_id' => $clubChatId,
    'text' => "🧪 Тестовое сообщение от бота\n\nЭто сообщение для проверки работы webhook.\n\nЕсли вы видите это сообщение, значит бот может отправлять сообщения в группу.\n\nПопробуйте отправить фото с '?' для тестирования распознавания номеров."
];

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => json_encode($messageData)
    ]
]);

$response = file_get_contents($sendMessageUrl, false, $context);
$result = json_decode($response, true);

echo "📤 Результат отправки:\n";
if ($result['ok']) {
    echo "✅ Сообщение отправлено успешно\n";
    echo "Message ID: " . $result['result']['message_id'] . "\n";
    echo "Chat ID: " . $result['result']['chat']['id'] . "\n";
    echo "Chat Type: " . $result['result']['chat']['type'] . "\n";
    echo "Date: " . date('Y-m-d H:i:s', $result['result']['date']) . "\n";
} else {
    echo "❌ Ошибка отправки: " . $result['description'] . "\n";
    echo "Error Code: " . $result['error_code'] . "\n";
}

echo "\n🎯 Следующие шаги:\n";
echo "1. Проверьте, появилось ли сообщение в группе\n";
echo "2. Отправьте фото с '?' в группу\n";
echo "3. Проверьте логи в bot/webhook.log\n";
echo "4. Если логи пустые, проблема в webhook\n";
echo "5. Если логи есть, но бот не отвечает, проблема в обработке\n";
?> 
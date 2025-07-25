<?php
/**
 * test_bot_in_group.php
 * 
 * Тест членства бота в группе
 */

// Включаем отображение ошибок
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "🔍 Тест членства бота в группе\n\n";

// Загружаем конфигурацию
require_once __DIR__ . '/../bot/config.php';

$botToken = getConfig('bot_token');
$clubChatId = getConfig('club_chat_id');

echo "📋 Конфигурация:\n";
echo "Bot Token: " . substr($botToken, 0, 10) . "...\n";
echo "Club Chat ID: " . $clubChatId . "\n\n";

// Получаем информацию о боте
$botInfoUrl = "https://api.telegram.org/bot{$botToken}/getMe";
$botInfo = file_get_contents($botInfoUrl);
$botData = json_decode($botInfo, true);

if ($botData['ok']) {
    $bot = $botData['result'];
    echo "🤖 Информация о боте:\n";
    echo "ID: " . $bot['id'] . "\n";
    echo "Username: @" . $bot['username'] . "\n";
    echo "Name: " . $bot['first_name'] . "\n";
    echo "Can join groups: " . ($bot['can_join_groups'] ? 'Да' : 'Нет') . "\n";
    echo "Can read all group messages: " . ($bot['can_read_all_group_messages'] ? 'Да' : 'Нет') . "\n";
    echo "Supports inline queries: " . ($bot['supports_inline_queries'] ? 'Да' : 'Нет') . "\n\n";
} else {
    echo "❌ Ошибка получения информации о боте\n\n";
}

// Проверяем членство бота в группе
$chatMemberUrl = "https://api.telegram.org/bot{$botToken}/getChatMember";
$chatMemberData = [
    'chat_id' => $clubChatId,
    'user_id' => $bot['id']
];

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => json_encode($chatMemberData)
    ]
]);

$chatMemberResponse = file_get_contents($chatMemberUrl, false, $context);
$chatMemberResult = json_decode($chatMemberResponse, true);

echo "👥 Членство в группе:\n";
if ($chatMemberResult['ok']) {
    $member = $chatMemberResult['result'];
    echo "Status: " . $member['status'] . "\n";
    echo "User ID: " . $member['user']['id'] . "\n";
    echo "Username: @" . $member['user']['username'] . "\n";
    echo "First Name: " . $member['user']['first_name'] . "\n";
    
    if (isset($member['is_member'])) {
        echo "Is Member: " . ($member['is_member'] ? 'Да' : 'Нет') . "\n";
    }
    
    if (isset($member['can_read_messages'])) {
        echo "Can Read Messages: " . ($member['can_read_messages'] ? 'Да' : 'Нет') . "\n";
    }
    
    if (isset($member['can_send_messages'])) {
        echo "Can Send Messages: " . ($member['can_send_messages'] ? 'Да' : 'Нет') . "\n";
    }
    
} else {
    echo "❌ Ошибка проверки членства: " . $chatMemberResult['description'] . "\n";
}

echo "\n🎯 Рекомендации:\n";
echo "1. Убедитесь, что бот добавлен в группу\n";
echo "2. Проверьте права бота (должен читать сообщения)\n";
echo "3. Отправьте тестовое сообщение в группу\n";
echo "4. Проверьте логи webhook\n";
?> 
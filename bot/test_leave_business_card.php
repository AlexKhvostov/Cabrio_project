<?php
/**
 * test_leave_business_card.php
 * 
 * Тестирует L3 Action leave-business-card с реальными Telegram данными
 */

require_once __DIR__ . '/services/BotService.php';
require_once __DIR__ . '/utils/Logger.php';

echo "🧪 Тестируем L3 Action leave-business-card...\n";

// Создаем экземпляр BotService
$botService = new BotService();

// Тестовые данные пользователя (как в реальном Telegram)
$userData = [
    'id' => 287536885, // Реальный Telegram ID
    'username' => 'LihachOK',
    'first_name' => 'Lex',
    'last_name' => null,
    'auth_date' => time()
];

// Тестовые данные для API (минимальный base64)
$apiData = [
    'photo' => 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==', // 1x1 пиксель
    'user_id' => $userData['id'],
    'location' => 'group_chat'
];

echo "📋 Данные пользователя:\n";
echo "- ID: " . $userData['id'] . "\n";
echo "- Username: " . $userData['username'] . "\n";
echo "- First Name: " . $userData['first_name'] . "\n";
echo "- Auth Date: " . $userData['auth_date'] . "\n";

echo "\n🔗 Тестируем эндпоинт: /api/actions/leave-business-card (POST)\n";

// Вызываем API
$result = $botService->callBackendApi('/api/actions/leave-business-card', $apiData, $userData);

echo "📡 Результат:\n";
echo "- Success: " . ($result['success'] ? 'true' : 'false') . "\n";
echo "- HTTP Code: " . $result['http_code'] . "\n";

if ($result['success'] && isset($result['data']['success'])) {
    echo "- API Success: " . ($result['data']['success'] ? 'true' : 'false') . "\n";
    
    if (!$result['data']['success'] && isset($result['data']['error'])) {
        echo "- Error Code: " . $result['data']['error']['code'] . "\n";
        echo "- Error Message: " . $result['data']['error']['message'] . "\n";
    } else {
        echo "- Data: " . json_encode($result['data'], JSON_UNESCAPED_UNICODE) . "\n";
    }
} else {
    echo "- Raw Response: " . substr(json_encode($result), 0, 500) . "...\n";
}

echo "\n✅ Тест завершен!\n";
?> 
<?php
/**
 * test_webhook_new_members.php
 * 
 * Тест webhook с new_chat_members
 */

// Включаем отображение ошибок
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "🔍 Тест webhook с new_chat_members\n\n";

// Симулируем webhook запрос с new_chat_members
$webhookData = [
    'update_id' => 123456789,
    'message' => [
        'message_id' => 999,
        'from' => [
            'id' => 123456789,
            'is_bot' => false,
            'first_name' => 'Admin',
            'username' => 'admin'
        ],
        'chat' => [
            'id' => -1002873258290,
            'title' => 'Cabrio Ride test',
            'type' => 'supergroup'
        ],
        'date' => time(),
        'new_chat_members' => [
            [
                'id' => 5625181605,
                'is_bot' => false,
                'first_name' => 'fotokubik.by',
                'username' => 'fotokubikby',
                'language_code' => 'ru'
            ]
        ]
    ]
];

echo "📤 Симулируем webhook запрос:\n";
echo "URL: https://your-domain.com/bot/webhook.php\n";
echo "Method: POST\n";
echo "Content-Type: application/json\n\n";

echo "📋 Данные запроса:\n";
echo json_encode($webhookData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
echo "\n\n";

echo "🎯 Ожидаемая обработка в webhook.php:\n";
echo "1. Получение данных через php://input\n";
echo "2. Декодирование JSON\n";
echo "3. Проверка наличия update['message']\n";
echo "4. Проверка наличия update['message']['new_chat_members']\n";
echo "5. Вызов handler->handle() с сообщением\n";
echo "6. Обработка в MessageHandler::handleNewChatMembers()\n\n";

echo "✅ Ожидаемый результат:\n";
echo "1. Запись в лог: 'Processing new chat members'\n";
echo "2. Запись в лог: 'New chat members'\n";
echo "3. Запись в лог: 'Welcome message sent for'\n";
echo "4. Отправка приветственного сообщения в группу\n\n";

echo "🔍 Проверка:\n";
echo "1. Откройте bot/logs/2025-07-20.log\n";
echo "2. Найдите записи с 'new_chat_members'\n";
echo "3. Проверьте сообщение в группе\n\n";

echo "💡 Для тестирования:\n";
echo "1. Добавьте нового участника в группу\n";
echo "2. Проверьте логи: Get-Content 'bot/logs/2025-07-20.log' -Tail 20\n";
echo "3. Проверьте сообщение в группе\n";
?> 
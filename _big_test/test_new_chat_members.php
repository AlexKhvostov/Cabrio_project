<?php
/**
 * test_new_chat_members.php
 * 
 * Тест обработки new_chat_members
 */

// Включаем отображение ошибок
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "🔍 Тест обработки new_chat_members\n\n";

// Симулируем сообщение с новыми участниками
$newMembersMessage = [
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
];

echo "📤 Симулируем добавление нового участника:\n";
echo "Пользователь: @fotokubikby\n";
echo "Имя: fotokubik.by\n";
echo "Chat ID: -1002873258290\n";
echo "Ожидаемое сообщение: Приветственное сообщение\n\n";

echo "🎯 Логика обработки:\n";
echo "1. Проверяется наличие new_chat_members\n";
echo "2. Проверяется, что это основная группа клуба\n";
echo "3. Пропускаются боты (is_bot = true)\n";
echo "4. Отправляется приветственное сообщение для каждого участника\n\n";

echo "✅ Ожидаемое приветственное сообщение:\n";
echo "👋 Привет, @fotokubikby!\n";
echo "🎉 Добро пожаловать в клуб CabrioRide!\n";
echo "💬 Расскажи пару слов о себе и переходи в бот для регистрации:\n";
echo "👉 @CabrioControl_bot\n\n";

echo "🔍 Проверка в логах:\n";
echo "1. Ищите запись 'New chat members'\n";
echo "2. Ищите запись 'Welcome message sent for'\n";
echo "3. Проверьте сообщение в группе\n\n";

echo "💡 Теперь бот должен реагировать на:\n";
echo "   • new_chat_members (добавление участников)\n";
echo "   • left_chat_member (выход участников)\n";
echo "   • chat_member (изменения статуса)\n";
?> 
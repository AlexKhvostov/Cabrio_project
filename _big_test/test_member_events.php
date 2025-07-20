<?php
/**
 * test_member_events.php
 * 
 * Тест симуляции событий участников
 */

// Включаем отображение ошибок
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "🔍 Тест симуляции событий участников\n\n";

// Симулируем событие присоединения участника
$joinEvent = [
    'update_id' => 999999999,
    'chat_member' => [
        'chat' => [
            'id' => -1002873258290,
            'title' => 'Cabrio Ride test',
            'type' => 'supergroup'
        ],
        'from' => [
            'id' => 123456789,
            'is_bot' => false,
            'first_name' => 'Тест',
            'username' => 'testuser'
        ],
        'date' => time(),
        'old_chat_member' => [
            'user' => [
                'id' => 123456789,
                'is_bot' => false,
                'first_name' => 'Тест',
                'username' => 'testuser'
            ],
            'status' => 'left'
        ],
        'new_chat_member' => [
            'user' => [
                'id' => 123456789,
                'is_bot' => false,
                'first_name' => 'Тест',
                'username' => 'testuser'
            ],
            'status' => 'member'
        ]
    ]
];

echo "📤 Симулируем присоединение участника:\n";
echo "Пользователь: @testuser\n";
echo "Статус: member (присоединился)\n";
echo "Ожидаемое сообщение: Приветственное сообщение\n\n";

// Симулируем событие выхода участника
$leaveEvent = [
    'update_id' => 999999998,
    'chat_member' => [
        'chat' => [
            'id' => -1002873258290,
            'title' => 'Cabrio Ride test',
            'type' => 'supergroup'
        ],
        'from' => [
            'id' => 123456789,
            'is_bot' => false,
            'first_name' => 'Тест',
            'username' => 'testuser'
        ],
        'date' => time(),
        'old_chat_member' => [
            'user' => [
                'id' => 123456789,
                'is_bot' => false,
                'first_name' => 'Тест',
                'username' => 'testuser'
            ],
            'status' => 'member'
        ],
        'new_chat_member' => [
            'user' => [
                'id' => 123456789,
                'is_bot' => false,
                'first_name' => 'Тест',
                'username' => 'testuser'
            ],
            'status' => 'left'
        ]
    ]
];

echo "📤 Симулируем выход участника:\n";
echo "Пользователь: @testuser\n";
echo "Статус: left (покинул)\n";
echo "Ожидаемое сообщение: Сообщение о выходе\n\n";

echo "🎯 Как тестировать:\n";
echo "1. Добавьте нового участника в группу\n";
echo "2. Удалите участника из группы\n";
echo "3. Проверьте логи в bot/logs/2025-07-20.log\n";
echo "4. Проверьте сообщения в группе\n\n";

echo "🔍 Ожидаемые записи в логах:\n";
echo "- 'Processing chat member update'\n";
echo "- 'User joined' или 'User left'\n";
echo "- 'Welcome message sent' или 'Farewell message sent'\n\n";

echo "💬 Ожидаемые сообщения в группе:\n";
echo "При присоединении:\n";
echo "👋 Привет, @username!\n";
echo "🎉 Добро пожаловать в клуб CabrioRide!\n";
echo "💬 Расскажи пару слов о себе и переходи в бот для регистрации:\n";
echo "👉 @CabrioControl_bot\n\n";

echo "При выходе:\n";
echo "😔 @username покинул клуб CabrioRide.\n";
echo "Будем скучать! Надеемся увидеться снова! 👋\n";
?> 
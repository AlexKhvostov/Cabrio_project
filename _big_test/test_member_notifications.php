<?php
/**
 * test_member_notifications.php
 * 
 * Тест уведомлений о присоединении и выходе участников
 */

// Включаем отображение ошибок
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "🔍 Тест уведомлений о участниках\n\n";

// Симулируем разные события участников
$testEvents = [
    // 1. Пользователь присоединился к группе
    [
        'type' => 'Пользователь присоединился',
        'event' => 'chat_member',
        'status' => 'member',
        'user' => [
            'id' => 123456789,
            'username' => 'testuser',
            'first_name' => 'Тест',
            'last_name' => 'Пользователь'
        ],
        'expected_message' => '👋 Привет, @testuser!'
    ],
    // 2. Пользователь покинул группу
    [
        'type' => 'Пользователь покинул группу',
        'event' => 'chat_member',
        'status' => 'left',
        'user' => [
            'id' => 123456789,
            'username' => 'testuser',
            'first_name' => 'Тест',
            'last_name' => 'Пользователь'
        ],
        'expected_message' => '😔 @testuser покинул клуб CabrioRide.'
    ],
    // 3. Пользователь без username присоединился
    [
        'type' => 'Пользователь без username присоединился',
        'event' => 'chat_member',
        'status' => 'member',
        'user' => [
            'id' => 987654321,
            'first_name' => 'Аноним',
            'last_name' => 'Пользователь'
        ],
        'expected_message' => '👋 Привет, Аноним!'
    ],
    // 4. Пользователь был удален из группы
    [
        'type' => 'Пользователь удален из группы',
        'event' => 'chat_member',
        'status' => 'kicked',
        'user' => [
            'id' => 555666777,
            'username' => 'baduser',
            'first_name' => 'Плохой',
            'last_name' => 'Пользователь'
        ],
        'expected_message' => '😔 @baduser покинул клуб CabrioRide.'
    ]
];

foreach ($testEvents as $i => $test) {
    echo "=== Тест " . ($i + 1) . ": " . $test['type'] . " ===\n";
    echo "📅 Событие: " . $test['event'] . "\n";
    echo "👤 Статус: " . $test['status'] . "\n";
    echo "👤 Пользователь: " . ($test['user']['username'] ?? $test['user']['first_name']) . "\n";
    echo "💬 Ожидаемое сообщение: " . $test['expected_message'] . "\n\n";
}

echo "🎯 Логика уведомлений:\n";
echo "1. Присоединение (member/administrator) → Приветственное сообщение\n";
echo "2. Выход (left/kicked) → Сообщение о выходе\n";
echo "3. Только в основной группе клуба\n";
echo "4. Упоминание через @username или имя\n\n";

echo "✅ Приветственное сообщение:\n";
echo "👋 Привет, @username!\n";
echo "🎉 Добро пожаловать в клуб CabrioRide!\n";
echo "💬 Расскажи пару слов о себе и переходи в бот для регистрации:\n";
echo "👉 @CabrioControl_bot\n\n";

echo "😔 Сообщение о выходе:\n";
echo "😔 @username покинул клуб CabrioRide.\n";
echo "Будем скучать! Надеемся увидеться снова! 👋\n\n";

echo "🔍 Проверка в логах:\n";
echo "1. Ищите записи 'Processing chat member update'\n";
echo "2. Ищите записи 'User joined' или 'User left'\n";
echo "3. Проверьте отправку сообщений в группу\n";
?> 
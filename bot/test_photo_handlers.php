<?php
/**
 * test_photo_handlers.php
 * 
 * Тестирует обработчики фото с комментариями
 */

require_once __DIR__ . '/handlers/MessageHandler.php';
require_once __DIR__ . '/config.php';

echo "🧪 Тест обработчиков фото с комментариями\n";
echo "==========================================\n\n";

// Создаем MessageHandler
$messageHandler = new MessageHandler();

// Тестовые данные для фото с комментарием "?"
$testMessage1 = [
    'chat' => [
        'id' => -1002873258290,
        'type' => 'supergroup',
        'title' => 'CabrioRide'
    ],
    'from' => [
        'id' => 123456789,
        'first_name' => 'Test',
        'username' => 'testuser'
    ],
    'photo' => [
        ['file_id' => 'test_file_id', 'width' => 640, 'height' => 480]
    ],
    'caption' => '?'
];

// Тестовые данные для фото с комментарием "!"
$testMessage2 = [
    'chat' => [
        'id' => -1002873258290,
        'type' => 'supergroup',
        'title' => 'CabrioRide'
    ],
    'from' => [
        'id' => 123456789,
        'first_name' => 'Test',
        'username' => 'testuser'
    ],
    'photo' => [
        ['file_id' => 'test_file_id', 'width' => 640, 'height' => 480]
    ],
    'caption' => '!'
];

// Тестовые данные для фото с комментарием "++"
$testMessage3 = [
    'chat' => [
        'id' => -1002873258290,
        'type' => 'supergroup',
        'title' => 'CabrioRide'
    ],
    'from' => [
        'id' => 123456789,
        'first_name' => 'Test',
        'username' => 'testuser'
    ],
    'photo' => [
        ['file_id' => 'test_file_id', 'width' => 640, 'height' => 480]
    ],
    'caption' => '++'
];

echo "1️⃣ Тест фото с комментарием \"?\"\n";
echo "Данные: " . json_encode($testMessage1, JSON_UNESCAPED_UNICODE) . "\n\n";

$messageHandler->handle($testMessage1);

echo "\n2️⃣ Тест фото с комментарием \"!\"\n";
echo "Данные: " . json_encode($testMessage2, JSON_UNESCAPED_UNICODE) . "\n\n";

$messageHandler->handle($testMessage2);

echo "\n3️⃣ Тест фото с комментарием \"++\"\n";
echo "Данные: " . json_encode($testMessage3, JSON_UNESCAPED_UNICODE) . "\n\n";

$messageHandler->handle($testMessage3);

echo "\n✅ Тестирование завершено!\n"; 
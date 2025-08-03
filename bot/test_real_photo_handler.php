<?php
/**
 * Тест обработчиков фото с реальными данными
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/handlers/MessageHandler.php';
require_once __DIR__ . '/services/BotService.php';

// Загружаем реальное фото и конвертируем в base64
$photoPath = __DIR__ . '/../uploads/car/car_1_237.jpg';
$photoBase64 = base64_encode(file_get_contents($photoPath));

echo "🧪 Тест обработчиков фото с реальными данными\n";
echo "=" . str_repeat("=", 50) . "\n";
echo "📸 Используем фото: car_1_237.jpg\n";
echo "📏 Размер base64: " . strlen($photoBase64) . " символов\n\n";

// Создаем тестовые сообщения с реальными данными
$testMessages = [
    [
        'name' => 'Поиск автомобиля (?)',
        'data' => [
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
                [
                    'file_id' => 'real_photo_id',
                    'width' => 640,
                    'height' => 480
                ]
            ],
            'caption' => '?'
        ]
    ],
    [
        'name' => 'Сброс визитки (!)',
        'data' => [
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
                [
                    'file_id' => 'real_photo_id',
                    'width' => 640,
                    'height' => 480
                ]
            ],
            'caption' => '!'
        ]
    ],
    [
        'name' => 'Добавление в гараж (++)',
        'data' => [
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
                [
                    'file_id' => 'real_photo_id',
                    'width' => 640,
                    'height' => 480
                ]
            ],
            'caption' => '++'
        ]
    ]
];

// Создаем MessageHandler
$messageHandler = new MessageHandler();

// Тестируем каждый обработчик
foreach ($testMessages as $test) {
    echo "🔍 Тестируем: {$test['name']}\n";
    echo "📦 Данные: " . json_encode($test['data'], JSON_UNESCAPED_UNICODE) . "\n";
    
    try {
        $messageHandler->handle($test['data']);
        echo "✅ Обработчик выполнен успешно\n";
    } catch (Exception $e) {
        echo "❌ Ошибка: " . $e->getMessage() . "\n";
    }
    
    echo "─" . str_repeat("─", 50) . "\n";
}

echo "\n✅ Тестирование завершено!\n";
echo "📋 Проверьте логи для деталей\n"; 
<?php
/**
 * Тест API эндпоинтов L3 Actions с реальным фото
 */

require_once __DIR__ . '/../utils/load_env.php';
require_once __DIR__ . '/../utils/Logger.php';

// URL для тестирования
$baseUrl = 'http://localhost/app/backend/routes/api.php';

// Загружаем реальное фото и конвертируем в base64
$photoPath = __DIR__ . '/../../uploads/car/car_1_237.jpg';
$photoBase64 = base64_encode(file_get_contents($photoPath));

echo "🧪 Тест API эндпоинтов L3 Actions с реальным фото\n";
echo "=" . str_repeat("=", 60) . "\n";
echo "📸 Используем фото: car_1_237.jpg\n";
echo "📏 Размер base64: " . strlen($photoBase64) . " символов\n\n";

// Тестовые данные пользователя
$testUserData = [
    'id' => 1,
    'telegram_id' => 123456789,
    'username' => 'test_user',
    'first_name' => 'Test',
    'last_name' => 'User',
    'role' => 4, // member
    'role_name' => 'member'
];

// Тестовые данные
$testData = [
    'check_car_in_club' => [
        'route' => '/api/actions/check-car-in-club',
        'data' => [
            'photo' => $photoBase64,
            'user_id' => $testUserData['id']
        ]
    ],
    'leave_business_card' => [
        'route' => '/api/actions/leave-business-card',
        'data' => [
            'photo' => $photoBase64,
            'user_id' => $testUserData['id'],
            'location' => 'test_location'
        ]
    ],
    'add_car_to_garage' => [
        'route' => '/api/actions/add-car-to-garage',
        'data' => [
            'photo' => $photoBase64,
            'user_id' => $testUserData['id']
        ]
    ]
];

/**
 * Выполнить POST запрос к API с Telegram данными
 */
function makeApiRequest($url, $data, $userData) {
    // Создаем заголовки с Telegram данными
    $headers = [
        'Content-Type: application/json',
        'X-Telegram-User-Id: ' . $userData['telegram_id'],
        'X-Telegram-Username: ' . $userData['username'],
        'X-Telegram-First-Name: ' . $userData['first_name'],
        'X-Telegram-Last-Name: ' . $userData['last_name'],
        'X-Telegram-Auth-Date: ' . time(),
        'X-Telegram-Hash: ' . md5($userData['telegram_id'] . time()),
        'User-Agent: TestBot/1.0'
    ];
    
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => implode("\r\n", $headers),
            'content' => json_encode($data),
            'timeout' => 60 // Увеличиваем таймаут для обработки фото
        ]
    ]);
    
    $response = file_get_contents($url, false, $context);
    $httpCode = $http_response_header[0] ?? 'Unknown';
    
    return [
        'http_code' => $httpCode,
        'response' => $response,
        'url' => $url
    ];
}

/**
 * Протестировать эндпоинт
 */
function testEndpoint($name, $route, $data, $userData) {
    global $baseUrl;
    
    echo "\n🔍 Тестируем: $name\n";
    echo "📍 Маршрут: $route\n";
    echo "👤 Пользователь: {$userData['username']} (роль: {$userData['role_name']})\n";
    echo "📦 Размер данных: " . strlen(json_encode($data)) . " байт\n";
    
    $url = $baseUrl . '?route=' . urlencode($route);
    $result = makeApiRequest($url, $data, $userData);
    
    echo "🌐 URL: $url\n";
    echo "📡 HTTP код: {$result['http_code']}\n";
    echo "📄 Ответ: {$result['response']}\n";
    echo "─" . str_repeat("─", 50) . "\n";
    
    return $result;
}

echo "🚀 Начинаем тестирование API эндпоинтов L3 Actions с реальным фото\n";
echo "=" . str_repeat("=", 70) . "\n";
echo "👤 Тестовый пользователь: {$testUserData['username']} (роль: {$testUserData['role_name']})\n\n";

// Тестируем все эндпоинты
foreach ($testData as $name => $test) {
    testEndpoint($name, $test['route'], $test['data'], $testUserData);
}

echo "\n✅ Тестирование завершено!\n";
echo "📋 Результаты:\n";
echo "- HTTP код 401 - проблема с авторизацией\n";
echo "- HTTP код 404 - проблема с маршрутизацией\n";
echo "- HTTP код 200 - эндпоинт работает успешно\n";
echo "- HTTP код 400 - эндпоинт работает, но есть ошибка в данных\n"; 
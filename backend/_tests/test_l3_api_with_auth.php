<?php
/**
 * Тест API эндпоинтов для L3 Actions с авторизацией
 * 
 * Тестирует новые эндпоинты с правильным токеном авторизации
 */

require_once __DIR__ . '/../utils/load_env.php';
require_once __DIR__ . '/../utils/Logger.php';
require_once __DIR__ . '/../utils/AuthHelper.php';

// URL для тестирования
$baseUrl = 'http://localhost/app/backend/routes/api.php';

// Создаем тестовый JWT токен для пользователя с ролью member
$testUserData = [
    'id' => 1,
    'telegram_id' => 123456789,
    'username' => 'test_user',
    'first_name' => 'Test',
    'last_name' => 'User',
    'role' => 4, // member
    'role_name' => 'member'
];

// Создаем JWT токен
$jwtToken = AuthHelper::createJWT($testUserData);

echo "🔑 Создан JWT токен для тестирования\n";
echo "👤 Пользователь: {$testUserData['username']} (роль: {$testUserData['role_name']})\n";
echo "🎫 Токен: " . substr($jwtToken, 0, 50) . "...\n\n";

// Тестовые данные
$testData = [
    'check_car_in_club' => [
        'route' => '/api/actions/check-car-in-club',
        'data' => [
            'photo' => 'test_photo_base64_data',
            'user_id' => $testUserData['id']
        ]
    ],
    'leave_business_card' => [
        'route' => '/api/actions/leave-business-card',
        'data' => [
            'photo' => 'test_photo_base64_data',
            'user_id' => $testUserData['id'],
            'location' => 'test_location'
        ]
    ],
    'add_car_to_garage' => [
        'route' => '/api/actions/add-car-to-garage',
        'data' => [
            'photo' => 'test_photo_base64_data',
            'user_id' => $testUserData['id']
        ]
    ]
];

/**
 * Выполнить POST запрос к API с авторизацией
 */
function makeApiRequest($url, $data, $jwtToken) {
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $jwtToken
            ],
            'content' => json_encode($data)
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
function testEndpoint($name, $route, $data, $jwtToken) {
    global $baseUrl;
    
    echo "\n🔍 Тестируем: $name\n";
    echo "📍 Маршрут: $route\n";
    echo "📦 Данные: " . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n";
    
    $url = $baseUrl . '?route=' . urlencode($route);
    $result = makeApiRequest($url, $data, $jwtToken);
    
    echo "🌐 URL: $url\n";
    echo "📡 HTTP код: {$result['http_code']}\n";
    echo "📄 Ответ: {$result['response']}\n";
    echo "─" . str_repeat("─", 50) . "\n";
    
    return $result;
}

echo "🚀 Начинаем тестирование API эндпоинтов L3 Actions с авторизацией\n";
echo "=" . str_repeat("=", 70) . "\n";

// Тестируем все эндпоинты
foreach ($testData as $name => $test) {
    testEndpoint($name, $test['route'], $test['data'], $jwtToken);
}

echo "\n✅ Тестирование завершено!\n";
echo "📋 Результаты:\n";
echo "- HTTP код 401 - проблема с авторизацией\n";
echo "- HTTP код 404 - проблема с маршрутизацией\n";
echo "- HTTP код 200 - эндпоинт работает успешно\n";
echo "- HTTP код 400 - эндпоинт работает, но есть ошибка в данных\n"; 
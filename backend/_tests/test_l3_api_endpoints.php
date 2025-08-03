<?php
/**
 * Тест API эндпоинтов для L3 Actions
 * 
 * Тестирует новые эндпоинты:
 * - /api/actions/check-car-in-club
 * - /api/actions/leave-business-card  
 * - /api/actions/add-car-to-garage
 */

require_once __DIR__ . '/../utils/load_env.php';
require_once __DIR__ . '/../utils/Logger.php';

// URL для тестирования
$baseUrl = 'http://localhost/app/backend/routes/api.php';

// Тестовые данные
$testData = [
    'check_car_in_club' => [
        'route' => '/api/actions/check-car-in-club',
        'data' => [
            'photo' => 'test_photo_base64_data',
            'user_id' => 1
        ]
    ],
    'leave_business_card' => [
        'route' => '/api/actions/leave-business-card',
        'data' => [
            'photo' => 'test_photo_base64_data',
            'user_id' => 1,
            'location' => 'test_location'
        ]
    ],
    'add_car_to_garage' => [
        'route' => '/api/actions/add-car-to-garage',
        'data' => [
            'photo' => 'test_photo_base64_data',
            'user_id' => 1
        ]
    ]
];

/**
 * Выполнить POST запрос к API
 */
function makeApiRequest($url, $data) {
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => [
                'Content-Type: application/json',
                'Authorization: Bearer test_token'
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
function testEndpoint($name, $route, $data) {
    global $baseUrl;
    
    echo "\n🔍 Тестируем: $name\n";
    echo "📍 Маршрут: $route\n";
    echo "📦 Данные: " . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n";
    
    $url = $baseUrl . '?route=' . urlencode($route);
    $result = makeApiRequest($url, $data);
    
    echo "🌐 URL: $url\n";
    echo "📡 HTTP код: {$result['http_code']}\n";
    echo "📄 Ответ: {$result['response']}\n";
    echo "─" . str_repeat("─", 50) . "\n";
    
    return $result;
}

echo "🚀 Начинаем тестирование API эндпоинтов L3 Actions\n";
echo "=" . str_repeat("=", 60) . "\n";

// Тестируем все эндпоинты
foreach ($testData as $name => $test) {
    testEndpoint($name, $test['route'], $test['data']);
}

echo "\n✅ Тестирование завершено!\n";
echo "📋 Результаты:\n";
echo "- Если HTTP код 401 - проблема с авторизацией\n";
echo "- Если HTTP код 404 - проблема с маршрутизацией\n";
echo "- Если HTTP код 200/400 - эндпоинт работает, но может быть ошибка в данных\n"; 
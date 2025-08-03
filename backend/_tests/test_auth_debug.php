<?php
/**
 * Тест для отладки авторизации
 */

require_once __DIR__ . '/../utils/load_env.php';
require_once __DIR__ . '/../utils/Logger.php';

// URL для тестирования
$baseUrl = 'http://localhost/app/backend/routes/api.php';

echo "🔍 Тест отладки авторизации\n";
echo "=" . str_repeat("=", 40) . "\n";

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

// Тестируем разные способы отправки данных
$testCases = [
    'headers_only' => [
        'method' => 'GET',
        'route' => '/api/health',
        'headers' => [
            'Content-Type: application/json',
            'X-Telegram-User-Id: ' . $testUserData['telegram_id'],
            'X-Telegram-Username: ' . $testUserData['username'],
            'X-Telegram-First-Name: ' . $testUserData['first_name'],
            'X-Telegram-Last-Name: ' . $testUserData['last_name'],
            'X-Telegram-Auth-Date: ' . time(),
            'X-Telegram-Hash: ' . md5($testUserData['telegram_id'] . time())
        ],
        'data' => null
    ],
    'json_body' => [
        'method' => 'POST',
        'route' => '/api/health',
        'headers' => [
            'Content-Type: application/json'
        ],
        'data' => [
            'telegram_id' => $testUserData['telegram_id'],
            'username' => $testUserData['username'],
            'first_name' => $testUserData['first_name'],
            'last_name' => $testUserData['last_name'],
            'auth_date' => time(),
            'hash' => md5($testUserData['telegram_id'] . time())
        ]
    ]
];

/**
 * Выполнить запрос к API
 */
function makeApiRequest($url, $method, $headers, $data = null) {
    $context = stream_context_create([
        'http' => [
            'method' => $method,
            'header' => implode("\r\n", $headers),
            'content' => $data ? json_encode($data) : null,
            'timeout' => 30
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
 * Протестировать случай
 */
function testCase($name, $case) {
    global $baseUrl;
    
    echo "\n🔍 Тестируем: $name\n";
    echo "📍 Маршрут: {$case['route']}\n";
    echo "📡 Метод: {$case['method']}\n";
    echo "📦 Заголовки: " . json_encode($case['headers'], JSON_UNESCAPED_UNICODE) . "\n";
    if ($case['data']) {
        echo "📄 Данные: " . json_encode($case['data'], JSON_UNESCAPED_UNICODE) . "\n";
    }
    
    $url = $baseUrl . '?route=' . urlencode($case['route']);
    $result = makeApiRequest($url, $case['method'], $case['headers'], $case['data']);
    
    echo "🌐 URL: $url\n";
    echo "📡 HTTP код: {$result['http_code']}\n";
    echo "📄 Ответ: {$result['response']}\n";
    echo "─" . str_repeat("─", 50) . "\n";
    
    return $result;
}

// Тестируем все случаи
foreach ($testCases as $name => $case) {
    testCase($name, $case);
}

echo "\n✅ Тестирование завершено!\n"; 
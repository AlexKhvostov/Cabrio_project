<?php
/**
 * Тест для получения точной ошибки API
 */

echo "🔍 Тест для получения точной ошибки API\n";
echo "=" . str_repeat("=", 40) . "\n";

// Загружаем реальное фото
$photoPath = __DIR__ . '/../../uploads/car/car_1_237.jpg';
$photoBase64 = base64_encode(file_get_contents($photoPath));

echo "📸 Используем фото: car_1_237.jpg\n";
echo "📏 Размер base64: " . strlen($photoBase64) . " символов\n\n";

// Тестовые данные
$testData = [
    'photo' => $photoBase64
];

$headers = [
    'Content-Type: application/json',
    'X-Telegram-User-Id: 123456789',
    'X-Telegram-Username: test_user',
    'X-Telegram-First-Name: Test',
    'X-Telegram-Last-Name: User',
    'X-Telegram-Auth-Date: 1754186642',
    'X-Telegram-Hash: a7738dafe01e025c6d287f9392f20889'
];

$url = 'http://localhost/app/backend/routes/api.php?route=/api/actions/check-car-in-club';

echo "🔍 Тестируем: check_car_in_club\n";
echo "🌐 URL: $url\n";
echo "📦 Размер данных: " . strlen(json_encode($testData)) . " байт\n";

// Создаем контекст
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => implode("\r\n", $headers),
        'content' => json_encode($testData),
        'timeout' => 30
    ]
]);

// Выполняем запрос
$response = file_get_contents($url, false, $context);
$httpCode = $http_response_header[0] ?? 'Unknown';

echo "📡 HTTP код: $httpCode\n";

if ($response === false) {
    echo "❌ Ошибка получения ответа\n";
    echo "🔍 Проверяем ошибки PHP:\n";
    $error = error_get_last();
    if ($error) {
        echo "- " . $error['message'] . "\n";
    }
} else {
    echo "📄 Ответ: " . $response . "\n";
    
    // Пытаемся декодировать JSON
    $jsonResponse = json_decode($response, true);
    if ($jsonResponse) {
        echo "✅ JSON декодирован успешно\n";
        if (isset($jsonResponse['error'])) {
            echo "❌ Ошибка API: " . json_encode($jsonResponse['error'], JSON_UNESCAPED_UNICODE) . "\n";
        }
    } else {
        echo "❌ Неверный JSON ответ\n";
    }
}

echo "\n✅ Тестирование завершено!\n"; 
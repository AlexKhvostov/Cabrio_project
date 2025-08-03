<?php
/**
 * Тест API с использованием cURL
 */

echo "🔍 Тест API с использованием cURL\n";
echo "=" . str_repeat("=", 35) . "\n";

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

// Используем cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_VERBOSE, true);

// Получаем ответ
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "📡 HTTP код: $httpCode\n";

if ($error) {
    echo "❌ cURL ошибка: $error\n";
} else {
    echo "📄 Ответ: " . $response . "\n";
    
    // Пытаемся декодировать JSON
    $jsonResponse = json_decode($response, true);
    if ($jsonResponse) {
        echo "✅ JSON декодирован успешно\n";
        if (isset($jsonResponse['error'])) {
            echo "❌ Ошибка API: " . json_encode($jsonResponse['error'], JSON_UNESCAPED_UNICODE) . "\n";
        } else {
            echo "✅ Успешный ответ\n";
        }
    } else {
        echo "❌ Неверный JSON ответ\n";
    }
}

echo "\n✅ Тестирование завершено!\n"; 
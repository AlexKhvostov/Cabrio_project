<?php
/**
 * simple_api_test.php
 * 
 * Простой тест API
 */

// Включаем отображение ошибок
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "🔍 Тест API\n\n";

// Загружаем конфигурацию бота
require_once __DIR__ . '/../bot/config.php';

$apiUrl = getConfig('api_url') . '/api/ocr/process.php';
echo "API URL: $apiUrl\n\n";

// Проверяем доступность через cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_NOBODY, true); // Только заголовки

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);

curl_close($ch);

echo "HTTP Code: $httpCode\n";
if ($curlError) {
    echo "CURL Error: $curlError\n";
} else {
    echo "✅ CURL работает\n";
}

// Тест с POST запросом (без файла)
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, ['test' => 'data']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);

curl_close($ch);

echo "\nPOST Test:\n";
echo "HTTP Code: $httpCode\n";
echo "Response: " . substr($response, 0, 200) . "...\n";

if ($curlError) {
    echo "CURL Error: $curlError\n";
} else {
    echo "✅ POST запрос работает\n";
}
?> 
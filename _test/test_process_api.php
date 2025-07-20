<?php
/**
 * test_process_api.php
 * 
 * Тест для проверки process.php API
 */

// Включаем отображение ошибок
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "🔍 Тест process.php API\n\n";

// Загружаем конфигурацию бота
require_once __DIR__ . '/../bot/config.php';

$apiUrl = getConfig('api_url') . '/backend/api/ocr/process.php';
echo "API URL: $apiUrl\n\n";

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

echo "POST Test:\n";
echo "HTTP Code: $httpCode\n";
echo "Response: " . substr($response, 0, 500) . "...\n";

if ($curlError) {
    echo "CURL Error: $curlError\n";
} else {
    echo "✅ POST запрос работает\n";
}

// Парсим JSON
$data = json_decode($response, true);
if ($data) {
    echo "\nParsed JSON:\n";
    print_r($data);
} else {
    echo "\nJSON Error: " . json_last_error_msg() . "\n";
}
?> 
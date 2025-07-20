<?php
/**
 * test_with_file.php
 * 
 * Тест API с реальным файлом
 */

// Включаем отображение ошибок
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "🔍 Тест API с файлом\n\n";

// Загружаем конфигурацию бота
require_once __DIR__ . '/../bot/config.php';

$apiUrl = getConfig('api_url') . '/backend/api/ocr/process.php';
echo "API URL: $apiUrl\n\n";

// Проверяем наличие тестового файла
$testImagePath = __DIR__ . '/test_quality.jpg';
if (!file_exists($testImagePath)) {
    echo "❌ Тестовый файл не найден: $testImagePath\n";
    echo "Создаем простой тестовый файл...\n";
    
    // Создаем простой тестовый файл
    $testImagePath = __DIR__ . '/test_simple.jpg';
    $imageData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');
    file_put_contents($testImagePath, $imageData);
    echo "✅ Создан тестовый файл: $testImagePath\n";
}

echo "Используем файл: $testImagePath\n";
echo "Размер файла: " . filesize($testImagePath) . " байт\n\n";

// Отправляем запрос с файлом
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'image' => new CURLFile($testImagePath)
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);

curl_close($ch);

echo "POST Test с файлом:\n";
echo "HTTP Code: $httpCode\n";
echo "Response: " . substr($response, 0, 500) . "...\n";

if ($curlError) {
    echo "CURL Error: $curlError\n";
} else {
    echo "✅ POST запрос с файлом работает\n";
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
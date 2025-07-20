<?php
/**
 * test_bot_api.php
 * 
 * Тест для проверки API бота
 */

// Включаем отображение ошибок
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 Тест API бота</h1>";

// Проверяем конфигурацию
echo "<h2>📋 Проверка конфигурации</h2>";

// Загружаем конфигурацию бота
require_once __DIR__ . '/../bot/config.php';

echo "<p><strong>APP_URL:</strong> " . getConfig('app_url') . "</p>";
echo "<p><strong>API_URL:</strong> " . getConfig('api_url') . "</p>";

// Проверяем существование эндпоинтов
$endpoints = [
    '/api/ocr/process.php',
    '/api/ocr/recognize.php',
    '/api/ocr/check.php'
];

echo "<h2>🔗 Проверка эндпоинтов</h2>";

foreach ($endpoints as $endpoint) {
    $url = getConfig('api_url') . $endpoint;
    echo "<p><strong>$endpoint:</strong> ";
    
    // Проверяем доступность
    $headers = get_headers($url);
    if ($headers && strpos($headers[0], '200') !== false) {
        echo "<span style='color: green;'>✅ Доступен</span>";
    } else {
        echo "<span style='color: red;'>❌ Недоступен</span>";
    }
    echo " (<a href='$url' target='_blank'>проверить</a>)</p>";
}

// Тест с реальным файлом
echo "<h2>📸 Тест с файлом</h2>";

$testImagePath = __DIR__ . '/test_quality.jpg';
if (file_exists($testImagePath)) {
    echo "<p>✅ Тестовый файл найден: $testImagePath</p>";
    
    // Тестируем API
    $apiUrl = getConfig('api_url') . '/api/ocr/process.php';
    
    echo "<p><strong>API URL:</strong> $apiUrl</p>";
    
    // Отправляем запрос
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
    
    echo "<p><strong>HTTP Code:</strong> $httpCode</p>";
    if ($curlError) {
        echo "<p><strong>CURL Error:</strong> $curlError</p>";
    }
    
    echo "<p><strong>Response:</strong></p>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
    
    // Парсим JSON
    $data = json_decode($response, true);
    if ($data) {
        echo "<p><strong>Parsed JSON:</strong></p>";
        echo "<pre>" . print_r($data, true) . "</pre>";
    } else {
        echo "<p><strong>JSON Error:</strong> " . json_last_error_msg() . "</p>";
    }
    
} else {
    echo "<p>❌ Тестовый файл не найден: $testImagePath</p>";
}

echo "<h2>🔧 Информация о системе</h2>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>cURL:</strong> " . (function_exists('curl_init') ? '✅ Доступен' : '❌ Недоступен') . "</p>";
echo "<p><strong>JSON:</strong> " . (function_exists('json_encode') ? '✅ Доступен' : '❌ Недоступен') . "</p>";
echo "<p><strong>File Uploads:</strong> " . (ini_get('file_uploads') ? '✅ Включены' : '❌ Отключены') . "</p>";
echo "<p><strong>Max Upload Size:</strong> " . ini_get('upload_max_filesize') . "</p>";
echo "<p><strong>Post Max Size:</strong> " . ini_get('post_max_size') . "</p>";
?> 
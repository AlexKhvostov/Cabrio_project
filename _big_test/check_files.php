<?php
/**
 * check_files.php
 * 
 * Проверка доступности файлов через Cloudflare Tunnel
 */

// Включаем отображение ошибок
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "🔍 Проверка файлов\n\n";

// Загружаем конфигурацию бота
require_once __DIR__ . '/../bot/config.php';

$baseUrl = getConfig('api_url');
echo "Base URL: $baseUrl\n\n";

// Список файлов для проверки
$files = [
    '/api/ocr/process.php',
    '/backend/api/ocr/process.php',
    '/api/ocr/recognize.php',
    '/backend/api/ocr/recognize.php',
    '/api/ocr/check.php',
    '/backend/api/ocr/check.php',
    '/index.php',
    '/backend/index.php'
];

foreach ($files as $file) {
    $url = $baseUrl . $file;
    echo "Проверяю: $url\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    
    curl_close($ch);
    
    if ($httpCode === 200) {
        echo "✅ 200 OK\n";
    } elseif ($httpCode === 404) {
        echo "❌ 404 Not Found\n";
    } else {
        echo "⚠️ HTTP $httpCode\n";
    }
    
    if ($curlError) {
        echo "CURL Error: $curlError\n";
    }
    
    echo "\n";
}
?> 
<?php
/**
 * debug_api_url.php
 * 
 * Отлаживает URL для API запросов
 */

require_once __DIR__ . '/config.php';

echo "🔍 Отладка API URL...\n\n";

// Проверяем переменные окружения
echo "📋 Переменные окружения:\n";
echo "BACKEND_API_URL: " . ($_ENV['BACKEND_API_URL'] ?? 'не задан') . "\n";
echo "SYSTEM_TOKEN: " . (substr($_ENV['SYSTEM_TOKEN'] ?? '', 0, 10) . '...') . "\n\n";

// Формируем URL как в обработчике
$backendPath = $_ENV['BACKEND_API_URL'] ?? '../backend';
$apiUrl = 'http://localhost/app/' . $backendPath . '/routes/api.php';

echo "🔗 Сформированный URL:\n";
echo "backendPath: $backendPath\n";
echo "apiUrl: $apiUrl\n\n";

// Проверяем доступность
echo "🌐 Проверяем доступность...\n";

$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'timeout' => 5
    ]
]);

$response = file_get_contents($apiUrl, false, $context);

if ($response === false) {
    echo "❌ URL недоступен\n";
    
    // Проверяем альтернативные варианты
    $alternatives = [
        'http://localhost/app/backend/routes/api.php',
        'http://localhost/app/backend/api.php',
        'http://localhost/app/backend/',
        'http://localhost/app/backend'
    ];
    
    echo "\n🔍 Проверяем альтернативные URL:\n";
    foreach ($alternatives as $alt) {
        $response = file_get_contents($alt, false, $context);
        if ($response !== false) {
            echo "✅ $alt - РАБОТАЕТ\n";
        } else {
            echo "❌ $alt - НЕ РАБОТАЕТ\n";
        }
    }
} else {
    echo "✅ URL доступен\n";
    echo "Ответ: " . substr($response, 0, 200) . "...\n";
} 
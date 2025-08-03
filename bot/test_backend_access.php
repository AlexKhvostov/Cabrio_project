<?php
/**
 * test_backend_access.php
 * 
 * Проверяет доступность backend
 */

echo "🔍 Проверяем доступность backend...\n\n";

$urls = [
    'http://localhost/app/backend/',
    'http://localhost/app/backend/routes/',
    'http://localhost/app/backend/routes/api.php',
    'http://localhost/app/backend/utils/',
    'http://localhost/app/backend/controllers/'
];

foreach ($urls as $url) {
    echo "Проверяем: $url\n";
    
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 5
        ]
    ]);
    
    $response = file_get_contents($url, false, $context);
    
    if ($response === false) {
        echo "❌ Недоступен\n";
    } else {
        echo "✅ Доступен\n";
        echo "Ответ: " . substr($response, 0, 100) . "...\n";
    }
    echo "\n";
} 
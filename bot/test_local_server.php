<?php
/**
 * test_local_server.php
 * 
 * Проверяет доступность локального сервера
 */

echo "🔍 Проверяем локальный сервер...\n";

// Проверяем Apache с POST запросом (как webhook)
$apacheUrl = "http://localhost/app/bot/webhook.php";
echo "Проверяем: $apacheUrl\n";

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => json_encode(['test' => true]),
        'timeout' => 5
    ]
]);

$response = file_get_contents($apacheUrl, false, $context);

if ($response === false) {
    echo "❌ Локальный сервер недоступен\n";
    echo "Убедитесь, что XAMPP запущен и Apache работает\n";
} else {
    echo "✅ Локальный сервер работает\n";
    echo "Ответ: " . substr($response, 0, 100) . "...\n";
}

// Проверяем Cloudflare туннель
$cloudflareUrl = "https://contributed-cm-component-consideration.trycloudflare.com/app/bot/webhook";
echo "\nПроверяем: $cloudflareUrl\n";

$response = file_get_contents($cloudflareUrl, false, $context);

if ($response === false) {
    echo "❌ Cloudflare туннель недоступен\n";
    echo "Нужно запустить cloudflared или настроить туннель\n";
} else {
    echo "✅ Cloudflare туннель работает\n";
    echo "Ответ: " . substr($response, 0, 100) . "...\n";
} 
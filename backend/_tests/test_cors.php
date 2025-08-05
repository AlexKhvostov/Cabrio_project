<?php
/**
 * Простой тестовый файл для проверки CORS
 * ТОЛЬКО ДЛЯ РАЗРАБОТКИ!
 */

// Устанавливаем CORS заголовки
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Telegram-Init-Data, X-Telegram-User-Id, X-Telegram-First-Name, X-Telegram-Last-Name, X-Telegram-Username, X-Telegram-Photo-URL, X-Telegram-Auth-Date, X-Telegram-Hash');
header('Access-Control-Max-Age: 86400');

// Обрабатываем preflight запросы
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Получаем информацию о запросе
$requestInfo = [
    'method' => $_SERVER['REQUEST_METHOD'],
    'headers' => function_exists('getallheaders') ? getallheaders() : [],
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
    'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
    'timestamp' => date('Y-m-d H:i:s')
];

// Возвращаем простой ответ
echo json_encode([
    'success' => true,
    'message' => 'CORS test successful',
    'data' => [
        'status' => 'ok',
        'request_info' => $requestInfo
    ]
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?> 
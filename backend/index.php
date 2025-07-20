<?php
// Включаем отображение ошибок для отладки
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Определяем константу для проверки прямого доступа
define('BASE_PATH', __DIR__);

// Подключаем конфиг
require_once __DIR__ . '/config/config.php';

// Устанавливаем заголовки для JSON API
header('Content-Type: application/json');

// Получаем путь запроса
$request_uri = $_SERVER['REQUEST_URI'];
$base_path = '/app/backend/';
$path = parse_url($request_uri, PHP_URL_PATH);

// Убираем базовый путь
if (strpos($path, $base_path) === 0) {
    $path = substr($path, strlen($base_path));
}

// Убираем trailing slash
$path = rtrim($path, '/');

// Маршрутизация
switch ($path) {
    case 'ocr/recognize':
        require __DIR__ . '/api/ocr/recognize.php';
        break;
        
    default:
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'API endpoint not found'
        ]);
} 
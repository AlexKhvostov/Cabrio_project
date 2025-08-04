<?php
/**
 * Временный тестовый файл для получения данных без авторизации
 * ТОЛЬКО ДЛЯ РАЗРАБОТКИ!
 */

// Загружаем необходимые файлы
require_once __DIR__ . '/utils/load_env.php';
require_once __DIR__ . '/utils/ResponseHelper.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/Car.php';

// Устанавливаем заголовки
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

// Получаем маршрут из GET параметра
$route = $_GET['route'] ?? '';

// Простая маршрутизация
switch ($route) {
    case '/api/health':
        echo json_encode([
            'success' => true,
            'data' => [
                'status' => 'ok',
                'message' => 'Test API is healthy',
                'timestamp' => date('Y-m-d H:i:s')
            ]
        ]);
        break;
        
    case '/api/users':
        try {
            // Получаем пользователей из БД
            $users = User::getAll();
            
            echo json_encode([
                'success' => true,
                'data' => $users
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => 'DB_ERROR',
                    'message' => $e->getMessage()
                ]
            ]);
        }
        break;
        
    case '/api/cars':
        try {
            // Получаем автомобили из БД
            $cars = Car::getAll();
            
            echo json_encode([
                'success' => true,
                'data' => $cars
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => 'DB_ERROR',
                    'message' => $e->getMessage()
                ]
            ]);
        }
        break;
        
    default:
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => [
                'code' => 'NOT_FOUND',
                'message' => 'Маршрут не найден'
            ]
        ]);
        break;
}
?> 
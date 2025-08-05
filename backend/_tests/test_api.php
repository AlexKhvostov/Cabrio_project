<?php
/**
 * Простой тестовый файл для проверки API
 */

// Загружаем необходимые файлы
require_once __DIR__ . '/utils/load_env.php';
require_once __DIR__ . '/utils/ResponseHelper.php';

// Устанавливаем заголовки
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Telegram-Init-Data');

// Обрабатываем preflight запросы
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Получаем маршрут из GET параметра
$route = $_GET['route'] ?? '';

// Простая маршрутизация
switch ($route) {
    case '/health':
        echo json_encode([
            'success' => true,
            'data' => [
                'status' => 'ok',
                'message' => 'API is healthy',
                'timestamp' => date('Y-m-d H:i:s')
            ]
        ]);
        break;
        
    case '/status':
        echo json_encode([
            'success' => true,
            'data' => [
                'status' => 'online',
                'version' => '1.0.0',
                'timestamp' => date('Y-m-d H:i:s')
            ]
        ]);
        break;
        
    case '/users':
        // Моковые данные пользователей
        echo json_encode([
            'success' => true,
            'data' => [
                [
                    'id' => 1,
                    'telegram_id' => 287536885,
                    'first_name' => 'Александр',
                    'last_name' => 'Петров',
                    'username' => 'alex_petrov',
                    'city' => 'Москва',
                    'role' => [
                        'id' => 1,
                        'code' => 'admin',
                        'name' => 'Администратор'
                    ],
                    'photo' => [
                        'id' => 1,
                        'url' => 'https://images.pexels.com/photos/220453/pexels-photo-220453.jpeg?auto=compress&cs=tinysrgb&w=64&h=64&dpr=1',
                        'description' => 'Аватар пользователя'
                    ]
                ],
                [
                    'id' => 2,
                    'telegram_id' => 123456789,
                    'first_name' => 'Мария',
                    'last_name' => 'Иванова',
                    'username' => 'maria_drive',
                    'city' => 'Санкт-Петербург',
                    'role' => [
                        'id' => 2,
                        'code' => 'moderator',
                        'name' => 'Модератор'
                    ],
                    'photo' => [
                        'id' => 2,
                        'url' => 'https://images.pexels.com/photos/415829/pexels-photo-415829.jpeg?auto=compress&cs=tinysrgb&w=64&h=64&dpr=1',
                        'description' => 'Аватар пользователя'
                    ]
                ]
            ]
        ]);
        break;
        
    case '/cars':
        // Моковые данные автомобилей
        echo json_encode([
            'success' => true,
            'data' => [
                [
                    'id' => 1,
                    'brand' => [
                        'id' => 1,
                        'name' => 'BMW'
                    ],
                    'model' => 'Z4',
                    'year' => 2019,
                    'reg_number' => 'М123АВ777',
                    'color' => 'Белый',
                    'engine_volume' => 2.0,
                    'owner' => [
                        'id' => 1,
                        'first_name' => 'Александр',
                        'last_name' => 'Петров'
                    ],
                    'status' => [
                        'id' => 1,
                        'code' => 'active',
                        'name' => 'Активный'
                    ],
                    'photo' => [
                        'id' => 1,
                        'url' => 'https://images.pexels.com/photos/3802510/pexels-photo-3802510.jpeg?auto=compress&cs=tinysrgb&w=400',
                        'description' => 'Фото автомобиля'
                    ]
                ],
                [
                    'id' => 2,
                    'brand' => [
                        'id' => 2,
                        'name' => 'Mercedes-Benz'
                    ],
                    'model' => 'SLK-Class',
                    'year' => 2016,
                    'reg_number' => 'А456ВС199',
                    'color' => 'Красный',
                    'engine_volume' => 1.8,
                    'owner' => [
                        'id' => 2,
                        'first_name' => 'Мария',
                        'last_name' => 'Иванова'
                    ],
                    'status' => [
                        'id' => 1,
                        'code' => 'active',
                        'name' => 'Активный'
                    ],
                    'photo' => [
                        'id' => 2,
                        'url' => 'https://images.pexels.com/photos/1592384/pexels-photo-1592384.jpeg?auto=compress&cs=tinysrgb&w=400',
                        'description' => 'Фото автомобиля'
                    ]
                ]
            ]
        ]);
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
<?php
/**
 * 🧪 API для тестирования L2 Actions
 * 
 * Назначение: Backend для web тестов L2 Actions
 * Использование: Вызывается из test_l2_actions_web.html
 */

// Загрузка переменных окружения
require_once __DIR__ . '/../utils/load_env.php';

// Подключение L2 Actions
require_once __DIR__ . '/../actions/level2/__SyncUserDataAction.php';
require_once __DIR__ . '/../actions/level2/__SearchCarAction.php';
require_once __DIR__ . '/../actions/level2/__AddCarToUserAction.php';
require_once __DIR__ . '/../actions/level2/__DropBusinessCardAction.php';

// Устанавливаем CORS заголовки
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

// Обработка preflight запросов
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Проверяем метод запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => [
            'message' => 'Метод не поддерживается'
        ]
    ]);
    exit;
}

// Получаем данные запроса
$action = $_POST['action'] ?? '';

// Функция для логирования запросов
function logRequest($action, $data) {
    $logData = [
        'timestamp' => date('Y-m-d H:i:s'),
        'action' => $action,
        'data' => $data
    ];
    error_log('L2 API Request: ' . json_encode($logData, JSON_UNESCAPED_UNICODE));
}

// Функция для логирования ответов
function logResponse($action, $response) {
    $logData = [
        'timestamp' => date('Y-m-d H:i:s'),
        'action' => $action,
        'response' => $response
    ];
    error_log('L2 API Response: ' . json_encode($logData, JSON_UNESCAPED_UNICODE));
}

try {
    $response = null;
    
    switch ($action) {
        case 'sync_user':
            // __SyncUserDataAction
            $data = [
                'telegram_id' => (int)($_POST['telegram_id'] ?? 0),
                'first_name' => $_POST['first_name'] ?? null,
                'last_name' => $_POST['last_name'] ?? null,
                'username' => $_POST['username'] ?? null
            ];
            
            // Обрабатываем фото если передана
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $_FILES['photo'] = $_FILES['photo'];
            }
            
            logRequest($action, $data);
            $response = __SyncUserDataAction::handle($data);
            logResponse($action, $response);
            break;
            
        case 'search_car':
            // __SearchCarAction
            $data = [
                'plate_number' => $_POST['plate_number'] ?? '',
                'create_user_id' => (int)($_POST['create_user_id'] ?? 0),
                'model' => $_POST['model'] ?? null,
                'color' => $_POST['color'] ?? null,
                'year' => $_POST['year'] ? (int)$_POST['year'] : null
            ];
            
            // Обрабатываем фото если передана
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $_FILES['photo'] = $_FILES['photo'];
            }
            
            logRequest($action, $data);
            $response = __SearchCarAction::handle($data);
            logResponse($action, $response);
            break;
            
        case 'add_car_to_user':
            // __AddCarToUserAction
            $data = [
                'plate_number' => $_POST['plate_number'] ?? '',
                'user_id' => (int)($_POST['user_id'] ?? 0),
                'model' => $_POST['model'] ?? null,
                'color' => $_POST['color'] ?? null,
                'year' => $_POST['year'] ? (int)$_POST['year'] : null
            ];
            
            // Обрабатываем фото если передана
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $_FILES['photo'] = $_FILES['photo'];
            }
            
            logRequest($action, $data);
            $response = __AddCarToUserAction::handle($data);
            logResponse($action, $response);
            break;
            
        case 'drop_business_card':
            // __DropBusinessCardAction
            $data = [
                'plate_number' => $_POST['plate_number'] ?? '',
                'user_id' => (int)($_POST['user_id'] ?? 0),
                'model' => $_POST['model'] ?? null,
                'color' => $_POST['color'] ?? null,
                'year' => $_POST['year'] ? (int)$_POST['year'] : null
            ];
            
            // Обрабатываем фото если передана
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $_FILES['photo'] = $_FILES['photo'];
            }
            
            logRequest($action, $data);
            $response = __DropBusinessCardAction::handle($data);
            logResponse($action, $response);
            break;
            
        default:
            $response = [
                'success' => false,
                'error' => [
                    'message' => 'Неизвестное действие: ' . $action
                ]
            ];
            break;
    }
    
    // Возвращаем ответ
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    $errorResponse = [
        'success' => false,
        'error' => [
            'message' => 'Внутренняя ошибка сервера: ' . $e->getMessage()
        ]
    ];
    
    logResponse($action, $errorResponse);
    echo json_encode($errorResponse, JSON_UNESCAPED_UNICODE);
} 
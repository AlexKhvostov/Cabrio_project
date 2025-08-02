<?php
/**
 * 🧪 API для тестирования L3 Actions
 * 
 * Назначение: Backend для web тестов L3 Actions
 * Использование: Вызывается из test_l3_actions_web.html
 */

// Загрузка переменных окружения
require_once __DIR__ . '/../utils/load_env.php';

// Подключение AppContext и L3 Actions
require_once __DIR__ . '/../utils/AppContext.php';
require_once __DIR__ . '/../actions/level3/___CheckCarInClubAction.php';
require_once __DIR__ . '/../actions/level3/___LeaveBusinessCardAction.php';
require_once __DIR__ . '/../actions/level3/___AddCarToGarageAction.php';

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
    error_log('L3 API Request: ' . json_encode($logData, JSON_UNESCAPED_UNICODE));
}

// Функция для логирования ответов
function logResponse($action, $response) {
    $logData = [
        'timestamp' => date('Y-m-d H:i:s'),
        'action' => $action,
        'response' => $response
    ];
    error_log('L3 API Response: ' . json_encode($logData, JSON_UNESCAPED_UNICODE));
}

// Функция для настройки тестового пользователя в AppContext
function setupTestUser($userId) {
    // Очищаем контекст
    AppContext::clear();
    
    // Создаем тестового пользователя
    $user = [
        'id' => $userId,
        'telegram_id' => '123456789',
        'first_name' => 'Test',
        'last_name' => 'User',
        'role' => 4 // member
    ];
    
    // Устанавливаем в контекст
    AppContext::setCurrentUser($user);
    AppContext::setRequestId('test_' . time());
    AppContext::setStartTime(microtime(true));
    
    return $user;
}

try {
    $response = null;
    
    switch ($action) {
        case 'check_car_in_club':
            // ___CheckCarInClubAction
            $userId = (int)($_POST['user_id'] ?? 0);
            
            // Настраиваем тестового пользователя в контексте
            setupTestUser($userId);
            
            $data = [
                // user_id больше не передается - берется из AppContext
            ];
            
            // Обрабатываем фото если передана
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $_FILES['photo'] = $_FILES['photo'];
            }
            
            logRequest($action, $data);
            $response = ___CheckCarInClubAction::handle($data);
            logResponse($action, $response);
            break;
            
        case 'leave_business_card':
            // ___LeaveBusinessCardAction
            $userId = (int)($_POST['user_id'] ?? 0);
            
            // Настраиваем тестового пользователя в контексте
            setupTestUser($userId);
            
            $data = [
                // user_id больше не передается - берется из AppContext
            ];
            
            // Обрабатываем фото если передана
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $_FILES['photo'] = $_FILES['photo'];
            }
            
            logRequest($action, $data);
            $response = ___LeaveBusinessCardAction::handle($data);
            logResponse($action, $response);
            break;
            
        case 'add_car_to_garage':
            // ___AddCarToGarageAction
            $userId = (int)($_POST['user_id'] ?? 0);
            
            // Настраиваем тестового пользователя в контексте
            setupTestUser($userId);
            
            $data = [
                // user_id больше не передается - берется из AppContext
            ];
            
            // Обрабатываем фото если передана
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $_FILES['photo'] = $_FILES['photo'];
            }
            
            logRequest($action, $data);
            $response = ___AddCarToGarageAction::handle($data);
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
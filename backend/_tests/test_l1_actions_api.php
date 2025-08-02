<?php
/**
 * 🧪 API для тестирования L1 Actions
 * 
 * Назначение: Обработка AJAX запросов от HTML теста L1 Actions
 * Использование: Вызывается из test_l1_actions_web.html
 */

// Загрузка переменных окружения
require_once __DIR__ . '/../utils/load_env.php';

// Подключение всех L1 Actions
require_once __DIR__ . '/../actions/level1/_CreateUserAction.php';
require_once __DIR__ . '/../actions/level1/_CheckUserByTelegramIdAction.php';
require_once __DIR__ . '/../actions/level1/_UpdateUserAction.php';
require_once __DIR__ . '/../actions/level1/_UpdateRoleUserAction.php';
require_once __DIR__ . '/../actions/level1/_CreateCarAction.php';
require_once __DIR__ . '/../actions/level1/_CheckCarInDbAction.php';
require_once __DIR__ . '/../actions/level1/_UpdateStatusAction.php';
require_once __DIR__ . '/../actions/level1/_UpdateOwnerToCarAction.php';
require_once __DIR__ . '/../actions/level1/_CreateBusinessCardAction.php';
require_once __DIR__ . '/../actions/level1/_CreatePhotoAction.php';

// Установка заголовков для JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Получение данных запроса
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['action'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Неверный запрос'
    ]);
    exit;
}

$action = $input['action'];
$response = ['success' => false, 'error' => 'Неизвестное действие'];

try {
    switch ($action) {
        // ============================================================================
        // USER ACTIONS
        // ============================================================================
        
        case 'create_user':
            if (!isset($input['data'])) {
                $response = ['success' => false, 'error' => 'Отсутствуют данные пользователя'];
                break;
            }
            
            $result = _CreateUserAction::handle($input['data']);
            $response = $result;
            break;

        case 'check_user':
            if (!isset($input['telegram_id'])) {
                $response = ['success' => false, 'error' => 'Отсутствует telegram_id'];
                break;
            }
            
            $result = _CheckUserByTelegramIdAction::handle(['telegram_id' => $input['telegram_id']]);
            $response = $result;
            break;

        case 'update_user':
            if (!isset($input['user_id']) || !isset($input['data'])) {
                $response = ['success' => false, 'error' => 'Отсутствуют user_id или данные для обновления'];
                break;
            }
            
            $data = $input['data'];
            $data['user_id'] = $input['user_id'];
            
            $result = _UpdateUserAction::handle($data);
            $response = $result;
            break;

        case 'update_role':
            if (!isset($input['user_id']) || !isset($input['role_id'])) {
                $response = ['success' => false, 'error' => 'Отсутствуют user_id или role_id'];
                break;
            }
            
            $result = _UpdateRoleUserAction::handle([
                'user_id' => $input['user_id'],
                'role_id' => $input['role_id']
            ]);
            $response = $result;
            break;

        // ============================================================================
        // CAR ACTIONS
        // ============================================================================
        
        case 'create_car':
            if (!isset($input['data'])) {
                $response = ['success' => false, 'error' => 'Отсутствуют данные автомобиля'];
                break;
            }
            
            $result = _CreateCarAction::handle($input['data']);
            $response = $result;
            break;

        case 'check_car':
            if (!isset($input['plate_number'])) {
                $response = ['success' => false, 'error' => 'Отсутствует plate_number'];
                break;
            }
            
            $result = _CheckCarInDbAction::handle(['plate_number' => $input['plate_number']]);
            $response = $result;
            break;

        case 'update_status':
            if (!isset($input['entity_type']) || !isset($input['entity_id']) || !isset($input['status_id'])) {
                $response = ['success' => false, 'error' => 'Отсутствуют entity_type, entity_id или status_id'];
                break;
            }
            
            $result = _UpdateStatusAction::handle([
                'entity_type' => $input['entity_type'],
                'entity_id' => $input['entity_id'],
                'status_id' => $input['status_id']
            ]);
            $response = $result;
            break;

        case 'update_owner':
            if (!isset($input['car_id']) || !isset($input['user_id'])) {
                $response = ['success' => false, 'error' => 'Отсутствуют car_id или user_id'];
                break;
            }
            
            $result = _UpdateOwnerToCarAction::handle([
                'car_id' => $input['car_id'],
                'user_id' => $input['user_id']
            ]);
            $response = $result;
            break;

        // ============================================================================
        // BUSINESS CARD ACTIONS
        // ============================================================================
        
        case 'create_business_card':
            if (!isset($input['data'])) {
                $response = ['success' => false, 'error' => 'Отсутствуют данные визитки'];
                break;
            }
            
            $result = _CreateBusinessCardAction::handle($input['data']);
            $response = $result;
            break;

        // ============================================================================
        // PHOTO ACTIONS
        // ============================================================================
        
        case 'create_photo':
            if (!isset($input['data'])) {
                $response = ['success' => false, 'error' => 'Отсутствуют данные фото'];
                break;
            }
            
            $result = _CreatePhotoAction::handle($input['data']);
            $response = $result;
            break;

        default:
            $response = ['success' => false, 'error' => 'Неизвестное действие: ' . $action];
            break;
    }
} catch (Exception $e) {
    $response = [
        'success' => false,
        'error' => 'Исключение: ' . $e->getMessage()
    ];
}

// Отправка ответа
echo json_encode($response); 
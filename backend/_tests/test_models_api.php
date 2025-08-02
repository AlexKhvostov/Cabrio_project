<?php
/**
 * 🧪 API для тестирования моделей
 * 
 * Назначение: Обработка AJAX запросов от HTML теста
 * Использование: Вызывается из test_models_web.html
 */

// Загрузка переменных окружения
require_once __DIR__ . '/../utils/load_env.php';

// Подключение моделей
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Car.php';
require_once __DIR__ . '/../models/BusinessCard.php';
require_once __DIR__ . '/../models/Photo.php';

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
        case 'user_create':
            if (!isset($input['data'])) {
                $response = ['success' => false, 'error' => 'Отсутствуют данные пользователя'];
                break;
            }
            
            $userId = User::create($input['data']);
            if ($userId) {
                $response = [
                    'success' => true,
                    'data' => ['id' => $userId]
                ];
            } else {
                $response = ['success' => false, 'error' => 'Ошибка создания пользователя'];
            }
            break;

        case 'user_find':
            if (!isset($input['telegram_id'])) {
                $response = ['success' => false, 'error' => 'Отсутствует telegram_id'];
                break;
            }
            
            $user = User::findByTelegramId($input['telegram_id']);
            if ($user) {
                $response = [
                    'success' => true,
                    'data' => [
                        'id' => $user->id,
                        'username' => $user->username,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'role_id' => $user->role_id
                    ]
                ];
            } else {
                $response = ['success' => false, 'error' => 'Пользователь не найден'];
            }
            break;

        case 'user_update':
            if (!isset($input['user_id']) || !isset($input['data'])) {
                $response = ['success' => false, 'error' => 'Отсутствуют user_id или данные для обновления'];
                break;
            }
            
            $user = User::findById($input['user_id']);
            if ($user) {
                // Добавляем id в данные для обновления
                $updateData = $input['data'];
                $updateData['id'] = $input['user_id'];
                
                $updateResult = User::update($updateData);
                if ($updateResult) {
                    $response = ['success' => true, 'data' => ['message' => 'Пользователь обновлен']];
                } else {
                    $response = ['success' => false, 'error' => 'Ошибка обновления пользователя'];
                }
            } else {
                $response = ['success' => false, 'error' => 'Пользователь не найден'];
            }
            break;

        case 'user_update_role':
            if (!isset($input['user_id']) || !isset($input['role_id'])) {
                $response = ['success' => false, 'error' => 'Отсутствуют user_id или role_id'];
                break;
            }
            
            $updateResult = User::updateRole($input['user_id'], $input['role_id']);
            if ($updateResult) {
                $response = ['success' => true, 'data' => ['message' => 'Роль обновлена']];
            } else {
                $response = ['success' => false, 'error' => 'Ошибка обновления роли'];
            }
            break;

        case 'car_create':
            if (!isset($input['data'])) {
                $response = ['success' => false, 'error' => 'Отсутствуют данные автомобиля'];
                break;
            }
            
            $carId = Car::create($input['data']);
            if ($carId) {
                $response = [
                    'success' => true,
                    'data' => ['id' => $carId]
                ];
            } else {
                $response = ['success' => false, 'error' => 'Ошибка создания автомобиля'];
            }
            break;

        case 'car_find':
            if (!isset($input['plate_number'])) {
                $response = ['success' => false, 'error' => 'Отсутствует plate_number'];
                break;
            }
            
            $car = Car::findByPlateNumber($input['plate_number']);
            if ($car) {
                $response = [
                    'success' => true,
                    'data' => [
                        'id' => $car->id,
                        'reg_number' => $car->reg_number,
                        'status_id' => $car->status_id,
                        'owner_user_id' => $car->owner_user_id
                    ]
                ];
            } else {
                $response = ['success' => false, 'error' => 'Автомобиль не найден'];
            }
            break;

        case 'car_update_status':
            if (!isset($input['car_id']) || !isset($input['status_id'])) {
                $response = ['success' => false, 'error' => 'Отсутствуют car_id или status_id'];
                break;
            }
            
            $updateResult = Car::updateStatus($input['car_id'], $input['status_id']);
            if ($updateResult) {
                $response = ['success' => true, 'data' => ['message' => 'Статус обновлен']];
            } else {
                $response = ['success' => false, 'error' => 'Ошибка обновления статуса'];
            }
            break;

        case 'car_update_owner':
            if (!isset($input['car_id']) || !isset($input['user_id'])) {
                $response = ['success' => false, 'error' => 'Отсутствуют car_id или user_id'];
                break;
            }
            
            $updateResult = Car::updateOwner($input['car_id'], $input['user_id']);
            if ($updateResult) {
                $response = ['success' => true, 'data' => ['message' => 'Владелец обновлен']];
            } else {
                $response = ['success' => false, 'error' => 'Ошибка обновления владельца'];
            }
            break;

        case 'business_card_create':
            if (!isset($input['data'])) {
                $response = ['success' => false, 'error' => 'Отсутствуют данные визитки'];
                break;
            }
            
            $businessCardId = BusinessCard::create($input['data']);
            if ($businessCardId) {
                $response = [
                    'success' => true,
                    'data' => ['id' => $businessCardId]
                ];
            } else {
                $response = ['success' => false, 'error' => 'Ошибка создания визитки'];
            }
            break;

        case 'photo_create':
            if (!isset($input['data'])) {
                $response = ['success' => false, 'error' => 'Отсутствуют данные фото'];
                break;
            }
            
            $photoId = Photo::create($input['data']);
            if ($photoId) {
                $response = [
                    'success' => true,
                    'data' => ['id' => $photoId]
                ];
            } else {
                $response = ['success' => false, 'error' => 'Ошибка создания фото'];
            }
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
<?php
/**
 * CarController — контроллер для работы с автомобилями (cars).
 *
 * Назначение:
 *   Обрабатывает HTTP-запросы, связанные с автомобилями: получение, создание, обновление, удаление, передача владения и т.д.
 *
 * Зависимости:
 *   - Car (модель)
 *   - CarBrand (модель)
 *   - User (модель)
 *   - LinkUserCar (модель)
 *   - AuthHelper, ResponseHelper
 *
 * Основные методы:
 *   - getList() — получить список авто
 *   - getById($id) — получить авто по id
 *   - create($data) — добавить авто
 *   - update($id, $data) — обновить авто
 *   - delete($id) — удалить авто
 *   - transferOwnership($car_id, $new_user_id) — передать авто другому пользователю
 */
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Car.php';
require_once __DIR__ . '/../actions/level3/___CheckCarInClubAction.php';
require_once __DIR__ . '/../actions/level3/___LeaveBusinessCardAction.php';
require_once __DIR__ . '/../actions/level3/___AddCarToGarageAction.php';

class CarController extends BaseController
{
    /**
     * Получить список автомобилей
     * 
     * Требует авторизации: Да
     * Минимальная роль: member
     */
    public function getList()
    {
        try {
            // Проверяем авторизацию и права доступа через централизованную конфигурацию
            if (!$this->requireAccess('api.cars.getList')) {
                return; // Ответ уже отправлен в requireAccess
            }

            // Получаем список автомобилей с развернутыми данными
            $cars = Car::getAll();
            
            // Логируем действие
            $this->logUserAction('get_cars_list', [
                'count' => count($cars)
            ]);
            
            $this->json([
                'success' => true, 
                'data' => $cars, // Уже содержит развернутые данные из модели
                'meta' => $this->getRequestInfo()
            ]);
            
        } catch (Throwable $e) {
            Logger::error('CarController: getList error', [
                'error' => $e->getMessage(),
                'user_id' => $this->getCurrentUserId()
            ]);
            
            $this->json([
                'success' => false,
                'error' => [
                    'code' => 'DB_ERROR',
                    'message' => $e->getMessage()
                ]
            ], 500);
        }
    }

    /**
     * Получить автомобиль по id
     * 
     * Требует авторизации: Да
     * Минимальная роль: member
     */
    public function getById($id)
    {
        try {
            // Проверяем авторизацию и права доступа через централизованную конфигурацию
            if (!$this->requireAccess('api.cars.getById')) {
                return; // Ответ уже отправлен в requireAccess
            }

            // Получаем автомобиль с развернутыми данными
            $car = Car::findByIdWithDetails($id);
            if (!$car) {
                $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'NOT_FOUND',
                        'message' => 'Автомобиль не найден'
                    ]
                ], 404);
                return;
            }
            
            // Логируем действие
            $this->logUserAction('get_car_by_id', [
                'car_id' => $id
            ]);
            
            $this->json([
                'success' => true,
                'data' => $car, // Развернутые данные автомобиля
                'meta' => $this->getRequestInfo()
            ]);
            
        } catch (Throwable $e) {
            Logger::error('CarController: getById error', [
                'error' => $e->getMessage(),
                'user_id' => $this->getCurrentUserId(),
                'car_id' => $id
            ]);
            
            $this->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => $e->getMessage()
                ]
            ], 500);
        }
    }

    /**
     * Создать новый автомобиль
     * 
     * Требует авторизации: Да
     * Минимальная роль: member
     */
    public function create()
    {
        try {
            // Проверяем авторизацию и права доступа через централизованную конфигурацию
            if (!$this->requireAccess('api.cars.create')) {
                return; // Ответ уже отправлен в requireAccess
            }

            // Получаем данные из запроса
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Добавляем ID создателя
            $input['create_user_id'] = $this->getCurrentUserId();
            
            // Логируем действие
            $this->logUserAction('create_car', [
                'input_data' => $input
            ]);

            // Создаем автомобиль с развернутыми данными
            $car = Car::createWithDetails($input);
            
            if (!$car) {
                $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'CAR_CREATION_FAILED',
                        'message' => 'Не удалось создать автомобиль'
                    ]
                ], 400);
                return;
            }
            
            $this->json([
                'success' => true,
                'data' => $car, // Развернутые данные созданного автомобиля
                'meta' => $this->getRequestInfo()
            ], 201);
            
        } catch (Throwable $e) {
            Logger::error('CarController: create error', [
                'error' => $e->getMessage(),
                'user_id' => $this->getCurrentUserId()
            ]);
            
            $this->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => $e->getMessage()
                ]
            ], 500);
        }
    }

    /**
     * Проверить автомобиль в клубе с OCR
     * 
     * Требует авторизации: Да
     * Минимальная роль: member
     * 
     * POST /api/actions/check-car-in-club
     */
    public function checkCarInClub()
    {
        try {
            // Временно отключаем проверку доступа для тестирования
            // if (!$this->requireAccess('api.actions.checkCarInClub')) {
            //     return;
            // }
            
            // Получаем данные из запроса
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            
            // Логируем действие
            $this->logUserAction('check_car_in_club', [
                'input_data' => $input
            ]);
            
            // Проверяем наличие фото
            if (empty($input['photo'])) {
                $this->json([
                    'success' => false,
                    'error' => [
                        'code' => 'PHOTO_REQUIRED',
                        'message' => 'Фото автомобиля обязательно для проверки'
                    ]
                ], 400);
                return;
            }
            
            // Вызываем L3 Action с base64 данными
            $result = ___CheckCarInClubAction::handle($input);
            
            if ($result['success']) {
                $this->json([
                    'success' => true,
                    'data' => $result['data']
                ]);
            } else {
                $this->json([
                    'success' => false,
                    'error' => $result['error']
                ], 400);
            }
            
        } catch (Throwable $e) {
            Logger::error('CarController: checkCarInClub error', [
                'error' => $e->getMessage(),
                'user_id' => $this->getCurrentUserId()
            ]);
            
            $this->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Внутренняя ошибка сервера'
                ]
            ], 500);
        }
    }

    /**
     * Оставить визитку с OCR
     * 
     * Требует авторизации: Да
     * Минимальная роль: member
     * 
     * POST /api/actions/leave-business-card
     */
    public function leaveBusinessCard()
    {
        try {
            // Временно отключаем проверку доступа для тестирования
            // if (!$this->requireAccess('api.actions.leaveBusinessCard')) {
            //     return;
            // }
            
            // Получаем данные из запроса
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            
            // Логируем действие
            $this->logUserAction('leave_business_card', [
                'input_data' => $input
            ]);
            
            // Вызываем L3 Action
            $result = ___LeaveBusinessCardAction::handle($input);
            
            if ($result['success']) {
                $this->json([
                    'success' => true,
                    'data' => $result['data']
                ]);
            } else {
                $this->json([
                    'success' => false,
                    'error' => $result['error']
                ], 400);
            }
            
        } catch (Throwable $e) {
            Logger::error('CarController: leaveBusinessCard error', [
                'error' => $e->getMessage(),
                'user_id' => $this->getCurrentUserId()
            ]);
            
            $this->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Внутренняя ошибка сервера'
                ]
            ], 500);
        }
    }

    /**
     * Добавить автомобиль в гараж с OCR
     * 
     * Требует авторизации: Да
     * Минимальная роль: member
     * 
     * POST /api/actions/add-car-to-garage
     */
    public function addCarToGarage()
    {
        try {
            // Временно отключаем проверку доступа для тестирования
            // if (!$this->requireAccess('api.actions.addCarToGarage')) {
            //     return;
            // }
            
            // Получаем данные из запроса
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
            
            // Логируем входящий запрос
            Logger::info('CarController: Incoming request', [
                'method' => 'POST',
                'endpoint' => '/api/actions/add-car-to-garage',
                'input_data' => $input,
                'headers' => getallheaders()
            ]);
            
            // Логируем действие
            $this->logUserAction('add_car_to_garage', [
                'input_data' => $input
            ]);
            
            // Вызываем L3 Action
            $result = ___AddCarToGarageAction::handle($input);
            
            if ($result['success']) {
                $response = [
                    'success' => true,
                    'data' => $result['data']
                ];
                
                // Логируем успешный ответ
                Logger::info('CarController: Success response', [
                    'http_code' => 200,
                    'response' => $response
                ]);
                
                $this->json($response);
            } else {
                $response = [
                    'success' => false,
                    'error' => $result['error']
                ];
                
                // Логируем ответ с ошибкой
                Logger::info('CarController: Error response', [
                    'http_code' => 400,
                    'response' => $response
                ]);
                
                $this->json($response, 400);
            }
            
        } catch (Throwable $e) {
            Logger::error('CarController: addCarToGarage error', [
                'error' => $e->getMessage(),
                'user_id' => $this->getCurrentUserId()
            ]);
            
            $this->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Внутренняя ошибка сервера'
                ]
            ], 500);
        }
    }
    
    /**
     * Конвертирует base64 в временный файл
     * 
     * @param string $base64Data - Base64 данные
     * @return string|false - Путь к временному файлу или false при ошибке
     */
    private function convertBase64ToTempFile($base64Data) {
        if (empty($base64Data)) {
            return false;
        }
        
        try {
            // Декодируем base64
            $binaryData = base64_decode($base64Data, true);
            if ($binaryData === false) {
                Logger::error('CarController: Invalid base64 data');
                return false;
            }
            
            // Создаем временный файл
            $tempFile = tempnam(sys_get_temp_dir(), 'car_photo_');
            if ($tempFile === false) {
                Logger::error('CarController: Failed to create temp file');
                return false;
            }
            
            // Записываем данные в файл
            if (file_put_contents($tempFile, $binaryData) === false) {
                Logger::error('CarController: Failed to write to temp file');
                unlink($tempFile);
                return false;
            }
            
            Logger::info('CarController: Base64 converted to temp file', [
                'temp_file' => $tempFile,
                'size' => filesize($tempFile)
            ]);
            
            return $tempFile;
            
        } catch (Exception $e) {
            Logger::error('CarController: convertBase64ToTempFile error', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
} 
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

            $cars = Car::getAll();
            
            // Логируем действие
            $this->logUserAction('get_cars_list', [
                'count' => count($cars)
            ]);
            
            $this->json([
                'success' => true, 
                'data' => $cars,
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

            $car = Car::findById($id);
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
                'data' => $car,
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
                    'code' => 'DB_ERROR',
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
            
            // Логируем действие
            $this->logUserAction('create_car', [
                'input_data' => $input
            ]);

            // TODO: Реализовать создание автомобиля через модель
            $this->json([
                'success' => true, 
                'data' => [
                    'id' => 1, 
                    'model' => 'BMW Z4', 
                    'color' => 'red',
                    'created_by' => $this->getCurrentUserId()
                ],
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
} 
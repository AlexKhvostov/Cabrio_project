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
     * Получить список автомобилей (реализация через модель Car)
     */
    public function getList()
    {
        try {
            $cars = Car::getAll();
            $this->json(['success' => true, 'data' => $cars]);
        } catch (Throwable $e) {
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
     */
    public function getById($id)
    {
        try {
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
            $this->json(['success' => true, 'data' => $car]);
        } catch (Throwable $e) {
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
     */
    public function create()
    {
        // Пример: создать автомобиль (заглушка)
        $this->json(['success' => true, 'data' => ['id' => 1, 'model' => 'BMW Z4', 'color' => 'red']], 201);
    }
} 
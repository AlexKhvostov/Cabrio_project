<?php
/**
 * Модель Car — работа с таблицей cars.
 *
 * Назначение:
 *   Представляет автомобиль участника клуба CabrioRide.
 *   Используется для профиля пользователя, событий, связей с владельцем и пассажирами.
 *
 * Ключевые поля:
 *   - id: уникальный идентификатор
 *   - car_brand_id: FK на ref_car_brands (марка)
 *   - model, color, year, owner_user_id, status_id, created_at, updated_at и др.
 *
 * Связи:
 *   - CarBrand (car_brand_id → ref_car_brands.id)
 *   - Owner (owner_user_id → users.id)
 *   - Status (status_id → ref_statuses.id)
 *   - Users (через link_user_cars)
 *   - Photos (entity_type = 'car')
 *
 * Пример использования:
 *   $car = Car::findById(1);
 *   $newCar = Car::create([...]);
 */
class Car {
    public $id;
    public $car_brand_id;
    public $model;
    public $color;
    public $year;
    public $owner_user_id;
    public $status_id;
    public $created_at;
    public $updated_at;
    // ... другие поля по необходимости

    public function __construct($data) {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Найти автомобиль по id
     */
    public static function findById($id) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM cars WHERE id = ?');
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        return $data ? new self($data) : null;
    }

    /**
     * Создать новый автомобиль
     */
    public static function create($data) {
        // ... реализация вставки в БД
    }

    /**
     * Обновить автомобиль
     */
    public function update($data) {
        // ... реализация обновления в БД
    }

    /**
     * Удалить автомобиль
     */
    public function delete() {
        // ... реализация удаления из БД
    }
} 
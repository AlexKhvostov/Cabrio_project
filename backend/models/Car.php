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
require_once __DIR__ . '/../utils/Database.php';

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
     * Найти автомобиль по номеру
     */
    public static function findByPlateNumber($plateNumber) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM cars WHERE reg_number = ?');
        $stmt->execute([$plateNumber]);
        $data = $stmt->fetch();
        return $data ? new self($data) : null;
    }

    /**
     * Создать новый автомобиль
     */
    public static function create($data) {
        $pdo = Database::getInstance();
        
        // Подготовка данных для вставки
        $fields = ['reg_number', 'car_brand_id', 'model', 'color', 'year', 'owner_user_id', 'status_id', 'create_user_id'];
        $placeholders = implode(', ', array_fill(0, count($fields), '?'));
        $fieldNames = implode(', ', $fields);
        
        $stmt = $pdo->prepare("INSERT INTO cars ($fieldNames, created_at, updated_at) VALUES ($placeholders, NOW(), NOW())");
        
        $values = [
            $data['reg_number'] ?? null,
            $data['car_brand_id'] ?? null,
            $data['model'] ?? null,
            $data['color'] ?? null,
            $data['year'] ?? null,
            $data['owner_user_id'] ?? null,
            $data['status_id'] ?? 1, // "Замечена" по умолчанию
            $data['create_user_id'] ?? null
        ];
        
        $stmt->execute($values);
        return $pdo->lastInsertId();
    }

    /**
     * Обновить автомобиль
     */
    public function update($data) {
        $pdo = Database::getInstance();
        
        $updates = [];
        $values = [];
        
        // Подготавливаем поля для обновления
        $fields = ['car_brand_id', 'model', 'color', 'year', 'reg_number', 'vin', 'description', 'show_reg_number'];
        
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                $values[] = $data[$field];
            }
        }
        
        if (empty($updates)) {
            return false; // Нет данных для обновления
        }
        
        $values[] = $this->id; // ID для WHERE
        $updates[] = "updated_at = NOW()";
        
        $sql = "UPDATE cars SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        
        return $stmt->execute($values);
    }

    /**
     * Обновить статус автомобиля
     */
    public static function updateStatus($carId, $statusId) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('UPDATE cars SET status_id = ?, updated_at = NOW() WHERE id = ?');
        return $stmt->execute([$statusId, $carId]);
    }

    /**
     * Обновить владельца автомобиля
     */
    public static function updateOwner($carId, $userId) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('UPDATE cars SET owner_user_id = ?, updated_at = NOW() WHERE id = ?');
        return $stmt->execute([$userId, $carId]);
    }

    /**
     * Удалить автомобиль
     */
    public function delete() {
        // ... реализация удаления из БД
    }

    /**
     * Преобразовать объект автомобиля в массив
     */
    public function toArray() {
        return [
            'id' => $this->id,
            'car_brand_id' => $this->car_brand_id,
            'model' => $this->model,
            'color' => $this->color,
            'year' => $this->year,
            'reg_number' => $this->reg_number,
            'owner_user_id' => $this->owner_user_id,
            'status_id' => $this->status_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }

    /**
     * Получить список автомобилей с раскрытыми объектами brand, owner, status и photo
     */
    public static function getAll()
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->query(
            'SELECT c.*, 
                    cb.id as brand_id, cb.brand as brand_name,
                    u.id as owner_id, u.first_name_app as owner_first_name, u.last_name_app as owner_last_name,
                    s.id as status_id, s.code as status_code, s.name as status_name,
                    p.id as photo_id, p.url as photo_url, p.description as photo_description
             FROM cars c
             LEFT JOIN ref_car_brands cb ON c.car_brand_id = cb.id
             LEFT JOIN users u ON c.owner_user_id = u.id
             LEFT JOIN ref_statuses s ON c.status_id = s.id
             LEFT JOIN photos p ON p.id = (
                 SELECT id FROM photos 
                 WHERE entity_type = "car" AND entity_id = c.id 
                 ORDER BY id DESC LIMIT 1
             )'
        );
        $rows = $stmt->fetchAll();
        $cars = [];
        foreach ($rows as $row) {
            $car = $row;
            
            // Формируем объект brand
            $car['brand'] = [
                'id' => $row['brand_id'],
                'name' => $row['brand_name'],
            ];
            unset($car['brand_id'], $car['brand_name']);

            // Формируем объект owner
            $car['owner'] = $row['owner_id'] ? [
                'id' => $row['owner_id'],
                'first_name' => $row['owner_first_name'],
                'last_name' => $row['owner_last_name'],
            ] : null;
            unset($car['owner_id'], $car['owner_first_name'], $car['owner_last_name']);

            // Формируем объект status
            $car['status'] = [
                'id' => $row['status_id'],
                'code' => $row['status_code'],
                'name' => $row['status_name'],
            ];
            unset($car['status_id'], $car['status_code'], $car['status_name']);

            // Формируем объект photo
            $car['photo'] = $row['photo_id'] ? [
                'id' => $row['photo_id'],
                'url' => $row['photo_url'],
                'description' => $row['photo_description'],
            ] : null;
            unset($car['photo_id'], $car['photo_url'], $car['photo_description']);

            $cars[] = $car;
        }
        return $cars;
    }
} 
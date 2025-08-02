<?php
/**
 * Модель BusinessCard — работа с таблицей business_cards.
 *
 * Назначение:
 *   Представляет визитку/приглашение, оставленную участником клуба.
 *   Используется для отслеживания приглашений, связи с авто и пользователями.
 *
 * Ключевые поля:
 *   - id: уникальный идентификатор
 *   - car_id: FK на cars (автомобиль)
 *   - location, notes, inviter_user_id, created_at, updated_at
 *
 * Связи:
 *   - Car (car_id → cars.id)
 *   - Inviter (inviter_user_id → users.id)
 *
 * Пример использования:
 *   $card = BusinessCard::findById(1);
 *   $newCard = BusinessCard::create([...]);
 */
require_once __DIR__ . '/../utils/Database.php';

class BusinessCard {
    public $id;
    public $car_id;
    public $location;
    public $notes;
    public $inviter_user_id;
    public $created_at;
    public $updated_at;

    public function __construct($data) {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Найти визитку по id
     */
    public static function findById($id) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM business_cards WHERE id = ?');
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        return $data ? new self($data) : null;
    }

    /**
     * Создать новую визитку
     */
    public static function create($data) {
        $pdo = Database::getInstance();
        
        // Подготовка данных для вставки
        $fields = ['car_id', 'location', 'notes', 'inviter_user_id'];
        $placeholders = implode(', ', array_fill(0, count($fields), '?'));
        $fieldNames = implode(', ', $fields);
        
        $stmt = $pdo->prepare("INSERT INTO business_cards ($fieldNames, created_at, updated_at) VALUES ($placeholders, NOW(), NOW())");
        
        $values = [
            $data['car_id'],
            $data['location'] ?? null,
            $data['notes'] ?? $data['message'] ?? null,
            $data['user_id'] ?? $data['inviter_user_id']
        ];
        
        $stmt->execute($values);
        return $pdo->lastInsertId();
    }

    /**
     * Преобразовать объект визитки в массив
     */
    public function toArray() {
        return [
            'id' => $this->id,
            'car_id' => $this->car_id,
            'location' => $this->location,
            'notes' => $this->notes,
            'inviter_user_id' => $this->inviter_user_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }

    /**
     * Удалить визитку
     */
    public function delete() {
        // ... реализация удаления из БД
    }
} 
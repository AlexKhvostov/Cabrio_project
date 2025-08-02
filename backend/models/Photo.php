<?php
/**
 * Модель Photo — работа с таблицей photos.
 *
 * Назначение:
 *   Представляет фото, связанное с любой сущностью (user, car, event, review и т.д.).
 *   Используется для аватаров, галерей, обложек и других изображений.
 *
 * Ключевые поля:
 *   - id: уникальный идентификатор
 *   - entity_type: тип сущности (user, car, event, ...)
 *   - entity_id: ID сущности
 *   - file_name, url, photo_type, description, uploaded_at, uploaded_by
 *
 * Связи:
 *   - User, Car, Event, Review и др. (логическая связь по entity_type и entity_id)
 *   - UploadedBy (uploaded_by → users.id)
 *
 * Пример использования:
 *   $photo = Photo::findById(1);
 *   $newPhoto = Photo::create([...]);
 */
require_once __DIR__ . '/../utils/Database.php';

class Photo {
    public $id;
    public $entity_type;
    public $entity_id;
    public $file_name;
    public $url;
    public $photo_type;
    public $description;
    public $uploaded_at;
    public $uploaded_by;

    public function __construct($data) {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Найти фото по id
     */
    public static function findById($id) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM photos WHERE id = ?');
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        return $data ? new self($data) : null;
    }

    /**
     * Получить следующий ID для фото
     */
    public static function getNextId() {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('SELECT MAX(id) as max_id FROM photos');
        $stmt->execute();
        $result = $stmt->fetch();
        return ($result['max_id'] ?? 0) + 1;
    }

    /**
     * Создать новое фото
     */
    public static function create($data) {
        $pdo = Database::getInstance();
        
        // Подготовка данных для вставки
        $fields = ['entity_type', 'entity_id', 'file_name', 'url', 'photo_type', 'description', 'uploaded_by'];
        $placeholders = implode(', ', array_fill(0, count($fields), '?'));
        $fieldNames = implode(', ', $fields);
        
        $stmt = $pdo->prepare("INSERT INTO photos ($fieldNames, uploaded_at) VALUES ($placeholders, NOW())");
        
        $values = [
            $data['entity_type'],
            $data['entity_id'],
            $data['file_name'],
            $data['url'],
            $data['photo_type'] ?? null,
            $data['description'] ?? null,
            $data['uploaded_by'] ?? null
        ];
        
        $stmt->execute($values);
        return $pdo->lastInsertId();
    }

    /**
     * Преобразовать объект фото в массив
     */
    public function toArray() {
        return [
            'id' => $this->id,
            'entity_type' => $this->entity_type,
            'entity_id' => $this->entity_id,
            'file_name' => $this->file_name,
            'url' => $this->url,
            'photo_type' => $this->photo_type,
            'description' => $this->description,
            'uploaded_at' => $this->uploaded_at,
            'uploaded_by' => $this->uploaded_by
        ];
    }

    /**
     * Обновить фото
     */
    public static function update($id, $data) {
        $pdo = Database::getInstance();
        
        // Подготовка данных для обновления
        $fields = [];
        $values = [];
        
        foreach ($data as $field => $value) {
            $fields[] = "$field = ?";
            $values[] = $value;
        }
        
        $values[] = $id; // для WHERE id = ?
        
        $fieldUpdates = implode(', ', $fields);
        $stmt = $pdo->prepare("UPDATE photos SET $fieldUpdates WHERE id = ?");
        
        $stmt->execute($values);
        return $stmt->rowCount() > 0;
    }

    /**
     * Удалить фото
     */
    public function delete() {
        // ... реализация удаления из БД
    }
} 
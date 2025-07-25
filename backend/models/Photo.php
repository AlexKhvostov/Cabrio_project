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
     * Создать новое фото
     */
    public static function create($data) {
        // ... реализация вставки в БД
    }

    /**
     * Удалить фото
     */
    public function delete() {
        // ... реализация удаления из БД
    }
} 
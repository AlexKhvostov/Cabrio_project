<?php
/**
 * Модель GuideObject — работа с таблицей guide_objects.
 *
 * Назначение:
 *   Представляет гид-объект (место, сервис, точку интереса).
 *   Используется для отображения на карте, фильтрации, отзывов и модерации.
 *
 * Ключевые поля:
 *   - id: уникальный идентификатор
 *   - guide_object_type_id: FK на ref_guide_object_types
 *   - guide_object_kind_id: FK на ref_guide_object_kinds
 *   - name, city, address, website, phone, description, add_user_id, status_id, created_at, updated_at
 *
 * Связи:
 *   - Type (guide_object_type_id → ref_guide_object_types.id)
 *   - Kind (guide_object_kind_id → ref_guide_object_kinds.id)
 *   - Author (add_user_id → users.id)
 *   - Status (status_id → ref_statuses.id)
 *   - Photos (entity_type = 'guide_object')
 *   - Reviews (reviews.guide_object_id)
 *
 * Пример использования:
 *   $obj = GuideObject::findById(1);
 *   $newObj = GuideObject::create([...]);
 */
class GuideObject {
    public $id;
    public $guide_object_type_id;
    public $guide_object_kind_id;
    public $name;
    public $city;
    public $address;
    public $website;
    public $phone;
    public $description;
    public $add_user_id;
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
     * Найти гид-объект по id
     */
    public static function findById($id) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM guide_objects WHERE id = ?');
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        return $data ? new self($data) : null;
    }

    /**
     * Создать новый гид-объект
     */
    public static function create($data) {
        // ... реализация вставки в БД
    }

    /**
     * Обновить гид-объект
     */
    public function update($data) {
        // ... реализация обновления в БД
    }

    /**
     * Удалить гид-объект
     */
    public function delete() {
        // ... реализация удаления из БД
    }
} 
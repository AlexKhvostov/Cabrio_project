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
require_once __DIR__ . '/../utils/Database.php';
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

    /**
     * Обновить статус гид-объекта
     */
    public static function updateStatus($guideObjectId, $statusId) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('UPDATE guide_objects SET status_id = ?, updated_at = NOW() WHERE id = ?');
        return $stmt->execute([$statusId, $guideObjectId]);
    }

    /**
     * Получить список гид-объектов с раскрытыми объектами type, kind, author и photo
     */
    public static function getAll()
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->query(
            'SELECT go.*, 
                    got.id as guide_object_type_id, got.code as guide_object_type_code, got.name as guide_object_type_name,
                    gok.id as guide_object_kind_id, gok.code as guide_object_kind_code, gok.name as guide_object_kind_name,
                    u.id as add_user_id, u.first_name_app as author_first_name, u.last_name_app as author_last_name,
                    s.id as status_id, s.code as status_code, s.name as status_name,
                    p.id as photo_id, p.url as photo_url, p.description as photo_description
             FROM guide_objects go
             LEFT JOIN ref_guide_object_types got ON go.guide_object_type_id = got.id
             LEFT JOIN ref_guide_object_kinds gok ON go.guide_object_kind_id = gok.id
             LEFT JOIN users u ON go.add_user_id = u.id
             LEFT JOIN ref_statuses s ON go.status_id = s.id
             LEFT JOIN photos p ON p.id = (
                 SELECT id FROM photos 
                 WHERE entity_type = "guide_object" AND entity_id = go.id 
                 ORDER BY id DESC LIMIT 1
             )'
        );
        $rows = $stmt->fetchAll();
        $guideObjects = [];
        foreach ($rows as $row) {
            $guideObject = $row;
            $guideObject['guide_object_type'] = [
                'id' => $row['guide_object_type_id'],
                'code' => $row['guide_object_type_code'],
                'name' => $row['guide_object_type_name'],
            ];
            unset($guideObject['guide_object_type_id'], $guideObject['guide_object_type_code'], $guideObject['guide_object_type_name']);

            $guideObject['guide_object_kind'] = [
                'id' => $row['guide_object_kind_id'],
                'code' => $row['guide_object_kind_code'],
                'name' => $row['guide_object_kind_name'],
            ];
            unset($guideObject['guide_object_kind_id'], $guideObject['guide_object_kind_code'], $guideObject['guide_object_kind_name']);

            $guideObject['author'] = $row['add_user_id'] ? [
                'id' => $row['add_user_id'],
                'first_name' => $row['author_first_name'],
                'last_name' => $row['author_last_name'],
            ] : null;
            unset($guideObject['add_user_id'], $guideObject['author_first_name'], $guideObject['author_last_name']);

            $guideObject['status'] = [
                'id' => $row['status_id'],
                'code' => $row['status_code'],
                'name' => $row['status_name'],
            ];
            unset($guideObject['status_id'], $guideObject['status_code'], $guideObject['status_name']);

            $guideObject['photo'] = $row['photo_id'] ? [
                'id' => $row['photo_id'],
                'url' => $row['photo_url'],
                'description' => $row['photo_description'],
            ] : null;
            unset($guideObject['photo_id'], $guideObject['photo_url'], $guideObject['photo_description']);

            $guideObjects[] = $guideObject;
        }
        return $guideObjects;
    }
} 
<?php
/**
 * Модель GuideObjectKind — справочник видов гид-объектов (таблица ref_guide_object_kinds).
 *
 * Назначение:
 *   Представляет вид гид-объекта (например, "breakfast" для кафе).
 *   Используется для фильтрации, создания и отображения гид-объектов.
 *
 * Ключевые поля:
 *   - id: уникальный идентификатор
 *   - type_id: FK на ref_guide_object_types
 *   - code: строковый код вида
 *   - name: название вида
 *   - description: описание
 *
 * Связи:
 *   - GuideObjectType (type_id → ref_guide_object_types.id)
 *   - GuideObjects (guide_objects.guide_object_kind_id → ref_guide_object_kinds.id)
 *
 * Пример использования:
 *   $kind = GuideObjectKind::findById(1);
 *   $kind = GuideObjectKind::findByCode('breakfast');
 */
class GuideObjectKind {
    public $id;
    public $type_id;
    public $code;
    public $name;
    public $description;

    public function __construct($data) {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Найти вид по id
     */
    public static function findById($id) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM ref_guide_object_kinds WHERE id = ?');
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        return $data ? new self($data) : null;
    }

    /**
     * Найти вид по code
     */
    public static function findByCode($code) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM ref_guide_object_kinds WHERE code = ?');
        $stmt->execute([$code]);
        $data = $stmt->fetch();
        return $data ? new self($data) : null;
    }
} 
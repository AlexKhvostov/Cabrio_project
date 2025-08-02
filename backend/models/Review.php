<?php
/**
 * Модель Review — работа с таблицей reviews.
 *
 * Назначение:
 *   Представляет отзыв пользователя о гид-объекте.
 *   Используется для отображения рейтинга, модерации, обратной связи.
 *
 * Ключевые поля:
 *   - id: уникальный идентификатор
 *   - guide_object_id: FK на guide_objects
 *   - quality_rating, speed_rating, price_rating, feedback, author_user_id, status_id, created_at, updated_at
 *
 * Связи:
 *   - GuideObject (guide_object_id → guide_objects.id)
 *   - Author (author_user_id → users.id)
 *   - Status (status_id → ref_statuses.id)
 *   - Photos (entity_type = 'review')
 *
 * Пример использования:
 *   $review = Review::findById(1);
 *   $newReview = Review::create([...]);
 */
require_once __DIR__ . '/../utils/Database.php';

class Review {
    public $id;
    public $guide_object_id;
    public $quality_rating;
    public $speed_rating;
    public $price_rating;
    public $feedback;
    public $author_user_id;
    public $status_id;
    public $created_at;
    public $updated_at;

    public function __construct($data) {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Найти отзыв по id
     */
    public static function findById($id) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM reviews WHERE id = ?');
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        return $data ? new self($data) : null;
    }

    /**
     * Получить все отзывы
     */
    public static function getAll() {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM reviews ORDER BY created_at DESC');
        $stmt->execute();
        $data = $stmt->fetchAll();
        
        $reviews = [];
        foreach ($data as $row) {
            $reviews[] = (new self($row))->toArray();
        }
        
        return $reviews;
    }

    /**
     * Создать новый отзыв
     */
    public static function create($data) {
        // ... реализация вставки в БД
    }

    /**
     * Преобразовать объект отзыва в массив
     */
    public function toArray() {
        return [
            'id' => $this->id,
            'guide_object_id' => $this->guide_object_id,
            'quality_rating' => $this->quality_rating,
            'speed_rating' => $this->speed_rating,
            'price_rating' => $this->price_rating,
            'feedback' => $this->feedback,
            'author_user_id' => $this->author_user_id,
            'status_id' => $this->status_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }

    /**
     * Обновить отзыв
     */
    public function update($data) {
        // ... реализация обновления в БД
    }

    /**
     * Удалить отзыв
     */
    public function delete() {
        // ... реализация удаления из БД
    }
} 
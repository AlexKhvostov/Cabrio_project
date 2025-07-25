<?php
/**
 * Модель Event — работа с таблицей events.
 *
 * Назначение:
 *   Представляет событие/мероприятие клуба CabrioRide.
 *   Используется для организации, участия, отображения событий.
 *
 * Ключевые поля:
 *   - id: уникальный идентификатор
 *   - event_type_id: FK на ref_event_types (тип события)
 *   - title, description, event_date, event_time, city, org_user_id, status_id, created_at, updated_at
 *
 * Связи:
 *   - EventType (event_type_id → ref_event_types.id)
 *   - Organizer (org_user_id → users.id)
 *   - Status (status_id → ref_statuses.id)
 *   - Participants (через link_event_participants)
 *   - Photos (entity_type = 'event')
 *
 * Пример использования:
 *   $event = Event::findById(1);
 *   $newEvent = Event::create([...]);
 */
class Event {
    public $id;
    public $event_type_id;
    public $title;
    public $description;
    public $event_date;
    public $event_time;
    public $city;
    public $org_user_id;
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
     * Найти событие по id
     */
    public static function findById($id) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM events WHERE id = ?');
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        return $data ? new self($data) : null;
    }

    /**
     * Создать новое событие
     */
    public static function create($data) {
        // ... реализация вставки в БД
    }

    /**
     * Обновить событие
     */
    public function update($data) {
        // ... реализация обновления в БД
    }

    /**
     * Удалить событие
     */
    public function delete() {
        // ... реализация удаления из БД
    }
} 
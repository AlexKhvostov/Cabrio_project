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
require_once __DIR__ . '/../utils/Database.php';
require_once __DIR__ . '/../utils/UrlHelper.php';
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

    /**
     * Обновить статус события
     */
    public static function updateStatus($eventId, $statusId) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('UPDATE events SET status_id = ?, updated_at = NOW() WHERE id = ?');
        return $stmt->execute([$statusId, $eventId]);
    }

    /**
     * Получить список событий с раскрытыми объектами type, organizer и photo
     */
    public static function getAll()
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->query(
            'SELECT e.*, 
                    et.id as event_type_id, et.code as event_type_code, et.name as event_type_name,
                    u.id as org_user_id, u.first_name_app as org_first_name, u.last_name_app as org_last_name,
                    s.id as status_id, s.code as status_code, s.name as status_name,
                    p.id as photo_id, p.url as photo_url, p.description as photo_description
             FROM events e
             LEFT JOIN ref_event_types et ON e.event_type_id = et.id
             LEFT JOIN users u ON e.org_user_id = u.id
             LEFT JOIN ref_statuses s ON e.status_id = s.id
             LEFT JOIN photos p ON p.id = (
                 SELECT id FROM photos 
                 WHERE entity_type = "event" AND entity_id = e.id 
                 ORDER BY id DESC LIMIT 1
             )'
        );
        $rows = $stmt->fetchAll();
        $events = [];
        foreach ($rows as $row) {
            $event = $row;
            $event['event_type'] = [
                'id' => $row['event_type_id'],
                'code' => $row['event_type_code'],
                'name' => $row['event_type_name'],
            ];
            unset($event['event_type_id'], $event['event_type_code'], $event['event_type_name']);

            $event['organizer'] = $row['org_user_id'] ? [
                'id' => $row['org_user_id'],
                'first_name' => $row['org_first_name'],
                'last_name' => $row['org_last_name'],
            ] : null;
            unset($event['org_user_id'], $event['org_first_name'], $event['org_last_name']);

            $event['status'] = [
                'id' => $row['status_id'],
                'code' => $row['status_code'],
                'name' => $row['status_name'],
            ];
            unset($event['status_id'], $event['status_code'], $event['status_name']);

            $event['photo'] = $row['photo_id'] ? [
                'id' => $row['photo_id'],
                'url' => UrlHelper::buildUploadsUrl($row['photo_url']),
                'description' => $row['photo_description'],
            ] : null;
            unset($event['photo_id'], $event['photo_url'], $event['photo_description']);

            $events[] = $event;
        }
        return $events;
    }
} 
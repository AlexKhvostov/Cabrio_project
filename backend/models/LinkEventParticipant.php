<?php
/**
 * Модель LinkEventParticipant — связь участника и события (таблица link_event_participants).
 *
 * Назначение:
 *   Представляет участие пользователя в событии (статус участия, +1 и т.д.).
 *   Используется для отображения участников, контроля лимитов, управления регистрацией.
 *
 * Ключевые поля:
 *   - event_id: FK на events
 *   - user_id: FK на users
 *   - confidence: уверенность участия ('yes', 'maybe', 'no')
 *   - plus_one: будет ли +1
 *   - created_at
 *
 * Связи:
 *   - Event (event_id → events.id)
 *   - User (user_id → users.id)
 *
 * Пример использования:
 *   $link = LinkEventParticipant::find($event_id, $user_id);
 *   $newLink = LinkEventParticipant::create([...]);
 */
class LinkEventParticipant {
    public $event_id;
    public $user_id;
    public $confidence;
    public $plus_one;
    public $created_at;

    public function __construct($data) {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Найти связь по event_id и user_id
     */
    public static function find($event_id, $user_id) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM link_event_participants WHERE event_id = ? AND user_id = ?');
        $stmt->execute([$event_id, $user_id]);
        $data = $stmt->fetch();
        return $data ? new self($data) : null;
    }

    /**
     * Создать новую связь
     */
    public static function create($data) {
        // ... реализация вставки в БД
    }

    /**
     * Удалить связь
     */
    public function delete() {
        // ... реализация удаления из БД
    }
} 
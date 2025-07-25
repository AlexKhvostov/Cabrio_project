<?php
/**
 * Модель Session — работа с таблицей sessions.
 *
 * Назначение:
 *   Представляет сессию пользователя (авторизация, хранение токена).
 *   Используется для проверки авторизации, управления сессиями, интеграции с Telegram.
 *
 * Ключевые поля:
 *   - id: уникальный идентификатор
 *   - user_id: FK на users (пользователь)
 *   - session_token: токен сессии
 *   - telegram_data: JSON-данные Telegram (если применимо)
 *   - created_at, expires_at, is_active
 *
 * Связи:
 *   - User (user_id → users.id)
 *
 * Пример использования:
 *   $session = Session::findById(1);
 *   $session = Session::findByToken('...');
 *   $session = Session::findByTelegramId(123456789);
 *   $newSession = Session::create([...]);
 */
class Session {
    public $id;
    public $user_id;
    public $session_token;
    public $telegram_data;
    public $created_at;
    public $expires_at;
    public $is_active;

    public function __construct($data) {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Найти сессию по id
     */
    public static function findById($id) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM sessions WHERE id = ?');
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        return $data ? new self($data) : null;
    }

    /**
     * Найти сессию по токену
     */
    public static function findByToken($token) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM sessions WHERE session_token = ?');
        $stmt->execute([$token]);
        $data = $stmt->fetch();
        return $data ? new self($data) : null;
    }

    /**
     * Найти сессию по telegram_id пользователя
     * (telegram_id ищется через users, предполагается связь sessions.user_id = users.id)
     */
    public static function findByTelegramId($telegram_id) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('
            SELECT s.* FROM sessions s
            JOIN users u ON s.user_id = u.id
            WHERE u.telegram_id = ?
            ORDER BY s.created_at DESC
            LIMIT 1
        ');
        $stmt->execute([$telegram_id]);
        $data = $stmt->fetch();
        return $data ? new self($data) : null;
    }

    /**
     * Создать новую сессию
     */
    public static function create($data) {
        // ... реализация вставки в БД
    }
} 
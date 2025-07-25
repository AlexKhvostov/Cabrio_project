<?php
/**
 * Модель User — работа с таблицей users.
 * 
 * Назначение:
 *   Представляет пользователя платформы CabrioRide.
 *   Используется для авторизации, профиля, связей с авто, событиями, фото и т.д.
 *
 * Ключевые поля:
 *   - id: уникальный идентификатор
 *   - telegram_id: Telegram ID пользователя
 *   - username: username в Telegram
 *   - role_id: FK на ref_roles (роль пользователя)
 *   - city, email, created_at, updated_at и др.
 *
 * Связи:
 *   - Role (role_id → ref_roles.id)
 *   - Cars (через link_user_cars)
 *   - Events (через link_event_participants)
 *   - Photos (entity_type = 'user')
 *   - Sessions (sessions.user_id)
 *
 * Пример использования:
 *   $user = User::findById(1);
 *   $user = User::findByTelegramId(123456789);
 *   $newUser = User::create([...]);
 */
require_once __DIR__ . '/../utils/Database.php';

class User {
    public $id;
    public $first_name;
    public $last_name;
    public $telegram_id;
    public $username;
    public $role_id;
    public $created_at;
    public $updated_at;
    public $photo; // главное фото пользователя (объект или null)

    public function __construct($data) {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Найти пользователя по id
     */
    public static function findById($id) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        return $data ? new self($data) : null;
    }

    /**
     * Найти пользователя по telegram_id
     */
    public static function findByTelegramId($telegram_id) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE telegram_id = ?');
        $stmt->execute([$telegram_id]);
        $data = $stmt->fetch();
        return $data ? new self($data) : null;
    }

    /**
     * Создать нового пользователя
     */
    public static function create($data) {
        // ... реализация вставки в БД
    }

    /**
     * Обновить пользователя
     */
    public function update($data) {
        // ... реализация обновления в БД
    }

    /**
     * Удалить пользователя
     */
    public function delete() {
        // ... реализация удаления из БД
    }

    /**
     * Получить список пользователей с раскрытым объектом role и photo
     */
    public static function getAll()
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->query(
            'SELECT u.*, 
                    r.id as role_id, r.code as role_code, r.name as role_name,
                    p.id as photo_id, p.url as photo_url, p.description as photo_description
             FROM users u
             LEFT JOIN ref_roles r ON u.role_id = r.id
             LEFT JOIN photos p ON p.id = (
                 SELECT id FROM photos 
                 WHERE entity_type = "user" AND entity_id = u.id 
                 ORDER BY id DESC LIMIT 1
             )'
        );
        $rows = $stmt->fetchAll();
        $users = [];
        foreach ($rows as $row) {
            $user = $row;
            $user['role'] = [
                'id' => $row['role_id'],
                'code' => $row['role_code'],
                'name' => $row['role_name'],
            ];
            unset($user['role_id'], $user['role_code'], $user['role_name']);

            $user['photo'] = $row['photo_id'] ? [
                'id' => $row['photo_id'],
                'url' => $row['photo_url'],
                'description' => $row['photo_description'],
            ] : null;
            unset($user['photo_id'], $user['photo_url'], $user['photo_description']);

            $users[] = $user;
        }
        return $users;
    }
} 
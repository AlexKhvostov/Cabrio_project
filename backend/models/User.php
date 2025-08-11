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
require_once __DIR__ . '/../utils/ExpandHelper.php';
require_once __DIR__ . '/../utils/UrlHelper.php';

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
     * Найти пользователя по id с развернутыми данными
     * 
     * @param int $id ID пользователя
     * @return array|null Развернутые данные пользователя или null
     */
    public static function findByIdWithDetails($id) {
        $pdo = Database::getInstance();
        // Подтягиваем роль и последнее фото пользователя (как в getAll)
        $stmt = $pdo->prepare(
            'SELECT u.*, 
                    r.id as role_id, r.code as role_code, r.name as role_name,
                    p.id as photo_id, p.url as photo_url, p.description as photo_description
             FROM users u
             LEFT JOIN ref_roles r ON u.role_id = r.id
             LEFT JOIN photos p ON p.id = (
                 SELECT id FROM photos 
                 WHERE entity_type = "user" AND entity_id = u.id 
                 ORDER BY id DESC LIMIT 1
             )
             WHERE u.id = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        // Формируем пользователя с role и photo
        $user = $row;

        // Объект role
        $user['role'] = [
            'id' => $row['role_id'],
            'code' => $row['role_code'],
            'name' => $row['role_name'],
        ];
        unset($user['role_id'], $user['role_code'], $user['role_name']);

        // Объект photo (со склейкой URL + размеры)
        $user['photo'] = $row['photo_id'] ? [
            'id' => $row['photo_id'],
            'url' => UrlHelper::buildUploadsUrl($row['photo_url']),
            'urls' => [
                'medium' => UrlHelper::buildUploadsUrlSized($row['photo_url'], 'medium'),
                'mini'   => UrlHelper::buildUploadsUrlSized($row['photo_url'], 'mini'),
            ],
            'description' => $row['photo_description'],
        ] : null;
        unset($user['photo_id'], $user['photo_url'], $user['photo_description']);

        // Прикладываем машины пользователя (если есть)
        require_once __DIR__ . '/Car.php';
        // Для профиля текущего пользователя НЕ маскируем номер (maskPrivate=false)
        $cars = Car::getByOwnerIds([(int)$row['id']], false);
        $carsForUser = [];
        foreach ($cars as $car) {
            $carForOutput = $car;
            unset($carForOutput['owner_user_id']);
            // Так как это профиль текущего пользователя, он может редактировать свои авто
            $carForOutput['permissions'] = [ 'canEdit' => true ];
            $carsForUser[] = $carForOutput;
        }
        $user['cars'] = $carsForUser;

        return $user;
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
     * Найти пользователя по telegram_id с развернутыми данными
     * 
     * @param string $telegramId Telegram ID пользователя
     * @return array|null Развернутые данные пользователя или null
     */
    public static function findByTelegramIdWithDetails($telegramId) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE telegram_id = ?');
        $stmt->execute([$telegramId]);
        $data = $stmt->fetch();
        
        if (!$data) {
            return null;
        }
        
        // Развертываем данные с помощью ExpandHelper
        return ExpandHelper::expandUserData($data);
    }

    /**
     * Создать нового пользователя
     */
    public static function create($data) {
        $pdo = Database::getInstance();
        
        // Подготовка данных для вставки
        $fields = ['telegram_id', 'username', 'first_name_tg', 'last_name_tg', 'role_id'];
        $placeholders = implode(', ', array_fill(0, count($fields), '?'));
        $fieldNames = implode(', ', $fields);
        
        $stmt = $pdo->prepare("INSERT INTO users ($fieldNames, created_at, updated_at) VALUES ($placeholders, NOW(), NOW())");
        
        $values = [
            $data['telegram_id'],
            $data['username'] ?? null,
            $data['first_name'] ?? null,  // Сохраняем в поле first_name_tg
            $data['last_name'] ?? null,   // Сохраняем в поле last_name_tg
            $data['role_id'] ?? 2 // guest по умолчанию
        ];
        
        $stmt->execute($values);
        return $pdo->lastInsertId();
    }

    /**
     * Создать нового пользователя с возвратом развернутых данных
     * 
     * @param array $data Данные для создания
     * @return array|null Развернутые данные созданного пользователя или null
     */
    public static function createWithDetails($data) {
        $userId = self::create($data);
        
        if (!$userId) {
            return null;
        }
        
        // Возвращаем развернутые данные созданного пользователя
        return self::findByIdWithDetails($userId);
    }

    /**
     * Обновить пользователя (статический метод)
     */
    public static function update($data) {
        $pdo = Database::getInstance();
        
        if (!isset($data['id'])) {
            return false;
        }
        
        $updates = [];
        $values = [];
        
        // Подготавливаем поля для обновления
        $fields = [
            'username',
            'first_name_tg', 'last_name_tg',
            'first_name_app', 'last_name_app',
            'email', 'phone',
            'city', 'country',
            'about', 'notes',
            'telegram_photo_id'
        ];
        
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                $values[] = $data[$field];
            }
        }
        
        if (empty($updates)) {
            return false; // Нет данных для обновления
        }
        
        $values[] = $data['id']; // ID для WHERE
        $updates[] = "updated_at = NOW()";
        
        $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        
        return $stmt->execute($values);
    }

    /**
     * Обновить пользователя с возвратом развернутых данных
     * 
     * @param int $id ID пользователя
     * @param array $data Данные для обновления
     * @return array|null Развернутые данные обновленного пользователя или null
     */
    public static function updateWithDetails($id, $data) {
        $data['id'] = $id; // Добавляем ID для обновления
        
        $updateResult = self::update($data);
        
        if (!$updateResult) {
            return null;
        }
        
        // Возвращаем развернутые данные обновленного пользователя
        return self::findByIdWithDetails($id);
    }

    /**
     * Обновить роль пользователя
     */
    public static function updateRole($userId, $roleId) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('UPDATE users SET role_id = ?, updated_at = NOW() WHERE id = ?');
        return $stmt->execute([$roleId, $userId]);
    }

    /**
     * Удалить пользователя
     */
    public function delete() {
        // ... реализация удаления из БД
    }

    /**
     * Преобразовать объект пользователя в массив
     */
    public function toArray() {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'telegram_id' => $this->telegram_id,
            'username' => $this->username,
            'role_id' => $this->role_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
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
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!$rows) {
            return [];
        }

        // 1) Собираем ID пользователей
        $userIds = [];
        foreach ($rows as $row) {
            $userIds[] = (int)$row['id'];
        }

        // 2) Получаем машины всех владельцев одним запросом и группируем по owner_user_id
        require_once __DIR__ . '/Car.php';
        $cars = Car::getByOwnerIds($userIds);
        $carsByOwnerId = [];
        foreach ($cars as $car) {
            $ownerId = (int)$car['owner_user_id'];
            if (!isset($carsByOwnerId[$ownerId])) {
                $carsByOwnerId[$ownerId] = [];
            }
            $carForOutput = $car;
            unset($carForOutput['owner_user_id']);
            $carsByOwnerId[$ownerId][] = $carForOutput;
        }

        // 3) Формируем пользователей с role, photo и cars
        $users = [];
        foreach ($rows as $row) {
            $user = $row;

            // Формируем объект role
            $user['role'] = [
                'id' => $row['role_id'],
                'code' => $row['role_code'],
                'name' => $row['role_name'],
            ];
            unset($user['role_id'], $user['role_code'], $user['role_name']);

            // Формируем объект photo (склеиваем с UPLOADS_BASE_URL + размеры)
            $user['photo'] = $row['photo_id'] ? [
                'id' => $row['photo_id'],
                'url' => UrlHelper::buildUploadsUrl($row['photo_url']),
                'urls' => [
                    'medium' => UrlHelper::buildUploadsUrlSized($row['photo_url'], 'medium'),
                    'mini'   => UrlHelper::buildUploadsUrlSized($row['photo_url'], 'mini'),
                ],
                'description' => $row['photo_description'],
            ] : null;
            unset($user['photo_id'], $user['photo_url'], $user['photo_description']);

            // Прикладываем машины пользователя (если есть)
            $uid = (int)$row['id'];
            $user['cars'] = $carsByOwnerId[$uid] ?? [];

            $users[] = $user;
        }
        return $users;
    }
} 
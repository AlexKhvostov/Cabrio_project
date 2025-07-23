<?php
/**
 * Эндпоинт: "Забрать" авто без владельца
 * Позволяет пользователю назначить себя владельцем автомобиля, если у него нет владельца
 * POST /api/cars/claim.php
 *
 * Вход:
 *   auth: { user_id, role }
 *   data: { car_id }
 *
 * Условия:
 *   - Только для member+
 *   - Только если owner_user_id IS NULL
 *   - После claim: owner_user_id = user_id, создаётся связь в link_user_cars (owner)
 *   - Ошибка 404 — если авто не найдено
 *   - Ошибка 422 — если уже есть владелец
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../utils/Database.php';
require_once __DIR__ . '/../../utils/ApiHandler.php';

class ClaimCarEndpoint extends ApiHandler {
    protected function process() {
        // 1. Проверка прав доступа
        $this->checkAccess('guest'); // было 'member', теперь 'guest' по бизнес-логике
        $db = Database::getInstance()->getConnection();
        $userId = $this->getAuth('user_id');
        $carId = $this->requireField('car_id', 'car_id обязателен');

        // 2. Ищем авто
        $carStmt = $db->prepare('SELECT id, owner_user_id FROM cars WHERE id = ?');
        $carStmt->execute([$carId]);
        $car = $carStmt->fetch(PDO::FETCH_ASSOC);
        if (!$car) {
            return $this->error('Автомобиль не найден', 404, 'NOT_FOUND');
        }
        if (!empty($car['owner_user_id'])) {
            return $this->error('У автомобиля уже есть владелец', 422, 'ALREADY_CLAIMED');
        }

        // 3. Обновляем owner_user_id
        $updateStmt = $db->prepare('UPDATE cars SET owner_user_id = ? WHERE id = ?');
        $updateStmt->execute([$userId, $carId]);

        // 4. Добавляем связь в link_user_cars (если нет)
        $checkLink = $db->prepare('SELECT 1 FROM link_user_cars WHERE user_id = ? AND car_id = ? AND role_id = 1');
        $checkLink->execute([$userId, $carId]);
        if (!$checkLink->fetch()) {
            $insertLink = $db->prepare('INSERT INTO link_user_cars (user_id, car_id, role_id) VALUES (?, ?, 1)');
            $insertLink->execute([$userId, $carId]);
        }

        // 5. (Опционально) Логируем событие — можно добавить запись в moderation_logs или отдельный лог
        // ...

        // 6. Возвращаем успех
        return $this->success([
            'car_id' => $carId,
            'owner_user_id' => $userId
        ], 'Вы успешно стали владельцем автомобиля');
    }
}

$endpoint = new ClaimCarEndpoint();
$endpoint->handle(); 
<?php
/**
 * /api/cars/update.php
 * Эндпоинт для обновления автомобиля: смена владельца, статуса, других полей
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../utils/Database.php';
require_once __DIR__ . '/../../utils/ApiHandler.php';

class UpdateCarEndpoint extends ApiHandler {
    protected function process() {
        // Проверка прав (минимальная роль: member)
        $this->checkAccess('member');

        $pdo = Database::getInstance()->getConnection();
        $carId = $this->requireField('car_id');
        $data = $this->data;

        // Собираем только разрешённые к обновлению поля
        $fields = [];
        if (isset($data['owner_user_id'])) {
            $fields['owner_user_id'] = (int)$data['owner_user_id'];
        }
        if (isset($data['status'])) {
            $fields['status'] = $data['status'];
        }
        if (isset($data['model'])) {
            $fields['model'] = $data['model'];
        }
        if (isset($data['color'])) {
            $fields['color'] = $data['color'];
        }
        if (isset($data['year'])) {
            $fields['year'] = (int)$data['year'];
        }
        // ... другие поля по необходимости

        if (empty($fields)) {
            return $this->error(422, 'NO_FIELDS', 'Нет данных для обновления');
        }

        // Формируем SQL
        $set = [];
        $params = [];
        foreach ($fields as $k => $v) {
            $set[] = "$k = :$k";
            $params[$k] = $v;
        }
        $params['car_id'] = $carId;
        $sql = 'UPDATE cars SET ' . implode(', ', $set) . ', updated_at = NOW() WHERE id = :car_id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        // Получаем обновлённую запись
        $stmt = $pdo->prepare('SELECT * FROM cars WHERE id = :car_id');
        $stmt->execute(['car_id' => $carId]);
        $car = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$car) {
            return $this->error(404, 'NOT_FOUND', 'Автомобиль не найден');
        }

        $userId = $this->getAuth('user_id');
        $userRole = $this->getAuth('role');

        // Только владелец или модератор/админ может обновлять авто
        if ($car['owner_user_id'] != $userId && !in_array($userRole, ['moderator', 'admin'])) {
            return $this->error('Нет прав на изменение этого авто', 403, 'ACCESS_DENIED');
        }

        // Смена владельца — только для модератора/админа
        if (isset($fields['owner_user_id']) && $fields['owner_user_id'] != $car['owner_user_id']) {
            if (!in_array($userRole, ['moderator', 'admin'])) {
                return $this->error('Сменить владельца может только модератор или админ', 403, 'ACCESS_DENIED');
            }
        }

        return $this->success([
            'car_id' => $car['id'],
            'reg_number' => $car['reg_number'],
            'owner_user_id' => $car['owner_user_id'],
            'status' => $car['status_id'],
            'model' => $car['model'],
            'color' => $car['color'],
            'year' => $car['year']
        ], 'Автомобиль обновлён');
    }
}

$endpoint = new UpdateCarEndpoint();
$endpoint->handle(); 
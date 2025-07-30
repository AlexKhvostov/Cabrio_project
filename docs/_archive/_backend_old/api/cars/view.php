<?php
/**
 * /api/cars/view.php
 * Эндпоинт для просмотра информации об автомобиле по car_id или reg_number
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../utils/Database.php';
require_once __DIR__ . '/../../utils/ApiHandler.php';

class ViewCarEndpoint extends ApiHandler {
    protected function process() {
        $this->checkAccess('guest'); // Просмотр доступен всем авторизованным
        $db = Database::getInstance();
        $data = $this->data;

        // Получаем идентификатор авто
        $carId = $data['car_id'] ?? null;
        $regNumber = $data['reg_number'] ?? null;
        if (!$carId && !$regNumber) {
            return $this->error(422, 'NO_ID', 'Не указан car_id или reg_number');
        }

        $pdo = Database::getInstance()->getConnection();
        // Получаем авто
        if ($carId) {
            $stmt = $pdo->prepare('SELECT * FROM cars WHERE id = :car_id');
            $stmt->execute(['car_id' => $carId]);
            $car = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $stmt = $pdo->prepare('SELECT * FROM cars WHERE reg_number = :reg_number');
            $stmt->execute(['reg_number' => $regNumber]);
            $car = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        if (!$car) {
            return $this->error(404, 'NOT_FOUND', 'Автомобиль не найден');
        }

        // Получаем владельца
        $owner = null;
        if ($car['owner_user_id']) {
            $stmt = $pdo->prepare('SELECT id, username, first_name_tg, last_name_tg FROM users WHERE id = :id');
            $stmt->execute(['id' => $car['owner_user_id']]);
            $owner = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        // Получаем фото
        $stmt = $pdo->prepare('SELECT url FROM photos WHERE entity_type = "car" AND entity_id = :car_id ORDER BY id ASC');
        $stmt->execute(['car_id' => $car['id']]);
        $photos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $photo_urls = array_map(function($p) { return $p['url']; }, $photos);

        return $this->success([
            'car_id' => $car['id'],
            'reg_number' => $car['reg_number'],
            'model' => $car['model'],
            'color' => $car['color'],
            'year' => $car['year'],
            'status' => $car['status_id'],
            'owner_user_id' => $car['owner_user_id'],
            'owner_username' => $owner['username'] ?? null,
            'owner_name' => trim(($owner['first_name_tg'] ?? '') . ' ' . ($owner['last_name_tg'] ?? '')),
            'photos' => $photo_urls
        ], 'Информация об автомобиле получена');
    }
}

$endpoint = new ViewCarEndpoint();
$endpoint->handle(); 
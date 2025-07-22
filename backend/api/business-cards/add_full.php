<?php
/**
 * API Endpoint: Оркестратор — добавление машины и визитки (если машины нет)
 * POST /api/business-cards/add_full.php
 *
 * Вход: все поля для машины и визитки (reg_number, photo, location, notes, ...)
 * Требует роль: member+
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../utils/Database.php';
require_once __DIR__ . '/../../utils/ApiHandler.php';

class AddBusinessCardFullEndpoint extends ApiHandler {
    protected function process() {
        $this->checkAccess('member');
        $db = $this->getDb();

        $regNumber = $this->requireField('reg_number', 'Номер автомобиля обязателен');
        $photo = $this->requireField('photo', 'Фото обязательно');
        $location = $this->getData('location', '');
        $notes = $this->getData('notes', '');
        $userId = $this->getAuth('user_id');

        // 1. Проверяем, есть ли машина
        $carStmt = $db->prepare('SELECT id, reg_number FROM cars WHERE reg_number = ?');
        $carStmt->execute([$regNumber]);
        $car = $carStmt->fetch(PDO::FETCH_ASSOC);
        $carId = null;
        $carCreated = false;
        $carResult = null;

        if (!$car) {
            // 2. Машины нет — вызываем cars/add.php
            $carResult = $this->callInternalEndpoint('/backend/api/cars/add.php', [
                'auth' => $this->auth,
                'data' => [
                    'reg_number' => $regNumber,
                    'photo' => $photo,
                    'show_reg_number' => $this->getData('show_reg_number', true),
                    'status_code' => 'business_card',
                    // Можно добавить другие поля машины, если нужно
                ]
            ]);
            if (!$carResult['success'] || empty($carResult['result']['data']['car_id'])) {
                return $this->error('Ошибка при добавлении машины: ' . ($carResult['error']['message'] ?? 'Не удалось создать машину'), 400, 'CAR_ADD_ERROR');
            }
            $carId = $carResult['result']['data']['car_id'];
            $carCreated = true;
        } else {
            $carId = $car['id'];
        }

        // 3. Добавляем визитку к машине
        $cardResult = $this->callInternalEndpoint('/backend/api/business-cards/add_to_car.php', [
            'auth' => $this->auth,
            'data' => [
                'car_id' => $carId,
                'photo' => $photo,
                'location' => $location,
                'notes' => $notes
            ]
        ]);
        if (!$cardResult['success']) {
            return $this->error('Ошибка при добавлении визитки: ' . ($cardResult['error']['message'] ?? 'Не удалось создать визитку'), 400, 'CARD_ADD_ERROR');
        }

        return $this->success([
            'car_created' => $carCreated,
            'car_result' => $carResult,
            'business_card' => $cardResult['result']['data'] ?? null
        ], 'Машина и визитка успешно добавлены');
    }

    private function callInternalEndpoint($url, $payload) {
        // Логируем отправляемый payload
        file_put_contents(__DIR__ . '/../../logs/add_full_debug.log', "CALL: $url\nPAYLOAD: " . json_encode($payload) . "\n", FILE_APPEND);

        // Формируем полный HTTP-адрес
        $endpointUrl = 'http://localhost/app' . $url;
        $ch = curl_init($endpointUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        $response = curl_exec($ch);
        curl_close($ch);

        // Логируем ответ
        file_put_contents(__DIR__ . '/../../logs/add_full_debug.log', "RESPONSE: " . $response . "\n", FILE_APPEND);

        return json_decode($response, true);
    }
}

$endpoint = new AddBusinessCardFullEndpoint();
$endpoint->handle(); 
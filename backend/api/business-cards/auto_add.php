<?php
/**
 * API Endpoint: Оркестратор — автоматическое добавление визитки по фото
 * POST /api/business-cards/auto_add.php
 *
 * Вход: только фото (base64)
 * Требует роль: member+
 *
 * Логика:
 * 1. Получаем профиль пользователя по user_id (или telegram_id)
 * 2. Если профиль не найден — создаём пользователя через /api/users/add.php
 * 3. Если роль guest — возвращаем ошибку (гость не может создавать визитки)
 * 4. Распознаём номер через /api/ocr/recognize.php
 * 5. Если номер не распознан — возвращаем ошибку
 * 6. Если номер распознан — ищем авто
 * 7. Если авто нет — вызываем add_full.php (создаём авто и визитку)
 * 8. Если авто есть — вызываем add_to_car.php (добавляем визитку к существующему авто)
 * 9. Возвращаем единый результат
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../utils/Database.php';
require_once __DIR__ . '/../../utils/ApiHandler.php';

class AutoAddBusinessCardEndpoint extends ApiHandler {
    protected function process() {
        $this->checkAccess('guest'); // разрешаем guest для проверки профиля
        $db = $this->getDb();

        $photo = $this->requireField('photo', 'Фото обязательно');
        $userId = $this->getAuth('user_id');
        $role = $this->getAuth('role');
        $telegramId = $this->getAuth('telegram_id'); // если вдруг передан

        // 1. Получаем профиль пользователя
        $profileResp = $this->callInternalEndpoint('/backend/api/users/profile.php', [
            // Для первого запроса профиля используем системные права
            'auth' => ['user_id' => 1, 'role' => 'admin'],
            'data' => $telegramId ? ['telegram_id' => $telegramId] : ['user_id' => $userId]
        ]);
        if (!$profileResp['success']) {
            // 2. Если профиль не найден — создаём пользователя
            $addResp = $this->callInternalEndpoint('/backend/api/users/add.php', [
                'auth' => ['user_id' => 1, 'role' => 'admin'],
                'data' => $telegramId ? ['telegram_id' => $telegramId] : ['user_id' => $userId]
            ]);
            // 3. После создания — снова получаем профиль
            $profileResp = $this->callInternalEndpoint('/backend/api/users/profile.php', [
                'auth' => ['user_id' => 1, 'role' => 'admin'],
                'data' => $telegramId ? ['telegram_id' => $telegramId] : ['user_id' => $userId]
            ]);
            if (!$profileResp['success']) {
                return $this->error('Не удалось создать профиль пользователя', 400, 'PROFILE_ERROR');
            }
        }
        $user = $profileResp['result']['data']['user'] ?? null;
        if (!$user) {
            return $this->error('Профиль пользователя не найден', 400, 'PROFILE_ERROR');
        }
        if ($user['role'] === 'guest') {
            return $this->error('Для добавления визитки нужно завершить регистрацию или получить доступ от модератора.', 403, 'FORBIDDEN');
        }
        $userId = $user['id'];
        $role = $user['role'];

        // 4. Распознаём номер через OCR
        $ocrResult = $this->callInternalEndpoint('/backend/api/ocr/recognize.php', [
            'auth' => [
                'user_id' => $userId,
                'role' => $role
            ],
            'data' => [
                'image' => $photo
            ]
        ]);
        if (!$ocrResult || empty($ocrResult['success']) || empty($ocrResult['result']['data']['plate'])) {
            return $this->error('Не удалось распознать номер на фото. Попробуйте другое фото.', 400, 'OCR_ERROR');
        }
        $regNumber = strtoupper($ocrResult['result']['data']['plate']);

        // 5. Проверяем, есть ли машина
        $carStmt = $db->prepare('SELECT id FROM cars WHERE reg_number = ?');
        $carStmt->execute([$regNumber]);
        $car = $carStmt->fetch(PDO::FETCH_ASSOC);
        $carId = $car ? $car['id'] : null;
        $carCreated = false;
        $carResult = null;
        $cardResult = null;

        if (!$carId) {
            // 6. Машины нет — вызываем add_full.php (создаём авто и визитку)
            $carResult = $this->callInternalEndpoint('/backend/api/business-cards/add_full.php', [
                'auth' => ['user_id' => $userId, 'role' => $role],
                'data' => [
                    'reg_number' => $regNumber,
                    'photo' => $photo
                ]
            ]);
            if (!$carResult['success']) {
                return $this->error('Ошибка при создании авто и визитки: ' . ($carResult['error']['message'] ?? 'Не удалось создать'), 400, 'CAR_ADD_ERROR');
            }
            $carCreated = true;
            $cardResult = $carResult['result']['business_card'] ?? null;
        } else {
            // 7. Машина есть — вызываем add_to_car.php (добавляем визитку)
            $cardResp = $this->callInternalEndpoint('/backend/api/business-cards/add_to_car.php', [
                'auth' => ['user_id' => $userId, 'role' => $role],
                'data' => [
                    'car_id' => $carId,
                    'photo' => $photo
                ]
            ]);
            if (!$cardResp['success']) {
                return $this->error('Ошибка при добавлении визитки: ' . ($cardResp['error']['message'] ?? 'Не удалось создать визитку'), 400, 'CARD_ADD_ERROR');
            }
            $cardResult = $cardResp['result']['data'] ?? null;
        }

        return $this->success([
            'car_created' => $carCreated,
            'reg_number' => $regNumber,
            'business_card' => $cardResult
        ], 'Визитка успешно добавлена');
    }

    private function callInternalEndpoint($url, $payload) {
        // Логируем отправляемый payload
        file_put_contents(__DIR__ . '/../../logs/auto_add_debug.log', "CALL: $url\nPAYLOAD: " . json_encode($payload) . "\n", FILE_APPEND);

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
        file_put_contents(__DIR__ . '/../../logs/auto_add_debug.log', "RESPONSE: " . $response . "\n", FILE_APPEND);

        return json_decode($response, true);
    }
}

$endpoint = new AutoAddBusinessCardEndpoint();
$endpoint->handle(); 
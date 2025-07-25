<?php
/**
 * API Endpoint: Добавление визитки к существующей машине
 * POST /api/business-cards/add_to_car.php
 *
 * Вход: car_id (или reg_number), photo, location, notes
 * Требует роль: member+
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../utils/Database.php';
require_once __DIR__ . '/../../utils/ApiHandler.php';

class AddBusinessCardToCarEndpoint extends ApiHandler {
    protected function process() {
        // Получаем идентификатор пользователя из auth (стандарт CabrioRide)
        $userId = $this->getAuth('user_id');
        $this->checkAccess('member');
        $db = $this->getDb();

        // Получаем идентификатор машины
        $carId = $this->getData('car_id');
        $regNumber = $this->getData('reg_number');
        if (empty($carId) && empty($regNumber)) {
            return $this->error('Нужно указать car_id или reg_number', 400, 'VALIDATION_ERROR', [
                'field' => 'car_id/reg_number',
                'rule' => 'required'
            ]);
        }

        // Находим машину
        if (!empty($carId)) {
            $carStmt = $db->prepare('SELECT id, reg_number FROM cars WHERE id = ?');
            $carStmt->execute([$carId]);
        } else {
            $carStmt = $db->prepare('SELECT id, reg_number FROM cars WHERE reg_number = ?');
            $carStmt->execute([$regNumber]);
        }
        $car = $carStmt->fetch(PDO::FETCH_ASSOC);
        if (!$car) {
            return $this->error('Машина не найдена', 404, 'NOT_FOUND');
        }

        // Валидация фото
        $photo = $this->requireField('photo', 'Фото обязательно');
        $photoUrl = $this->savePhoto($photo, $userId); // Сохраняем фото и получаем url
        // Можно добавить validatePhoto($photo) при необходимости

        $location = $this->getData('location', '');
        $notes = $this->getData('notes', '');

        // Сохраняем визитку (без фото, только основные поля)
        $stmt = $db->prepare('INSERT INTO business_cards (car_id, inviter_user_id, location, notes, created_at) VALUES (?, ?, ?, ?, NOW())');
        $stmt->execute([
            $car['id'],
            $userId, // inviter_user_id — кто оставил визитку
            $location,
            $notes
        ]);
        $businessCardId = $db->lastInsertId();

        // Сохраняем фото в таблицу photos, привязывая к визитке
        $photoFileName = basename($photoUrl); // если нужно имя файла
        $stmtPhoto = $db->prepare('INSERT INTO photos (entity_type, entity_id, file_name, url, uploaded_by, uploaded_at) VALUES (?, ?, ?, ?, ?, NOW())');
        $stmtPhoto->execute([
            'business_card', // entity_type
            $businessCardId, // entity_id
            $photoFileName,  // file_name
            $photoUrl,       // url
            $userId          // uploaded_by
        ]);

        return $this->success([
            'business_card_id' => $businessCardId,
            'car_id' => $car['id'],
            'reg_number' => $car['reg_number']
        ], 'Визитка успешно добавлена к машине');
    }

    /**
     * Сохраняет фото визитки (base64) в папку uploads/business_cards/ и возвращает url
     */
    protected function savePhoto($base64Photo, $userId) {
        $uploadDir = __DIR__ . '/../../../uploads/business_cards/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        // Извлекаем данные из base64
        if (!preg_match('/^data:image\/(jpeg|jpg|png);base64,/', $base64Photo)) {
            $this->error('Неверный формат фото. Ожидается base64', 400, 'VALIDATION_ERROR', [
                'field' => 'photo',
                'rule' => 'base64_image'
            ]);
        }
        $base64Data = substr($base64Photo, strpos($base64Photo, ',') + 1);
        $imageData = base64_decode($base64Data);
        // Определяем расширение
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_buffer($finfo, $imageData);
        finfo_close($finfo);
        $extension = 'jpg';
        if ($mimeType === 'image/png') {
            $extension = 'png';
        }
        // Генерируем имя файла
        $filename = 'business_card_' . $userId . '_' . time() . '.' . $extension;
        $filepath = $uploadDir . $filename;
        // Сохраняем файл
        if (file_put_contents($filepath, $imageData)) {
            return '/uploads/business_cards/' . $filename;
        }
        $this->error('Ошибка сохранения фото', 500, 'FILE_SAVE_ERROR');
    }
}

$endpoint = new AddBusinessCardToCarEndpoint();
$endpoint->handle(); 
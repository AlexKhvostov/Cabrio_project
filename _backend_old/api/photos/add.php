<?php
/**
 * /api/photos/add.php
 * Универсальный эндпоинт для добавления фото к любой сущности (car, user, event и т.д.)
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../utils/Database.php';
require_once __DIR__ . '/../../utils/ApiHandler.php';

class AddPhotoEndpoint extends ApiHandler {
    protected function process() {
        $this->checkAccess('guest'); // было 'member', теперь 'guest' по бизнес-логике
        $pdo = Database::getInstance()->getConnection();
        $data = $this->data;
        $userId = $this->getAuth('user_id');

        $entityType = $data['entity_type'] ?? null;
        $entityId = $data['entity_id'] ?? null;
        $photo = $data['photo'] ?? null;
        $description = $data['description'] ?? null;

        if (!$entityType || !$entityId || !$photo) {
            return $this->error('entity_type, entity_id и photo обязательны', 422, 'VALIDATION_ERROR');
        }

        // Сохраняем фото (base64) в файл
        $fileName = null;
        if (preg_match('/^data:image\/(\w+);base64,/', $photo, $type)) {
            $photoData = substr($photo, strpos($photo, ',') + 1);
            $photoData = base64_decode($photoData);
            if ($photoData === false) {
                return $this->error('Ошибка декодирования base64', 400, 'PHOTO_DECODE_ERROR');
            }
            $ext = strtolower($type[1]);
            $fileName = $entityType . '_' . $entityId . '_' . time() . '_' . rand(1000,9999) . '.' . $ext;
            $uploadDir = __DIR__ . '/../../../uploads/' . $entityType . 's/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $filePath = $uploadDir . $fileName;
            file_put_contents($filePath, $photoData);
            $url = '/uploads/' . $entityType . 's/' . $fileName;
        } else {
            // Если это уже url
            $url = $photo;
            $fileName = basename($url);
        }

        // Добавляем запись в photos
        $stmt = $pdo->prepare('INSERT INTO photos (entity_type, entity_id, url, file_name, description, uploaded_by, uploaded_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
        $stmt->execute([$entityType, $entityId, $url, $fileName, $description, $userId]);
        $photoId = $pdo->lastInsertId();

        return $this->success([
            'photo_id' => $photoId,
            'url' => $url
        ], 'Фото добавлено');
    }
}

$endpoint = new AddPhotoEndpoint();
$endpoint->handle(); 
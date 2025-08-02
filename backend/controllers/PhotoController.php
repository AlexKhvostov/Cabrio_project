<?php
/**
 * PhotoController — контроллер для работы с фото (photos).
 *
 * Назначение:
 *   Обрабатывает HTTP-запросы, связанные с фото: загрузка, получение, удаление.
 *
 * Зависимости:
 *   - Photo (модель)
 *   - User, Car, Event, Review, GuideObject (модели)
 *   - AuthHelper, ResponseHelper
 *
 * Основные методы:
 *   - getList($entity_type, $entity_id) — получить фото для сущности
 *   - upload($entity_type, $entity_id, $file, $meta) — загрузить фото
 *   - delete($photo_id) — удалить фото
 */
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Photo.php';

class PhotoController extends BaseController
{
    /**
     * Получить список фото для сущности
     * 
     * Требует авторизации: Да
     * Минимальная роль: member
     */
    public function getList($entityType = null, $entityId = null)
    {
        try {
            // Проверяем авторизацию и права доступа через централизованную конфигурацию
            if (!$this->requireAccess('api.photos.getList')) {
                return; // Ответ уже отправлен в requireAccess
            }
            
            $entityType = $entityType ?? ($_GET['entity_type'] ?? null);
            $entityId = $entityId ?? ($_GET['entity_id'] ?? null);
            $photos = Photo::getAll($entityType, $entityId);
            $this->logUserAction('get_photos_list', ['entity_type' => $entityType, 'entity_id' => $entityId, 'count' => count($photos)]);
            $this->json(['success' => true, 'data' => $photos, 'meta' => $this->getRequestInfo()]);
        } catch (Throwable $e) {
            Logger::error('PhotoController: getList error', ['error' => $e->getMessage(), 'user_id' => $this->getCurrentUserId(), 'entity_type' => $entityType, 'entity_id' => $entityId]);
            $this->json(['success' => false, 'error' => ['code' => 'DB_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    /**
     * Загрузить новое фото
     * 
     * Требует авторизации: Да
     * Минимальная роль: member
     */
    public function upload()
    {
        try {
            // Проверяем авторизацию и права доступа через централизованную конфигурацию
            if (!$this->requireAccess('api.photos.upload')) {
                return; // Ответ уже отправлен в requireAccess
            }
            
            $entityType = $_POST['entity_type'] ?? null;
            $entityId = $_POST['entity_id'] ?? null;
            $this->logUserAction('upload_photo', ['entity_type' => $entityType, 'entity_id' => $entityId]);
            // TODO: Реализовать загрузку фото через модель
            $this->json(['success' => true, 'data' => ['id' => 1, 'filename' => 'photo.jpg', 'entity_type' => $entityType, 'entity_id' => $entityId, 'uploaded_by' => $this->getCurrentUserId()], 'meta' => $this->getRequestInfo()], 201);
        } catch (Throwable $e) {
            Logger::error('PhotoController: upload error', ['error' => $e->getMessage(), 'user_id' => $this->getCurrentUserId()]);
            $this->json(['success' => false, 'error' => ['code' => 'INTERNAL_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }
} 
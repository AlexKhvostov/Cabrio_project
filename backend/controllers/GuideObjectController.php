<?php
/**
 * GuideObjectController — контроллер для работы с гид-объектами (guide_objects).
 *
 * Назначение:
 *   Обрабатывает HTTP-запросы, связанные с гид-объектами: получение, создание, обновление, удаление и т.д.
 *
 * Зависимости:
 *   - GuideObject (модель)
 *   - GuideObjectKind (модель)
 *   - User (модель)
 *   - AuthHelper, ResponseHelper
 *
 * Основные методы:
 *   - getList() — получить список гид-объектов
 *   - getById($id) — получить гид-объект по id
 *   - create($data) — создать гид-объект
 *   - update($id, $data) — обновить гид-объект
 *   - delete($id) — удалить гид-объект
 */
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/GuideObject.php';

class GuideObjectController extends BaseController
{
    /**
     * Получить список гид-объектов
     * 
     * Требует авторизации: Да
     * Минимальная роль: member
     */
    public function getList()
    {
        try {
            // Проверяем авторизацию и права доступа через централизованную конфигурацию
            if (!$this->requireAccess('api.guide-objects.getList')) {
                return; // Ответ уже отправлен в requireAccess
            }

            $guideObjects = GuideObject::getAll();
            
            // Логируем действие
            $this->logUserAction('get_guide_objects_list', [
                'count' => count($guideObjects)
            ]);
            
            $this->json([
                'success' => true, 
                'data' => $guideObjects,
                'meta' => $this->getRequestInfo()
            ]);
            
        } catch (Throwable $e) {
            Logger::error('GuideObjectController: getList error', [
                'error' => $e->getMessage(),
                'user_id' => $this->getCurrentUserId()
            ]);
            
            $this->json([
                'success' => false,
                'error' => [
                    'code' => 'DB_ERROR',
                    'message' => $e->getMessage()
                ]
            ], 500);
        }
    }

    /**
     * Создать новый гид-объект
     * 
     * Требует авторизации: Да
     * Минимальная роль: moderator
     */
    public function create()
    {
        try {
            // Проверяем авторизацию и права доступа через централизованную конфигурацию
            if (!$this->requireAccess('api.guide-objects.create')) {
                return; // Ответ уже отправлен в requireAccess
            }

            // Получаем данные из запроса
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Логируем действие
            $this->logUserAction('create_guide_object', [
                'input_data' => $input
            ]);

            // TODO: Реализовать создание гид-объекта через модель
            $this->json([
                'success' => true, 
                'data' => [
                    'id' => 1, 
                    'title' => 'Новый гид-объект',
                    'created_by' => $this->getCurrentUserId()
                ],
                'meta' => $this->getRequestInfo()
            ], 201);
            
        } catch (Throwable $e) {
            Logger::error('GuideObjectController: create error', [
                'error' => $e->getMessage(),
                'user_id' => $this->getCurrentUserId()
            ]);
            
            $this->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => $e->getMessage()
                ]
            ], 500);
        }
    }
} 
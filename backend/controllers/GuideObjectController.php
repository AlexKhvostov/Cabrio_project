<?php
/**
 * GuideObjectController — контроллер для работы с гид-объектами (guide_objects).
 *
 * Назначение:
 *   Обрабатывает HTTP-запросы, связанные с гид-объектами: получение, создание, обновление, удаление, модерация и т.д.
 *
 * Зависимости:
 *   - GuideObject (модель)
 *   - GuideObjectType, GuideObjectKind (модели)
 *   - User (модель)
 *   - Status (модель)
 *   - AuthHelper, ResponseHelper
 *
 * Основные методы:
 *   - getList() — получить список гид-объектов
 *   - getById($id) — получить гид-объект по id
 *   - create($data) — добавить гид-объект
 *   - update($id, $data) — обновить гид-объект
 *   - delete($id) — удалить гид-объект
 *   - approve($id) — одобрить гид-объект (модерация)
 */
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/GuideObject.php';

class GuideObjectController extends BaseController
{
    /**
     * Получить список гид-объектов (реализация через модель GuideObject)
     */
    public function getList()
    {
        try {
            $guideObjects = GuideObject::getAll();
            $this->json(['success' => true, 'data' => $guideObjects]);
        } catch (Throwable $e) {
            $this->json([
                'success' => false,
                'error' => [
                    'code' => 'DB_ERROR',
                    'message' => $e->getMessage()
                ]
            ], 500);
        }
    }

    public function create()
    {
        // Пример: создать гид-объект (заглушка)
        $this->json(['success' => true, 'data' => ['id' => 1, 'name' => 'Новый гид-объект']], 201);
    }
} 
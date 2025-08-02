<?php
/**
 * EventController — контроллер для работы с событиями (events).
 *
 * Назначение:
 *   Обрабатывает HTTP-запросы, связанные с событиями: получение, создание, обновление, удаление, участие и т.д.
 *
 * Зависимости:
 *   - Event (модель)
 *   - EventType (модель)
 *   - User (модель)
 *   - AuthHelper, ResponseHelper
 *
 * Основные методы:
 *   - getList() — получить список событий
 *   - getById($id) — получить событие по id
 *   - create($data) — создать событие
 *   - update($id, $data) — обновить событие
 *   - delete($id) — удалить событие
 *   - join($event_id) — присоединиться к событию
 */
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Event.php';

class EventController extends BaseController
{
    /**
     * Получить список событий
     * 
     * Требует авторизации: Да
     * Минимальная роль: member
     */
    public function getList()
    {
        try {
            // Проверяем авторизацию и права доступа через централизованную конфигурацию
            if (!$this->requireAccess('api.events.getList')) {
                return; // Ответ уже отправлен в requireAccess
            }

            $events = Event::getAll();
            
            // Логируем действие
            $this->logUserAction('get_events_list', [
                'count' => count($events)
            ]);
            
            $this->json([
                'success' => true, 
                'data' => $events,
                'meta' => $this->getRequestInfo()
            ]);
            
        } catch (Throwable $e) {
            Logger::error('EventController: getList error', [
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
     * Создать новое событие
     * 
     * Требует авторизации: Да
     * Минимальная роль: moderator
     */
    public function create()
    {
        try {
            // Проверяем авторизацию и права доступа через централизованную конфигурацию
            if (!$this->requireAccess('api.events.create')) {
                return; // Ответ уже отправлен в requireAccess
            }

            // Получаем данные из запроса
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Логируем действие
            $this->logUserAction('create_event', [
                'input_data' => $input
            ]);

            // TODO: Реализовать создание события через модель
            $this->json([
                'success' => true, 
                'data' => [
                    'id' => 1, 
                    'title' => 'Новое событие',
                    'created_by' => $this->getCurrentUserId()
                ],
                'meta' => $this->getRequestInfo()
            ], 201);
            
        } catch (Throwable $e) {
            Logger::error('EventController: create error', [
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
<?php
/**
 * EventController — контроллер для работы с событиями (events).
 *
 * Назначение:
 *   Обрабатывает HTTP-запросы, связанные с событиями: получение, создание, обновление, удаление, регистрация участников и т.д.
 *
 * Зависимости:
 *   - Event (модель)
 *   - EventType (модель)
 *   - User (модель)
 *   - LinkEventParticipant (модель)
 *   - AuthHelper, ResponseHelper
 *
 * Основные методы:
 *   - getList() — получить список событий
 *   - getById($id) — получить событие по id
 *   - create($data) — создать событие
 *   - update($id, $data) — обновить событие
 *   - delete($id) — удалить событие
 *   - join($event_id, $user_id) — зарегистрировать пользователя
 *   - leave($event_id, $user_id) — выйти из события
 */
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Event.php';

class EventController extends BaseController
{
    /**
     * Получить список событий (реализация через модель Event)
     */
    public function getList()
    {
        try {
            $events = Event::getAll();
            $this->json(['success' => true, 'data' => $events]);
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
        // Пример: создать событие (заглушка)
        $this->json(['success' => true, 'data' => ['id' => 1, 'title' => 'Новое событие']], 201);
    }
} 
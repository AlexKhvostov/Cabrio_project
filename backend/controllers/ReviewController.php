<?php
/**
 * ReviewController — контроллер для работы с отзывами (reviews).
 *
 * Назначение:
 *   Обрабатывает HTTP-запросы, связанные с отзывами: получение, создание, обновление, удаление, модерация и т.д.
 *
 * Зависимости:
 *   - Review (модель)
 *   - GuideObject (модель)
 *   - User (модель)
 *   - Status (модель)
 *   - AuthHelper, ResponseHelper
 *
 * Основные методы:
 *   - getList() — получить список отзывов
 *   - getById($id) — получить отзыв по id
 *   - create($data) — добавить отзыв
 *   - update($id, $data) — обновить отзыв
 *   - delete($id) — удалить отзыв
 *   - approve($id) — одобрить отзыв (модерация)
 */
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Review.php';

class ReviewController extends BaseController
{
    /**
     * Получить список отзывов
     * 
     * Требует авторизации: Да
     * Минимальная роль: member
     */
    public function getList()
    {
        try {
            // Проверяем авторизацию и права доступа через централизованную конфигурацию
            if (!$this->requireAccess('api.reviews.getList')) {
                return; // Ответ уже отправлен в requireAccess
            }
            
            $reviews = Review::getAll();
            $this->logUserAction('get_reviews_list', ['count' => count($reviews)]);
            $this->json(['success' => true, 'data' => $reviews, 'meta' => $this->getRequestInfo()]);
        } catch (Throwable $e) {
            Logger::error('ReviewController: getList error', ['error' => $e->getMessage(), 'user_id' => $this->getCurrentUserId()]);
            $this->json(['success' => false, 'error' => ['code' => 'DB_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    /**
     * Создать новый отзыв
     * 
     * Требует авторизации: Да
     * Минимальная роль: member
     */
    public function create()
    {
        try {
            // Проверяем авторизацию и права доступа через централизованную конфигурацию
            if (!$this->requireAccess('api.reviews.create')) {
                return; // Ответ уже отправлен в requireAccess
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            $this->logUserAction('create_review', ['input_data' => $input]);
            // TODO: Реализовать создание отзыва через модель
            $this->json(['success' => true, 'data' => ['id' => 1, 'title' => 'Новый отзыв', 'created_by' => $this->getCurrentUserId()], 'meta' => $this->getRequestInfo()], 201);
        } catch (Throwable $e) {
            Logger::error('ReviewController: create error', ['error' => $e->getMessage(), 'user_id' => $this->getCurrentUserId()]);
            $this->json(['success' => false, 'error' => ['code' => 'INTERNAL_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }
} 
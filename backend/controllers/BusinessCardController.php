<?php
/**
 * BusinessCardController — контроллер для работы с визитками (business_cards).
 *
 * Назначение:
 *   Обрабатывает HTTP-запросы, связанные с визитками: получение, создание, удаление.
 *
 * Зависимости:
 *   - BusinessCard (модель)
 *   - Car (модель)
 *   - User (модель)
 *   - AuthHelper, ResponseHelper
 *
 * Основные методы:
 *   - getList() — получить список визиток
 *   - getById($id) — получить визитку по id
 *   - create($data) — добавить визитку
 *   - delete($id) — удалить визитку
 */
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/BusinessCard.php';

class BusinessCardController extends BaseController
{
    public function getList()
    {
        try {
            // Проверяем авторизацию и права доступа через централизованную конфигурацию
            if (!$this->requireAccess('api.businessCards.getList')) {
                return; // Ответ уже отправлен в requireAccess
            }
            
            $businessCards = BusinessCard::getAll();
            $this->logUserAction('get_business_cards_list', ['count' => count($businessCards)]);
            $this->json(['success' => true, 'data' => $businessCards, 'meta' => $this->getRequestInfo()]);
        } catch (Throwable $e) {
            Logger::error('BusinessCardController: getList error', ['error' => $e->getMessage(), 'user_id' => $this->getCurrentUserId()]);
            $this->json(['success' => false, 'error' => ['code' => 'DB_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }

    public function create()
    {
        try {
            // Проверяем авторизацию и права доступа через централизованную конфигурацию
            if (!$this->requireAccess('api.businessCards.create')) {
                return; // Ответ уже отправлен в requireAccess
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            $this->logUserAction('create_business_card', ['input_data' => $input]);
            // TODO: Реализовать создание визитки через модель
            $this->json(['success' => true, 'data' => ['id' => 1, 'title' => 'Новая визитка', 'created_by' => $this->getCurrentUserId()], 'meta' => $this->getRequestInfo()], 201);
        } catch (Throwable $e) {
            Logger::error('BusinessCardController: create error', ['error' => $e->getMessage(), 'user_id' => $this->getCurrentUserId()]);
            $this->json(['success' => false, 'error' => ['code' => 'INTERNAL_ERROR', 'message' => $e->getMessage()]], 500);
        }
    }
} 
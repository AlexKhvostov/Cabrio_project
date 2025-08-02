<?php
/**
 * UserController — контроллер для работы с пользователями (users).
 *
 * Назначение:
 *   Обрабатывает HTTP-запросы, связанные с пользователями: получение, создание, обновление, удаление, авторизация и т.д.
 *
 * Зависимости:
 *   - User (модель)
 *   - Role (модель)
 *   - AuthHelper (утилита для авторизации)
 *   - ResponseHelper (утилита для формирования ответов)
 *
 * Основные методы:
 *   - getList() — получить список пользователей
 *   - getById($id) — получить пользователя по id
 *   - create($data) — создать пользователя
 *   - update($id, $data) — обновить пользователя
 *   - delete($id) — удалить пользователя
 *   - ... (дополнительные методы: смена роли, поиск по telegram_id и т.д.)
 */
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/User.php';

class UserController extends BaseController
{
    /**
     * Получить список пользователей
     * 
     * Требует авторизации: Да
     * Минимальная роль: member
     */
    public function getList()
    {
        try {
            // Проверяем авторизацию и права доступа через централизованную конфигурацию
            if (!$this->requireAccess('api.users.getList')) {
                return; // Ответ уже отправлен в requireAccess
            }

            $users = User::getAll();
            
            // Логируем действие
            $this->logUserAction('get_users_list', [
                'count' => count($users)
            ]);
            
            $this->json([
                'success' => true, 
                'data' => $users,
                'meta' => $this->getRequestInfo()
            ]);
            
        } catch (Throwable $e) {
            Logger::error('UserController: getList error', [
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
     * Создать нового пользователя
     * 
     * Требует авторизации: Да
     * Минимальная роль: admin
     */
    public function create()
    {
        try {
            // Проверяем авторизацию и права доступа через централизованную конфигурацию
            if (!$this->requireAccess('api.users.create')) {
                return; // Ответ уже отправлен в requireAccess
            }

            // Получаем данные из запроса
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Логируем действие
            $this->logUserAction('create_user', [
                'input_data' => $input
            ]);

            // TODO: Реализовать создание пользователя через модель
            $this->json([
                'success' => true, 
                'data' => [
                    'id' => 3, 
                    'name' => 'Новый пользователь',
                    'created_by' => $this->getCurrentUserId()
                ],
                'meta' => $this->getRequestInfo()
            ], 201);
            
        } catch (Throwable $e) {
            Logger::error('UserController: create error', [
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

    /**
     * Получить профиль текущего пользователя
     * 
     * Требует авторизации: Да
     * Минимальная роль: guest
     */
    public function getProfile()
    {
        try {
            // Проверяем авторизацию и права доступа через централизованную конфигурацию
            if (!$this->requireAccess('api.users.getProfile')) {
                return; // Ответ уже отправлен в requireAccess
            }
            
            // Получаем текущего пользователя
            $user = $this->requireUser();
            
            // Логируем действие
            $this->logUserAction('get_profile');
            
            $this->json([
                'success' => true,
                'data' => $user,
                'meta' => $this->getRequestInfo()
            ]);
            
        } catch (Exception $e) {
            $this->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => $e->getMessage()
                ]
            ], 401);
        } catch (Throwable $e) {
            Logger::error('UserController: getProfile error', [
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
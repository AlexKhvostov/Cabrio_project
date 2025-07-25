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
     * Получить список пользователей (реализация через модель User)
     */
    public function getList()
    {
        try {
            $users = User::getAll();
            $this->json(['success' => true, 'data' => $users]);
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
        // Пример: создать пользователя (заглушка)
        $this->json(['success' => true, 'data' => ['id' => 3, 'name' => 'Новый пользователь']], 201);
    }
} 
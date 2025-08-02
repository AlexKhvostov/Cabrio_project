<?php
/**
 * 🔧 BaseController — базовый класс для всех контроллеров CabrioRide
 * 
 * Назначение: Общие методы для всех контроллеров
 * Использование: Наследуется всеми контроллерами
 * 
 * Основные возможности:
 * - Формирование JSON ответов
 * - Работа с глобальным контекстом (AppContext)
 * - Получение текущего пользователя
 * - Проверка авторизации
 * - Логирование
 */

require_once __DIR__ . '/../utils/AppContext.php';
require_once __DIR__ . '/../utils/Logger.php';
require_once __DIR__ . '/../../config/sectionGroups.php';

class BaseController
{
    /**
     * Быстро вернуть JSON-ответ с нужным статусом
     * 
     * @param array $data Данные для ответа
     * @param int $status HTTP статус код
     * @return void
     */
    protected function json($data, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

        /**
     * Получить текущего пользователя из глобального контекста
     *
     * @return array|null Данные пользователя или null
     */
    public function getCurrentUser()
    {
        return AppContext::getCurrentUser();
    }

        /**
     * Получить текущего пользователя с проверкой существования
     *
     * @return array Данные пользователя
     * @throws Exception Если пользователь не найден
     */
    public function requireUser()
    {
        $user = AppContext::getCurrentUser();
        
        if (!$user) {
            Logger::warning('BaseController: User not found in context');
            throw new Exception('Пользователь не найден в контексте');
        }
        
        return $user;
    }

        /**
     * Проверить, авторизован ли пользователь
     *
     * @return bool
     */
    public function isAuthenticated()
    {
        return AppContext::hasCurrentUser();
    }

        /**
     * Получить ID текущего пользователя
     *
     * @return int|null ID пользователя или null
     */
    public function getCurrentUserId()
    {
        $user = AppContext::getCurrentUser();
        return $user ? $user['id'] : null;
    }

        /**
     * Получить роль текущего пользователя
     *
     * @return string|null Роль пользователя или null
     */
    public function getCurrentUserRole()
    {
        $user = AppContext::getCurrentUser();
        return $user ? $user['role'] : null;
    }

        /**
     * Проверить, имеет ли пользователь указанную роль
     *
     * @param string $role Роль для проверки
     * @return bool
     */
    public function hasRole($role)
    {
        $userRole = $this->getCurrentUserRole();
        return $userRole === $role;
    }

            /**
     * Проверить, является ли пользователь администратором
     *
     * @return bool
     */
    public function isAdmin()
    {
        $userRole = $this->getCurrentUserRole();
        return $userRole === 6; // admin = 6
    }

    /**
     * Проверить, является ли пользователь модератором
     *
     * @return bool
     */
    public function isModerator()
    {
        $userRole = $this->getCurrentUserRole();
        return $userRole === 5 || $userRole === 6; // moderator = 5, admin = 6
    }

    /**
     * Проверить, является ли пользователь участником или выше
     *
     * @return bool
     */
    public function isMember()
    {
        $userRole = $this->getCurrentUserRole();
        return $userRole === 4 || $userRole === 5 || $userRole === 6; // member = 4, moderator = 5, admin = 6
    }

        /**
     * Получить информацию о запросе из контекста
     *
     * @return array Информация о запросе
     */
    public function getRequestInfo()
    {
        return [
            'request_id' => AppContext::getRequestId(),
            'session_id' => AppContext::getSessionId(),
            'execution_time' => AppContext::getExecutionTime(),
            'user_id' => $this->getCurrentUserId()
        ];
    }

    /**
     * Логировать действие пользователя
     * 
     * @param string $action Действие
     * @param array $data Дополнительные данные
     * @return void
     */
    protected function logUserAction($action, $data = [])
    {
        $user = $this->getCurrentUser();
        $userId = $user ? $user['id'] : 'unknown';
        
        Logger::info("User action: {$action}", array_merge([
            'user_id' => $userId,
            'request_id' => AppContext::getRequestId()
        ], $data));
    }
    
    /**
     * Проверить доступ к функции через централизованную конфигурацию
     * 
     * @param string $function Имя функции (например, 'api.users.getList')
     * @return bool
     */
    protected function checkAccess($function)
    {
        return AccessUtils::checkApiAccess($function);
    }
    
    /**
     * Проверить доступ и вернуть ошибку если доступ запрещен
     * 
     * @param string $function Имя функции
     * @return bool true если доступ разрешен, false если запрещен (ответ уже отправлен)
     */
    protected function requireAccess($function)
    {
        if (!$this->checkAccess($function)) {
            $requiredRole = AccessUtils::getRequiredRoleForApi($function);
            $userRole = $this->getCurrentUserRole();
            $userRoleCode = Roles::getRoleByCode($userRole ?? 2);
            
            Logger::warning("Access denied", [
                'function' => $function,
                'user_role' => $userRoleCode,
                'required_role' => $requiredRole,
                'user_id' => $this->getCurrentUserId()
            ]);
            
            $this->json([
                'success' => false,
                'error' => [
                    'code' => 'FORBIDDEN',
                    'message' => "Недостаточно прав для выполнения действия. Требуется роль: {$requiredRole}"
                ]
            ], 403);
            
            return false;
        }
        
        return true;
    }
    
    /**
     * Проверить доступ по числовому ID роли (для работы с БД)
     * 
     * @param int $userRoleId ID роли пользователя
     * @param string $function Имя функции
     * @return bool
     */
    protected function checkAccessById($userRoleId, $function)
    {
        return AccessUtils::checkAccessById($userRoleId, $function);
    }
    
    /**
     * Получить строковый код роли по числовому ID
     * 
     * @param int $roleId ID роли
     * @return string Код роли
     */
    protected function getRoleCode($roleId)
    {
        return Roles::getRoleByCode($roleId);
    }
    
    /**
     * Получить числовой ID роли по строковому коду
     * 
     * @param string $roleCode Код роли
     * @return int ID роли
     */
    protected function getRoleId($roleCode)
    {
        return Roles::getRoleId($roleCode);
    }
} 
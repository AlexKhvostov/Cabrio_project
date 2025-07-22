<?php
// Глобальный обработчик исключений для возврата JSON даже при фатальных ошибках
set_exception_handler(function($e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 500,
            'type' => 'FATAL_ERROR',
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]
    ]);
    exit;
});

/**
 * API Endpoint: Смена роли пользователя
 * POST /api/users/set_role.php
 *
 * Запрос:
 * {
 *   "auth": { "user_id": 1, "role": "moderator" },
 *   "data": { "user_id": 2, "new_role_code": "member", "reason": "Тестовая смена роли" }
 * }
 *
 * Ответ:
 * {
 *   "success": true,
 *   "result": {
 *     "message": "Роль пользователя успешно изменена",
 *     "data": { "user_id": 2, "new_role": "member" }
 *   }
 * }
 */

// Отключаем вывод ошибок для чистого JSON
error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../utils/Database.php';
require_once __DIR__ . '/../../utils/ApiHandler.php';
require_once __DIR__ . '/../../../config/sectionGroups.php';

class SetUserRoleEndpoint extends ApiHandler {
    protected function process() {
        // Проверяем права доступа (минимум moderator)
        $accessResult = $this->checkAccess('moderator');
        if ($accessResult !== true) {
            return $accessResult;
        }

        // Получаем и валидируем данные
        $userId = $this->requireField('user_id', 'user_id обязателен');
        $newRoleCode = $this->requireField('new_role_code', 'new_role_code обязателен');
        $reason = $this->requireField('reason', 'reason обязателен для аудита');

        try {
            $db = $this->getDb();

            // Получаем пользователя
            $stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$user) {
                return $this->error('Пользователь не найден', 404, 'NOT_FOUND');
            }

            // Получаем id роли по коду
            $roleStmt = $db->prepare('SELECT id FROM ref_roles WHERE code = ?');
            $roleStmt->execute([$newRoleCode]);
            $role = $roleStmt->fetch(PDO::FETCH_ASSOC);
            if (!$role) {
                return $this->error('Роль не найдена', 400, 'VALIDATION_ERROR', ['field' => 'new_role_code']);
            }
            $newRoleId = $role['id'];

            // Модератор не может назначать admin/moderator
            $initiatorRole = $this->getAuth('role');
            if ($initiatorRole === 'moderator' && in_array($newRoleCode, ['admin', 'moderator'])) {
                return $this->error('Модератор не может назначать admin/moderator', 403, 'ACCESS_DENIED');
            }

            // Обновляем роль пользователя
            $update = $db->prepare('UPDATE users SET role_id = ? WHERE id = ?');
            $update->execute([$newRoleId, $userId]);

            // Логируем действие
            $log = $db->prepare('INSERT INTO moderation_logs (user_id, moderator_id, action, reason, created_at) VALUES (?, ?, ?, ?, NOW())');
            $log->execute([
                $userId,
                $this->getAuth('user_id'),
                'set_role_' . $newRoleCode,
                $reason
            ]);

            return $this->success([
                'user_id' => $userId,
                'new_role' => $newRoleCode
            ], 'Роль пользователя успешно изменена');
        } catch (Exception $e) {
            return $this->error('Ошибка смены роли: ' . $e->getMessage(), 500, 'DATABASE_ERROR');
        }
    }
}

$endpoint = new SetUserRoleEndpoint();
$endpoint->handle(); 
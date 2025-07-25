<?php
require_once __DIR__ . '/load_env.php';

/**
 * AuthHelper — утилита для авторизации и проверки токенов в backend CabrioRide.
 * Используется во всех контроллерах и actions для централизованной проверки прав.
 *
 * Методы:
 * - checkAuth() — проверяет наличие и валидность токена, возвращает user_id или 'system'
 * - getUserFromToken() — возвращает объект пользователя по токену
 * - requireRole($role) — проверяет, что у пользователя есть нужная роль
 */
class AuthHelper {
    /**
     * Проверяет наличие и валидность токена в заголовке Authorization.
     * Возвращает user_id (int) или 'system' (string) для системных запросов.
     * В случае ошибки завершает выполнение с ошибкой авторизации.
     */
    public static function checkAuth() {
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $authHeader = $headers['Authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (empty($authHeader)) {
            http_response_code(401);
            echo ResponseHelper::error('NO_TOKEN', 'Требуется авторизация');
            exit;
        }
        if (strpos($authHeader, 'Bearer ') !== 0) {
            http_response_code(401);
            echo ResponseHelper::error('INVALID_TOKEN', 'Некорректный формат токена');
            exit;
        }
        $token = trim(substr($authHeader, 7));

        // Проверка на SYSTEM_TOKEN
        if ($token === getenv('SYSTEM_TOKEN')) {
            return 'system'; // спец. идентификатор для системных запросов
        }

        // JWT-подпись
        $jwtSecret = getenv('JWT_SECRET');
        $payload = self::decodeJWT($token, $jwtSecret);
        if (!$payload || empty($payload['user_id'])) {
            http_response_code(401);
            echo ResponseHelper::error('INVALID_TOKEN', 'Токен недействителен');
            exit;
        }
        // Можно добавить проверку срока действия (exp)
        return $payload['user_id'];
    }

    /**
     * Декодирует и проверяет подпись JWT (без сторонних библиотек).
     * Возвращает payload (array) или false.
     */
    public static function decodeJWT($jwt, $secret) {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) return false;
        $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
        // Проверка подписи (упрощённо)
        $signature = hash_hmac('sha256', $parts[0] . '.' . $parts[1], $secret, true);
        $signature_b64 = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
        if ($signature_b64 !== $parts[2]) return false;
        return $payload;
    }

    /**
     * Возвращает объект пользователя по токену (или null).
     * (Заготовка — требует доработки под вашу модель User)
     */
    public static function getUserFromToken() {
        $userId = self::checkAuth();
        if ($userId === 'system') return null;
        // TODO: реализовать User::findById($userId)
        return null;
    }

    /**
     * Проверяет, что у пользователя есть нужная роль (по коду роли).
     * Если нет — возвращает ошибку доступа.
     * (Заготовка — требует доработки под вашу модель User)
     */
    public static function requireRole($role) {
        $user = self::getUserFromToken();
        if (!$user || empty($user['role']) || $user['role']['code'] !== $role) {
            http_response_code(403);
            echo ResponseHelper::error('FORBIDDEN', 'Недостаточно прав');
            exit;
        }
        return true;
    }
} 
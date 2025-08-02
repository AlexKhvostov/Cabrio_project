<?php

require_once __DIR__ . '/../utils/AuthHelper.php';
require_once __DIR__ . '/../utils/AppContext.php';
require_once __DIR__ . '/../utils/SessionHelper.php';
require_once __DIR__ . '/../utils/Logger.php';
require_once __DIR__ . '/../actions/level2/__SyncUserDataAction.php';

/**
 * 🔐 Middleware для авторизации
 * 
 * Централизованная обработка авторизации для всех запросов:
 * - Извлечение Telegram данных
 * - Валидация данных
 * - Синхронизация пользователя
 * - Создание/обновление сессии
 * - Установка глобального контекста
 * 
 * @package CabrioRide\Middleware
 */
class AuthMiddleware
{
    /** @var string Префикс для ID запроса */
    const REQUEST_ID_PREFIX = 'req_';

    // ========================================
    // ОСНОВНОЙ МЕТОД ОБРАБОТКИ
    // ========================================

    /**
     * Обработать авторизацию для текущего запроса
     * 
     * @return array Результат обработки
     */
    public static function process()
    {
        try {
            Logger::info('AuthMiddleware: Starting authentication process');
            
            // 1. Извлекаем Telegram данные
            $telegramData = AuthHelper::extractTelegramData();
            
            if (!$telegramData) {
                Logger::warning('AuthMiddleware: No Telegram data found');
                return [
                    'success' => false,
                    'error' => [
                        'code' => 'NO_TELEGRAM_DATA',
                        'message' => 'Данные Telegram не найдены'
                    ]
                ];
            }
            
            Logger::info('AuthMiddleware: Telegram data extracted', [
                'telegram_id' => $telegramData['telegram_id'] ?? 'unknown'
            ]);
            
            // 2. Валидируем Telegram данные
            $validationResult = AuthHelper::validateTelegramData($telegramData);
            
            if (!$validationResult['success']) {
                Logger::warning('AuthMiddleware: Telegram data validation failed', [
                    'error' => $validationResult['error']['message']
                ]);
                
                return [
                    'success' => false,
                    'error' => [
                        'code' => 'TELEGRAM_VALIDATION_ERROR',
                        'message' => 'Ошибка валидации данных Telegram: ' . $validationResult['error']['message']
                    ]
                ];
            }
            
            Logger::info('AuthMiddleware: Telegram data validated');
            
            // 3. Синхронизируем пользователя
            $userResult = __SyncUserDataAction::handle($telegramData);
            
            if (!$userResult['success']) {
                Logger::error('AuthMiddleware: User sync failed', [
                    'error' => $userResult['error']['message']
                ]);
                
                return [
                    'success' => false,
                    'error' => [
                        'code' => 'USER_SYNC_ERROR',
                        'message' => 'Ошибка синхронизации пользователя: ' . $userResult['error']['message']
                    ]
                ];
            }
            
            $user = $userResult['data'];
            Logger::info('AuthMiddleware: User synchronized', [
                'user_id' => $user['id'],
                'telegram_id' => $user['telegram_id']
            ]);
            
            // 4. Создаем или обновляем сессию
            $sessionResult = SessionHelper::createOrUpdateSession($user['id']);
            
            if (!$sessionResult['success']) {
                Logger::error('AuthMiddleware: Session creation failed', [
                    'user_id' => $user['id'],
                    'error' => $sessionResult['error']['message']
                ]);
                
                return [
                    'success' => false,
                    'error' => [
                        'code' => 'SESSION_CREATION_ERROR',
                        'message' => 'Ошибка создания сессии: ' . $sessionResult['error']['message']
                    ]
                ];
            }
            
            Logger::info('AuthMiddleware: Session created/updated', [
                'user_id' => $user['id'],
                'session_id' => substr($sessionResult['session_id'], 0, 8) . '...',
                'action' => $sessionResult['action']
            ]);
            
            // 5. Устанавливаем глобальный контекст
            self::setupGlobalContext($telegramData, $user, $sessionResult);
            
            Logger::info('AuthMiddleware: Global context setup complete', [
                'user_id' => $user['id'],
                'request_id' => AppContext::getRequestId()
            ]);
            
            return [
                'success' => true,
                'user_id' => $user['id'],
                'session_id' => $sessionResult['session_id'],
                'message' => 'Авторизация успешна'
            ];
            
        } catch (Exception $e) {
            Logger::error('AuthMiddleware: Unexpected error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'error' => [
                    'code' => 'AUTH_ERROR',
                    'message' => 'Ошибка авторизации: ' . $e->getMessage()
                ]
            ];
        }
    }

    // ========================================
    // ВСПОМОГАТЕЛЬНЫЕ МЕТОДЫ
    // ========================================

    /**
     * Настроить глобальный контекст
     * 
     * @param array $telegramData Данные из Telegram
     * @param array $user Данные пользователя
     * @param array $sessionResult Результат создания сессии
     * @return void
     */
    private static function setupGlobalContext($telegramData, $user, $sessionResult)
    {
        // Устанавливаем Telegram данные
        AppContext::setTelegramData($telegramData);
        
        // Устанавливаем пользователя
        AppContext::setCurrentUser($user);
        
        // Устанавливаем ID сессии
        AppContext::setSessionId($sessionResult['session_id']);
        
        // Генерируем уникальный ID запроса
        $requestId = self::generateRequestId();
        AppContext::setRequestId($requestId);
        
        // Устанавливаем время начала запроса
        AppContext::setStartTime(microtime(true));
        
        Logger::info('Global context setup', [
            'user_id' => $user['id'],
            'session_id' => substr($sessionResult['session_id'], 0, 8) . '...',
            'request_id' => $requestId
        ]);
    }

    /**
     * Сгенерировать уникальный ID запроса
     * 
     * @return string
     */
    private static function generateRequestId()
    {
        $timestamp = date('Ymd_His');
        $microtime = substr(microtime(), 2, 6);
        $random = substr(bin2hex(random_bytes(4)), 0, 8);
        
        return self::REQUEST_ID_PREFIX . $timestamp . '_' . $microtime . '_' . $random;
    }

    // ========================================
    // МЕТОДЫ ДЛЯ СПЕЦИАЛЬНЫХ СЛУЧАЕВ
    // ========================================

    /**
     * Обработать запрос без авторизации (для публичных эндпоинтов)
     * 
     * @return array Результат обработки
     */
    public static function processPublic()
    {
        try {
            Logger::info('AuthMiddleware: Processing public request');
            
            // Устанавливаем только базовый контекст
            $requestId = self::generateRequestId();
            AppContext::setRequestId($requestId);
            AppContext::setStartTime(microtime(true));
            
            Logger::info('AuthMiddleware: Public request processed', [
                'request_id' => $requestId
            ]);
            
            return [
                'success' => true,
                'message' => 'Публичный запрос обработан'
            ];
            
        } catch (Exception $e) {
            Logger::error('AuthMiddleware: Public request error', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => [
                    'code' => 'PUBLIC_AUTH_ERROR',
                    'message' => 'Ошибка обработки публичного запроса: ' . $e->getMessage()
                ]
            ];
        }
    }

    /**
     * Проверить, требуется ли авторизация для эндпоинта
     * 
     * @param string $route Маршрут
     * @param string $method HTTP метод
     * @return bool
     */
    public static function requiresAuth($route, $method)
    {
        // Список публичных эндпоинтов (не требующих авторизации)
        $publicEndpoints = [
            ['route' => '/api/health', 'method' => 'GET'],
            ['route' => '/api/status', 'method' => 'GET'],
            ['route' => '/api/telegram/webhook', 'method' => 'POST'],
            ['route' => '/api/bot/webhook', 'method' => 'POST']
        ];
        
        foreach ($publicEndpoints as $endpoint) {
            if ($endpoint['route'] === $route && $endpoint['method'] === $method) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Получить информацию о текущем состоянии авторизации
     * 
     * @return array
     */
    public static function getAuthInfo()
    {
        return [
            'has_user' => AppContext::hasCurrentUser(),
            'has_session' => AppContext::hasSession(),
            'has_telegram_data' => AppContext::hasTelegramData(),
            'user_id' => AppContext::getCurrentUser() ? AppContext::getCurrentUser()['id'] : null,
            'session_id' => AppContext::getSessionId() ? substr(AppContext::getSessionId(), 0, 8) . '...' : null,
            'request_id' => AppContext::getRequestId(),
            'execution_time' => AppContext::getExecutionTime()
        ];
    }

    /**
     * Очистить контекст авторизации
     * 
     * @return void
     */
    public static function clearAuth()
    {
        AppContext::clear();
        Logger::info('AuthMiddleware: Auth context cleared');
    }
} 
<?php
/**
 * UserJoinedHandler.php
 * 
 * Обработчик события входа пользователя в клуб
 * Приветствует нового участника и синхронизирует данные в БД
 */

require_once __DIR__ . '/../../utils/Logger.php';

class UserJoinedHandler {
    /** @var BotService */
    private $botService;
    
    /**
     * Конструктор
     */
    public function __construct($botService) {
        $this->botService = $botService;
    }
    
    /**
     * Обрабатывает событие входа пользователя в клуб
     * 
     * @param array $chatMemberUpdate Данные об обновлении участника чата
     */
    public function handle($chatMemberUpdate) {
        try {
            $chat = $chatMemberUpdate['chat'];
            $newMember = $chatMemberUpdate['new_chat_member'];
            $user = $newMember['user'];
            
            writeToLog("UserJoinedHandler: Processing user joined event", [
                'chat_id' => $chat['id'],
                'user_id' => $user['id'],
                'username' => $user['username'] ?? 'unknown',
                'first_name' => $user['first_name'] ?? 'unknown'
            ]);
            
            // Проверяем, что это клубный чат
            $club_chat_id = $_ENV['CLUB_CHAT_ID'] ?? '-1002873258290';
            if ($chat['id'] != $club_chat_id) {
                writeToLog("UserJoinedHandler: Not club chat, ignoring");
                return;
            }
            
            // Проверяем, что это действительно новый участник (не бот)
            if ($newMember['status'] !== 'member' || $user['is_bot'] ?? false) {
                writeToLog("UserJoinedHandler: Not a new member or is bot, ignoring");
                return;
            }
            
            // Синхронизируем пользователя в БД
            $syncResult = $this->syncUserToDatabase($user);
            
            // Временно отключаем API для тестирования сообщений
            $syncResult = [
                'success' => true,
                'data' => [
                    'action' => 'created',
                    'user_id' => $user['id']
                ]
            ];
            
            if ($syncResult['success']) {
                // Отправляем приветственное сообщение
                $this->sendWelcomeMessage($chat['id'], $user, $syncResult['data']);
                
                writeToLog("UserJoinedHandler: User joined successfully", [
                    'user_id' => $user['id'],
                    'sync_action' => $syncResult['data']['action'] ?? 'unknown'
                ]);
            } else {
                writeToLog("UserJoinedHandler: Failed to sync user", [
                    'user_id' => $user['id'],
                    'error' => $syncResult['error']['message'] ?? 'unknown error'
                ]);
            }
            
        } catch (Exception $e) {
            writeToLog("UserJoinedHandler: Error processing join event - " . $e->getMessage());
        }
    }
    
    /**
     * Синхронизирует пользователя в базе данных
     * 
     * @param array $user Данные пользователя из Telegram
     * @return array Результат синхронизации
     */
    private function syncUserToDatabase($user) {
        try {
            // Формируем правильный URL для backend API
            $backendApiUrl = $_ENV['BACKEND_API_URL'] ?? 'http://localhost/app/backend';
            $apiUrl = $backendApiUrl . '/routes/api.php';
            $systemToken = $_ENV['SYSTEM_TOKEN'] ?? '';
            
            if (empty($systemToken)) {
                writeToLog("UserJoinedHandler: SYSTEM_TOKEN not configured");
                return [
                    'success' => false,
                    'error' => ['message' => 'SYSTEM_TOKEN not configured']
                ];
            }
            
            // Подготавливаем данные для синхронизации
            $syncData = [
                'telegram_id' => $user['id'],
                'first_name' => $user['first_name'] ?? null,
                'last_name' => $user['last_name'] ?? null,
                'username' => $user['username'] ?? null,
                'role_id' => 2 // guest - новый участник чата
            ];
            
            // Отправляем запрос к API
            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => [
                        'Authorization: Bearer ' . $systemToken,
                        'Content-Type: application/json'
                    ],
                    'content' => json_encode($syncData)
                ]
            ]);
            
            $response = file_get_contents($apiUrl . '?route=/api/system/user-sync', false, $context);
            
            if ($response === false) {
                writeToLog("UserJoinedHandler: Failed to make API request");
                return [
                    'success' => false,
                    'error' => ['message' => 'Failed to make API request']
                ];
            }
            
            $result = json_decode($response, true);
            
            writeToLog("UserJoinedHandler: API response", [
                'response' => $result
            ]);
            
            return $result;
            
        } catch (Exception $e) {
            writeToLog("UserJoinedHandler: Error syncing user to database - " . $e->getMessage());
            return [
                'success' => false,
                'error' => ['message' => $e->getMessage()]
            ];
        }
    }
    
    /**
     * Отправляет приветственное сообщение
     * 
     * @param int $chatId ID чата
     * @param array $user Данные пользователя
     * @param array $syncData Данные синхронизации
     */
    private function sendWelcomeMessage($chatId, $user, $syncData) {
        $username = $user['first_name'] ?? $user['username'] ?? 'Участник';
        $action = $syncData['action'] ?? 'joined';
        
        $message = "🎉 <b>Добро пожаловать в CabrioRide!</b>\n\n";
        $message .= "Привет, <b>$username</b>! 👋\n\n";
        
        if ($action === 'created') {
            $message .= "✅ Вы успешно присоединились к клубу!\n";
            $message .= "📝 Для полного доступа заполните профиль в боте.\n\n";
        } else {
            $message .= "✅ С возвращением в клуб!\n\n";
        }
        
        $message .= "🚗 <b>Что дальше?</b>\n";
        $message .= "• Напишите боту @CabrioRideBot для регистрации\n";
        $message .= "• Добавьте свой автомобиль в гараж\n";
        $message .= "• Участвуйте в событиях клуба\n\n";
        $message .= "💬 <b>Правила клуба:</b>\n";
        $message .= "• Уважайте других участников\n";
        $message .= "• Делитесь опытом и знаниями\n";
        $message .= "• Поддерживайте дружескую атмосферу";
        
        $this->botService->sendMessage($chatId, $message);
    }
} 
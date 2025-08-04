<?php
/**
 * UserLeftHandler.php
 * 
 * Обработчик события выхода пользователя из клуба
 * Отправляет сообщение с сожалением и обновляет роль в БД
 */

require_once __DIR__ . '/../../utils/Logger.php';

class UserLeftHandler {
    /** @var BotService */
    private $botService;
    
    /**
     * Конструктор
     */
    public function __construct($botService) {
        $this->botService = $botService;
    }
    
    /**
     * Обрабатывает событие выхода пользователя из клуба
     * 
     * @param array $chatMemberUpdate Данные об обновлении участника чата
     */
    public function handle($chatMemberUpdate) {
        try {
            $chat = $chatMemberUpdate['chat'];
            $leftMember = $chatMemberUpdate['new_chat_member'];
            $user = $leftMember['user'];
            
            writeToLog("UserLeftHandler: Processing user left event", [
                'chat_id' => $chat['id'],
                'user_id' => $user['id'],
                'username' => $user['username'] ?? 'unknown',
                'first_name' => $user['first_name'] ?? 'unknown'
            ]);
            
            // Проверяем, что это клубный чат
            $club_chat_id = $_ENV['CLUB_CHAT_ID'] ?? '-1002873258290';
            if ($chat['id'] != $club_chat_id) {
                writeToLog("UserLeftHandler: Not club chat, ignoring");
                return;
            }
            
            // Проверяем, что пользователь действительно покинул чат
            if ($leftMember['status'] !== 'left' && $leftMember['status'] !== 'kicked') {
                writeToLog("UserLeftHandler: User didn't leave, ignoring");
                return;
            }
            
            // Проверяем, что это не бот
            if ($user['is_bot'] ?? false) {
                writeToLog("UserLeftHandler: Bot left, ignoring");
                return;
            }
            
            // Обновляем роль пользователя в БД
            $updateResult = $this->updateUserRole($user);
            
            // Временно отключаем API для тестирования сообщений
            $updateResult = [
                'success' => true,
                'data' => [
                    'action' => 'updated',
                    'user_id' => $user['id']
                ]
            ];
            
            if ($updateResult['success']) {
                // Отправляем сообщение с сожалением
                $this->sendFarewellMessage($chat['id'], $user);
                
                writeToLog("UserLeftHandler: User left successfully", [
                    'user_id' => $user['id'],
                    'update_action' => $updateResult['data']['action'] ?? 'unknown'
                ]);
            } else {
                writeToLog("UserLeftHandler: Failed to update user role", [
                    'user_id' => $user['id'],
                    'error' => $updateResult['error']['message'] ?? 'unknown error'
                ]);
            }
            
        } catch (Exception $e) {
            writeToLog("UserLeftHandler: Error processing leave event - " . $e->getMessage());
        }
    }
    
    /**
     * Обновляет роль пользователя в базе данных
     * 
     * @param array $user Данные пользователя из Telegram
     * @return array Результат обновления
     */
    private function updateUserRole($user) {
        try {
            // Формируем правильный URL для backend API
            $backendApiUrl = $_ENV['BACKEND_API_URL'] ?? 'http://localhost/app/backend';
            $apiUrl = $backendApiUrl . '/routes/api.php';
            $systemToken = $_ENV['SYSTEM_TOKEN'] ?? '';
            
            if (empty($systemToken)) {
                writeToLog("UserLeftHandler: SYSTEM_TOKEN not configured");
                return [
                    'success' => false,
                    'error' => ['message' => 'SYSTEM_TOKEN not configured']
                ];
            }
            
            // Подготавливаем данные для обновления роли
            $updateData = [
                'telegram_id' => $user['id'],
                'role_id' => 1 // external - покинул чат
            ];
            
            // Отправляем запрос к API
            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => [
                        'Authorization: Bearer ' . $systemToken,
                        'Content-Type: application/json'
                    ],
                    'content' => json_encode($updateData)
                ]
            ]);
            
            $response = file_get_contents($apiUrl . '?route=/api/system/user-role', false, $context);
            
            if ($response === false) {
                writeToLog("UserLeftHandler: Failed to make API request");
                return [
                    'success' => false,
                    'error' => ['message' => 'Failed to make API request']
                ];
            }
            
            $result = json_decode($response, true);
            
            writeToLog("UserLeftHandler: API response", [
                'response' => $result
            ]);
            
            return $result;
            
        } catch (Exception $e) {
            writeToLog("UserLeftHandler: Error updating user role - " . $e->getMessage());
            return [
                'success' => false,
                'error' => ['message' => $e->getMessage()]
            ];
        }
    }
    
    /**
     * Отправляет сообщение с сожалением о выходе пользователя
     * 
     * @param int $chatId ID чата
     * @param array $user Данные пользователя
     */
    private function sendFarewellMessage($chatId, $user) {
        $username = $user['first_name'] ?? $user['username'] ?? 'Участник';
        
        $message = "😔 <b>$username покинул клуб</b>\n\n";
        $message .= "Надеемся на скорую встречу на дорогах! 🚗";
        
        $this->botService->sendMessage($chatId, $message);
    }
} 
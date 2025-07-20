<?php
/**
 * StartCommand.php
 * 
 * Команда /start - начало работы с ботом
 */

require_once __DIR__ . '/../utils/Logger.php';

class StartCommand {
    /** @var BotService */
    private $botService;
    
    /**
     * Конструктор
     */
    public function __construct($botService) {
        $this->botService = $botService;
    }
    
    /**
     * Выполняет команду /start
     */
    public function execute($message) {
        try {
            writeToLog("StartCommand: Starting command execution");
            
            $chat_id = $message['chat']['id'];
            $user = $message['from'];
            
            // Для команды /start проверяем членство без отправки сообщения,
            // так как у нас будет свой формат ответа
            if (!$this->botService->verifyMembership($user['id'], $chat_id, false)) {
                writeToLog("StartCommand: User is not a member");
                $this->botService->sendNonMemberMessage($chat_id);
                return;
            }
            
            // Проверяем роль пользователя
            $role = $this->botService->checkUserRole($user['id']);
            
            writeToLog("StartCommand: User role check", [
                'user_id' => $user['id'],
                'role' => $role
            ]);
            
            // Если роль external или new - предлагаем зарегистрироваться
            if ($role === 'external' || $role === 'new') {
                $this->botService->sendRegistrationMessage($chat_id, $user['first_name']);
                return;
            }
            
            // Для остальных ролей показываем приветствие и кнопку входа
            $this->botService->sendWelcomeMessage($chat_id, $user['first_name'], $role);
            
        } catch (Exception $e) {
            writeToLog("Error in StartCommand: " . $e->getMessage());
            $this->botService->sendMessage($chat_id, getMessage('error.general'));
        }
    }
} 
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
    public function execute($message, $userSyncResult = null) {
        try {
            writeToLog("StartCommand: Starting command execution");
            
            $chat_id = $message['chat']['id'];
            $user = $message['from'];
            $role = $userSyncResult['role'] ?? 'external';
            if ($role === 'external') {
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
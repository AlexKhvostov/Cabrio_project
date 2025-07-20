<?php
/**
 * HelpCommand.php
 * 
 * Команда /help - справка по командам бота
 */

class HelpCommand {
    /** @var BotService */
    private $botService;
    
    /**
     * Конструктор
     */
    public function __construct($botService) {
        $this->botService = $botService;
    }
    
    /**
     * Выполняет команду /help
     */
    public function execute($message) {
        try {
            $chat_id = $message['chat']['id'];
            $text = getMessage('help.main');
            
            $buttons = getButtons('member');
            
            $this->botService->sendInlineKeyboard($chat_id, $text, $buttons);
            
        } catch (Exception $e) {
            error_log("Error in HelpCommand: " . $e->getMessage());
            $this->botService->sendMessage($chat_id, getMessage('error.general'));
        }
    }
    
    /**
     * Обрабатывает callback
     */
    public function handleCallback($callback) {
        try {
            $chat_id = $callback['message']['chat']['id'];
            $this->execute(['chat' => ['id' => $chat_id]]);
        } catch (Exception $e) {
            error_log("Error in HelpCommand callback: " . $e->getMessage());
        }
    }
} 
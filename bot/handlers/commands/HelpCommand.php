<?php
/**
 * HelpCommand.php
 * 
 * Команда /help - список доступных команд
 */

require_once __DIR__ . '/../../utils/Logger.php';

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
        $chat_id = $message['chat']['id'];
        $user = $message['from'];
        
        writeToLog("HelpCommand: Executing /help command", [
            'user_id' => $user['id'],
            'username' => $user['username'] ?? 'unknown'
        ]);
        
        $text = $this->getHelpMessage();
        $webAppUrl = $this->getWebAppUrl();
        
        $this->botService->sendWebAppButton($chat_id, $text, '🌐 Открыть приложение', $webAppUrl);
        
        writeToLog("HelpCommand: Help message sent successfully");
    }
    
    /**
     * Формирует сообщение помощи
     */
    private function getHelpMessage() {
        return "📋 <b>Справка CabrioRide</b>\n\n" .
               "📸 <b>Работа с фото в группе:</b>\n" .
               "• <code>?</code> - проверить авто в клубе\n" .
               "• <code>!</code> - зафиксировать визитку\n" .
               "• <code>++</code> - добавить свой авто\n\n" .
               
               "🔧 <b>Команды:</b>\n" .
               "/start - приветствие\n" .
               "/help - эта справка\n\n" .
               
               "💡 <b>Как это работает:</b>\n" .
               "1. Сделайте фото автомобиля\n" .
               "2. Добавьте комментарий (?, !, ++)\n" .
               "3. Отправьте в группу\n" .
               "4. Получите результат\n\n" .
               
               "🌐 <b>Больше полезной информации в приложении</b>";
    }
    
    /**
     * Получает URL WebApp
     */
    private function getWebAppUrl() {
        // Используем туннельный домен для WebApp
        return $_ENV['APP_URL'] ?? 'https://contributed-cm-component-consideration.trycloudflare.com/app/frontend/dist/';
    }
} 
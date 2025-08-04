<?php
/**
 * StartCommand.php
 * 
 * Команда /start - приветствие и основная информация
 */

require_once __DIR__ . '/../../utils/Logger.php';

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
        $chat_id = $message['chat']['id'];
        $user = $message['from'];
        
        writeToLog("StartCommand: Executing /start command", [
            'user_id' => $user['id'],
            'username' => $user['username'] ?? 'unknown',
            'chat_type' => $message['chat']['type']
        ]);
        
        $text = $this->getStartMessage($user);
        $buttons = $this->getStartButtons();
        
        $this->botService->sendInlineKeyboard($chat_id, $text, $buttons);
        
        writeToLog("StartCommand: Start message sent successfully");
    }
    
    /**
     * Формирует приветственное сообщение
     */
    private function getStartMessage($user) {
        $username = $user['first_name'] ?? $user['username'] ?? 'Участник';
        $botUsername = $_ENV['BOT_USERNAME'] ?? 'CabrioRideBot';
        
        return "🎉 <b>Привет, $username!</b>\n\n" .
               "Я бот клуба CabrioRide.\n\n" .
               "💡 <b>Напишите @$botUsername</b> чтобы узнать как работать с фото в группе\n\n" .
               "🌐 <b>Больше полезной информации в приложении</b> (в разработке)";
    }
    
    /**
     * Формирует кнопки для команды start
     */
    private function getStartButtons() {
        return [
            [
                ['text' => '🌐 Открыть приложение', 'url' => $_ENV['APP_URL'] ?? 'https://cabrioride.ru']
            ]
        ];
    }
} 
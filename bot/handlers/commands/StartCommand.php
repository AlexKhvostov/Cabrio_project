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
        
        return "🎉 <b>Добро пожаловать в CabrioRide!</b>\n\n" .
               "Привет, <b>$username</b>! 👋\n\n" .
               "Я бот клуба CabrioRide - вашего сообщества любителей кабриолетов.\n\n" .
               "🔧 <b>Что я умею:</b>\n" .
               "• Проверять членство в клубе\n" .
               "• Помогать с регистрацией\n" .
               "• Отвечать на вопросы\n" .
               "• Связывать с администрацией\n\n" .
               "💡 <b>Используйте /help для списка команд</b>";
    }
    
    /**
     * Формирует кнопки для команды start
     */
    private function getStartButtons() {
        return [
            [
                ['text' => '📋 Помощь', 'callback_data' => 'help'],
                ['text' => '💬 Клубный чат', 'url' => $_ENV['CHAT_INVITE_LINK'] ?? 'https://t.me/+Iwe_Bi1rZWI5Yjcy']
            ],
            [
                ['text' => '👥 Профиль', 'callback_data' => 'profile'],
                ['text' => '🚗 Мои авто', 'callback_data' => 'my_cars']
            ]
        ];
    }
} 
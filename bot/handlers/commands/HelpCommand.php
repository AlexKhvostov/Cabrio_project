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
        $buttons = $this->getHelpButtons();
        
        $this->botService->sendInlineKeyboard($chat_id, $text, $buttons);
        
        writeToLog("HelpCommand: Help message sent successfully");
    }
    
    /**
     * Формирует сообщение помощи
     */
    private function getHelpMessage() {
        return "📋 <b>Справка по командам CabrioRide</b>\n\n" .
               "🔧 <b>Основные команды:</b>\n" .
               "/start - Приветствие и основная информация\n" .
               "/help - Эта справка\n\n" .
               
               "📱 <b>Интерактивные кнопки:</b>\n" .
               "• <b>Профиль</b> - информация о вашем профиле\n" .
               "• <b>Мои авто</b> - ваши зарегистрированные автомобили\n" .
               "• <b>Клубный чат</b> - ссылка на основной чат\n" .
               "• <b>Помощь</b> - эта справка\n\n" .
               
               "💡 <b>Дополнительные возможности:</b>\n" .
               "• Отправьте фото автомобиля для регистрации\n" .
               "• Отправьте фото с номером для поиска\n" .
               "• Напишите вопрос администрации\n\n" .
               
               "🎯 <b>Для участников клуба доступны:</b>\n" .
               "• Регистрация автомобилей\n" .
               "• Поиск по номеру\n" .
               "• Полный доступ к функциям";
    }
    
    /**
     * Формирует кнопки для команды help
     */
    private function getHelpButtons() {
        return [
            [
                ['text' => '🏠 Главная', 'callback_data' => 'start'],
                ['text' => '💬 Клубный чат', 'url' => $_ENV['CHAT_INVITE_LINK'] ?? 'https://t.me/+Iwe_Bi1rZWI5Yjcy']
            ],
            [
                ['text' => '👥 Профиль', 'callback_data' => 'profile'],
                ['text' => '🚗 Мои авто', 'callback_data' => 'my_cars']
            ],
            [
                ['text' => '📞 Поддержка', 'callback_data' => 'support']
            ]
        ];
    }
} 
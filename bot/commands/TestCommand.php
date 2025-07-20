<?php
/**
 * TestCommand.php
 * 
 * Команда /test - тестирование функционала бота
 */

require_once __DIR__ . '/../utils/Logger.php';
require_once __DIR__ . '/../config.php';

class TestCommand {
    /** @var BotService */
    private $botService;
    
    /**
     * Конструктор
     */
    public function __construct($botService) {
        $this->botService = $botService;
    }
    
    /**
     * Выполняет команду /test
     */
    public function execute($message) {
        try {
            writeToLog("TestCommand: Starting command execution");
            
            $chat_id = $message['chat']['id'];
            $user = $message['from'];
            
            // Проверяем членство в чате
            if (!$this->botService->verifyMembership($user['id'], $chat_id)) {
                return;
            }
            
            // Отправляем тестовое сообщение с inline-кнопками
            $text = "🔧 Панель разработки\n\n" .
                   "Выберите функцию для тестирования:";
            
            $buttons = [
                [
                    ['text' => '🔍 OCR Test', 'callback_data' => 'test_ocr'],
                    ['text' => '📋 Search Test', 'callback_data' => 'test_search']
                ],
                [
                    ['text' => '🌐 App Test', 'callback_data' => 'test_app'],
                    ['text' => '📝 Help Test', 'callback_data' => 'test_help']
                ],
                [
                    ['text' => '🎹 Keyboard Test', 'callback_data' => 'test_keyboards']
                ]
            ];
            
            writeToLog("TestCommand: Sending test menu", [
                'chat_id' => $chat_id,
                'buttons' => $buttons
            ]);
            
            $this->botService->sendInlineKeyboard($chat_id, $text, $buttons);
            
        } catch (Exception $e) {
            writeToLog("Error in TestCommand: " . $e->getMessage());
            $this->botService->sendMessage($chat_id, getMessage('error.general'));
        }
    }
    
    /**
     * Обрабатывает callback-запросы от кнопок
     */
    public function handleCallback($callback) {
        try {
            $data = $callback['data'];
            $chat_id = $callback['message']['chat']['id'];
            $user_id = $callback['from']['id'];
            
            // Проверяем членство в чате
            if (!$this->botService->verifyMembership($user_id, $chat_id)) {
                return;
            }
            
            writeToLog("TestCommand: Processing callback", [
                'data' => $data,
                'chat_id' => $chat_id
            ]);
            
            switch ($data) {
                case 'test_ocr':
                    $text = "📸 Тест OCR распознавания\n\n" .
                           "Отправьте фото номера автомобиля для распознавания";
                    $this->botService->sendMessage($chat_id, $text);
                    break;
                    
                case 'test_search':
                    $text = "🔍 Тест поиска авто\n\n" .
                           "Введите номер автомобиля для поиска";
                    $this->botService->sendMessage($chat_id, $text);
                    break;
                    
                case 'test_app':
                    $text = "🌐 Тест WebApp\n\n" .
                           "Нажмите кнопку для перехода в приложение:";
                    $buttons = [[
                        ['text' => '🌐 Открыть приложение', 'web_app' => ['url' => getWebAppUrl()]]
                    ]];
                    $this->botService->sendInlineKeyboard($chat_id, $text, $buttons);
                    break;
                    
                case 'test_help':
                    $helpCommand = new HelpCommand($this->botService);
                    $helpCommand->execute(['chat' => ['id' => $chat_id]]);
                    break;
                    
                case 'test_keyboards':
                    $this->testKeyboards($chat_id);
                    break;
            }
            
        } catch (Exception $e) {
            writeToLog("Error in TestCommand callback: " . $e->getMessage());
            $this->botService->sendMessage($chat_id, getMessage('error.general'));
        }
    }
    
    /**
     * Тестирует разные типы клавиатур
     */
    private function testKeyboards($chat_id) {
        // Тест обычной клавиатуры
        $text = "🎹 Тест обычной клавиатуры:";
        $buttons = [
            [
                ['text' => '🔍 Проверить авто'],
                ['text' => '📋 Справка']
            ],
            [
                ['text' => '🌐 Перейти в приложение']
            ]
        ];
        
        writeToLog("TestCommand: Testing regular keyboard", [
            'chat_id' => $chat_id,
            'buttons' => $buttons
        ]);
        
        $this->botService->sendKeyboard($chat_id, $text, $buttons);
        
        // Тест inline-клавиатуры
        $text = "🎯 Тест inline-клавиатуры:";
        $buttons = [
            [
                ['text' => '🔍 Найти авто', 'callback_data' => 'search_car'],
                ['text' => '📋 Помощь', 'callback_data' => 'help']
            ],
            [
                ['text' => '🌐 WebApp', 'web_app' => ['url' => getWebAppUrl()]]
            ]
        ];
        
        writeToLog("TestCommand: Testing inline keyboard", [
            'chat_id' => $chat_id,
            'buttons' => $buttons
        ]);
        
        $this->botService->sendInlineKeyboard($chat_id, $text, $buttons);
    }
} 
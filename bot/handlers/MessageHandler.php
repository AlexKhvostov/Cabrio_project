<?php
/**
 * MessageHandler.php
 * 
 * Главный обработчик сообщений Telegram бота
 * Маршрутизирует входящие сообщения к соответствующим обработчикам
 */

require_once __DIR__ . '/../services/BotService.php';
require_once __DIR__ . '/../utils/Logger.php';
require_once __DIR__ . '/commands/StartCommand.php';
require_once __DIR__ . '/commands/HelpCommand.php';
require_once __DIR__ . '/events/UserJoinedHandler.php';
require_once __DIR__ . '/events/UserLeftHandler.php';
require_once __DIR__ . '/messages/PhotoQuestionHandler.php';
require_once __DIR__ . '/messages/PhotoExclamationHandler.php';
require_once __DIR__ . '/messages/PhotoPlusPlusHandler.php';

class MessageHandler {
    /** @var BotService */
    private $botService;
    
    /**
     * Конструктор
     */
    public function __construct() {
        $this->botService = new BotService();
        writeToLog("MessageHandler: Initialized");
    }
    
    /**
     * Обрабатывает входящее сообщение
     * 
     * @param array $message — сообщение от Telegram
     */
    public function handle($message) {
        try {
            writeToLog("MessageHandler: Processing message", [
                'chat_type' => $message['chat']['type'] ?? 'unknown',
                'has_text' => isset($message['text']),
                'has_photo' => isset($message['photo']),
                'user_id' => $message['from']['id'] ?? null
            ]);
            
            $chat_type = $message['chat']['type'] ?? null;
            $chat_id = $message['chat']['id'] ?? null;
            
            // Проверяем тип чата
            if ($chat_type === 'private') {
                $this->handlePrivateMessage($message);
            } elseif ($chat_type === 'group' || $chat_type === 'supergroup') {
                $this->handleGroupMessage($message);
            } else {
                writeToLog("MessageHandler: Unknown chat type", [
                    'chat_type' => $chat_type
                ]);
            }
            
        } catch (Exception $e) {
            writeToLog("Error in MessageHandler: " . $e->getMessage());
            $this->botService->sendMessage(
                $message['chat']['id'],
                "❌ Произошла ошибка при обработке сообщения. Попробуйте позже."
            );
        }
    }
    
    /**
     * Обрабатывает сообщения в личных чатах
     */
    private function handlePrivateMessage($message) {
        writeToLog("MessageHandler: Handling private message");
        
        $chat_id = $message['chat']['id'];
        $user = $message['from'];
        
        // Проверяем членство пользователя в клубном чате
        $club_chat_id = $_ENV['CLUB_CHAT_ID'] ?? '-1002873258290';
        $isMember = $this->botService->checkChatMember($club_chat_id, $user['id']);
        
        if (!$isMember) {
            writeToLog("MessageHandler: User not in club chat");
            $this->botService->sendNonMemberMessage($chat_id);
            return;
        }
        
        // Пользователь в клубе - обрабатываем сообщение
        writeToLog("MessageHandler: User is club member, processing message");
        
        // Обрабатываем команды
        $this->handleCommands($message);
    }
    
    /**
     * Обрабатывает сообщения в групповых чатах
     */
    private function handleGroupMessage($message) {
        writeToLog("MessageHandler: Handling group message");
        
        $chat_id = $message['chat']['id'];
        $club_chat_id = $_ENV['CLUB_CHAT_ID'] ?? '-1002873258290';
        $club_chat_name = $_ENV['CLUB_CHAT_NAME'] ?? 'CabrioRide';
        
        // Проверяем, что это клубный чат
        if ($chat_id != $club_chat_id) {
            writeToLog("MessageHandler: Not club chat, ignoring");
            $this->botService->sendMessage($chat_id, "❌ Бот работает только в клубном чате: @$club_chat_name");
            return;
        }
        
        // Это клубный чат - обрабатываем сообщение
        writeToLog("MessageHandler: Club chat message, processing");
        
        // Обрабатываем фото с комментариями
        if (isset($message['photo']) && isset($message['caption'])) {
            $this->handlePhotoWithCaption($message);
        }
    }
    
    /**
     * Обрабатывает callback-запросы от inline-кнопок
     */
    public function handleCallback($callback) {
        try {
            $data = $callback['data'] ?? '';
            $chat_id = $callback['message']['chat']['id'] ?? null;
            $user = $callback['from'];
            
            writeToLog("MessageHandler: Processing callback", [
                'data' => $data,
                'user_id' => $user['id'] ?? null
            ]);
            
            switch ($data) {
                case 'start':
                    $startCommand = new StartCommand($this->botService);
                    $startCommand->execute(['chat' => ['id' => $chat_id], 'from' => $user]);
                    break;
                    
                case 'help':
                    $helpCommand = new HelpCommand($this->botService);
                    $helpCommand->execute(['chat' => ['id' => $chat_id], 'from' => $user]);
                    break;
                    
                case 'profile':
                    $this->botService->sendMessage($chat_id, "👥 <b>Профиль</b>\n\nФункция профиля пока в разработке.");
                    break;
                    
                case 'my_cars':
                    $this->botService->sendMessage($chat_id, "🚗 <b>Мои автомобили</b>\n\nФункция пока в разработке.");
                    break;
                    
                case 'support':
                    $this->botService->sendMessage($chat_id, "📞 <b>Поддержка</b>\n\nОбратитесь к администрации клуба.");
                    break;
                    
                default:
                    $this->botService->sendMessage($chat_id, "❓ Неизвестная кнопка.");
                    break;
            }
            
        } catch (Exception $e) {
            writeToLog("Error in MessageHandler callback: " . $e->getMessage());
        }
    }
    
    /**
     * Обрабатывает обновления участников чата
     */
    public function handleChatMemberUpdate($update) {
        try {
            $chat = $update['chat'];
            $newMember = $update['new_chat_member'];
            $user = $newMember['user'];
            
            writeToLog("MessageHandler: Processing chat member update", [
                'chat_id' => $chat['id'] ?? null,
                'user_id' => $user['id'] ?? null,
                'status' => $newMember['status'] ?? null
            ]);
            
            // Проверяем, что это клубный чат
            $club_chat_id = $_ENV['CLUB_CHAT_ID'] ?? '-1002873258290';
            if ($chat['id'] != $club_chat_id) {
                writeToLog("MessageHandler: Not club chat, ignoring chat member update");
                return;
            }
            
            // Обрабатываем в зависимости от статуса
            switch ($newMember['status']) {
                case 'member':
                    // Пользователь присоединился к чату
                    $joinedHandler = new UserJoinedHandler($this->botService);
                    $joinedHandler->handle($update);
                    break;
                    
                case 'left':
                case 'kicked':
                    // Пользователь покинул чат
                    $leftHandler = new UserLeftHandler($this->botService);
                    $leftHandler->handle($update);
                    break;
                    
                default:
                    writeToLog("MessageHandler: Unknown member status", [
                        'status' => $newMember['status']
                    ]);
                    break;
            }
            
        } catch (Exception $e) {
            writeToLog("Error in MessageHandler chat member update: " . $e->getMessage());
        }
    }
    
    /**
     * Обрабатывает фото с комментариями
     */
    private function handlePhotoWithCaption($message) {
        $caption = trim($message['caption'] ?? '');
        
        writeToLog("MessageHandler: Processing photo with caption", [
            'caption' => $caption,
            'user_id' => $message['from']['id'] ?? null
        ]);
        
        // Обрабатываем разные комментарии
        switch ($caption) {
            case '?':
                $handler = new PhotoQuestionHandler($this->botService);
                $handler->handle($message);
                break;
                
            case '!':
                $handler = new PhotoExclamationHandler($this->botService);
                $handler->handle($message);
                break;
                
            case '++':
                $handler = new PhotoPlusPlusHandler($this->botService);
                $handler->handle($message);
                break;
                
            default:
                writeToLog("MessageHandler: Unknown photo caption", [
                    'caption' => $caption
                ]);
                break;
        }
    }
    
    /**
     * Обрабатывает команды пользователя
     */
    private function handleCommands($message) {
        $text = $message['text'] ?? '';
        $chat_id = $message['chat']['id'];
        
        writeToLog("MessageHandler: Processing command", [
            'text' => $text,
            'chat_id' => $chat_id
        ]);
        
        switch ($text) {
            case '/start':
                $startCommand = new StartCommand($this->botService);
                $startCommand->execute($message);
                break;
                
            case '/help':
                $helpCommand = new HelpCommand($this->botService);
                $helpCommand->execute($message);
                break;
                
            default:
                // Неизвестная команда
                $this->botService->sendMessage($chat_id, 
                    "❓ Неизвестная команда. Используйте /help для списка доступных команд."
                );
                break;
        }
    }
} 
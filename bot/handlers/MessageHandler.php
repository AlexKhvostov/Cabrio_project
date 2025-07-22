<?php
/**
 * MessageHandler.php
 * 
 * Главный обработчик сообщений бота
 */

require_once __DIR__ . '/../services/BotService.php';
require_once __DIR__ . '/../commands/StartCommand.php';
require_once __DIR__ . '/../commands/HelpCommand.php';
require_once __DIR__ . '/../commands/TestCommand.php';
require_once __DIR__ . '/../commands/OcrCommand.php';
require_once __DIR__ . '/../commands/PlateSearchCommand.php';
require_once __DIR__ . '/../utils/Logger.php';
require_once __DIR__ . '/../commands/LeaveBusinessCardCommand.php';

class MessageHandler {
    /** @var BotService */
    private $botService;
    
    /** @var TestCommand */
    private $testCommand;
    
    /** @var OcrCommand */
    private $ocrCommand;
    
    /**
     * Конструктор
     */
    public function __construct() {
        $this->botService = new BotService();
        $this->testCommand = new TestCommand($this->botService);
        $this->ocrCommand = new OcrCommand($this->botService);
    }
    
    /**
     * Обрабатывает входящее сообщение
     */
    public function handle($message) {
        try {
            writeToLog("MessageHandler: Processing message");
            
            // Создаем сервис бота
            $botService = new BotService();
            
            // Если это команда
            if (isset($message['entities']) && 
                $message['entities'][0]['type'] === 'bot_command') {
                
                $command = strtolower(explode(' ', $message['text'])[0]);
                
                switch ($command) {
                    case '/start':
                        $cmd = new StartCommand($botService);
                        $cmd->execute($message);
                        break;
                        
                    case '/help':
                        $cmd = new HelpCommand($botService);
                        $cmd->execute($message);
                        break;
                        
                    case '/test':
                        $cmd = new TestCommand($botService);
                        $cmd->execute($message);
                        break;
                        
                    case '/search':
                        $cmd = new PlateSearchCommand($botService);
                        $cmd->execute($message);
                        break;
                        
                    default:
                        $botService->sendMessage(
                            $message['chat']['id'],
                            "⚠️ Неизвестная команда. Используйте /help для списка команд."
                        );
                }
                return;
            }
            
            // Если это фото с текстом "?" в групповом чате
            if (isset($message['photo']) && 
                (isset($message['text']) && trim($message['text']) === '?' || 
                 isset($message['caption']) && trim($message['caption']) === '?')) {
                $cmd = new OcrCommand($botService);
                $cmd->executeGroupPhoto($message);
                return;
            }
            
            // Если это фото в личных сообщениях
            if (isset($message['photo']) && $message['chat']['type'] === 'private') {
                $cmd = new OcrCommand($botService);
                $cmd->execute($message);
                return;
            }
            
            // Если это текстовый поиск номера (не команда)
            if (isset($message['text']) && $message['chat']['type'] === 'private') {
                $text = trim($message['text']);
                
                // Проверяем, что это похоже на номер (содержит буквы и цифры)
                if (preg_match('/[а-яёa-z0-9]{4,}/ui', $text)) {
                    $cmd = new PlateSearchCommand($botService);
                    $cmd->execute($message);
                    return;
                }
            }
            
            // Если это новые участники в группе
            if (isset($message['new_chat_members']) && !empty($message['new_chat_members'])) {
                $this->handleNewChatMembers($botService, $message);
                return;
            }
            
            // Если это участник покинул группу
            if (isset($message['left_chat_member'])) {
                $this->handleLeftChatMember($botService, $message);
                return;
            }
            
            // Если это текст в личных сообщениях
            if (isset($message['text']) && $message['chat']['type'] === 'private') {
                // TODO: Здесь будет поиск по номеру
                $botService->sendMessage(
                    $message['chat']['id'],
                    "🔍 Для поиска авто отправьте фото номера или используйте команду /search"
                );
                return;
            }
            
            // Если это фото с текстом "!" в групповом чате — оставить визитку
            if (isset($message['photo']) && (
                (isset($message['text']) && trim($message['text']) === '!') ||
                (isset($message['caption']) && trim($message['caption']) === '!')
            )) {
                // TODO: вызвать команду LeaveBusinessCardCommand (реализовать отдельно)
                $cmd = new LeaveBusinessCardCommand($botService);
                $cmd->execute($message);
                return;
            }
            
            // В групповом чате игнорируем все остальные сообщения
            if ($message['chat']['type'] !== 'private') {
                return; // Бот не реагирует на обычные сообщения в группе
            }
            
        } catch (Exception $e) {
            writeToLog("Error in MessageHandler: " . $e->getMessage());
            $botService->sendMessage(
                $message['chat']['id'],
                "❌ Произошла ошибка при обработке сообщения. Попробуйте позже."
            );
        }
    }
    
    /**
     * Обрабатывает callback-запросы от inline-кнопок
     */
    public function handleCallback($callback) {
        try {
            writeToLog("MessageHandler: Processing callback", [
                'data' => $callback['data']
            ]);
            
            $botService = new BotService();
            
            // Определяем тип callback по префиксу
            if (strpos($callback['data'], 'test_') === 0) {
                $cmd = new TestCommand($botService);
                $cmd->handleCallback($callback);
                
            } elseif (strpos($callback['data'], 'search_') === 0) {
                $cmd = new OcrCommand($botService);
                $cmd->handleCallback($callback);
                
            } elseif (strpos($callback['data'], 'leave_card_') === 0) {
                // Обработка кнопки "Оставить визитку"
                $plate = str_replace('leave_card_', '', $callback['data']);
                $this->handleLeaveCard($callback, $plate);
                
            } elseif ($callback['data'] === 'cancel_card') {
                // Обработка кнопки "Отмена"
                $this->handleCancelCard($callback);
                
            } else {
                writeToLog("MessageHandler: Unknown callback type", [
                    'data' => $callback['data']
                ]);
                
                // Отвечаем на callback (убираем "часики")
                $botService->answerCallbackQuery($callback['id'], "Неизвестная команда");
            }
            
        } catch (Exception $e) {
            writeToLog("Error in MessageHandler callback: " . $e->getMessage());
            $botService->sendMessage(
                $callback['message']['chat']['id'],
                "❌ Произошла ошибка при обработке запроса. Попробуйте позже."
            );
        }
    }
    
    /**
     * Обрабатывает нажатие кнопки "Оставить визитку"
     */
    private function handleLeaveCard($callback, $plate) {
        try {
            $chat_id = $callback['message']['chat']['id'];
            $user = $callback['from'];
            
            writeToLog("Handling leave card", [
                'plate' => $plate,
                'user_id' => $user['id']
            ]);
            
            // Проверяем роль пользователя
            $user_role = $this->botService->checkUserRole($user['id']);
            if (!in_array($user_role, ['member', 'moderator', 'admin'])) {
                $this->botService->answerCallbackQuery($callback['id'], "Только участники клуба могут оставлять визитки");
                return;
            }
            
            // Открываем WebApp для создания визитки
            $webAppUrl = getWebAppUrl() . '/visiting-card/' . urlencode($plate);
            
            $text = "💼 Создание визитки\n\n🚗 Номер: $plate\n\nОткроется приложение для заполнения визитки.";
            
            $buttons = [[
                [
                    'text' => '📝 Заполнить визитку',
                    'web_app' => ['url' => $webAppUrl]
                ]
            ]];
            
            $this->botService->sendInlineKeyboard($chat_id, $text, $buttons);
            
            // Отвечаем на callback
            $this->botService->answerCallbackQuery($callback['id'], "Открываю форму визитки");
            
        } catch (Exception $e) {
            writeToLog("Error handling leave card: " . $e->getMessage());
            $this->botService->answerCallbackQuery($callback['id'], "Ошибка при создании визитки");
        }
    }
    
    /**
     * Обрабатывает нажатие кнопки "Отмена"
     */
    private function handleCancelCard($callback) {
        try {
            $chat_id = $callback['message']['chat']['id'];
            
            writeToLog("Handling cancel card");
            
            $text = "✅ Хорошо, визитка не будет создана.\n\nМожете отправить другое фото для проверки.";
            
            $this->botService->sendMessage($chat_id, $text);
            
            // Отвечаем на callback
            $this->botService->answerCallbackQuery($callback['id'], "Визитка отменена");
            
        } catch (Exception $e) {
            writeToLog("Error handling cancel card: " . $e->getMessage());
            $this->botService->answerCallbackQuery($callback['id'], "Ошибка при отмене");
        }
    }
    
    /**
     * Обрабатывает обновления статуса участника чата
     */
    public function handleChatMemberUpdate($update) {
        try {
            writeToLog("MessageHandler: Processing chat member update", $update);
            
            $botService = new BotService();
            $chatMember = $update['new_chat_member'] ?? $update['old_chat_member'] ?? null;
            
            if (!$chatMember) {
                writeToLog("MessageHandler: No chat member data in update");
                return;
            }
            
            $chat_id = $update['chat']['id'];
            $user = $chatMember['user'];
            $status = $chatMember['status'];
            
            writeToLog("MessageHandler: Chat member update details", [
                'chat_id' => $chat_id,
                'user_id' => $user['id'],
                'username' => $user['username'] ?? 'Нет username',
                'first_name' => $user['first_name'] ?? 'Нет имени',
                'status' => $status
            ]);
            
            // Проверяем, что это основная группа клуба
            $mainChatId = getConfig('main_chat_id');
            if ((int)$chat_id != (int)$mainChatId) {
                writeToLog("MessageHandler: Not main chat, ignoring");
                return;
            }
            
            // Обрабатываем разные статусы
            switch ($status) {
                case 'member':
                case 'administrator':
                    // Пользователь присоединился к группе
                    $this->handleUserJoined($botService, $chat_id, $user);
                    break;
                    
                case 'left':
                case 'kicked':
                    // Пользователь покинул группу
                    $this->handleUserLeft($botService, $chat_id, $user);
                    break;
                    
                default:
                    writeToLog("MessageHandler: Unknown status", ['status' => $status]);
                    break;
            }
            
        } catch (Exception $e) {
            writeToLog("Error handling chat member update: " . $e->getMessage());
        }
    }
    
    /**
     * Обрабатывает присоединение пользователя к группе
     */
    private function handleUserJoined($botService, $chat_id, $user) {
        // Делегируем обработку отдельному классу
        require_once __DIR__ . '/events/UserJoinedHandler.php';
        $handler = new UserJoinedHandler($botService);
        // Пока joinType определяем как unknown, можно доработать по событиям
        $handler->handle($chat_id, $user, 'unknown');
    }
    /**
     * Обрабатывает выход пользователя из группы
     */
    private function handleUserLeft($botService, $chat_id, $user) {
        require_once __DIR__ . '/events/UserLeftHandler.php';
        $handler = new UserLeftHandler($botService);
        // Пока leaveType определяем как unknown, можно доработать по событиям
        $handler->handle($chat_id, $user, 'unknown');
    }
    
    /**
     * Обрабатывает новых участников в группе
     */
    private function handleNewChatMembers($botService, $message) {
        try {
            $chat_id = $message['chat']['id'];
            $newMembers = $message['new_chat_members'];
            writeToLog("MessageHandler: New chat members", [
                'chat_id' => $chat_id,
                'members_count' => count($newMembers)
            ]);
            // Проверяем, что это основная группа клуба
            $mainChatId = getConfig('main_chat_id');
            if ((int)$chat_id != (int)$mainChatId) {
                writeToLog("MessageHandler: Not main chat, ignoring new members");
                return;
            }
            foreach ($newMembers as $member) {
                // Пропускаем ботов
                if ($member['is_bot']) {
                    continue;
                }
                // Вместо отправки приветствия вызываем handleUserJoined
                $this->handleUserJoined($botService, $chat_id, $member);
            }
        } catch (Exception $e) {
            writeToLog("Error handling new chat members: " . $e->getMessage());
        }
    }
    
    /**
     * Обрабатывает участника, покинувшего группу
     */
    private function handleLeftChatMember($botService, $message) {
        $chat_id = $message['chat']['id'];
        $leftMember = $message['left_chat_member'];
        $this->handleUserLeft($botService, $chat_id, $leftMember);
    }
} 
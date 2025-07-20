<?php
/**
 * BotService.php
 * 
 * Главный сервис для работы с Telegram API
 * Отвечает за отправку сообщений, клавиатур, кнопок и других элементов интерфейса
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../utils/Logger.php';

class BotService {
    /** @var string Токен бота из конфигурации */
    private $token;
    
    /** @var array Конфигурация бота */
    private $config;
    
    /**
     * Конструктор
     */
    public function __construct() {
        writeToLog("BotService: Initializing...");
        
        $this->token = getConfig('bot_token');
        $this->config = getConfig();
        
        writeToLog("BotService: Token check", [
            'token_prefix' => substr($this->token, 0, 5) . '...',
            'is_valid' => $this->isTokenValid()
        ]);
        
        if (!$this->isTokenValid()) {
            writeToLog('CRITICAL ERROR: BOT_TOKEN not configured');
            throw new Exception('Bot token not configured');
        }
        
        writeToLog("BotService: Initialized successfully");
    }
    
    /**
     * Проверяет валидность токена
     */
    public function isTokenValid() {
        return !empty($this->token) && $this->token !== 'your_bot_token_here';
    }
    
    /**
     * Отправляет текстовое сообщение
     */
    public function sendMessage($chat_id, $text, $parse_mode = 'HTML') {
        writeToLog("BotService: Sending message", [
            'chat_id' => $chat_id,
            'text' => $text,
            'parse_mode' => $parse_mode
        ]);
        
        $result = $this->makeRequest('sendMessage', [
            'chat_id' => $chat_id,
            'text' => $text,
            'parse_mode' => $parse_mode
        ]);
        
        writeToLog("BotService: Message sent successfully");
        return $result;
    }
    
    /**
     * Отправляет сообщение с нативной клавиатурой
     */
    public function sendKeyboard($chat_id, $text, $buttons, $resize = true) {
        writeToLog("BotService: Sending keyboard message", [
            'chat_id' => $chat_id,
            'text' => $text,
            'buttons' => $buttons,
            'resize' => $resize
        ]);
        
            $keyboard = [
            'keyboard' => $buttons,
            'resize_keyboard' => $resize,
            'one_time_keyboard' => false
        ];
        
        $result = $this->makeRequest('sendMessage', [
            'chat_id' => $chat_id,
            'text' => $text,
            'parse_mode' => 'HTML',
            'reply_markup' => json_encode($keyboard)
        ]);
        
        writeToLog("BotService: Keyboard sent successfully");
        return $result;
    }
    
    /**
     * Отправляет сообщение с inline-кнопками
     */
    public function sendInlineKeyboard($chat_id, $text, $buttons) {
        writeToLog("BotService: Sending inline keyboard", [
            'chat_id' => $chat_id,
            'text' => $text,
            'buttons' => $buttons
        ]);
        
            $inline_keyboard = [
                'inline_keyboard' => $buttons
            ];
            
        $result = $this->makeRequest('sendMessage', [
                'chat_id' => $chat_id,
                'text' => $text,
                'parse_mode' => 'HTML',
                'reply_markup' => json_encode($inline_keyboard)
        ]);
        
        writeToLog("BotService: Inline keyboard sent successfully");
        return $result;
    }
    
    /**
     * Отправляет кнопку для WebApp
     */
    public function sendWebAppButton($chat_id, $text, $buttonText, $webAppUrl) {
        writeToLog("BotService: Sending WebApp button", [
            'chat_id' => $chat_id,
            'text' => $text,
            'button_text' => $buttonText,
            'web_app_url' => $webAppUrl
        ]);
        
            $inline_keyboard = [
                'inline_keyboard' => [[
                    [
                        'text' => $buttonText,
                        'web_app' => ['url' => $webAppUrl]
                    ]
                ]]
            ];
            
        $result = $this->makeRequest('sendMessage', [
                'chat_id' => $chat_id,
                'text' => $text,
                'parse_mode' => 'HTML',
                'reply_markup' => json_encode($inline_keyboard)
        ]);
        
        writeToLog("BotService: WebApp button sent successfully");
        return $result;
    }
    
    /**
     * Отправляет фото
     */
    public function sendPhoto($chat_id, $photo, $caption = '') {
        writeToLog("BotService: Sending photo", [
            'chat_id' => $chat_id,
            'photo' => $photo,
            'caption' => $caption
        ]);
        
            $data = [
                'chat_id' => $chat_id,
                'photo' => $photo
            ];
            
            if (!empty($caption)) {
                $data['caption'] = $caption;
                $data['parse_mode'] = 'HTML';
            }
            
        $result = $this->makeRequest('sendPhoto', $data);
        
        writeToLog("BotService: Photo sent successfully");
        return $result;
    }
    
    /**
     * Проверяет членство пользователя в чате
     */
    public function checkChatMember($chat_id, $user_id) {
        writeToLog("BotService: Checking chat member", [
            'chat_id' => $chat_id,
            'user_id' => $user_id
        ]);
        
        try {
            $result = $this->makeRequest('getChatMember', [
                'chat_id' => $chat_id,
                'user_id' => $user_id
            ]);
            
            writeToLog("BotService: getChatMember API response", $result);
            
            if ($result && isset($result['ok']) && $result['ok']) {
                $status = $result['result']['status'] ?? '';
                $isMember = in_array($status, ['creator', 'administrator', 'member']);
                
                writeToLog("BotService: Member check result", [
                    'status' => $status,
                    'is_member' => $isMember,
                    'raw_result' => $result
                ]);
                
                return $isMember;
            }
            
            writeToLog("BotService: Failed to get member status", $result);
            return false;
        } catch (Exception $e) {
            writeToLog("Error checking chat member: " . $e->getMessage());
            writeToLog("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }
    
    /**
     * Отправляет сообщение о необходимости вступить в чат
     * 
     * @param int $chat_id ID чата для отправки
     * @return void
     */
    public function sendNonMemberMessage($chat_id) {
        writeToLog("BotService: Sending non-member message");
        
        $text = getMessage('start.non_member');
        $buttons = [[
            ['text' => '💬 Вступить в чат', 'url' => getConfig('chat_invite_link')]
        ]];
        
        $this->sendInlineKeyboard($chat_id, $text, $buttons);
    }
    
    /**
     * Проверяет членство пользователя в чате и отправляет сообщение если не участник
     * 
     * @param int $user_id ID пользователя
     * @param int $chat_id ID чата для ответа
     * @param bool $sendMessage Отправлять ли сообщение если не участник
     * @return bool true если пользователь участник чата
     */
    public function verifyMembership($user_id, $chat_id, $sendMessage = true) {
        writeToLog("BotService: Verifying membership", [
            'user_id' => $user_id,
            'chat_id' => $chat_id
        ]);
        
        $main_chat_id = getConfig('main_chat_id');
        $isMember = $this->checkChatMember($main_chat_id, $user_id);
        
        if (!$isMember && $sendMessage) {
            $this->sendNonMemberMessage($chat_id);
        }
        
        return $isMember;
    }
    
    /**
     * Проверяет роль пользователя через API
     * 
     * @param int $user_id ID пользователя в Telegram
     * @return string Роль пользователя (пока всегда 'external' через заглушку в backend)
     */
    public function checkUserRole($user_id) {
        writeToLog("BotService: Checking user role via API", [
            'user_id' => $user_id
        ]);
        
        try {
            $api_url = getConfig('app_url') . '/api/users/check-role.php';
            
            $data = [
                'telegram_id' => $user_id
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $api_url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            writeToLog("BotService: API response", [
                'http_code' => $http_code,
                'response' => $response
            ]);
            
            if ($http_code === 200) {
                $result = json_decode($response, true);
                if (isset($result['role'])) {
                    return $result['role'];
                }
            }
            
            writeToLog("BotService: Failed to get user role", [
                'http_code' => $http_code,
                'response' => $response
            ]);
            
            // В случае ошибки API возвращаем external
            return 'external';
            
        } catch (Exception $e) {
            writeToLog("Error checking user role: " . $e->getMessage());
            // В случае ошибки возвращаем external
            return 'external';
        }
    }
    
    /**
     * Отправляет сообщение с кнопкой регистрации
     * 
     * @param int $chat_id ID чата
     * @param string $name Имя пользователя
     */
    public function sendRegistrationMessage($chat_id, $name) {
        writeToLog("BotService: Sending registration message");
        
        $text = sprintf(
            "Привет, %s! 👋\n\n" .
            "Для использования бота нужно зарегистрироваться в приложении CabrioRide.",
            htmlspecialchars($name)
        );
        
        $buttons = [[
            [
                'text' => '📝 Зарегистрироваться',
                'web_app' => ['url' => getWebAppUrl('/registration')]
            ]
        ]];
        
        $this->sendInlineKeyboard($chat_id, $text, $buttons);
    }
    
    /**
     * Отправляет приветственное сообщение с кнопкой входа в приложение
     * 
     * @param int $chat_id ID чата
     * @param string $name Имя пользователя
     * @param string $role Роль пользователя
     */
    public function sendWelcomeMessage($chat_id, $name, $role) {
        writeToLog("BotService: Sending welcome message", [
            'role' => $role
        ]);
        
        $text = sprintf(
            "С возвращением, %s! 👋\n\n" .
            "Вы можете открыть приложение CabrioRide, нажав кнопку ниже.",
            htmlspecialchars($name)
        );
        
        $buttons = [[
            [
                'text' => '🌐 Открыть приложение',
                'web_app' => ['url' => getWebAppUrl()]
            ]
        ]];
        
        $this->sendInlineKeyboard($chat_id, $text, $buttons);
    }
    
    /**
     * Получает информацию о файле через Telegram API
     * 
     * @param string $file_id ID файла
     * @return array|false Информация о файле или false при ошибке
     */
    public function getFile($file_id) {
        writeToLog("BotService: Getting file info", [
            'file_id' => $file_id
        ]);
        
        try {
            $response = $this->makeRequest('getFile', [
                'file_id' => $file_id
            ]);
            
            if ($response['ok']) {
                return $response['result'];
            }
            
            writeToLog("BotService: Failed to get file info", [
                'response' => $response
            ]);
            return false;
            
        } catch (Exception $e) {
            writeToLog("Error getting file: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Скачивает файл из Telegram
     * 
     * @param string $file_path Путь к файлу (из getFile)
     * @return string|false Путь к скачанному файлу или false при ошибке
     */
    public function downloadFile($file_path) {
        writeToLog("BotService: Downloading file", [
            'file_path' => $file_path
        ]);
        
        try {
            $url = "https://api.telegram.org/file/bot" . getConfig('bot_token') . "/" . $file_path;
            
            // Создаем временный файл
            $temp_path = tempnam(sys_get_temp_dir(), 'tg_');
            if ($temp_path === false) {
                writeToLog("BotService: Failed to create temp file");
                return false;
            }
            
            // Скачиваем файл
            $fp = fopen($temp_path, 'w');
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            
            curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            curl_close($ch);
            fclose($fp);
            
            if ($http_code === 200) {
                writeToLog("BotService: File downloaded successfully", [
                    'temp_path' => $temp_path
                ]);
                return $temp_path;
            }
            
            unlink($temp_path);
            writeToLog("BotService: Failed to download file", [
                'http_code' => $http_code
            ]);
            return false;
            
        } catch (Exception $e) {
            writeToLog("Error downloading file: " . $e->getMessage());
            if (isset($temp_path)) {
                unlink($temp_path);
            }
            return false;
        }
    }
    
    /**
     * Делает запрос к Telegram API
     */
    private function makeRequest($method, $data = []) {
        writeToLog("BotService: Making request", [
            'method' => $method,
            'data' => $data
        ]);
        
        $url = "https://api.telegram.org/bot{$this->token}/{$method}";
        
        writeToLog("BotService: API URL", [
            'url' => preg_replace('/bot[^\/]+/', 'bot***', $url)
        ]);
        
        $options = [
            'http' => [
                'header' => "Content-Type: application/json\r\n",
                'method' => 'POST',
                'content' => json_encode($data),
                'ignore_errors' => true
            ]
        ];
        
        writeToLog("BotService: Request options", [
            'headers' => $options['http']['header'],
            'method' => $options['http']['method'],
            'content' => $data
        ]);
        
        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        
        if ($response === false) {
            $error = error_get_last();
            writeToLog("Telegram API request failed", [
                'method' => $method,
                'error' => $error['message'] ?? 'Unknown error',
                'response' => $response
            ]);
            throw new Exception("Failed to make request to Telegram API: {$method}");
        }
        
        $result = json_decode($response, true);
        
        writeToLog("BotService: API response", [
            'method' => $method,
            'response' => $result
        ]);
        
        if (!isset($result['ok']) || $result['ok'] !== true) {
            $errorMsg = $result['description'] ?? 'Unknown error';
            writeToLog("Telegram API error", [
                'method' => $method,
                'error' => $errorMsg,
                'response' => $result
            ]);
            throw new Exception("Telegram API error: {$errorMsg}");
        }
        
        writeToLog("BotService: Request successful", [
            'method' => $method,
            'result' => $result
        ]);
        
        return $result;
    }
    
    /**
     * Отвечает на callback query (убирает "часики" у кнопки)
     * 
     * @param string $callback_query_id ID callback query
     * @param string $text Текст ответа (опционально)
     * @param bool $show_alert Показывать ли alert (опционально)
     */
    public function answerCallbackQuery($callback_query_id, $text = '', $show_alert = false) {
        writeToLog("BotService: Answering callback query", [
            'callback_query_id' => $callback_query_id,
            'text' => $text,
            'show_alert' => $show_alert
        ]);
        
        try {
            $data = [
                'callback_query_id' => $callback_query_id
            ];
            
            if (!empty($text)) {
                $data['text'] = $text;
            }
            
            if ($show_alert) {
                $data['show_alert'] = true;
            }
            
            $result = $this->makeRequest('answerCallbackQuery', $data);
            
            writeToLog("BotService: Callback query answered successfully");
            return $result;
            
        } catch (Exception $e) {
            writeToLog("Error answering callback query: " . $e->getMessage());
            return false;
        }
    }
}

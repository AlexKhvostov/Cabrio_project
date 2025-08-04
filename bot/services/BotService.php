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
        
        $this->token = $_ENV['BOT_TOKEN'] ?? null;
        
        writeToLog("BotService: Token check", [
            'token_prefix' => $this->token ? substr($this->token, 0, 5) . '...' : 'null',
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
     * Отправляет сообщение с WebApp кнопкой
     */
    public function sendWebAppButton($chat_id, $text, $buttonText, $webAppUrl) {
        writeToLog("BotService: Sending WebApp button", [
            'chat_id' => $chat_id,
            'text' => $text,
            'button_text' => $buttonText,
            'web_app_url' => $webAppUrl
        ]);
        
        $inline_keyboard = [
            'inline_keyboard' => [
                [
                    [
                        'text' => $buttonText,
                        'web_app' => ['url' => $webAppUrl]
                    ]
                ]
            ]
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
            return false;
        }
    }
    
    /**
     * Отправляет сообщение о необходимости вступить в чат
     */
    public function sendNonMemberMessage($chat_id) {
        writeToLog("BotService: Sending non-member message");
        
        $text = "❌ Для использования бота необходимо вступить в клубный чат.";
        $buttons = [[
            ['text' => '💬 Вступить в чат', 'url' => $_ENV['CHAT_INVITE_LINK'] ?? 'https://t.me/+r4avCK_b3v5iZmFi']
        ]];
        
        $this->sendInlineKeyboard($chat_id, $text, $buttons);
    }
    
    /**
     * Делает запрос к Telegram API
     */
    public function makeRequest($method, $data = []) {
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
     * Выполняет запрос к backend API
     * 
     * @param string $endpoint Эндпоинт API
     * @param array $data Данные для отправки
     * @param array $userData Данные пользователя для авторизации
     * @return array Результат запроса
     */
    public function callBackendApi($endpoint, $data, $userData) {
        try {
            $apiUrl = $_ENV['BACKEND_API_URL'] ?? 'http://localhost/app/backend';
            $url = $apiUrl . '/routes/api.php?route=' . urlencode($endpoint);
            
            writeToLog("BotService: Calling backend API", [
                'endpoint' => $endpoint,
                'url' => $url,
                'user_id' => $userData['id'] ?? 'unknown'
            ]);
            
            // Создаем заголовки с Telegram данными
            $headers = [
                'Content-Type: application/json',
                'X-Telegram-User-Id: ' . ($userData['id'] ?? ''),
                'X-Telegram-Username: ' . ($userData['username'] ?? ''),
                'X-Telegram-First-Name: ' . ($userData['first_name'] ?? ''),
                'X-Telegram-Last-Name: ' . ($userData['last_name'] ?? '')
            ];
            
            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => implode("\r\n", $headers),
                    'content' => json_encode($data),
                    'timeout' => 30
                ]
            ]);
            
            $response = file_get_contents($url, false, $context);
            $httpCode = $http_response_header[0] ?? 'Unknown';
            
            writeToLog("BotService: API response received", [
                'http_code' => $httpCode,
                'response_length' => strlen($response)
            ]);
            
            if ($response === false) {
                throw new Exception("Failed to make API request");
            }
            
            $result = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Invalid JSON response: " . json_last_error_msg());
            }
            
            return [
                'success' => strpos($httpCode, '200') !== false,
                'http_code' => $httpCode,
                'data' => $result,
                'raw_response' => $response
            ];
            
        } catch (Exception $e) {
            writeToLog("BotService: API call failed", [
                'endpoint' => $endpoint,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'http_code' => 'ERROR'
            ];
        }
    }
} 
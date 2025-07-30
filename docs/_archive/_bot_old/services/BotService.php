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
                // Подробное логирование статуса
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
        
        $club_chat_id = getConfig('club_chat_id');
        $isMember = $this->checkChatMember($club_chat_id, $user_id);
        
        if (!$isMember && $sendMessage) {
            $this->sendNonMemberMessage($chat_id);
        }
        
        return $isMember;
    }
    
    /**
     * Проверяет роль пользователя через API
     * 
     * @param int $user_id ID пользователя в Telegram
     * @return string Роль пользователя
     */
    public function checkUserRole($user_id) {
        writeToLog("BotService: Checking user role via API", [
            'user_id' => $user_id
        ]);
        
        try {
            $api_url = getApiUrl() . '/users/profile.php';
            
            // Формируем запрос согласно новому API стандарту
            $request_data = [
                'auth' => [
                    'user_id' => $user_id,
                    'role' => 'guest' // Временно используем guest для проверки
                ],
                'data' => [
                    'telegram_id' => $user_id
                ]
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $api_url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request_data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            writeToLog("BotService: API response", [
                'http_code' => $http_code,
                'response' => $response
            ]);
            
            if ($http_code === 200) {
                $result = json_decode($response, true);
                if ($result && isset($result['success']) && $result['success']) {
                    // Пользователь найден, возвращаем его роль
                    if (isset($result['result']['data']['role'])) {
                        return $result['result']['data']['role'];
                    }
                } else if ($result && isset($result['success']) && !$result['success']) {
                    // Пользователь не найден, возвращаем external
                    if (isset($result['error']['code']) && $result['error']['code'] === 404) {
                        return 'external';
                    }
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
     * Универсальный вызов backend API (POST JSON)
     * @param string $endpoint относительный путь (например, /backend/api/users/add.php)
     * @param array $payload ассоциативный массив данных
     * @return array|null
     */
    public function callBackendApi($endpoint, $payload) {
        $api_url = getApiUrl() . $endpoint;
        writeToLog("BotService: callBackendApi", [
            'url' => $api_url,
            'payload' => $payload
        ]);
        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        writeToLog("BotService: callBackendApi response", [
            'http_code' => $http_code,
            'response' => $response
        ]);
        if ($response) {
            return json_decode($response, true);
        }
        return null;
    }
    
    /**
     * Синхронизирует профиль Telegram-пользователя с backend
     * Вызывает /users/add.php с telegram_requestor_profile (создаёт или обновляет профиль)
     * @param array $user — массив с данными Telegram-пользователя (id, username, first_name, last_name, photo_id, language_code и др.)
     * @param int|null $chat_id — id чата для отправки сообщения, если пользователь не в клубе
     * @return array|null — данные пользователя из backend или null
     */
    public function syncTelegramRequestorProfile($user) {
        // Получаем file_id аватарки пользователя через getUserProfilePhotos
        $photo_id = null;
        try {
            $photos = $this->makeRequest('getUserProfilePhotos', [
                'user_id' => $user['id'],
                'limit' => 1
            ]);
            if ($photos && $photos['ok'] && !empty($photos['result']['photos'][0])) {
                $photo = end($photos['result']['photos'][0]);
                $photo_id = $photo['file_id'];
            }
        } catch (Exception $e) {
            writeToLog('syncTelegramRequestorProfile: ошибка получения аватарки', [
                'user_id' => $user['id'],
                'error' => $e->getMessage()
            ]);
        }
        $telegramProfile = [
            'telegram_id' => $user['id'],
            'username' => $user['username'] ?? null,
            'first_name' => $user['first_name'] ?? null,
            'last_name' => $user['last_name'] ?? null,
            'telegram_photo_id' => $photo_id,
            'language_code' => $user['language_code'] ?? null
        ];
        $payload = [
            'auth' => [],
            'data' => [
                'telegram_requestor_profile' => $telegramProfile
            ]
        ];
        writeToLog('syncTelegramRequestorProfile: payload для backend', $payload);
        $resp = $this->callBackendApi('/users/add.php', $payload);
        writeToLog('syncTelegramRequestorProfile: ответ backend', $resp);
        $result = $resp['result']['data'] ?? null;
        
        // Используем строку роли из backend (role)
        if ($result && isset($result['role'])) {
            $result['role_string'] = $result['role'];
            writeToLog('syncTelegramRequestorProfile: роль из backend', [
                'role_id' => $result['role_id'] ?? null,
                'role_string' => $result['role_string']
            ]);
        }
        
        if ($result && isset($result['updated_at'])) {
            writeToLog('syncTelegramRequestorProfile: профиль обновлён', [
                'user_id' => $user['id'],
                'updated_at' => $result['updated_at']
            ]);
        }
        return $result;
    }
    
    /**
     * Возвращаемый объект (userSyncResult):
     * {
     *   user_id: int,              // внутренний ID пользователя в базе
     *   telegram_id: int,          // Telegram ID пользователя
     *   role_id: int,              // числовой код роли (например, 2)
     *   role: string,              // строковый код роли (например, 'new', 'member', 'admin')
     *   role_string: string,       // строковый код роли (дублирует role для совместимости)
     *   created: bool,             // был ли создан новый пользователь
     *   updated_at: string,        // дата/время последнего обновления профиля
     *   ... (дополнительные поля по необходимости)
     * }
     */
    /**
     * Универсальный запуск обработчика с синхронизацией профиля Telegram-пользователя
     * @param object $handler — экземпляр обработчика с методом execute($message, $userSyncResult)
     * @param array $message — Telegram update message
     */
    public function handleWithProfileSync($handler, $message) {
        $user = $message['from'];
        $chat_id = $message['chat']['id'] ?? null;
        $chat_type = $message['chat']['type'] ?? null;
        $club_chat_id = getConfig('club_chat_id');
        $club_chat_name = getConfig('club_chat_name');
        
        // 1. Синхронизируем профиль (только обновление)
        $userSyncResult = $this->syncTelegramRequestorProfile($user);
        
        // 2. Проверка по типу чата
        if ($chat_type === 'group' || $chat_type === 'supergroup') {
            if ($chat_id == $club_chat_id) {
                // Всё ок, вызываем обработчик
                $handler->execute($message, $userSyncResult);
                return;
            } else {
                $this->sendMessage($chat_id, "❌ Бот работает только в клубном чате: @$club_chat_name");
                return;
            }
        }
        if ($chat_type === 'private') {
            // Проверяем членство пользователя в клубном чате
            $isMember = $this->checkChatMember($club_chat_id, $user['id']);
            if ($isMember) {
                $handler->execute($message, $userSyncResult);
                return;
            } else {
                // Получаем внутренний user_id из базы
                $userId = $userSyncResult['user_id'] ?? null;
                if ($userId) {
                    $this->callBackendApi('/users/set_role.php', [
                        'auth' => [
                            'user_id' => $userId,
                            'role' => 'admin'
                        ],
                        'data' => [
                            'user_id' => $userId,
                            'new_role_code' => 'external',
                            'reason' => 'Пользователь не состоит в клубном чате'
                        ]
                    ]);
                }
                $this->sendNonMemberMessage($chat_id);
                return;
            }
        }
        // Если тип чата неизвестен — ничего не делаем
    }
    
    /**
     * Переводит пользователя из роли guest в роль new
     * @param int $user_id — внутренний ID пользователя
     * @param string $currentRole — исходная строковая роль
     * @return string — новая роль ('new' при успехе, иначе исходная)
     */
    public function promoteGuestToNew($user_id, $currentRole = 'guest') {
        writeToLog('BotService: promoteGuestToNew — попытка смены роли', [
            'user_id' => $user_id,
            'currentRole' => $currentRole
        ]);
        $payload = [
            'auth' => [
                'user_id' => 1,
                'role' => 'admin' // Для смены роли требуется высокая роль
            ],
            'data' => [
                'user_id' => $user_id,
                'new_role_code' => 'new',
                'reason' => 'Отправил фото в клубный чат'
            ]
        ];
        $result = $this->callBackendApi('/users/set_role.php', $payload);
        writeToLog('BotService: promoteGuestToNew — результат', [
            'user_id' => $user_id,
            'result' => $result
        ]);
        if ($result['success'] ?? false) {
            return 'new';
        }
        return $currentRole;
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

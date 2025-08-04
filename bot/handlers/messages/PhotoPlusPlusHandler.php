<?php
/**
 * PhotoPlusPlusHandler.php
 * 
 * Обработчик фото с комментарием "++"
 * Использует __AddCarToUserAction для добавления автомобиля пользователю
 */

require_once __DIR__ . '/../../utils/Logger.php';

class PhotoPlusPlusHandler {
    /** @var BotService */
    private $botService;
    
    /**
     * Конструктор
     */
    public function __construct($botService) {
        $this->botService = $botService;
    }
    
    /**
     * Обрабатывает фото с комментарием "++"
     * 
     * @param array $message Данные сообщения
     */
    public function handle($message) {
        try {
            $chat = $message['chat'];
            $user = $message['from'];
            $photo = $message['photo'];
            $caption = $message['caption'] ?? '';
            
            writeToLog("PhotoPlusPlusHandler: Processing photo with '++' comment", [
                'chat_id' => $chat['id'],
                'user_id' => $user['id'],
                'username' => $user['username'] ?? 'unknown',
                'first_name' => $user['first_name'] ?? 'unknown',
                'caption' => $caption,
                'photo_count' => count($photo)
            ]);
            
            // Проверяем, что это клубный чат
            $club_chat_id = $_ENV['CLUB_CHAT_ID'] ?? '-1002873258290';
            if ($chat['id'] != $club_chat_id) {
                writeToLog("PhotoPlusPlusHandler: Not club chat, ignoring");
                return;
            }
            
            // Проверяем, что комментарий именно "++"
            if (trim($caption) !== '++') {
                writeToLog("PhotoPlusPlusHandler: Caption is not '++', ignoring");
                return;
            }
            
            // Вызываем реальный API эндпоинт
            $this->processAddCarToGarage($chat['id'], $user, $photo);
            
            writeToLog("PhotoPlusPlusHandler: Photo with '++' processed successfully", [
                'user_id' => $user['id']
            ]);
            
        } catch (Exception $e) {
            writeToLog("PhotoPlusPlusHandler: Error processing photo with '++' - " . $e->getMessage());
        }
    }
    
    /**
     * Отправляет сообщение о добавлении автомобиля пользователю
     * 
     * @param int $chatId ID чата
     * @param array $user Данные пользователя
     */
    private function sendAddCarToUserMessage($chatId, $user) {
        $username = $user['first_name'] ?? $user['username'] ?? 'Участник';
        
        $message = "🚗 <b>Добавление автомобиля</b>\n\n";
        $message .= "Привет, <b>$username</b>! 👋\n\n";
        $message .= "📸 Вы отправили фото с комментарием \"++\"\n";
        $message .= "🔧 Используется эндпоинт: <code>__AddCarToUserAction</code>\n\n";
        $message .= "🚗 <b>Что происходит:</b>\n";
        $message .= "• Анализируем фото автомобиля\n";
        $message .= "• Добавляем автомобиль в ваш гараж\n";
        $message .= "• Обновляем профиль пользователя\n\n";
        $message .= "⏳ Обработка в разработке...";
        
        $this->botService->sendMessage($chatId, $message);
    }
    
    /**
     * Обрабатывает добавление автомобиля в гараж через API
     * 
     * @param int $chatId ID чата
     * @param array $user Данные пользователя
     * @param array $photo Данные фото
     */
    private function processAddCarToGarage($chatId, $user, $photo) {
        try {
            $username = $user['first_name'] ?? $user['username'] ?? 'Участник';
            
            // Отправляем начальное сообщение
            $initialMessage = "🚗 <b>Добавляю авто...</b>";
            
            $this->botService->sendMessage($chatId, $initialMessage);
            
            // Получаем фото в base64
            $photoData = end($photo);
            $photoId = $photoData['file_id'];
            
            // Скачиваем фото и конвертируем в base64
            $photoBase64 = $this->downloadAndConvertPhoto($photoId);
            
            if (!$photoBase64) {
                $this->sendErrorMessage($chatId, "Не удалось загрузить фото");
                return;
            }
            
            // Подготавливаем данные для API
            $apiData = [
                'photo' => $photoBase64,
                'user_id' => $user['id']
            ];
            
            // Вызываем API
            $result = $this->botService->callBackendApi('/api/actions/add-car-to-garage', $apiData, $user);
            
            // Логируем сырой ответ от сервера
            writeToLog("PhotoPlusPlusHandler: RAW backend response", [
                'raw_response' => json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            ]);
            
            // Проверяем успешность операции по данным ответа, а не по HTTP коду
            if (isset($result['data']['success']) && $result['data']['success']) {
                $this->sendSuccessMessage($chatId, $user, $result['data']);
            } else {
                // Получаем ошибку из данных ответа
                $errorData = $result['data'] ?? [];
                $errorCode = $errorData['error']['code'] ?? 'UNKNOWN_ERROR';
                $errorMsg = $errorData['error']['message'] ?? 'Неизвестная ошибка';
                
                writeToLog("PhotoPlusPlusHandler: Backend error", [
                    'error_code' => $errorCode,
                    'error_message' => $errorMsg,
                    'user_id' => $user['id']
                ]);
                
                $this->sendErrorMessage($chatId, $errorMsg);
            }
            
        } catch (Exception $e) {
            writeToLog("PhotoPlusPlusHandler: Error in processAddCarToGarage", [
                'error' => $e->getMessage(),
                'user_id' => $user['id']
            ]);
            
            $this->sendErrorMessage($chatId, "Ошибка обработки: " . $e->getMessage());
        }
    }
    
    /**
     * Скачивает фото и конвертирует в base64
     */
    private function downloadAndConvertPhoto($fileId) {
        try {
            $fileInfo = $this->botService->makeRequest('getFile', ['file_id' => $fileId]);
            
            if (!$fileInfo || !isset($fileInfo['result']['file_path'])) {
                writeToLog("PhotoPlusPlusHandler: Failed to get file info", ['file_id' => $fileId]);
                return false;
            }
            
            $filePath = $fileInfo['result']['file_path'];
            $token = $_ENV['BOT_TOKEN'];
            $fileUrl = "https://api.telegram.org/file/bot{$token}/{$filePath}";
            
            $fileContent = file_get_contents($fileUrl);
            if ($fileContent === false) {
                writeToLog("PhotoPlusPlusHandler: Failed to download file", ['url' => $fileUrl]);
                return false;
            }
            
            $base64 = base64_encode($fileContent);
            
            writeToLog("PhotoPlusPlusHandler: Photo converted to base64", [
                'file_id' => $fileId,
                'size' => strlen($base64)
            ]);
            
            return $base64;
            
        } catch (Exception $e) {
            writeToLog("PhotoPlusPlusHandler: Error downloading photo", [
                'file_id' => $fileId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Отправляет сообщение об успешном результате
     */
    private function sendSuccessMessage($chatId, $user, $data) {
        // Извлекаем данные из вложенной структуры ответа
        $carData = $data['data'] ?? $data;
        
        $message = "✅ <b>Автомобиль добавлен</b>\n\n";
        
        // Номер автомобиля
        $plateNumber = strtoupper($carData['plate_number'] ?? 'НЕ РАСПОЗНАН');
        $message .= "🔢 <b>Номер авто:</b> <code>$plateNumber</code>\n\n";
        
        // Статус автомобиля
        $carStatus = $carData['status']['name'] ?? 'Неизвестен';
        $carStatusDescription = $carData['status']['description'] ?? 'Нет описания';
        $message .= "📊 <b>Статус авто:</b> $carStatus ($carStatusDescription)\n\n";
        
        // Владелец - формируем кликабельный ник
        $ownerDisplay = $user['username'] ? '@' . $user['username'] : ($user['first_name'] ?? 'Участник');
        $message .= "👤 <b>Владелец:</b> $ownerDisplay\n\n";
        
        // Сообщение от сервера
        $serverMessage = $carData['message'] ?? 'Автомобиль добавлен в гараж';
        $message .= "🎯 <b>Результат:</b> $serverMessage";
        
        $this->botService->sendMessage($chatId, $message);
    }
    
    /**
     * Отправляет сообщение об ошибке
     */
    private function sendErrorMessage($chatId, $errorMsg) {
        $message = "❌ <b>Ошибка добавления авто</b>\n\n";
        
        // Специальные сообщения для разных типов ошибок
        if (strpos($errorMsg, 'уже добавлен в ваш гараж') !== false || strpos($errorMsg, 'уже в вашем гараже') !== false) {
            $message .= "✅ <b>Автомобиль уже принадлежит вам!</b>\n\n";
        //  $message .= "Этот автомобиль уже добавлен в ваш гараж.\n";
            $message .= "Вы можете просмотреть его в своем профиле.";
        } elseif (strpos($errorMsg, 'принадлежит другому участнику') !== false) {
            $message .= "🚫 <b>Автомобиль принадлежит другому участнику!</b>\n\n";
        //  $message .= "Этот автомобиль уже зарегистрирован другим участником клуба.\n";
            $message .= "Если вы считаете, что это ошибка, обратитесь к администратору.";
        } elseif (strpos($errorMsg, 'уже имеет владельца') !== false) {
            $message .= "🚫 <b>Автомобиль уже зарегистрирован!</b>\n\n";
        //  $message .= "Этот автомобиль уже принадлежит другому участнику клуба.\n";
            $message .= "Если вы считаете, что это ошибка, обратитесь к администратору.";
        } else {
            $message .= "😔 $errorMsg";
        }
        
        $this->botService->sendMessage($chatId, $message);
    }
} 
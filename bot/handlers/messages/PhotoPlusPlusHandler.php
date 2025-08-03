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
            $initialMessage = "🚗 <b>Добавление автомобиля</b>\n\n";
            $initialMessage .= "Привет, <b>$username</b>! 👋\n\n";
            $initialMessage .= "📸 Анализируем фото автомобиля...\n";
            $initialMessage .= "⏳ Добавляем в ваш гараж...";
            
            $this->botService->sendMessage($chatId, $initialMessage);
            
            // Получаем фото в base64
            $photoData = end($photo);
            $photoId = $photoData['file_id'];
            
            // Скачиваем фото и конвертируем в base64
            $photoBase64 = $this->downloadAndConvertPhoto($photoId);
            
            if (!$photoBase64) {
                $this->sendErrorMessage($chatId, $username, "Не удалось загрузить фото");
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
            
            if ($result['success']) {
                $this->sendSuccessMessage($chatId, $username, $result['data']);
            } else {
                $errorMsg = $result['data']['error']['message'] ?? 'Неизвестная ошибка';
                $this->sendErrorMessage($chatId, $username, $errorMsg);
            }
            
        } catch (Exception $e) {
            writeToLog("PhotoPlusPlusHandler: Error in processAddCarToGarage", [
                'error' => $e->getMessage(),
                'user_id' => $user['id']
            ]);
            
            $this->sendErrorMessage($chatId, $username ?? 'Участник', "Ошибка обработки: " . $e->getMessage());
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
    private function sendSuccessMessage($chatId, $username, $data) {
        // Извлекаем данные из вложенной структуры ответа
        $carData = $data['data'] ?? $data;
        
        $message = "✅ <b>Автомобиль добавлен!</b>\n\n";
        $message .= "Привет, <b>$username</b>! 👋\n\n";
        
        // Номер автомобиля
        $plateNumber = strtoupper($carData['plate_number'] ?? 'НЕ РАСПОЗНАН');
        $message .= "🔢 <b>Номер:</b> <code>$plateNumber</code>\n\n";
        
        // Сообщение от сервера
        $serverMessage = $carData['message'] ?? 'Автомобиль добавлен в гараж';
        $message .= "🚗 <b>Результат:</b> $serverMessage\n\n";
        
        // Действие
        $action = $carData['action'] ?? 'unknown';
        $actionText = '';
        switch ($action) {
            case 'assigned':
                $actionText = "Автомобиль назначен вам и добавлен в гараж";
                break;
            case 'created':
                $actionText = "Автомобиль создан и добавлен в ваш гараж";
                break;
            default:
                $actionText = "Автомобиль добавлен в гараж";
        }
        $message .= "🎯 <b>Действие:</b> $actionText";
        
        $this->botService->sendMessage($chatId, $message);
    }
    
    /**
     * Отправляет сообщение об ошибке
     */
    private function sendErrorMessage($chatId, $username, $errorMsg) {
        $message = "❌ <b>Ошибка добавления автомобиля</b>\n\n";
        $message .= "Привет, <b>$username</b>! 👋\n\n";
        $message .= "😔 К сожалению, произошла ошибка:\n";
        $message .= "• $errorMsg\n\n";
        $message .= "🔄 Попробуйте еще раз или обратитесь к администратору";
        
        $this->botService->sendMessage($chatId, $message);
    }
} 
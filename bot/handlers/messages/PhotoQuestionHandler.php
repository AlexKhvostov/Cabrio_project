<?php
/**
 * PhotoQuestionHandler.php
 * 
 * Обработчик фото с комментарием "?"
 * Использует __SearchCarAction для поиска автомобиля
 */

require_once __DIR__ . '/../../utils/Logger.php';

class PhotoQuestionHandler {
    /** @var BotService */
    private $botService;
    
    /**
     * Конструктор
     */
    public function __construct($botService) {
        $this->botService = $botService;
    }
    
    /**
     * Обрабатывает фото с комментарием "?"
     * 
     * @param array $message Данные сообщения
     */
    public function handle($message) {
        try {
            $chat = $message['chat'];
            $user = $message['from'];
            $photo = $message['photo'];
            $caption = $message['caption'] ?? '';
            
            writeToLog("PhotoQuestionHandler: Processing photo with '?' comment", [
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
                writeToLog("PhotoQuestionHandler: Not club chat, ignoring");
                return;
            }
            
            // Проверяем, что комментарий именно "?"
            if (trim($caption) !== '?') {
                writeToLog("PhotoQuestionHandler: Caption is not '?', ignoring");
                return;
            }
            
            // Вызываем реальный API эндпоинт
            $this->processCarSearch($chat['id'], $user, $photo);
            
            writeToLog("PhotoQuestionHandler: Photo with '?' processed successfully", [
                'user_id' => $user['id']
            ]);
            
        } catch (Exception $e) {
            writeToLog("PhotoQuestionHandler: Error processing photo with '?' - " . $e->getMessage());
        }
    }
    
    /**
     * Отправляет сообщение о поиске автомобиля
     * 
     * @param int $chatId ID чата
     * @param array $user Данные пользователя
     */
    private function sendSearchCarMessage($chatId, $user) {
        $username = $user['first_name'] ?? $user['username'] ?? 'Участник';
        
        $message = "🔍 <b>Поиск автомобиля</b>\n\n";
        $message .= "Привет, <b>$username</b>! 👋\n\n";
        $message .= "📸 Вы отправили фото с комментарием \"?\"\n";
        $message .= "🔧 Используется эндпоинт: <code>__SearchCarAction</code>\n\n";
        $message .= "🚗 <b>Что происходит:</b>\n";
        $message .= "• Анализируем фото автомобиля\n";
        $message .= "• Ищем похожие в базе данных\n";
        $message .= "• Возвращаем результаты поиска\n\n";
        $message .= "⏳ Обработка в разработке...";
        
        $this->botService->sendMessage($chatId, $message);
    }
    
    /**
     * Обрабатывает поиск автомобиля через API
     * 
     * @param int $chatId ID чата
     * @param array $user Данные пользователя
     * @param array $photo Данные фото
     */
    private function processCarSearch($chatId, $user, $photo) {
        try {
            $username = $user['first_name'] ?? $user['username'] ?? 'Участник';
            
            // Отправляем начальное сообщение
            $initialMessage = "🔍 <b>Проверяю авто...</b>";
            
   //         $this->botService->sendMessage($chatId, $initialMessage);
            
            // Получаем фото в base64 (берем самое большое фото)
            $photoData = end($photo); // Последний элемент - самое большое фото
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
            $result = $this->botService->callBackendApi('/api/actions/check-car-in-club', $apiData, $user);
            
            // Логируем сырой ответ от сервера
            writeToLog("PhotoQuestionHandler: RAW backend response", [
                'raw_response' => json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            ]);
            
            // Проверяем HTTP код и содержимое ответа
            if (!empty($result['success']) && $result['http_code'] === 200) {
                $apiData = $result['data'] ?? [];
                $this->sendSuccessMessage($chatId, $username, $apiData);
            } else {
                // HTTP ошибка или невалидный ответ
                if ($result['http_code'] === 403) {
                    $errorMsg = 'Недостаточно прав для выполнения действия';
                } elseif ($result['http_code'] === 404) {
                    $errorMsg = 'Эндпоинт не найден';
                } elseif ($result['http_code'] === 500) {
                    $errorMsg = 'Внутренняя ошибка сервера';
                } else {
                    $errorMsg = $result['error']['message'] ?? ($result['data']['error']['message'] ?? 'Неизвестная ошибка');
                }
                $this->sendErrorMessage($chatId, $errorMsg);
            }
            
        } catch (Exception $e) {
            writeToLog("PhotoQuestionHandler: Error in processCarSearch", [
                'error' => $e->getMessage(),
                'user_id' => $user['id']
            ]);
            
            $this->sendErrorMessage($chatId, "Ошибка обработки: " . $e->getMessage());
        }
    }
    
    /**
     * Скачивает фото и конвертирует в base64
     * 
     * @param string $fileId ID файла в Telegram
     * @return string|false Base64 данные или false при ошибке
     */
    private function downloadAndConvertPhoto($fileId) {
        try {
            // Получаем информацию о файле
            $fileInfo = $this->botService->makeRequest('getFile', ['file_id' => $fileId]);
            
            if (!$fileInfo || !isset($fileInfo['result']['file_path'])) {
                writeToLog("PhotoQuestionHandler: Failed to get file info", ['file_id' => $fileId]);
                return false;
            }
            
            $filePath = $fileInfo['result']['file_path'];
            $token = $_ENV['BOT_TOKEN'];
            $fileUrl = "https://api.telegram.org/file/bot{$token}/{$filePath}";
            
            // Скачиваем файл
            $fileContent = file_get_contents($fileUrl);
            if ($fileContent === false) {
                writeToLog("PhotoQuestionHandler: Failed to download file", ['url' => $fileUrl]);
                return false;
            }
            
            // Конвертируем в base64
            $base64 = base64_encode($fileContent);
            
            writeToLog("PhotoQuestionHandler: Photo converted to base64", [
                'file_id' => $fileId,
                'size' => strlen($base64)
            ]);
            
            return $base64;
            
        } catch (Exception $e) {
            writeToLog("PhotoQuestionHandler: Error downloading photo", [
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
        
        $message = "✅ <b>Результат проверки</b>\n\n";
        
        // 1. Номер автомобиля (в верхнем регистре)
        $plateNumber = strtoupper($carData['plate_number'] ?? 'НЕ РАСПОЗНАН');
        $message .= "🔢 <b>Номер авто:</b> <code>$plateNumber</code>\n\n";
        
        // 2. Статус автомобиля в базе
        $carStatus = $carData['status']['name'] ?? 'Неизвестен';
        $carStatusDescription = $carData['status']['description'] ?? 'Нет описания';
        $message .= "📊 <b>Статус авто:</b> $carStatus ($carStatusDescription)\n\n";
        
        // 3. Результат выполнения
        $serverMessage = $carData['message'] ?? 'Проверка выполнена';
        $message .= "🎯 <b>Результат:</b> $serverMessage";
        
        $this->botService->sendMessage($chatId, $message);
    }
    
    /**
     * Отправляет сообщение об ошибке
     */
    private function sendErrorMessage($chatId, $errorMsg) {
        $message = "❌ <b>Ошибка проверки</b>\n\n";
        $message .= "😔 $errorMsg";
        
        $this->botService->sendMessage($chatId, $message);
    }
} 
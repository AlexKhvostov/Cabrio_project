<?php
/**
 * OcrCommand.php
 * 
 * Команда для распознавания номера авто по фото и проверки в БД
 * Использует объединенный эндпоинт /api/ocr/process.php
 */

require_once __DIR__ . '/../utils/Logger.php';

class OcrCommand {
    /** @var BotService */
    private $botService;
    
    /**
     * Конструктор
     */
    public function __construct($botService) {
        $this->botService = $botService;
    }
    
    /**
     * Обрабатывает фото для распознавания номера
     */
    public function execute($message) {
        try {
            $chat_id = $message['chat']['id'];
            $user = $message['from'];
            
            // Проверяем членство в чате
            if (!$this->botService->verifyMembership($user['id'], $chat_id)) {
                return;
            }
            
            // Проверяем наличие фото
            if (!isset($message['photo'])) {
                $this->botService->sendMessage($chat_id, 
                    "⚠️ Пожалуйста, отправьте фотографию номера автомобиля."
                );
                return;
            }
            
            // Берем последнее (самое большое) фото
            $photo = end($message['photo']);
            $file_id = $photo['file_id'];
            
            // Получаем файл через Telegram API
            $file_info = $this->botService->getFile($file_id);
            if (!$file_info) {
                $this->botService->sendMessage($chat_id, 
                    "❌ Не удалось получить фото. Попробуйте еще раз."
                );
                return;
            }
            
            // Скачиваем фото
            $photo_path = $this->botService->downloadFile($file_info['file_path']);
            if (!$photo_path) {
                $this->botService->sendMessage($chat_id, 
                    "❌ Не удалось скачать фото. Попробуйте еще раз."
                );
                return;
            }
            
            // Отправляем на обработку через объединенный эндпоинт
            $result = $this->processPlate($photo_path);
            
            // Обрабатываем результат
            $this->handlePrivateResult($chat_id, $user, $result);
            
        } catch (Exception $e) {
            writeToLog("Error in OcrCommand: " . $e->getMessage());
            $this->botService->sendMessage($chat_id, 
                "❌ Произошла ошибка при обработке фото. Попробуйте позже."
            );
        }
    }
    
    /**
     * Обрабатывает фото с текстом "?" в групповом чате
     */
    public function executeGroupPhoto($message) {
        try {
            $chat_id = $message['chat']['id'];
            $user = $message['from'];
            
            // Проверяем, что это групповой чат
            if ($message['chat']['type'] === 'private') {
                return; // Игнорируем в личных сообщениях
            }
            
            // Проверяем членство в чате
            if (!$this->botService->verifyMembership($user['id'], $chat_id)) {
                return;
            }
            
            // Проверяем наличие фото
            if (!isset($message['photo'])) {
                return; // Игнорируем, если нет фото
            }
            
            // Берем последнее (самое большое) фото
            $photo = end($message['photo']);
            $file_id = $photo['file_id'];
            
            // Получаем файл через Telegram API
            $file_info = $this->botService->getFile($file_id);
            if (!$file_info) {
                $this->botService->sendMessage($chat_id, 
                    "❌ Не удалось получить фото. Попробуйте еще раз."
                );
                return;
            }
            
            // Скачиваем фото
            $photo_path = $this->botService->downloadFile($file_info['file_path']);
            if (!$photo_path) {
                $this->botService->sendMessage($chat_id, 
                    "❌ Не удалось скачать фото. Попробуйте еще раз."
                );
                return;
            }
            
            // Отправляем на обработку через объединенный эндпоинт
            $result = $this->processPlate($photo_path);
            
            // Обрабатываем результат для группового чата
            $this->handleGroupResult($chat_id, $user, $result);
            
        } catch (Exception $e) {
            writeToLog("Error in OcrCommand group photo: " . $e->getMessage());
            $this->botService->sendMessage($chat_id, 
                "❌ Произошла ошибка при обработке фото. Попробуйте позже."
            );
        }
    }
    
    /**
     * Отправляет фото на обработку через объединенный эндпоинт
     */
    private function processPlate($photo_path) {
        try {
            writeToLog("OcrCommand: Sending photo to process API", [
                'photo_path' => $photo_path,
                'file_exists' => file_exists($photo_path),
                'file_size' => file_exists($photo_path) ? filesize($photo_path) : 0
            ]);
            
            $api_url = getApiUrl() . '/backend/api/ocr/process.php';
            writeToLog("OcrCommand: API URL", ['url' => $api_url]);
            
            // Проверяем существование файла
            if (!file_exists($photo_path)) {
                writeToLog("OcrCommand: Photo file not found");
                return ['success' => false, 'error' => 'Файл не найден'];
            }
            
            // Готовим файл для отправки
            $file = new CURLFile($photo_path);
            $data = ['image' => $file];
            
            // Отправляем запрос
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $api_url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Увеличиваем таймаут
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_error = curl_error($ch);
            
            curl_close($ch);
            
            writeToLog("OcrCommand: API Response", [
                'http_code' => $http_code,
                'response' => $response,
                'curl_error' => $curl_error
            ]);
            
            if ($http_code === 200) {
                $result = json_decode($response, true);
                if ($result && isset($result['success'])) {
                    return $result;
                }
            }
            
            writeToLog("OcrCommand: Request failed", [
                'http_code' => $http_code,
                'curl_error' => $curl_error
            ]);
            
            return ['success' => false, 'error' => 'Ошибка API'];
            
        } catch (Exception $e) {
            writeToLog("Error processing plate: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        } finally {
            // Удаляем временный файл
            if (file_exists($photo_path)) {
                unlink($photo_path);
            }
        }
    }
    
    /**
     * Обрабатывает результат от API
     */
    private function handleResult($chat_id, $user, $result) {
        if (!$result['success']) {
            $this->botService->sendMessage($chat_id,
                "❌ Не удалось обработать фото. Попробуйте еще раз."
            );
            return;
        }
        
        // Проверяем успешность OCR
        if (!$result['ocr_success']) {
            $this->botService->sendMessage($chat_id,
                "❌ Не удалось распознать номер на фото.\n\n" .
                "💡 Советы для лучшего распознавания:\n" .
                "• Сделайте четкое фото номера\n" .
                "• Номер должен занимать значительную часть кадра\n" .
                "• Избегайте бликов и теней\n" .
                "• Убедитесь, что номер читаем"
            );
            return;
        }
        
        // Номер распознан успешно
        $plate = $result['plate'];
        
        // Формируем структурированный ответ
        $text = "Распознан номер\n";
        $text .= "🚗 " . $plate . "\n";
        
        if ($result['found']) {
            // Автомобиль найден в базе
            $status = $result['status'] ?? 'Неизвестно';
            $text .= "✅ " . $status;
        } else {
            // Автомобиль не найден в базе
            $text .= "🚫 Нет в клубе";
        }
        
        // Добавляем вопрос о визитке
        $text .= "\n\n💼 Оставляешь визитку?";
        
        // Создаем кнопки
        $buttons = [[
            [
                'text' => '✅ Да',
                'callback_data' => 'leave_card_' . $plate
            ],
            [
                'text' => '❌ Нет',
                'callback_data' => 'cancel_card'
            ]
        ]];
        
        $this->botService->sendInlineKeyboard($chat_id, $text, $buttons);
    }
    
    /**
     * Обрабатывает результат от API для группового чата (БЕЗ вопроса о визитке)
     */
    private function handleGroupResult($chat_id, $user, $result) {
        if (!$result['success']) {
            $this->botService->sendMessage($chat_id,
                "❌ Не удалось обработать фото. Попробуйте еще раз."
            );
            return;
        }
        
        // Проверяем успешность OCR
        if (!$result['ocr_success']) {
            $this->botService->sendMessage($chat_id,
                "❌ Не удалось распознать номер на фото.\n\n" .
                "💡 Советы для лучшего распознавания:\n" .
                "• Сделайте четкое фото номера\n" .
                "• Номер должен занимать значительную часть кадра\n" .
                "• Избегайте бликов и теней\n" .
                "• Убедитесь, что номер читаем"
            );
            return;
        }
        
        // Номер распознан успешно
        $plate = $result['plate'];
        
        // Формируем структурированный ответ для группы (БЕЗ визитки)
        $text = "Распознан номер\n";
        $text .= "🚗 " . $plate . "\n";
        
        if ($result['found']) {
            // Автомобиль найден в базе
            $status = $result['status'] ?? 'Неизвестно';
            $text .= "✅ " . $status;
        } else {
            // Автомобиль не найден в базе
            $text .= "🚫 Нет в клубе";
        }
        
        $this->botService->sendMessage($chat_id, $text);
    }
    
    /**
     * Обрабатывает результат от API для личных сообщений (С вопросом о визитке)
     */
    private function handlePrivateResult($chat_id, $user, $result) {
        if (!$result['success']) {
            $this->botService->sendMessage($chat_id, "❌ Не удалось обработать фото. Попробуйте еще раз.");
            return;
        }
        if (!$result['ocr_success']) {
            $this->botService->sendMessage($chat_id,
                "❌ Не удалось распознать номер на фото.\n\n" .
                "💡 Советы для лучшего распознавания:\n" .
                "• Сделайте четкое фото номера\n" .
                "• Номер должен занимать значительную часть кадра\n" .
                "• Избегайте бликов и теней\n" .
                "• Убедитесь, что номер читаем"
            );
            return;
        }
        $plate = $result['plate'];
        $text = "Распознан номер\n";
        $text .= "🚗 " . $plate . "\n";
        if ($result['found']) {
            $status = $result['status'] ?? 'Неизвестно';
            $text .= "✅ " . $status;
        } else {
            $text .= "🚫 Нет в клубе";
        }
        $text .= "\n\n💼 Оставляешь визитку?";
        $buttons = [[
            ['text' => '✅ Да', 'callback_data' => 'leave_card_' . $plate],
            ['text' => '❌ Нет', 'callback_data' => 'cancel_card']
        ]];
        $this->botService->sendInlineKeyboard($chat_id, $text, $buttons);
    }
} 
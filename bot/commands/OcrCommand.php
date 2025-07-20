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
            
            // Отправляем на обработку через новый эндпоинт
            $result = $this->processPlate($photo_path, $user);
            
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
                return; // Игнорируем ошибки в групповом чате
            }
            
            // Скачиваем фото
            $photo_path = $this->botService->downloadFile($file_info['file_path']);
            if (!$photo_path) {
                return; // Игнорируем ошибки в групповом чате
            }
            
            // Отправляем на обработку через новый эндпоинт
            $result = $this->processPlate($photo_path, $user);
            
            // Обрабатываем результат для группового чата
            $this->handleGroupResult($chat_id, $user, $result);
            
        } catch (Exception $e) {
            writeToLog("Error in OcrCommand group photo: " . $e->getMessage());
            // В групповом чате не отправляем ошибки пользователю
        }
    }
    
    /**
     * Обрабатывает фото через новый API стандарт
     */
    private function processPlate($photo_path, $user) {
        try {
            writeToLog("OcrCommand: Sending photo to recognize API", [
                'photo_path' => $photo_path,
                'file_exists' => file_exists($photo_path),
                'file_size' => file_exists($photo_path) ? filesize($photo_path) : 0
            ]);
            
            $api_url = getApiUrl() . '/backend/api/ocr/recognize.php';
            writeToLog("OcrCommand: API URL", ['url' => $api_url]);
            
            // Проверяем существование файла
            if (!file_exists($photo_path)) {
                writeToLog("OcrCommand: Photo file not found");
                return ['success' => false, 'error' => 'Файл не найден'];
            }
            
            // Конвертируем фото в base64
            $image_data = file_get_contents($photo_path);
            $base64_image = 'data:image/jpeg;base64,' . base64_encode($image_data);
            
            // Формируем запрос согласно новому API стандарту
            $request_data = [
                'auth' => [
                    'user_id' => $user['id'],
                    'role' => 'guest' // Временно используем guest для OCR
                ],
                'data' => [
                    'image' => $base64_image
                ]
            ];
            
            // Отправляем запрос
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $api_url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request_data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);
            
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
                    // Если распознавание успешно, проверяем номер в БД
                    if ($result['success'] && isset($result['result']['data']['plate'])) {
                        $plate_number = $result['result']['data']['plate'];
                        return $this->checkPlateInDatabase($plate_number, $user);
                    }
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
     * Проверяет номер в базе данных
     */
    private function checkPlateInDatabase($plate_number, $user) {
        try {
            $api_url = getApiUrl() . '/backend/api/ocr/check.php';
            
            // Формируем запрос согласно новому API стандарту
            $request_data = [
                'auth' => [
                    'user_id' => $user['id'],
                    'role' => 'guest'
                ],
                'data' => [
                    'plate' => $plate_number // Используем правильное название поля
                ]
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $api_url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request_data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            writeToLog("OcrCommand: Check API Response", [
                'http_code' => $http_code,
                'response' => $response
            ]);
            
            if ($http_code === 200) {
                $result = json_decode($response, true);
                if ($result && isset($result['success'])) {
                    // Добавляем информацию о распознанном номере
                    $result['plate_number'] = $plate_number;
                    return $result;
                }
            }
            
            return ['success' => false, 'error' => 'Ошибка проверки номера'];
            
        } catch (Exception $e) {
            writeToLog("Error checking plate: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
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
        if (!isset($result['result']['data']['plate'])) {
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
        $plate = $result['result']['data']['plate'];
        
        // Формируем структурированный ответ
        $text = "Распознан номер\n";
        $text .= "🚗 " . $plate . "\n";
        
        if (isset($result['result']['data']['found']) && $result['result']['data']['found']) {
            // Автомобиль найден в базе
            $text .= "\n✅ Автомобиль найден в базе клуба!\n";
            $text .= "📋 Статус: " . ($result['result']['data']['status'] ?? 'Активный') . "\n";
            
            if (isset($result['result']['data']['owner_info'])) {
                $text .= "👤 Владелец: " . $result['result']['data']['owner_info'] . "\n";
            }
        } else {
            // Автомобиль не найден
            $text .= "\n❌ Автомобиль не найден в базе клуба.\n";
            $text .= "💡 Возможно, владелец еще не зарегистрировался в приложении.";
        }
        
        $this->botService->sendMessage($chat_id, $text);
    }
    
    /**
     * Обрабатывает результат для группового чата
     */
    private function handleGroupResult($chat_id, $user, $result) {
        if (!$result['success']) {
            return; // В групповом чате не показываем ошибки
        }
        
        // Проверяем успешность OCR
        if (!isset($result['result']['data']['plate'])) {
            return; // В групповом чате не показываем ошибки распознавания
        }
        
        // Номер распознан успешно
        $plate = $result['result']['data']['plate'];
        
        // Формируем краткий ответ для группового чата
        $text = "🔍 " . $plate;
        
        if (isset($result['result']['data']['found']) && $result['result']['data']['found']) {
            $text .= " ✅ В базе";
        } else {
            $text .= " ❌ Не найден";
        }
        
        $this->botService->sendMessage($chat_id, $text);
    }
    
    /**
     * Обрабатывает результат для личного чата
     */
    private function handlePrivateResult($chat_id, $user, $result) {
        if (!$result['success']) {
            $this->botService->sendMessage($chat_id,
                "❌ Не удалось обработать фото. Попробуйте еще раз."
            );
            return;
        }
        
        // Проверяем успешность OCR
        if (!isset($result['result']['data']['plate'])) {
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
        $plate = $result['result']['data']['plate'];
        
        // Формируем структурированный ответ
        $text = "🔍 Результат распознавания:\n\n";
        $text .= "🚗 Номер: " . $plate . "\n";
        
        if (isset($result['result']['data']['found']) && $result['result']['data']['found']) {
            // Автомобиль найден в базе
            $text .= "\n✅ Автомобиль найден в базе клуба!\n";
            $text .= "📋 Статус: " . ($result['result']['data']['status'] ?? 'Активный') . "\n";
            
            if (isset($result['result']['data']['owner_info'])) {
                $text .= "👤 Владелец: " . $result['result']['data']['owner_info'] . "\n";
            }
        } else {
            // Автомобиль не найден
            $text .= "\n❌ Автомобиль не найден в базе клуба.\n";
            $text .= "💡 Возможно, владелец еще не зарегистрировался в приложении.";
        }
        
        $this->botService->sendMessage($chat_id, $text);
    }
} 
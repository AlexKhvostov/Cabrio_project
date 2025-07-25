<?php
/**
 * OcrCommand.php
 * 
 * Команда для распознавания номера авто по фото и проверки в БД
 * Использует объединенный эндпоинт /api/ocr/process.php
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
     * Универсальный публичный метод: делегирует обработку по типу чата
     */
    public function execute($message, $userSyncResult = null) {
        $chatType = $message['chat']['type'] ?? null;
        if ($chatType === 'private') {
            $this->executePrivate($message, $userSyncResult);
        } elseif ($chatType === 'group' || $chatType === 'supergroup') {
            $this->executeGroupPhoto($message, $userSyncResult);
        }
        // Для других типов чатов ничего не делаем
    }

    /**
     * Обрабатывает фото для распознавания номера в личном чате
     */
    public function executePrivate($message, $userSyncResult = null) {
        try {
            $chat_id = $message['chat']['id'];
            $user = $message['from'];
            // --- Синхронизируем профиль Telegram-пользователя с backend (создаём или обновляем) ---
            $role = $userSyncResult['role'] ?? 'external';
            if ($role === 'external') {
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
            writeToLog("Error in PhotoQuestionHandler: " . $e->getMessage());
            $this->botService->sendMessage($chat_id, 
                "❌ Произошла ошибка при обработке фото. Попробуйте позже."
            );
        }
    }
    
    /**
     * Обрабатывает фото с текстом "?" в групповом чате
     */
    public function executeGroupPhoto($message, $userSyncResult = null) {
        writeToLog('PhotoQuestionHandler: executeGroupPhoto called', $message);
        try {
            $chat_id = $message['chat']['id'];
            $user = $message['from'];
            // --- Синхронизируем профиль Telegram-пользователя с backend (создаём или обновляем) ---
            $role = $userSyncResult['role'] ?? 'external';
            if ($role === 'external') {
                return;
            }
            
            // Проверяем, что это групповой чат
            if ($message['chat']['type'] === 'private') {
                return; // Игнорируем в личных сообщениях
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
            $plate_result = $this->processPlate($photo_path, $user);
            $result = is_array($plate_result) && isset($plate_result['result']) ? $plate_result['result'] : $plate_result;
            $base64_image = is_array($plate_result) && isset($plate_result['base64_image']) ? $plate_result['base64_image'] : null;
            
            // Обрабатываем результат для группового чата
            $this->handleGroupResult($chat_id, $user, $result, $base64_image);
            
        } catch (Exception $e) {
            writeToLog("Error in PhotoQuestionHandler group photo: " . $e->getMessage());
            // В групповом чате не отправляем ошибки пользователю
        }
    }
    
    /**
     * Обрабатывает фото через новый API стандарт
     */
    private function processPlate($photo_path, $user) {
        try {
            writeToLog("PhotoQuestionHandler: Sending photo to recognize API", [
                'photo_path' => $photo_path,
                'file_exists' => file_exists($photo_path),
                'file_size' => file_exists($photo_path) ? filesize($photo_path) : 0
            ]);
            
            $api_url = getApiUrl() . '/ocr/recognize.php';
            writeToLog("PhotoQuestionHandler: API URL", ['url' => $api_url]);
            
            // Проверяем существование файла
            if (!file_exists($photo_path)) {
                writeToLog("PhotoQuestionHandler: Photo file not found");
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
            
            writeToLog("PhotoQuestionHandler: API Response", [
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
                        $check_result = $this->checkPlateInDatabase($plate_number, $user);
                        return [ 'result' => $check_result, 'base64_image' => $base64_image ];
                    }
                    return [ 'result' => $result, 'base64_image' => $base64_image ];
                }
            }
            
            writeToLog("PhotoQuestionHandler: Request failed", [
                'http_code' => $http_code,
                'curl_error' => $curl_error
            ]);
            
            return [ 'result' => ['success' => false, 'error' => 'Ошибка API'], 'base64_image' => $base64_image ];
            
        } catch (Exception $e) {
            writeToLog("Error processing plate: " . $e->getMessage());
            return [ 'result' => ['success' => false, 'error' => $e->getMessage()], 'base64_image' => null ];
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
            $api_url = getApiUrl() . '/ocr/check.php';
            
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
            
            writeToLog("PhotoQuestionHandler: Check API Response", [
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
     * @param int $chat_id
     * @param array $user
     * @param array $result — результат работы processPlate (ответ от backend)
     * @param string|null $base64_image — base64 фото для возможного добавления авто
     */
    private function handleGroupResult($chat_id, $user, $result, $base64_image = null) {
        // Логируем результат для диагностики
        writeToLog('PhotoQuestionHandler: handleGroupResult result', $result);
        // В ответе от backend данные лежат глубоко: result['result']['result']['data']
        $data = $result['result']['result']['data'] ?? null;
        $success = $result['result']['success'] ?? false;
        // Проверяем успешность запроса к backend
        if (!$success) {
            return; // В групповом чате не показываем ошибки
        }
        // Проверяем, что номер распознан
        if (!$data || !isset($data['plate'])) {
            writeToLog('PhotoQuestionHandler: handleGroupResult no plate', $result);
            return; // В групповом чате не показываем ошибки распознавания
        }
        // Формируем ответ для чата
        $plate = strtoupper($data['plate']);
        $text = "Распознан номер\n";
        $text .= "🚗 " . $plate . "\n";
        // Логируем структуру $data и значение found
        writeToLog('PhotoQuestionHandler: handleGroupResult $data', $data);
        writeToLog('PhotoQuestionHandler: handleGroupResult $data[found]', [
            'found' => $data['found'] ?? null,
            'isset_found' => isset($data['found']),
            'empty_found' => empty($data['found'])
        ]);
        if (isset($data['found']) && $data['found']) {
            // Автомобиль найден в базе
            $text .= "В базе клуба\n";
            $text .= "📋 Статус: " . ($data['status'] ?? '??');
        } else {
            // Логируем вход в ветку else (добавление авто)
            writeToLog('PhotoQuestionHandler: ДО добавления авто, ветка else', [
                'data' => $data,
                'plate' => $plate
            ]);
            $short_base64 = $base64_image ? (substr($base64_image, 0, 20) . '... [base64]') : '[нет фото]';
            $payload = [
                'auth' => [ 'user_id' => 1, 'role' => 'admin' ],
                'data' => [
                    'reg_number' => $plate,
                    'status_code' => 'noticed',
                    'no_owner' => true,
                    'photo' => $base64_image,
                    'source' => 'bot_photo_question'
                ]
            ];
            writeToLog('PhotoQuestionHandler: вызываем добавление авто noticed', [
                'plate' => $plate,
                'user_id' => $user['id'],
                'base64_image' => $short_base64,
                'payload' => $payload
            ]);
            $add_car_result = $this->botService->callBackendApi('/cars/add.php', $payload);
            // Логируем результат добавления авто
            writeToLog('PhotoQuestionHandler: ПОСЛЕ добавления авто', [
                'add_car_result' => $add_car_result
            ]);
            writeToLog('PhotoQuestionHandler: результат добавления авто с статусом noticed', [
                'payload' => [
                    'reg_number' => $plate,
                    'base64_image' => $short_base64
                ],
                'result' => $add_car_result
            ]);
            // Универсальная проверка success
            $add_success = false;
            if (is_array($add_car_result)) {
                if (array_key_exists('success', $add_car_result)) {
                    $add_success = $add_car_result['success'];
                } elseif (isset($add_car_result['result']['success'])) {
                    $add_success = $add_car_result['result']['success'];
                }
            }
            if ($add_success) {
                $text .= "\n🟡 Пометил в базу со статусом \"замечен\"";
            } else {
                // Если есть error — показать пользователю
                if (is_array($add_car_result) && isset($add_car_result['error']['message'])) {
                    writeToLog('PhotoQuestionHandler: return из else по ошибке add_car_result', $add_car_result);
                    $this->botService->sendMessage($chat_id, "❌ " . $add_car_result['error']['message']);
                    return;
                } else {
                    writeToLog('PhotoQuestionHandler: add_car_result не содержит success или не массив, return из else', $add_car_result);
                    return;
                }
            }
        }
        // Перед отправкой сообщения логируем текст
        writeToLog('PhotoQuestionHandler: перед отправкой сообщения в чат', ['text' => $text]);
        $this->botService->sendMessage($chat_id, $text);
    }
    
    /**
     * Обрабатывает результат для личного чата
     * @param int $chat_id
     * @param array $user
     * @param array $result — результат работы processPlate (ответ от backend)
     */
    private function handlePrivateResult($chat_id, $user, $result) {
        // Логируем результат для диагностики
        writeToLog('PhotoQuestionHandler: handlePrivateResult result', $result);
        // В ответе от backend данные лежат глубоко: result['result']['result']['data']
        $data = $result['result']['result']['data'] ?? null;
        $success = $result['result']['success'] ?? false;
        // Проверяем успешность запроса к backend
        if (!$success) {
            $this->botService->sendMessage($chat_id,
                "❌ Не удалось обработать фото. Попробуйте еще раз."
            );
            return;
        }
        // Проверяем, что номер распознан
        if (!$data || !isset($data['plate'])) {
            writeToLog('PhotoQuestionHandler: handlePrivateResult no plate', $result);
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
        // Формируем ответ для пользователя
        $plate = strtoupper($data['plate']);
        $text = "🔍 Результат распознавания:\n\n";
        $text .= "🚗 Номер: " . $plate . "\n";
        if (isset($data['found']) && $data['found']) {
            // Автомобиль найден в базе
            $text .= "\n✅ Автомобиль найден в базе клуба!\n";
            $text .= "📋 Статус: " . ($data['status'] ?? 'Активный') . "\n";
            if (isset($data['owner_info'])) {
                $text .= "👤 Владелец: " . $data['owner_info'] . "\n";
            }
        } else {
            // Автомобиль не найден
            $text .= "\n❌ Автомобиль не найден в базе клуба.\n";
            $text .= "💡 Возможно, владелец еще не зарегистрировался в приложении.";
        }
        $this->botService->sendMessage($chat_id, $text);
    }
} 
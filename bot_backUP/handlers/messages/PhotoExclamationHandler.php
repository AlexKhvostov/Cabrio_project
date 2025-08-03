<?php
/**
 * PhotoExclamationHandler.php
 * 
 * Обработчик для оставления визитки по фото с подписью "!" в групповом чате
 * Использует новый backend API с развернутыми данными
 */

require_once __DIR__ . '/../../utils/Logger.php';

class PhotoExclamationHandler {
    /** @var BotService */
    private $botService;

    public function __construct($botService) {
        $this->botService = $botService;
    }

    /**
     * Основной метод: обрабатывает фото с "!" в группе
     */
    public function execute($message, $userSyncResult = null) {
        try {
            $chat_id = $message['chat']['id'];
            $user = $message['from'];
            
            // Проверяем, что это групповой чат
            $chatType = $message['chat']['type'] ?? null;
            if ($chatType !== 'group' && $chatType !== 'supergroup') {
                writeToLog('PhotoExclamationHandler: не групповой чат, игнорируем');
                return;
            }
            
            // Проверяем роль пользователя
            $role = $userSyncResult['role'] ?? 'external';
            if ($role === 'external') {
                writeToLog('PhotoExclamationHandler: пользователь не в клубе, игнорируем');
                return;
            }
            
            // Проверяем наличие фото
            if (!isset($message['photo'])) {
                $this->botService->sendMessage($chat_id, "⚠️ Пожалуйста, отправьте фотографию номера автомобиля.");
                return;
            }
            
            // Берем последнее (самое большое) фото
            $photo = end($message['photo']);
            $file_id = $photo['file_id'];
            
            // Получаем файл через Telegram API
            $file_info = $this->botService->getFile($file_id);
            if (!$file_info) {
                $this->botService->sendMessage($chat_id, "❌ Не удалось получить фото. Попробуйте ещё раз.");
                return;
            }
            
            // Скачиваем фото
            $photo_path = $this->botService->downloadFile($file_info['file_path']);
            if (!$photo_path) {
                $this->botService->sendMessage($chat_id, "❌ Не удалось скачать фото. Попробуйте ещё раз.");
                return;
            }
            
            // Конвертируем фото в base64
            $image_data = file_get_contents($photo_path);
            $base64_image = 'data:image/jpeg;base64,' . base64_encode($image_data);
            
            // Отправляем на обработку через новый API
            $result = $this->processPhotoWithNewAPI($base64_image, $userSyncResult);
            
            // Обрабатываем результат
            $this->handleResult($chat_id, $result);
            
            // Удаляем временный файл
            if (file_exists($photo_path)) {
                unlink($photo_path);
            }
            
        } catch (Exception $e) {
            writeToLog("Error in PhotoExclamationHandler: " . $e->getMessage());
            $this->botService->sendMessage($chat_id, "❌ Произошла ошибка при обработке фото. Попробуйте позже.");
        }
    }

    /**
     * Обрабатывает фото через новый API
     */
    private function processPhotoWithNewAPI($base64_image, $userSyncResult) {
        try {
            // Получаем токен для пользователя
            $token = $this->botService->getUserToken($userSyncResult);
            if (!$token) {
                writeToLog('PhotoExclamationHandler: не удалось получить токен');
                return ['success' => false, 'error' => 'Ошибка авторизации'];
            }
            
            $api_url = getApiUrl() . '/api/actions/leave-business-card';
            
            // Формируем запрос для нового API
            $request_data = [
                'photo' => $base64_image
            ];
            
            writeToLog('PhotoExclamationHandler: отправка запроса на новый API', [
                'url' => $api_url,
                'image_length' => strlen($base64_image)
            ]);
            
            // Отправляем запрос
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $api_url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request_data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json'
            ]);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_error = curl_error($ch);
            curl_close($ch);
            
            writeToLog('PhotoExclamationHandler: ответ от нового API', [
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
            
            return ['success' => false, 'error' => 'Ошибка API'];
            
        } catch (Exception $e) {
            writeToLog("Error processing photo with new API: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Обрабатывает результат от нового API
     */
    private function handleResult($chat_id, $result) {
        if (!$result['success']) {
            $this->botService->sendMessage($chat_id, 
                "❌ Не удалось оставить визитку. Попробуйте ещё раз."
            );
            return;
        }
        
        $data = $result['data'];
        $action = $data['action'] ?? 'unknown';
        $plate_number = $data['plate_number'] ?? 'Неизвестен';
        
        // Формируем ответ
        $text = "📋 Оставление визитки:\n\n";
        $text .= "🚗 Номер: $plate_number\n";
        
        switch ($action) {
            case 'card_left':
                // Визитка оставлена успешно
                $businessCard = $data['business_card'];
                $car = $data['car'];
                $status = $car['status']['name'] ?? 'Неизвестен';
                
                $text .= "\n✅ Визитка оставлена успешно!";
                $text .= "\n📋 Статус авто: $status";
                
                if (isset($businessCard['message'])) {
                    $text .= "\n💬 Сообщение: " . $businessCard['message'];
                }
                break;
                
            case 'car_not_found':
                // Автомобиль не найден
                $text .= "\n❌ Автомобиль не найден в базе клуба.";
                $text .= "\n💡 Сначала добавьте автомобиль в базу.";
                break;
                
            case 'already_has_card':
                // У автомобиля уже есть визитка
                $text .= "\n⚠️ У этого автомобиля уже есть визитка.";
                $text .= "\n💡 Используйте другое действие для обновления.";
                break;
                
            default:
                $text .= "\n❓ Неизвестный результат обработки.";
                break;
        }
        
        $this->botService->sendMessage($chat_id, $text);
    }
} 
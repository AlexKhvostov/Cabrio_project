<?php
/**
 * GroupPhotoCheckHandler.php
 * 
 * Обработчик для распознавания номера авто по фото с подписью "?" ТОЛЬКО в групповом чате.
 * Если авто не найдено — добавляет его в базу со статусом "замечен".
 */

require_once __DIR__ . '/../../utils/Logger.php';

class GroupPhotoCheckHandler {
    /** @var BotService */
    private $botService;

    public function __construct($botService) {
        $this->botService = $botService;
    }

    /**
     * Основной метод: обрабатывает фото с "?" в группе
     */
    public function execute($message, $userSyncResult = null) {
        try {
            $chat_id = $message['chat']['id'];
            $user = $message['from'];
            // Проверяем, что это групповой чат
            $chatType = $message['chat']['type'] ?? null;
            $user_id = $userSyncResult['user_id'] ?? null;
            $role = $userSyncResult['role'] ?? null;
            // Если роль guest — переводим в new
            if ($user_id && $role === 'guest') {
                $role = $this->botService->promoteGuestToNew($user_id, $role);
            }
            if ($chatType !== 'group' && $chatType !== 'supergroup') {
                writeToLog('GroupPhotoCheckHandler: не групповой чат, игнорируем');
                return;
            }
            // Проверяем наличие фото
            if (!isset($message['photo'])) {
                $this->botService->sendMessage($chat_id, "⚠️ Пожалуйста, отправьте фотографию номера автомобиля.");
                return;
            }
            // Берём последнее (самое большое) фото
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
            // Формируем base64 сразу после скачивания
            $image_data = file_get_contents($photo_path);
            $base64_image = 'data:image/jpeg;base64,' . base64_encode($image_data);
            // Отправляем фото на распознавание
            $ocr_result = $this->processPlate($photo_path, $user);
            // Проверяем результат
            if (!$ocr_result['success'] || !isset($ocr_result['result']['data']['plate'])) {
                $this->botService->sendMessage($chat_id, "❌ Не удалось распознать номер на фото.");
                if (file_exists($photo_path)) unlink($photo_path);
                return;
            }
            $plate = strtoupper($ocr_result['result']['data']['plate']);
            // Проверяем наличие авто в базе
            $check_result = $this->checkPlateInDatabase($plate, $user);
            $found = $check_result['result']['data']['found'] ?? false;
            $text = "Распознан номер\n🚗 $plate\n";
            if ($found) {
                $text .= "\n✅ Автомобиль найден в базе клуба!";
                $text .= "\n📋 Статус: " . ($check_result['result']['data']['status'] ?? 'Неизвестен');
            } else {
                $text .= "\n❌ Автомобиль не найден в базе клуба.";
                // Добавляем авто в базу со статусом "замечен"
                $add_result = $this->addNoticedCar($plate, $user, $base64_image, $userSyncResult);
                if ($add_result['success']) {
                    $text .= "\n🟡 Пометил в базу со статусом \"замечен\"";
                } else {
                    $text .= "\n⚠️ Не удалось добавить авто в базу.";
                }
            }
            $this->botService->sendMessage($chat_id, $text);
            // Удаляем временный файл только после всех операций
            if (file_exists($photo_path)) unlink($photo_path);
        } catch (Exception $e) {
            writeToLog("Error in GroupPhotoCheckHandler: " . $e->getMessage());
            $this->botService->sendMessage($chat_id, "❌ Произошла ошибка при обработке фото. Попробуйте позже.");
        }
    }

    /**
     * Отправляет фото на распознавание номера
     */
    private function processPlate($photo_path, $user) {
        try {
            $api_url = getApiUrl() . '/ocr/recognize.php';
            $image_data = file_get_contents($photo_path);
            $base64_image = 'data:image/jpeg;base64,' . base64_encode($image_data);
            $request_data = [
                'auth' => [
                    'user_id' => $user['id'],
                    'role' => 'guest'
                ],
                'data' => [
                    'image' => $base64_image
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
            writeToLog('GroupPhotoCheckHandler: ответ от ocr/recognize.php', [
                'http_code' => $http_code,
                'response' => $response
            ]);
            if ($http_code === 200) {
                $result = json_decode($response, true);
                if ($result && isset($result['success'])) {
                    return $result;
                }
            }
            return ['success' => false, 'error' => 'Ошибка API'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        } finally {
            if (file_exists($photo_path)) {
                unlink($photo_path);
            }
        }
    }

    /**
     * Проверяет номер в базе данных
     */
    private function checkPlateInDatabase($plate, $user) {
        try {
            $api_url = getApiUrl() . '/ocr/check.php';
            $request_data = [
                'auth' => [
                    'user_id' => $user['id'],
                    'role' => 'guest'
                ],
                'data' => [
                    'plate' => $plate
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
            writeToLog('GroupPhotoCheckHandler: ответ от ocr/check.php', [
                'http_code' => $http_code,
                'response' => $response
            ]);
            if ($http_code === 200) {
                $result = json_decode($response, true);
                if ($result && isset($result['success'])) {
                    return $result;
                }
            }
            return ['success' => false, 'error' => 'Ошибка проверки номера'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Добавляет авто в базу со статусом "замечен"
     */
    private function addNoticedCar($plate, $user, $base64_image, $userSyncResult = null) {
        try {
            $api_url = getApiUrl() . '/cars/add.php';
            // Получаем реальные user_id и роль из userSyncResult
            $user_id = $userSyncResult['user_id'] ?? null;
            $role = $userSyncResult['role'] ?? null; // Используем строковую роль
            if (!$user_id || !$role) {
                writeToLog('GroupPhotoCheckHandler: нет user_id или role для auth при добавлении авто', [
                    'userSyncResult' => $userSyncResult
                ]);
                return ['success' => false, 'error' => 'Нет данных пользователя для auth'];
            }
            writeToLog('GroupPhotoCheckHandler: base64 для фото', [
                'length' => strlen($base64_image),
                'first_40' => substr($base64_image, 0, 40)
            ]);
            $payload = [
                'auth' => [ 'user_id' => $user_id, 'role' => $role ],
                'data' => [
                    'reg_number' => $plate,
                    'status_code' => 'noticed',
                    'no_owner' => true,
                    'photo' => $base64_image,
                    'source' => 'bot_group_photo_check'
                ]
            ];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $api_url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            writeToLog('GroupPhotoCheckHandler: ответ от cars/add.php', [
                'http_code' => $http_code,
                'response' => $response
            ]);
            if ($http_code === 200) {
                $result = json_decode($response, true);
                if ($result && isset($result['success'])) {
                    return $result;
                }
            }
            return ['success' => false, 'error' => 'Ошибка добавления авто'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
} 
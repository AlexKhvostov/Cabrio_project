<?php
/**
 * LeaveBusinessCardCommand.php
 * Команда для добавления визитки по фото с '!' в групповом чате CabrioRide
 * Вызывает orchestrator /api/business-cards/add_full.php
 * Теперь бот сам распознаёт номер через OCR!
 */

require_once __DIR__ . '/../../utils/Logger.php';

class PhotoExclamationHandler {
    private $botService;
    public function __construct($botService) {
        $this->botService = $botService;
    }

    /**
     * Основной обработчик команды
     * @param array $message — сообщение Telegram
     */
    public function execute($message, $userSyncResult = null) {
        try {
            $chat_id = $message['chat']['id'];
            $user = $message['from'];
            // --- Синхронизируем профиль Telegram-пользователя с backend (создаём или обновляем) ---
            $user_id = $userSyncResult['user_id'] ?? null;
            $role = $userSyncResult['role'] ?? 'external';
            // Если роль guest — переводим в new
            if ($user_id && $role === 'guest') {
                $role = $this->botService->promoteGuestToNew($user_id, $role);
            }
            if ($role === 'external') {
                writeToLog('PhotoExclamationHandler: пользователь с ролью external', [
                    'user_id' => $user['id'],
                    'role' => $role
                ]);
                return;
            }

            writeToLog('PhotoExclamationHandler: called', [
                'chat_id' => $chat_id,
                'user_id' => $user['id'],
                'username' => $user['username'] ?? null
            ]);

            // Проверяем наличие фото
            if (!isset($message['photo'])) {
                writeToLog('PhotoExclamationHandler: no photo', []);
                $this->botService->sendMessage($chat_id, "⚠️ Пожалуйста, отправьте фото для визитки.");
                return;
            }

            // Берём самое большое фото
            $photo = end($message['photo']);
            $file_id = $photo['file_id'];
            $file_info = $this->botService->getFile($file_id);
            if (!$file_info) {
                writeToLog('PhotoExclamationHandler: getFile failed', ['file_id' => $file_id]);
                $this->botService->sendMessage($chat_id, "❌ Не удалось получить фото. Попробуйте ещё раз.");
                return;
            }
            $photo_path = $this->botService->downloadFile($file_info['file_path']);
            if (!$photo_path) {
                writeToLog('PhotoExclamationHandler: downloadFile failed', ['file_path' => $file_info['file_path']]);
                $this->botService->sendMessage($chat_id, "❌ Не удалось скачать фото. Попробуйте ещё раз.");
                return;
            }

            // Конвертируем фото в base64
            $image_data = file_get_contents($photo_path);
            $base64_image = 'data:image/jpeg;base64,' . base64_encode($image_data);
            if (file_exists($photo_path)) {
                unlink($photo_path);
            }

            // Формируем payload для orchestrator
            $payload = [
                'auth' => [
                    'user_id' => 1, // системный
                    'role' => 'admin',
                    'telegram_id' => $user['id']
                ],
                'data' => [
                    'photo' => $base64_image
                ]
            ];
            writeToLog('PhotoExclamationHandler: payload', $payload);
            $api_url = getApiUrl() . '/business-cards/auto_add.php';
            $ch = curl_init($api_url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            $response = curl_exec($ch);
            $curl_error = curl_error($ch);
            curl_close($ch);
            writeToLog('PhotoExclamationHandler: orchestrator response', [
                'response' => $response,
                'curl_error' => $curl_error
            ]);
            $result = json_decode($response, true);

            // Обрабатываем результат
            if ($result && !empty($result['success'])) {
                $reg_number = $result['result']['reg_number']
                    ?? $result['result']['data']['reg_number']
                    ?? '???';
                $car_created = !empty($result['result']['car_created'])
                    || !empty($result['result']['data']['car_created']);
                $who = $user['username'] ? '@' . $user['username'] : ($user['first_name'] ?? 'Пользователь');
                $msg = "Визитка оставлена в машине № $reg_number\n";
                $msg .= "Кто оставил: $who";
                if ($car_created) {
                    $msg .= "\n\nПоздравляем! Это авто только что добавлено в базу клуба.";
                }
                $this->botService->sendMessage($chat_id, $msg);
                writeToLog('PhotoExclamationHandler: success', ['reg_number' => $reg_number, 'car_created' => $car_created]);
            } else {
                $error = $result['error']['message'] ?? 'Не удалось добавить визитку.';
                writeToLog('PhotoExclamationHandler: error', ['error' => $error, 'result' => $result]);
                $this->botService->sendMessage($chat_id, "❌ $error");
            }
        } catch (Exception $e) {
            writeToLog('PhotoExclamationHandler: exception', ['error' => $e->getMessage()]);
            $this->botService->sendMessage($chat_id, "❌ Произошла ошибка: " . $e->getMessage());
        }
    }
} 
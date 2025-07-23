<?php
/**
 * AddCarCommand.php
 * Команда для обработки фото с подписью '++' в групповом чате CabrioRide
 * Алгоритм: получить/создать пользователя → распознать номер → проверить авто → обработать по сценарию → отправить лаконичный ответ
 * Подробные комментарии для не программиста!
 */

require_once __DIR__ . '/../utils/Logger.php';

class AddCarCommand {
    private $botService;
    public function __construct($botService) {
        $this->botService = $botService;
    }

    /**
     * Основной обработчик команды
     * @param array $message — сообщение Telegram
     */
    public function execute($message) {
        $chat_id = $message['chat']['id'];
        $user = $message['from'];
        $telegram_id = $user['id'];
        $first_name = $user['first_name'] ?? '';
        $last_name = $user['last_name'] ?? '';
        $username = $user['username'] ?? '';

        try {
            writeToLog('AddCarCommand: старт', [
                'chat_id' => $chat_id,
                'telegram_id' => $telegram_id,
                'username' => $username
            ]);

            // 1️⃣ Получаем/создаём/обновляем пользователя по Telegram ID
            $profile_data = [ 'telegram_id' => $telegram_id ];
            $profile_auth = [ 'user_id' => 1, 'role' => 'admin' ];
            $profile_result = $this->botService->callBackendApi('/backend/api/users/profile.php', [
                'auth' => $profile_auth,
                'data' => $profile_data
            ]);
            writeToLog('AddCarCommand: результат profile', $profile_result);

            if (($profile_result['success'] ?? false) && isset($profile_result['result']['data']['user']['id'])) {
                // Пользователь найден — обновляем данные на всякий случай
                $user_id = $profile_result['result']['data']['user']['id'];
                $role = $profile_result['result']['data']['user']['role'] ?? 'guest';
                $update_data = [
                    'user_id' => $user_id,
                    'username' => $username,
                    'first_name' => $first_name,
                    'last_name' => $last_name
                ];
                $update_result = $this->botService->callBackendApi('/backend/api/users/update.php', [
                    'auth' => $profile_auth,
                    'data' => $update_data
                ]);
                writeToLog('AddCarCommand: результат update', $update_result);
            } else {
                // Пользователь не найден — создаём
                $add_data = [
                    'telegram_id' => $telegram_id,
                    'username' => $username,
                    'first_name' => $first_name,
                    'last_name' => $last_name
                ];
                $add_result = $this->botService->callBackendApi('/backend/api/users/add.php', [
                    'auth' => $profile_auth,
                    'data' => $add_data
                ]);
                writeToLog('AddCarCommand: результат add', $add_result);
                if (!($add_result['success'] ?? false) || !isset($add_result['result']['data']['user_id'])) {
                    $this->botService->sendMessage($chat_id, '❌ Не удалось создать профиль пользователя.');
                    return;
                }
                $user_id = $add_result['result']['data']['user_id'];
                $role = $add_result['result']['data']['role'] ?? 'guest';
            }

            // 2️⃣ Распознаём номер по фото
            if (!isset($message['photo'])) {
                $this->botService->sendMessage($chat_id, '⚠️ Пожалуйста, отправьте фото для добавления авто.');
                return;
            }
            $photo = end($message['photo']);
            $file_id = $photo['file_id'];
            $file_info = $this->botService->getFile($file_id);
            if (!$file_info) {
                $this->botService->sendMessage($chat_id, '❌ Не удалось получить фото. Попробуйте ещё раз.');
                return;
            }
            $photo_path = $this->botService->downloadFile($file_info['file_path']);
            if (!$photo_path) {
                $this->botService->sendMessage($chat_id, '❌ Не удалось скачать фото. Попробуйте ещё раз.');
                return;
            }
            $image_data = file_get_contents($photo_path);
            $base64_image = 'data:image/jpeg;base64,' . base64_encode($image_data);
            if (file_exists($photo_path)) {
                unlink($photo_path);
            }
            $ocr_result = $this->botService->callBackendApi('/backend/api/ocr/recognize.php', [
                'auth' => [ 'user_id' => $user_id, 'role' => $role ],
                'data' => [ 'image' => $base64_image ]
            ]);
            writeToLog('AddCarCommand: результат OCR', $ocr_result);
            $plate_number = $ocr_result['result']['data']['plate'] ?? null;
            if (!$plate_number) {
                $this->botService->sendMessage($chat_id, "❌ Не удалось распознать номер на фото.\n💡 Советы: сделайте чёткое фото, избегайте бликов и теней.");
                return;
            }
            $plate_number_display = strtoupper($plate_number); // Только для экрана!

            // 3️⃣ Проверяем авто по номеру
            $check_result = $this->botService->callBackendApi('/backend/api/cars/check.php', [
                'auth' => [ 'user_id' => $user_id, 'role' => $role ],
                'data' => [ 'reg_number' => $plate_number ]
            ]);
            writeToLog('AddCarCommand: результат check', $check_result);
            $car_found = $check_result['result']['data']['found'] ?? false;
            $car_id = $check_result['result']['data']['car_id'] ?? null;
            $owner_user_id = $check_result['result']['data']['owner_user_id'] ?? null;
            writeToLog('AddCarCommand: owner_user_id перед ветвлением', [
                'owner_user_id' => $owner_user_id,
                'car_id' => $car_id,
                'user_id' => $user_id
            ]);

            // 4️⃣ Ветвление по ситуации
            // Логируем типы и значения для диагностики
            writeToLog('DEBUG: owner_user_id и user_id', [
                'owner_user_id' => $owner_user_id,
                'user_id' => $user_id,
                'type_owner' => gettype($owner_user_id),
                'type_user' => gettype($user_id)
            ]);
            if ($car_found) {
                $owner_id_int = (int)$owner_user_id;
                $user_id_int = (int)$user_id;
                if ($owner_id_int > 0 && $owner_id_int !== $user_id_int) {
                    $msg = "🚗 Номер: $plate_number_display\n❌ В базе данных другой владелец.\n⚠️ Если это ошибка — сообщите админам.";
                    $this->botService->sendMessage($chat_id, $msg);
                    writeToLog('AddCarCommand: авто с другим владельцем', [
                        'car_id' => $car_id,
                        'owner_user_id' => $owner_user_id,
                        'user_id' => $user_id
                    ]);
                    return;
                }
                if ($owner_id_int === $user_id_int && $owner_id_int > 0) {
                    // Пользователь уже владелец — обновляем статус и добавляем фото
                    $this->botService->callBackendApi('/backend/api/cars/update.php', [
                        'auth' => [ 'user_id' => $user_id, 'role' => $role ],
                        'data' => [ 'car_id' => $car_id, 'status' => 'active' ]
                    ]);
                    $this->botService->callBackendApi('/backend/api/photos/add.php', [
                        'auth' => [ 'user_id' => $user_id, 'role' => $role ],
                        'data' => [
                            'entity_type' => 'car',
                            'entity_id' => $car_id,
                            'photo' => $base64_image,
                            'description' => 'Фото добавлено через Telegram-бота'
                        ]
                    ]);
                    $owner_display = $username ? '@' . $username : ($first_name . ' ' . $last_name);
                    $msg = "🚗 Номер: $plate_number_display\n"
                         . "Владелец: $owner_display\n"
                         . "Статус: активный\n"
                         . "Фото добавлено.";
                    $this->botService->sendMessage($chat_id, $msg);
                    return;
                }
                // Только если владелец отсутствует — claim
                if ($owner_id_int === 0) {
                    writeToLog('AddCarCommand: вызываю claim', [ 'car_id' => $car_id, 'user_id' => $user_id ]);
                    $claim_result = $this->botService->callBackendApi('/backend/api/cars/claim.php', [
                        'auth' => [ 'user_id' => $user_id, 'role' => $role ],
                        'data' => [ 'car_id' => $car_id ]
                    ]);
                    writeToLog('AddCarCommand: результат claim', $claim_result);
                    if (!($claim_result['success'] ?? false)) {
                        $this->botService->sendMessage($chat_id, '❌ Не удалось присвоить авто пользователю.');
                        return;
                    }
                    // Если роль была guest — меняем на registered
                    if ($role === 'guest') {
                        $set_role_result = $this->botService->callBackendApi('/backend/api/users/set_role.php', [
                            'auth' => [ 'user_id' => $user_id, 'role' => $role ],
                            'data' => [ 'user_id' => $user_id, 'role' => 'registered' ]
                        ]);
                        writeToLog('AddCarCommand: set_role после claim', $set_role_result);
                    }
                    $this->botService->callBackendApi('/backend/api/cars/update.php', [
                        'auth' => [ 'user_id' => $user_id, 'role' => $role ],
                        'data' => [ 'car_id' => $car_id, 'status' => 'active' ]
                    ]);
                    $this->botService->callBackendApi('/backend/api/photos/add.php', [
                        'auth' => [ 'user_id' => $user_id, 'role' => $role ],
                        'data' => [
                            'entity_type' => 'car',
                            'entity_id' => $car_id,
                            'photo' => $base64_image,
                            'description' => 'Фото добавлено через Telegram-бота'
                        ]
                    ]);
                    $owner_display = $username ? '@' . $username : ($first_name . ' ' . $last_name);
                    $msg = "✅ Визитка сработала!\n"
                         . "🚗 Авто: $plate_number_display\n"
                         . "Владелец: $owner_display\n"
                         . "Статус: 'Активен'.\n"
                         . "Фото добавлено.";
                    $this->botService->sendMessage($chat_id, $msg);
                    return;
                }
            } else {
                // Авто не найдено — добавляем авто (фото прикладывается сразу)
                $add_car_result = $this->botService->callBackendApi('/backend/api/cars/add.php', [
                    'auth' => [ 'user_id' => $user_id, 'role' => $role ],
                    'data' => [
                        'reg_number' => $plate_number,
                        'photo' => $base64_image,
                        'create_user_id' => $user_id,
                        'owner_user_id' => $user_id
                    ]
                ]);
                writeToLog('AddCarCommand: результат add car', $add_car_result);
                if ($add_car_result['success'] ?? false) {
                    // Если роль была guest — меняем на registered
                    if ($role === 'guest') {
                        $set_role_result = $this->botService->callBackendApi('/backend/api/users/set_role.php', [
                            'auth' => [ 'user_id' => $user_id, 'role' => $role ],
                            'data' => [ 'user_id' => $user_id, 'role' => 'registered' ]
                        ]);
                        writeToLog('AddCarCommand: set_role после add', $set_role_result);
                    }
                    $owner_display = $username ? '@' . $username : ($first_name . ' ' . $last_name);
                    $this->botService->sendMessage($chat_id, "НОВЫЙ АВТО: \n🚗 Номер: $plate_number_display\nВладелец: $owner_display\n✅ Статус 'Активнен'.");
                } else {
                    $this->botService->sendMessage($chat_id, "❌ Не удалось добавить авто. Попробуйте позже.");
                }
                return;
            }
        } catch (Exception $e) {
            writeToLog('AddCarCommand: Ошибка', [ 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString() ]);
            $this->botService->sendMessage($chat_id, '❌ Произошла ошибка. Попробуйте позже.');
        }
    }
} 
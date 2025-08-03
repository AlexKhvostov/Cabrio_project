<?php
/**
 * __AddCarToUserAction — L2 Action для добавления автомобиля пользователю
 * 
 * Назначение: Добавляет автомобиль пользователю (если авто был в БД и без владельца то ставит владельца, если нет в БД то создает)
 * Уровень: L2 (бизнес-операция)
 * Префикс: __ (два подчёркивания)
 * 
 * Логика работы:
 * 1. Проверяем существование автомобиля по номеру
 * 2. Если автомобиль найден:
 *    - Проверяем есть ли у него владелец
 *    - Если владельца нет - назначаем пользователя владельцем
 *    - Если владелец есть - возвращаем ошибку
 *    - Возвращаем action: "assigned"
 * 3. Если автомобиль не найден:
 *    - Создаём новый автомобиль с пользователем как владельцем
 *    - Возвращаем action: "created"
 * 4. Если передана фото:
 *    - Сохраняем файл на сервер
 *    - Создаём запись в БД через L1 Action
 * 
 * Входные данные:
 *   - plate_number (string) — номер автомобиля (может быть null)
 *   - model (string, опционально) — модель автомобиля
 *   - color (string, опционально) — цвет автомобиля
 *   - year (int, опционально) — год выпуска
 *   - photo (file, опционально) — фото автомобиля
 * 
 * Пользователь получается из глобального контекста (AppContext)
 * 
 * Выходные данные:
 *   - success (boolean) — результат операции
 *   - data (array) — данные автомобиля и информация о действии
 *   - error (array, опционально) — информация об ошибке
 * 
 * Использует L1 Actions:
 *   - _CheckCarInDbAction — проверка существования автомобиля
 *   - _CreateCarAction — создание нового автомобиля
 *   - _UpdateOwnerToCarAction — назначение владельца автомобилю
 *   - _CreatePhotoAction — создание записи о фото (если передана фото)
 *   - _GetRoleIdAction — получение ID роли по коду
 *   - _UpdateRoleUserAction — обновление роли пользователя
 * 
 * Использует Helpers:
 *   - FileHelper — сохранение файла на сервер
 */

require_once __DIR__ . '/../level1/_CheckCarInDbAction.php';
require_once __DIR__ . '/../level1/_CreateCarAction.php';
require_once __DIR__ . '/../level1/_UpdateOwnerToCarAction.php';
require_once __DIR__ . '/../level1/_CreatePhotoAction.php';
require_once __DIR__ . '/../level1/_GetRoleIdAction.php';
require_once __DIR__ . '/../level1/_UpdateRoleUserAction.php';
require_once __DIR__ . '/../helpers/FileHelper.php';
require_once __DIR__ . '/../../utils/ValidationHelper.php';
require_once __DIR__ . '/../../utils/Logger.php';
require_once __DIR__ . '/../../utils/AppContext.php';
require_once __DIR__ . '/../../models/Photo.php';

class __AddCarToUserAction {
    
    public static function handle($data) {
        try {
            // Получаем пользователя из глобального контекста
            $user = AppContext::getCurrentUser();
            if (!$user) {
                return [
                    'success' => false,
                    'error' => [
                        'code' => 'NO_USER',
                        'message' => 'Пользователь не найден в контексте'
                    ]
                ];
            }
            
            $userId = $user['id'];
            
            // 0. Проверяем и обновляем роль пользователя
            $roleUpdated = self::checkAndUpdateUserRole($user);
            
            // Получаем номер (может быть null)
            $plateNumber = $data['plate_number'] ?? null;
            $action = null;
            $carData = null;
            
            // 1. Проверяем существование автомобиля (только если есть номер)
            if ($plateNumber) {
                $checkResult = _CheckCarInDbAction::handle(['plate_number' => $plateNumber]);
                
                if ($checkResult['success'] && $checkResult['data'] !== null) {
                    // Автомобиль найден
                    $carData = $checkResult['data'];
                    $carId = $carData['id'];
                    
                    // Проверяем есть ли владелец
                    if ($carData['owner_user_id'] !== null) {
                        return [
                            'success' => false,
                            'error' => [
                                'code' => 'CAR_ALREADY_OWNED',
                                'message' => 'Автомобиль уже имеет владельца'
                            ]
                        ];
                    }
                    
                    // Назначаем пользователя владельцем
                    $updateResult = _UpdateOwnerToCarAction::handle([
                        'car_id' => $carId,
                        'user_id' => $userId
                    ]);
                    
                    if ($updateResult['success']) {
                        $action = 'assigned';
                        // Получаем обновленные данные автомобиля
                        $carData = $updateResult['data'];
                    } else {
                        return $updateResult;
                    }
                    
                } else {
                    // Автомобиль не найден - создаём новый с владельцем
                    $createData = [
                        'reg_number' => $plateNumber,
                        'create_user_id' => $userId,
                        'owner_user_id' => $userId, // Пользователь как владелец
                        'model' => $data['model'] ?? null,
                        'color' => $data['color'] ?? null,
                        'year' => $data['year'] ?? null,
                        'status_id' => 7 // "Активный" по умолчанию
                    ];
                    
                    $createResult = _CreateCarAction::handle($createData);
                    
                    if ($createResult['success']) {
                        $action = 'created';
                        $carData = $createResult['data'];
                        $carId = $carData['id'];
                    } else {
                        return $createResult;
                    }
                }
            } else {
                // Нет номера - создаём авто без номера
                $createData = [
                    'reg_number' => null,
                    'create_user_id' => $userId,
                    'owner_user_id' => $userId, // Пользователь как владелец
                    'model' => $data['model'] ?? null,
                    'color' => $data['color'] ?? null,
                    'year' => $data['year'] ?? null,
                    'status_id' => 7 // "Активный" по умолчанию
                ];
                
                $createResult = _CreateCarAction::handle($createData);
                
                if ($createResult['success']) {
                    $action = 'created';
                    $carData = $createResult['data'];
                    $carId = $carData['id'];
                } else {
                    return $createResult;
                }
            }
            
                         // 2. Обрабатываем фото если передана
             $photoData = null;
             if (isset($data['photo']) && !empty($data['photo'])) {
                 try {
                     // Получаем следующий ID заранее
                     $photoId = Photo::getNextId();
                     $extension = 'jpg'; // Бот отправляет в формате JPEG
                     $fileName = FileHelper::generateCorrectFileName('car', $carId, $photoId, $extension);
                     
                     // Создаём временный файл из base64
                     $tempFileData = FileHelper::createTempFileFromBase64($data['photo'], $fileName);
                     
                     // Создаём директорию если не существует
                     $uploadDir = FileHelper::getUploadDir('car');
                     if (!is_dir($uploadDir)) {
                         mkdir($uploadDir, 0755, true);
                     }
                     
                     // Сохраняем файл на сервер
                     $filePath = $uploadDir . '/' . $fileName;
                     if (copy($tempFileData['tmp_name'], $filePath)) {
                         // Получаем относительный путь для БД
                         $savedPath = FileHelper::getRelativePath($filePath);
                         
                         Logger::info("Photo file saved: $filePath");
                         
                         // Создаём запись в БД
                         $photoResult = _CreatePhotoAction::handle([
                             'entity_type' => 'car',
                             'entity_id' => $carId,
                             'file_name' => $fileName,
                             'url' => $savedPath,
                             'photo_type' => 'cover',
                             'description' => 'Фото автомобиля',
                             'uploaded_by' => $userId
                         ]);
                         
                         if ($photoResult['success']) {
                             $photoData = $photoResult['data'];
                             Logger::info("Photo saved successfully: car_id=$carId, photo_id=" . $photoData['id']);
                         } else {
                             Logger::error("Failed to create photo record: " . json_encode($photoResult['error']));
                         }
                     } else {
                         Logger::error("Failed to copy photo file to upload directory");
                     }
                     
                     // Удаляем временный файл
                     if (file_exists($tempFileData['tmp_name'])) {
                         unlink($tempFileData['tmp_name']);
                     }
                     
                 } catch (Exception $e) {
                     Logger::error('Photo upload failed: ' . $e->getMessage());
                     // Не прерываем выполнение, только логируем ошибку
                 }
             }
            
            // 3. Формируем ответ
            $response = [
                'success' => true,
                'data' => [
                    'car_id' => $carData['id'],
                    'action' => $action,
                    'plate_number' => $carData['reg_number'],
                    'model' => $carData['model'],
                    'color' => $carData['color'],
                    'year' => $carData['year'],
                    'status_id' => $carData['status_id'] ?? $carData['status']['id'] ?? null,
                    'owner_user_id' => $carData['owner_user_id'],
                    'create_user_id' => $carData['create_user_id'] ?? $userId,
                    'message' => self::getActionMessage($action)
                ]
            ];
            
            // Добавляем информацию о фото если загружали
            if ($photoData) {
                $response['data']['photo'] = $photoData;
            }
            
            Logger::info("Car added to user: plate_number=$plateNumber, user_id=$userId, action=$action");
            
            return $response;
            
        } catch (Exception $e) {
            Logger::error('__AddCarToUserAction failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => 'Ошибка добавления автомобиля пользователю: ' . $e->getMessage()
                ]
            ];
        }
    }
    
    /**
     * Проверить и обновить роль пользователя
     * 
     * @param array $user Данные пользователя
     * @return bool Обновлена ли роль
     */
    private static function checkAndUpdateUserRole($user) {
        try {
            // Получаем ID роли "user" (обычно это 3)
            $userRoleId = _GetRoleIdAction::handle(['role_code' => 'user']);
            
            if (!$userRoleId['success']) {
                Logger::error('Failed to get user role ID');
                return false;
            }
            
            $requiredRoleId = $userRoleId['data'];
            $currentRoleId = $user['role_id'] ?? $user['role']['id'] ?? 1; // guest по умолчанию
            
            // Если роль меньше user, обновляем
            if ($currentRoleId < $requiredRoleId) {
                Logger::info("Updating user role from $currentRoleId to $requiredRoleId", [
                    'user_id' => $user['id']
                ]);
                
                $updateResult = _UpdateRoleUserAction::handle([
                    'user_id' => $user['id'],
                    'role_id' => $requiredRoleId
                ]);
                
                if ($updateResult['success']) {
                    Logger::info("User role updated successfully", [
                        'user_id' => $user['id'],
                        'old_role_id' => $currentRoleId,
                        'new_role_id' => $requiredRoleId
                    ]);
                    return true;
                } else {
                    Logger::error("Failed to update user role", [
                        'user_id' => $user['id'],
                        'error' => $updateResult['error']
                    ]);
                    return false;
                }
            }
            
            return false; // Роль не обновлялась
            
        } catch (Exception $e) {
            Logger::error('Error checking/updating user role: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Получить сообщение для действия
     */
    private static function getActionMessage($action) {
        switch ($action) {
            case 'assigned':
                return 'Автомобиль назначен пользователю';
            case 'created':
                return 'Автомобиль создан и назначен пользователю';
            default:
                return 'Операция выполнена';
        }
    }
} 
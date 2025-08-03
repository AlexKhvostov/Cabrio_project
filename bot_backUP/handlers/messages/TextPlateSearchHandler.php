<?php
/**
 * TextPlateSearchHandler.php
 * 
 * Обработчик для поиска автомобиля по номеру (текстовый поиск)
 * Использует новый backend API с развернутыми данными
 */

require_once __DIR__ . '/../../utils/Logger.php';

class TextPlateSearchHandler {
    /** @var BotService */
    private $botService;
    
    /**
     * Конструктор
     */
    public function __construct($botService) {
        $this->botService = $botService;
    }
    
    /**
     * Обрабатывает текстовый поиск номера
     */
    public function execute($message, $userSyncResult = null) {
        try {
            $chat_id = $message['chat']['id'];
            $role = $userSyncResult['role'] ?? 'external';
            
            if ($role === 'external') {
                $this->botService->sendMessage($chat_id, "❌ Только для участников клуба.");
                return;
            }
            
            $text = trim($message['text']);
            
            // Убираем команду /search если есть
            $plate_number = preg_replace('/^\/search\s*/', '', $text);
            $plate_number = trim($plate_number);
            
            // Проверяем, что есть номер для поиска
            if (empty($plate_number)) {
                $this->botService->sendMessage($chat_id, 
                    "🔍 Введите номер автомобиля для поиска.\n\n" .
                    "Пример: А123БВ77 или /search А123БВ77"
                );
                return;
            }
            
            // Очищаем номер от лишних символов
            $plate_number = $this->cleanPlateNumber($plate_number);
            
            if (empty($plate_number)) {
                $this->botService->sendMessage($chat_id, 
                    "❌ Неверный формат номера. Попробуйте еще раз."
                );
                return;
            }
            
            // Отправляем на поиск через новый API
            $result = $this->searchPlateWithNewAPI($plate_number, $userSyncResult);
            
            // Обрабатываем результат
            $this->handleSearchResult($chat_id, $result);
            
        } catch (Exception $e) {
            writeToLog("Error in TextPlateSearchHandler: " . $e->getMessage());
            $this->botService->sendMessage($chat_id, 
                "❌ Произошла ошибка при поиске. Попробуйте позже."
            );
        }
    }
    
    /**
     * Очищает номер от лишних символов
     */
    private function cleanPlateNumber($plate) {
        // Убираем все символы кроме букв и цифр
        $plate = preg_replace('/[^а-яёa-z0-9]/ui', '', $plate);
        
        // Проверяем минимальную длину
        if (strlen($plate) < 4) {
            return '';
        }
        
        return strtoupper($plate);
    }
    
    /**
     * Ищет номер в базе данных через новый API
     */
    private function searchPlateWithNewAPI($plate_number, $userSyncResult) {
        try {
            // Получаем токен для пользователя
            $token = $this->botService->getUserToken($userSyncResult);
            if (!$token) {
                writeToLog('TextPlateSearchHandler: не удалось получить токен');
                return ['success' => false, 'error' => 'Ошибка авторизации'];
            }
            
            $api_url = getApiUrl() . '/api/actions/check-car-in-club';
            
            // Формируем запрос для нового API
            $request_data = [
                'plate_number' => $plate_number
            ];
            
            writeToLog('TextPlateSearchHandler: отправка запроса на новый API', [
                'url' => $api_url,
                'plate_number' => $plate_number
            ]);
            
            // Отправляем запрос
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $api_url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request_data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json'
            ]);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_error = curl_error($ch);
            curl_close($ch);
            
            writeToLog('TextPlateSearchHandler: ответ от нового API', [
                'http_code' => $http_code,
                'response' => $response,
                'curl_error' => $curl_error
            ]);
            
            if ($http_code === 200) {
                $result = json_decode($response, true);
                if ($result && isset($result['success'])) {
                    // Добавляем информацию о номере
                    $result['plate_number'] = $plate_number;
                    return $result;
                }
            }
            
            writeToLog('TextPlateSearchHandler: ошибка запроса', [
                'http_code' => $http_code,
                'curl_error' => $curl_error
            ]);
            
            return ['success' => false, 'error' => 'Ошибка API'];
            
        } catch (Exception $e) {
            writeToLog("Error searching plate with new API: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Обрабатывает результат поиска
     */
    private function handleSearchResult($chat_id, $result) {
        if (!$result['success']) {
            $this->botService->sendMessage($chat_id,
                "❌ Не удалось выполнить поиск. Попробуйте еще раз."
            );
            return;
        }
        
        $data = $result['data'];
        $action = $data['action'] ?? 'unknown';
        $plate_number = $result['plate_number'] ?? 'Неизвестен';
        
        // Формируем ответ
        $text = "🔍 Результат поиска:\n\n";
        $text .= "🚗 Номер: $plate_number\n";
        
        switch ($action) {
            case 'found':
                // Автомобиль найден в базе
                $car = $data['car'];
                $status = $car['status']['name'] ?? 'Неизвестен';
                $owner = $car['owner'] ?? null;
                
                $text .= "\n✅ Автомобиль найден в базе клуба!";
                $text .= "\n📋 Статус: $status";
                
                if ($owner) {
                    $ownerName = trim($owner['first_name'] . ' ' . ($owner['last_name'] ?? ''));
                    $text .= "\n👤 Владелец: $ownerName";
                }
                break;
                
            case 'not_found':
                // Автомобиль не найден
                $text .= "\n❌ Автомобиль не найден в базе клуба.";
                $text .= "\n💡 Возможно, владелец еще не зарегистрировался в приложении.";
                break;
                
            default:
                $text .= "\n❓ Неизвестный результат поиска.";
                break;
        }
        
        $this->botService->sendMessage($chat_id, $text);
    }
} 
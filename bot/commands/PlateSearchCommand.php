<?php
/**
 * PlateSearchCommand.php
 * 
 * Команда для поиска автомобиля по номеру (текстовый поиск)
 * Использует новый эндпоинт /api/ocr/check.php
 */

require_once __DIR__ . '/../utils/Logger.php';

class PlateSearchCommand {
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
    public function execute($message) {
        try {
            $chat_id = $message['chat']['id'];
            $user = $message['from'];
            $text = trim($message['text']);
            
            // Проверяем членство в чате
            if (!$this->botService->verifyMembership($user['id'], $chat_id)) {
                return;
            }
            
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
            
            // Отправляем на поиск через новый эндпоинт
            $result = $this->searchPlate($plate_number, $user);
            
            // Обрабатываем результат
            $this->handleSearchResult($chat_id, $user, $result);
            
        } catch (Exception $e) {
            writeToLog("Error in PlateSearchCommand: " . $e->getMessage());
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
    private function searchPlate($plate_number, $user) {
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
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            writeToLog("PlateSearchCommand: API Response", [
                'http_code' => $http_code,
                'response' => $response
            ]);
            
            if ($http_code === 200) {
                $result = json_decode($response, true);
                if ($result && isset($result['success'])) {
                    // Добавляем информацию о номере
                    $result['plate_number'] = $plate_number;
                    return $result;
                }
            }
            
            writeToLog("PlateSearchCommand: Request failed", [
                'http_code' => $http_code
            ]);
            
            return ['success' => false, 'error' => 'Ошибка API'];
            
        } catch (Exception $e) {
            writeToLog("Error searching plate: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Обрабатывает результат поиска
     */
    private function handleSearchResult($chat_id, $user, $result) {
        if (!$result['success']) {
            $this->botService->sendMessage($chat_id,
                "❌ Не удалось выполнить поиск. Попробуйте еще раз."
            );
            return;
        }
        
        $plate_number = $result['plate_number'];
        
        // Формируем ответ
        $text = "🔍 Результат поиска:\n\n";
        $text .= "🚗 Номер: " . $plate_number . "\n";
        
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
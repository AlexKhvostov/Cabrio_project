<?php
/**
 * API Endpoint: Проверка номера автомобиля в базе
 * POST /api/ocr/check.php
 * 
 * Запрос:
 * {
 *   "auth": {
 *     "user_id": 123,
 *     "role": "member"
 *   },
 *   "data": {
 *     "plate": "A123BC"
 *   }
 * }
 * 
 * Ответ:
 * {
 *   "success": true,
 *   "result": {
 *     "message": "Автомобиль найден в базе клуба",
 *     "data": {
 *       "found": true,
 *       "plate": "A123BC",
 *       "status": "active",
 *       "can_leave_card": true
 *     }
 *   }
 * }
 */

// Отключаем вывод ошибок для чистого JSON
error_reporting(0);
ini_set('display_errors', 0);

// Загружаем конфигурацию
require_once __DIR__ . '/../../config/config.php';

// Функция для получения значения из конфига (если не определена)
if (!function_exists('getConfig')) {
    function getConfig($key, $default = null) {
        return $_ENV[$key] ?? $default;
    }
}

require_once __DIR__ . '/../../utils/Database.php';
require_once __DIR__ . '/../../utils/ApiHandler.php';

class OcrCheckEndpoint extends ApiHandler {
    
    protected function process() {
        // Проверяем права доступа (доступно всем)
        $accessResult = $this->checkAccess('guest');
        if ($accessResult !== true) {
            return $accessResult;
        }
        
        // Получаем и валидируем номер
        $plate = $this->requireField('plate', 'Номер автомобиля обязателен');
        $plate = trim($plate);
        
        if (empty($plate)) {
            return $this->error('Номер не может быть пустым', 400, 'VALIDATION_ERROR');
        }
        
        try {
            $db = $this->getDb();
            
            // Ищем автомобиль в БД
            $sql = "SELECT 
                c.id,
                c.reg_number,
                c.status_id,
                s.name as status_name
            FROM cars c
            LEFT JOIN ref_statuses s ON c.status_id = s.id
            WHERE c.reg_number = ?";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([$plate]);
            $car = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($car) {
                // Автомобиль найден
                $result = [
                    'found' => true,
                    'plate' => $car['reg_number'],
                    'status' => $car['status_name'],
                    'can_leave_card' => true
                ];
                
                return $this->success($result, 'Автомобиль найден в базе клуба');
            } else {
                // Автомобиль не найден
                $result = [
                    'found' => false,
                    'plate' => $plate,
                    'can_leave_card' => false
                ];
                
                return $this->success($result, 'Автомобиль с таким номером не найден в базе клуба');
            }
            
        } catch (Exception $e) {
            return $this->error('Ошибка проверки номера: ' . $e->getMessage(), 500, 'DATABASE_ERROR');
        }
    }
}

// Запускаем обработку
$endpoint = new OcrCheckEndpoint();
$endpoint->handle();
?> 
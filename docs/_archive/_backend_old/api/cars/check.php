<?php
/**
 * API Endpoint: Проверка автомобиля по номеру
 * POST /api/cars/check.php
 * 
 * Запрос:
 * {
 *   "auth": {
 *     "user_id": 123,
 *     "role": "member"
 *   },
 *   "data": {
 *     "reg_number": "A123BC"
 *   }
 * }
 * 
 * Ответ:
 * {
 *   "success": true,
 *   "result": {
 *     "message": "Автомобиль найден",
 *     "data": {
 *       "found": true,
 *       "status": "active",
 *       "car_id": 456,
 *       "reg_number": "A123BC"
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

class CheckCarEndpoint extends ApiHandler {
    
    protected function process() {
        // Проверяем права доступа (доступно всем)
        $accessResult = $this->checkAccess('guest');
        if ($accessResult !== true) {
            return $accessResult;
        }
        
        // Получаем и валидируем номер
        $regNumber = $this->requireField('reg_number', 'Номер автомобиля обязателен');
        $regNumber = $this->validateRegNumber($regNumber);
        
        try {
            $db = $this->getDb();
            
            // Ищем автомобиль в БД (упрощённый запрос)
            $sql = "SELECT id, reg_number, status_id, owner_user_id FROM cars WHERE reg_number = ?";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([$regNumber]);
            $car = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($car) {
                // Автомобиль найден
                $result = [
                    'found' => true,
                    'car_id' => $car['id'],
                    'reg_number' => $car['reg_number'],
                    'status_id' => $car['status_id'],
                    'owner_user_id' => $car['owner_user_id'] ?? null
                ];
                
                return $this->success($result, 'Автомобиль найден');
            } else {
                // Автомобиль не найден
                $result = [
                    'found' => false,
                    'reg_number' => $regNumber
                ];
                
                return $this->success($result, 'Автомобиль не найден');
            }
            
        } catch (Exception $e) {
            return $this->error('Ошибка проверки автомобиля: ' . $e->getMessage(), 500, 'DATABASE_ERROR');
        }
    }
}

// Запускаем обработку
$endpoint = new CheckCarEndpoint();
$endpoint->handle();
?> 
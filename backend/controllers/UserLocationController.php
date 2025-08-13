<?php

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/UserLocation.php';
require_once __DIR__ . '/../utils/AppContext.php';
require_once __DIR__ . '/../utils/Logger.php';
require_once __DIR__ . '/../utils/UrlHelper.php';

/**
 * Контроллер для управления координатами пользователей
 */
class UserLocationController extends BaseController
{
    /**
     * Сохранение координат пользователя
     * POST /api/user-locations
     */
    public function store()
    {
        try {
            // Доступ только для member и выше (централизованно)
            if (method_exists($this, 'requireAccess')) {
                if (!$this->requireAccess('api.userLocations.store')) { return; }
            }
            // Получаем данные из запроса
            $input = $this->getJsonInput();
            
            if (!$input) {
                return $this->jsonResponse(['error' => 'Неверный формат данных'], 400);
            }
            
            // Валидация входных данных
            $latitude = $input['latitude'] ?? null;
            $longitude = $input['longitude'] ?? null;
            $accuracy = $input['accuracy'] ?? null;
            
            if (!is_numeric($latitude) || !is_numeric($longitude)) {
                return $this->jsonResponse(['error' => 'Неверные координаты'], 400);
            }
            
            // Проверяем диапазон координат
            if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
                return $this->jsonResponse(['error' => 'Координаты вне допустимого диапазона'], 400);
            }
            
            // Получаем текущего пользователя
            $user = AppContext::getCurrentUser();
            if (!$user) {
                return $this->jsonResponse(['error' => 'Пользователь не авторизован'], 401);
            }
            
            // Создаем или обновляем запись о местоположении
            $userLocation = UserLocation::where('user_id', '=', $user['id'])->first();
            
            if ($userLocation) {
                // Обновляем существующую запись
                // Храним время в UTC (таймзона процесса уже UTC через load_env)
                $userLocation->update([
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'updated_at' => gmdate('Y-m-d H:i:s')
                ]);
                
                Logger::info("Обновлены координаты пользователя {$user['id']}", [
                    'user_id' => $user['id'],
                    'latitude' => $latitude,
                    'longitude' => $longitude
                ]);
            } else {
                // Создаем новую запись
                // Храним время в UTC (таймзона процесса уже UTC через load_env)
                UserLocation::create([
                    'user_id' => $user['id'],
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'updated_at' => gmdate('Y-m-d H:i:s')
                ]);
                
                Logger::info("Созданы координаты пользователя {$user['id']}", [
                    'user_id' => $user['id'],
                    'latitude' => $latitude,
                    'longitude' => $longitude
                ]);
            }
            
            return $this->jsonResponse([
                'success' => true,
                'message' => 'Координаты сохранены',
                'data' => [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'updated_at' => gmdate('Y-m-d H:i:s')
                ]
            ]);
            
        } catch (Exception $e) {
            Logger::error("Ошибка сохранения координат: " . $e->getMessage(), [
                'user_id' => AppContext::getCurrentUser()['id'] ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->jsonResponse(['error' => 'Внутренняя ошибка сервера'], 500);
        }
    }
    
    /**
     * Получение активных координат пользователей
     * GET /api/user-locations
     */
    public function index()
    {
        try {
            // Доступ только для member и выше (централизованно)
            if (method_exists($this, 'requireAccess')) {
                if (!$this->requireAccess('api.userLocations.index')) { return; }
            }
            // Получаем время жизни координат из env
            $liveTimeMinutes = (int)(getenv('map_live_time_min') ?: 40);
            $cutoffTime = date('Y-m-d H:i:s', strtotime("-{$liveTimeMinutes} minutes"));
            
            // Получаем активные координаты пользователей
            $locations = UserLocation::getActiveLocations($cutoffTime);
            
            $result = [];
            foreach ($locations as $location) {
                $result[] = [
                    'user_id' => $location->user_id,
                    'latitude' => $location->latitude,
                    'longitude' => $location->longitude,
                    'updated_at' => $location->updated_at,
                    'user' => [
                        'first_name' => $location->first_name_app ?: $location->first_name_tg ?: 'Пользователь',
                        'username' => $location->username,
                        // Отдаём mini если есть (из основной фото users.photo_url), иначе telegram_photo_url
                        'photo' => [
                            'mini' => ($location->photo_url ? UrlHelper::buildUploadsUrlSized($location->photo_url, 'mini') : null),
                            'fallback' => $location->telegram_photo_url
                        ]
                    ]
                ];
            }
            
            return $this->jsonResponse([
                'success' => true,
                'data' => $result,
                'live_time_minutes' => $liveTimeMinutes
            ]);
            
        } catch (Exception $e) {
            Logger::error("Ошибка получения координат: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->jsonResponse(['error' => 'Внутренняя ошибка сервера'], 500);
        }
    }

    // Вспомогательные JSON-ответы для совместимости
    private function jsonResponse($data, $status = 200) { $this->json($data, $status); }
    private function getJsonInput() { return json_decode(file_get_contents('php://input'), true) ?? null; }
}

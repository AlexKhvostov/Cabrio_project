<?php
/**
 * StatsController — короткие цифры для главной страницы.
 *
 * Зачем отдельный метод: раньше главная скачивала все списки пользователей,
 * машин и событий только чтобы показать три числа. Так нельзя при росте клуба.
 *
     * Ответ: { success, data: { users, cars_active, events } }
     * users — роли user и выше, без гостей чата и внешних.
 */
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Car.php';
require_once __DIR__ . '/../models/Event.php';

class StatsController extends BaseController
{
    /**
     * GET /api/stats
     * Минимальная роль: guest (кто уже открыл приложение в Telegram)
     */
    public function dashboard()
    {
        try {
            if (!$this->requireAccess('api.stats.dashboard')) {
                return;
            }

            $this->json([
                'success' => true,
                'data' => [
                    'users' => User::countRegistered(),
                    'cars_active' => Car::countActive(),
                    'events' => Event::countAll(),
                ],
                'meta' => $this->getRequestInfo()
            ]);
        } catch (Throwable $e) {
            Logger::error('StatsController: dashboard error', [
                'error' => $e->getMessage(),
                'user_id' => $this->getCurrentUserId()
            ]);

            $this->json([
                'success' => false,
                'error' => [
                    'code' => 'DB_ERROR',
                    'message' => $e->getMessage()
                ]
            ], 500);
        }
    }
}

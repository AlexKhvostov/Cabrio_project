<?php
/**
 * UserJoinedHandler.php
 * Обработчик сценариев входа пользователя в клубный чат CabrioRide
 * Варианты: сам вступил, добавили, вернулся, повторное вступление и т.д.
 */

class UserJoinedHandler {
    private $botService;
    public function __construct($botService) {
        $this->botService = $botService;
    }
    /**
     * Основной обработчик входа пользователя
     * @param int $chat_id
     * @param array $user — данные пользователя из Telegram
     * @param string $joinType — тип события: self, invited, returned, unknown
     */
    public function handle($chat_id, $user, $joinType = 'unknown') {
        // Логируем тип входа
        writeToLog("UserJoinedHandler: handle", [
            'user_id' => $user['id'],
            'username' => $user['username'] ?? 'Нет username',
            'joinType' => $joinType
        ]);
        // 1. Проверяем наличие пользователя в базе по telegram_id
        $profileResp = $this->botService->callBackendApi('/backend/api/users/profile.php', [
            'auth' => [
                'user_id' => $user['id'],
                'role' => 'guest'
            ],
            'data' => [
                'telegram_id' => $user['id']
            ]
        ]);
        $userId = null;
        $roleToSet = 'guest';
        $needCreate = false;
        if (!$profileResp || empty($profileResp['success']) || empty($profileResp['result']['data']['user'])) {
            // 2. Если нет — создаём пользователя
            $addResp = $this->botService->callBackendApi('/backend/api/users/add.php', [
                'auth' => [ ],
                'data' => [
                    'telegram_id' => $user['id'],
                    'username' => $user['username'] ?? null,
                    'first_name' => $user['first_name'] ?? null,
                    'last_name' => $user['last_name'] ?? null,
                    'photo' => $user['photo_url'] ?? null
                ]
            ]);
            if ($addResp && !empty($addResp['success']) && !empty($addResp['result']['data']['user_id'])) {
                $userId = $addResp['result']['data']['user_id'];
                $roleToSet = 'guest';
                $needCreate = true;
            }
        } else {
            // 3. Если есть — обновляем данные (опционально, если нужно)
            $userId = $profileResp['result']['data']['user']['id'];
            // 4. Определяем роль: если есть авто или host_user_id — registered, иначе guest
            $cars = $profileResp['result']['data']['cars'] ?? [];
            $hostUserId = $profileResp['result']['data']['user']['host_user_id'] ?? null;
            if (!empty($cars) || $hostUserId) {
                $roleToSet = 'registered';
            }
        }
        // 5. Назначаем роль (если нужно)
        if ($userId) {
            $setRoleResp = $this->botService->callBackendApi('/backend/api/users/set_role.php', [
                'auth' => [
                    'user_id' => $userId,
                    'role' => 'admin' // бот действует как системный админ
                ],
                'data' => [
                    'user_id' => $userId,
                    'new_role_code' => $roleToSet,
                    'reason' => $needCreate ? 'Вступил в чат (создан)' : 'Вступил в чат'
                ]
            ]);
            writeToLog("UserJoinedHandler: set_role response", $setRoleResp);
        }
        // 6. Приветствие (можно кастомизировать под joinType)
        $username = $user['username'] ? "@" . $user['username'] : $user['first_name'];
        $welcomeText = "👋 Привет, $username!\n\n";
        $welcomeText .= "🎉 Добро пожаловать в клуб CabrioRide!\n\n";
        if ($joinType === 'returned') {
            $welcomeText .= "Рады видеть тебя снова!\n\n";
        } elseif ($joinType === 'invited') {
            $welcomeText .= "Тебя добавили в клуб — расскажи о себе!\n\n";
        }
        $welcomeText .= "💬 Расскажи пару слов о себе и переходи в бот для регистрации:\n";
        $welcomeText .= "👉 @CabrioControl_bot";
        $this->botService->sendMessage($chat_id, $welcomeText);
        writeToLog("UserJoinedHandler: Welcome message sent");
    }
} 
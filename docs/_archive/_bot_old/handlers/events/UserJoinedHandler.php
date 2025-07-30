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
        // --- Синхронизируем профиль Telegram-пользователя с backend (создаём или обновляем) ---
        $userSyncResult = $this->botService->syncTelegramRequestorProfile($user);
        $userId = $userSyncResult['user_id'] ?? null;
        $roleToSet = 'guest';
        $needCreate = $userSyncResult['created'] ?? false;
        $role = $userSyncResult['role'] ?? 'external';
        if ($role === 'external') {
            $this->botService->sendNonMemberMessage($chat_id);
            return;
        }
        // Определяем роль: если есть авто или host_user_id — registered, иначе guest
        // (эту логику можно реализовать на backend, если нужно)
        // 5. Назначаем роль (если нужно)
        if ($userId) {
            $setRoleResp = $this->botService->callBackendApi('/users/set_role.php', [
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
        // Вместо хардкода используем имя бота из .env (BOT_NAME)
        $welcomeText .= "👉 @" . getBotName();
        $this->botService->sendMessage($chat_id, $welcomeText);
        writeToLog("UserJoinedHandler: Welcome message sent");
    }
} 
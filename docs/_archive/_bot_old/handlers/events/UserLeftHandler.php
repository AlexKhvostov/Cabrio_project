<?php
/**
 * UserLeftHandler.php
 * Обработчик сценариев выхода пользователя из клубного чата CabrioRide
 * Варианты: сам вышел, удалили, кик, повторный выход и т.д.
 */

class UserLeftHandler {
    private $botService;
    public function __construct($botService) {
        $this->botService = $botService;
    }
    /**
     * Основной обработчик выхода пользователя
     * @param int $chat_id
     * @param array $user — данные пользователя из Telegram
     * @param string $leaveType — тип события: self, kicked, removed, unknown
     */
    public function handle($chat_id, $user, $leaveType = 'unknown') {
        // Логируем тип выхода
        writeToLog("UserLeftHandler: handle", [
            'user_id' => $user['id'],
            'username' => $user['username'] ?? 'Нет username',
            'leaveType' => $leaveType
        ]);
        // --- Синхронизируем профиль Telegram-пользователя с backend (создаём или обновляем) ---
        $userSyncResult = $this->botService->syncTelegramRequestorProfile($user);
        $role = $userSyncResult['role'] ?? 'external';
        $userId = $userSyncResult['user_id'] ?? null;
        if ($role === 'external') {
            // Уже не член клуба, роль понижена, можно завершить обработку
            return;
        }
        // 2. Меняем роль на external (если потребуется)
        if ($userId) {
            $setRoleResp = $this->botService->callBackendApi('/users/set_role.php', [
                'auth' => [
                    'user_id' => $userId,
                    'role' => 'admin'
                ],
                'data' => [
                    'user_id' => $userId,
                    'new_role_code' => 'external',
                    'reason' => $leaveType === 'kicked' ? 'Удалён из чата' : 'Покинул чат'
                ]
            ]);
            writeToLog("UserLeftHandler: set_role (external) response", $setRoleResp);
        }
        $username = $user['username'] ? "@" . $user['username'] : $user['first_name'];
        $farewellText = "😔 $username покинул клуб CabrioRide.\n\n";
        if ($leaveType === 'kicked') {
            $farewellText .= "Пользователь был удалён из чата.\n\n";
        }
        $farewellText .= "Будем скучать! Надеемся увидеться снова! 👋";
        $this->botService->sendMessage($chat_id, $farewellText);
        writeToLog("UserLeftHandler: Farewell message sent");
    }
} 
# 🛠️ Гайд по созданию обработчиков (handlers) Telegram-бота CabrioRide

---

## 1. Общая структура обработчика
- Каждый обработчик — отдельный PHP-файл в папке `bot/handlers/`
- Имя файла: `ИмяHandler.php` (CamelCase)
- Имя класса: `ИмяHandler`
- Класс реализует публичный метод (например, `execute($message)` или `handle(...)`)
- Для доступа к Telegram API используйте сервис `BotService`
- Для логирования используйте класс `Logger`

---

## 2. Шаблон обработчика
```php
require_once __DIR__ . '/../services/BotService.php';
require_once __DIR__ . '/../utils/Logger.php';

class ExampleHandler {
    private $botService;
    private $logger;
    public function __construct($botService) {
        $this->botService = $botService;
        $this->logger = new Logger();
    }
    public function execute($message) {
        try {
            $this->logger->info('ExampleHandler: called', $message);
            // 1. Синхронизация профиля пользователя
            $user = $message['from'];
            $userSyncResult = $this->botService->syncTelegramRequestorProfile($user);
            $role = $userSyncResult['role'] ?? 'external';
            if ($role === 'external') {
                $this->botService->sendNonMemberMessage($message['chat']['id']);
                return;
            }
            // 2. Основная логика обработчика
            // ...
        } catch (Exception $e) {
            $this->logger->error('ExampleHandler: Ошибка', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            $this->botService->sendMessage($message['chat']['id'], '❌ Произошла ошибка. Попробуйте позже.');
        }
    }
}
```

---

## 3. Важные аспекты
- **Синхронизация профиля:**
  - В каждом обработчике обязательно вызывайте `$this->botService->syncTelegramRequestorProfile($user);`
  - Используйте результат для получения актуального user_id и роли.
  - Проверяйте роль: если `external` — не давайте доступ к клубным функциям.
- **Логирование:**
  - Используйте `$this->logger->info()` и `$this->logger->error()` для всех ключевых этапов и ошибок.
- **Обращение к backend:**
  - Для вызова API используйте `$this->botService->callBackendApi($endpoint, $payload);`
  - Формируйте payload по стандарту из [API_STANDARD.md](../backend/API_STANDARD.md)
- **Обработка ошибок:**
  - Всегда оборачивайте логику в try/catch.
  - Пользователю отправляйте только понятные сообщения.
- **Не дублируйте логику:**
  - Вынесите повторяющееся в сервисы (BotService, Logger и др.)

---

## 4. Шаблон обращения к backend-эндпоинтам
```php
$result = $this->botService->callBackendApi('/cars/add.php', [
    'auth' => [ 'user_id' => $user_id, 'role' => $role ],
    'data' => [ /* ... */ ]
]);
```
- Все обращения к backend должны соответствовать [API_STANDARD.md](../backend/API_STANDARD.md)

---

## 5. Важные документы
- [README по командам](commands/README.md)
- [API_STANDARD.md](../backend/API_STANDARD.md)
- [DOC_HANDLERS_LIST.md](DOC_HANDLERS_LIST.md)
- [SESSION_NOTES.md](../SESSION_NOTES.md)

---

## 6. Чек-лист для нового обработчика
- [ ] Класс и файл названы по шаблону
- [ ] Подключён BotService и Logger
- [ ] Вся логика в одном публичном методе
- [ ] Синхронизация профиля через syncTelegramRequestorProfile
- [ ] Проверка роли через результат syncTelegramRequestorProfile
- [ ] Внешние запросы через BotService
- [ ] Все ошибки и этапы логируются
- [ ] Нет дублирования кода
- [ ] Код соответствует стандарту проекта

---

**Если есть вопросы — см. примеры в существующих обработчиках и [README](commands/README.md).** 
# 📋 Список обработчиков (handlers) Telegram-бота CabrioRide

---

## Общая информация
В этом документе перечислены все обработчики событий и сообщений, которые реализуют бизнес-логику бота CabrioRide. Для каждого обработчика указано назначение, где используется и какие ключевые функции вызываются.

---

### Событийные обработчики (events)

- **UserJoinedHandler.php**
  - **Назначение:** обработка входа пользователя в клубный чат (вступил, вернулся, был приглашён)
  - **Где используется:** групповой чат
  - **Ключевые функции:** syncTelegramRequestorProfile, set_role, sendWelcomeMessage

- **UserLeftHandler.php**
  - **Назначение:** обработка выхода пользователя из клубного чата (вышел, кик, удалён)
  - **Где используется:** групповой чат
  - **Ключевые функции:** syncTelegramRequestorProfile, set_role, sendMessage

---

### Обработчики сообщений (messages)

- **PhotoPlusPlusHandler.php**
  - **Назначение:** обработка фото с подписью '++' — добавление авто в базу
  - **Где используется:** групповой чат
  - **Ключевые функции:** syncTelegramRequestorProfile, callBackendApi (OCR, cars/check, cars/add), sendMessage

- **PhotoQuestionHandler.php**
  - **Назначение:** обработка фото с подписью '?' — поиск авто по фото
  - **Где используется:** групповой чат, личка
  - **Ключевые функции:** syncTelegramRequestorProfile, callBackendApi (OCR, ocr/check), sendMessage

- **PhotoExclamationHandler.php**
  - **Назначение:** обработка фото с подписью '!' — оставить визитку
  - **Где используется:** групповой чат
  - **Ключевые функции:** syncTelegramRequestorProfile, callBackendApi (business-cards/auto_add), sendMessage

- **TextPlateSearchHandler.php**
  - **Назначение:** текстовый поиск авто по номеру
  - **Где используется:** групповой чат, личка
  - **Ключевые функции:** syncTelegramRequestorProfile, callBackendApi (ocr/check), sendMessage

---

### Команды (commands)

- **HelpCommand.php**
  - **Назначение:** команда /help — справка по возможностям бота
  - **Где используется:** групповой чат, личка
  - **Ключевые функции:** syncTelegramRequestorProfile, sendInlineKeyboard

- **StartCommand.php**
  - **Назначение:** команда /start — приветствие и запуск бота
  - **Где используется:** личка
  - **Ключевые функции:** syncTelegramRequestorProfile, sendWelcomeMessage

---

## Важно!
- В каждом обработчике и команде обязательно вызывается syncTelegramRequestorProfile для синхронизации профиля пользователя с backend.
- Проверка роли (guest/member/external) всегда строится на результате этой функции.
- Не дублируйте логику — используйте сервисы и шаблоны.

---

**См. также:**
- [README по командам](commands/README.md)
- [API_STANDARD.md](../backend/API_STANDARD.md)
- [SESSION_NOTES.md](../SESSION_NOTES.md) 
# 📖 Гайд по созданию API-эндпоинтов CabrioRide

---

## 🏗️ Архитектурные принципы

- **Единый стандарт API**: все эндпоинты принимают POST-запросы с JSON-структурой `{ auth, data }` и возвращают стандартный JSON-ответ.
- **Минимальная логика**: реализуем только необходимую бизнес-логику, избегаем избыточных проверок и усложнения (см. "Принцип не усложнения").
- **Строгая типизация и структура**: все поля запроса и ответа должны быть описаны и валидированы.
- **Права доступа**: все проверки доступа централизованы, роли пользователей строго соответствуют [docs/USER_ROLES.md](../docs/USER_ROLES.md).
- **Логирование**: все важные действия (особенно модерация, смена ролей, автоматические изменения) логируются в соответствующие таблицы (например, `moderation_logs`).

---

## 📚 Используемые классы и файлы

- **ApiHandler** (`backend/utils/ApiHandler.php`): базовый класс для всех эндпоинтов. Отвечает за парсинг запроса, валидацию, формирование ответа, проверку прав.
- **Database** (`backend/utils/Database.php`): singleton для подключения к MySQL.
- **Response** (`backend/utils/Response.php`): форматирование ответа (если не через ApiHandler).
- **Request** (`backend/utils/Request.php`): работа с параметрами запроса (если нужно).
- **docs/DATABASE_SCHEMA.md**: описание структуры таблиц, связей, индексов.
- **docs/USER_ROLES.md**: описание ролей, переходов, сценариев.
- **backend/API_STANDARD.md**: стандарт структуры запроса и ответа, коды ошибок, примеры.

---

## 📝 Структура запроса

```json
{
  "auth": {
    "user_id": 123,
    "role": "member",
    "token": "...", // если требуется
    "session_id": "..." // если требуется
  },
  "data": {
    // специфичные поля для конкретного эндпоинта
  }
}
```

- **auth**: всегда содержит идентификатор пользователя и его роль. Для защищённых эндпоинтов — также токен/сессию.
- **data**: только необходимые поля для бизнес-логики данного эндпоинта.

---

## 📝 Структура ответа

```json
// Успех
{
  "success": true,
  "timestamp": "2024-07-22T12:00:00Z",
  "request_id": "req_...",
  "auth": { "user_id": 123, "role": "member" },
  "result": {
    "message": "Операция выполнена успешно",
    "data": { /* ... */ }
  },
  "error": null
}

// Ошибка
{
  "success": false,
  "timestamp": "2024-07-22T12:00:00Z",
  "request_id": "req_...",
  "auth": { "user_id": 123, "role": "member" },
  "result": null,
  "error": {
    "code": 400,
    "type": "VALIDATION_ERROR",
    "message": "Описание ошибки",
    "details": { /* ... */ }
  }
}
```

- Формат строго описан в [backend/API_STANDARD.md](../backend/API_STANDARD.md).

---

## ⚡ Принцип "НЕ УСЛОЖНЕНИЯ"

- Не добавляйте лишних проверок, сложной валидации, дополнительных полей без необходимости.
- Не усложняйте логику, ответы, обработку ошибок.
- Примеры правильного и неправильного подхода — см. [backend/API_STANDARD.md](../backend/API_STANDARD.md).

---

## 🔑 Проверка прав доступа

- Все проверки доступа должны опираться на роль пользователя (`auth.role`).
- Для каждой функции/эндпоинта определяйте минимальную роль доступа через [config/sectionGroups.php](../config/sectionGroups.php) — это единая точка правды для схемы доступа.
- Для модерации, смены ролей и других критичных действий — обязательно логирование в `moderation_logs`.

---

## 🛠️ Подключение к БД

- Используйте класс `Database` для получения PDO:
  ```php
  $db = Database::getInstance()->getConnection();
  ```
- Не используйте прямое подключение через `new PDO` вне класса Database.
- Все запросы должны быть подготовленными (prepared statements).

---

## 🗂️ Логирование действий

- Для всех действий модераторов, смены ролей, автоматических изменений используйте таблицу `moderation_logs` (см. [docs/DATABASE_SCHEMA.md](../docs/DATABASE_SCHEMA.md)).
- Для системных действий используйте `moderator_id = NULL`.
- В поле `action` пишите, например, `set_role_member`.
- В поле `reason` — причину смены роли или комментарий.

---

## 🧩 Пример шаблона эндпоинта

```php
require_once __DIR__ . '/../utils/ApiHandler.php';
require_once __DIR__ . '/../utils/Database.php';

class ExampleEndpoint extends ApiHandler {
    protected function process() {
        // Проверка прав
        $this->checkAccess('member');
        // Получение данных
        $userId = $this->getAuth('user_id');
        $data = $this->data;
        // ... бизнес-логика ...
        return $this->success([ 'result' => 'ok' ], 'Операция выполнена');
    }
}

$endpoint = new ExampleEndpoint();
$endpoint->handle();
```

---

## 📎 Ссылки на ключевые документы

- [config/sectionGroups.php](../config/sectionGroups.php) — схема доступа и минимальные роли для всех функций/эндпоинтов
- [docs/USER_ROLES.md](../docs/USER_ROLES.md) — описание ролей и переходов
- [docs/DATABASE_SCHEMA.md](../docs/DATABASE_SCHEMA.md) — структура таблиц
- [backend/API_STANDARD.md](../backend/API_STANDARD.md) — стандарт API
- [backend/utils/ApiHandler.php](../backend/utils/ApiHandler.php) — базовый класс эндпоинтов
- [backend/utils/Database.php](../backend/utils/Database.php) — подключение к БД
- [backend/utils/Response.php](../backend/utils/Response.php) — форматирование ответа
- [backend/utils/Request.php](../backend/utils/Request.php) — работа с запросом

---

## 🟢 Рекомендации для будущих разработчиков

- Всегда следуйте единому стандарту API.
- Не усложняйте бизнес-логику без необходимости.
- Всегда валидируйте входные данные.
- Проверяйте права доступа и логируйте критичные действия.
- Используйте только подготовленные запросы к БД.
- Обновляйте документацию при изменении архитектуры или бизнес-логики.
- Всегда сверяйтесь с [docs/USER_ROLES.md](../docs/USER_ROLES.md) и [backend/API_STANDARD.md](../backend/API_STANDARD.md) при добавлении новых функций.

---

> Если есть вопросы — сверяйтесь с этим файлом и ключевыми документами. Соблюдение архитектурных принципов гарантирует стабильность и безопасность проекта! 
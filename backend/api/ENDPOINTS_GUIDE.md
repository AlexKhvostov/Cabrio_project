# 📖 Гайд по созданию API-эндпоинтов CabrioRide

---

## 🏗️ Архитектурные принципы

- Все эндпоинты реализуются в виде отдельных PHP-файлов в папке `backend/api/`.
- Каждый эндпоинт — отдельный класс, наследующийся от `ApiHandler`.
- Вся логика валидации, проверки прав, формирования ответа, логирования и работы с БД инкапсулирована в соответствующих классах.
- В коде эндпоинта не должно быть ручной работы с JSON, ручной проверки ролей, ручного формирования ответа — только вызовы методов классов.

---

## 📚 Обязательные классы и файлы

- **ApiHandler** (`backend/utils/ApiHandler.php`): базовый класс для всех эндпоинтов. Инкапсулирует парсинг запроса, валидацию, формирование ответа, проверку прав.
- **Database** (`backend/utils/Database.php`): singleton для подключения к MySQL. Используйте только его для работы с БД.
- **SessionMiddleware** (`backend/middleware/SessionMiddleware.php`): централизованная проверка токена, сессии, прав доступа (используется для защищённых эндпоинтов).
- **config.php** (`backend/config/config.php`): подключение всех параметров окружения, токенов, ключей, глобальных настроек. Должен быть подключён во всех эндпоинтах.
- **sectionGroups.php** (`config/sectionGroups.php`): схема доступа и минимальные роли для всех функций/эндпоинтов.

---

## 🧩 Шаблон вызова методов классов

```php
<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../utils/Database.php';
require_once __DIR__ . '/../../utils/ApiHandler.php';
// Если требуется авторизация по токену:
// require_once __DIR__ . '/../../middleware/SessionMiddleware.php';
// Если требуется схема доступа:
// require_once __DIR__ . '/../../../config/sectionGroups.php';

class ExampleEndpoint extends ApiHandler {
    protected function process() {
        // Проверка прав (минимальная роль)
        $this->checkAccess('member');
        // Получение данных из запроса
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

## ✅ Чек-лист для нового эндпоинта

- [ ] Подключён `config.php` (всегда)
- [ ] Наследование от `ApiHandler`
- [ ] Работа с БД только через `Database`
- [ ] Проверка прав через `checkAccess()` и/или `SessionMiddleware` (если требуется)
- [ ] Формирование ответа только через методы `ApiHandler`
- [ ] Все параметры и ключи берутся из конфига/окружения
- [ ] Нет ручной работы с JSON, Response, Request
- [ ] Все критичные действия логируются через соответствующие методы классов
- [ ] Документация и структура соответствуют стандарту

---

## 🟢 Рекомендации

- Не реализуйте вручную логику, уже инкапсулированную в классах (валидация, права, ответы, логирование).
- Используйте только публичные методы классов (`checkAccess`, `requireField`, `getAuth`, `getData`, `success`, `error` и др.).
- Для сложных кейсов — смотрите документацию к классам и примеры в существующих эндпоинтах.
- Все детали формата запроса/ответа, коды ошибок, структура данных — реализованы внутри классов и описаны в их документации.

---

> Если есть вопросы — сверяйтесь с этим файлом и документацией к классам. Соблюдение архитектурных принципов гарантирует стабильность и безопасность проекта! 

---

## 🚩 Обязательные требования к новому эндпоинту

- **Именование:**  
  - Имя файла — в snake_case (например, set_role.php)  
  - Имя класса — в CamelCase + Endpoint (например, SetRoleEndpoint)
- **Комментарий:**  
  - В начале файла — краткое описание назначения эндпоинта (1-2 строки)
- **Права доступа:**  
  - Минимальная роль явно указывается через `$this->checkAccess('role')`
- **Обработка ошибок:**  
  - Только через `$this->error()`, никаких echo/exit
- **Тестирование:**  
  - Для каждого эндпоинта — ручной тест или автотест, ссылка на тестовую страницу
- **Документация:**  
  - После добавления — обновить API_READY_ENDPOINTS.md и/или API_ENDPOINTS.md
- **Логирование:**  
  - Все критичные действия логировать через методы/таблицы, не вручную 

---

## 📚 Полезные инструкции

- [Инструкция по ApiHandler](../utils/ApiHandler_README.md)
- [Инструкция по Database](../utils/Database_README.md)
- [Инструкция по Logger](../utils/Logger_README.md) 

---

## 🛠️ Как работать с Database.php (PDO)

Для работы с базой данных используйте только Database::getInstance()->getConnection(), который возвращает PDO:

```php
$pdo = Database::getInstance()->getConnection();
// Для одной строки
$stmt = $pdo->prepare('SELECT * FROM cars WHERE id = :car_id');
$stmt->execute(['car_id' => $carId]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
// Для массива строк
$stmt = $pdo->prepare('SELECT * FROM cars');
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Для обновления/вставки
$stmt = $pdo->prepare('UPDATE cars SET color = :color WHERE id = :car_id');
$stmt->execute(['color' => $color, 'car_id' => $carId]);
```

- Не используйте прямой new PDO — только через Database.php
- Не используйте устаревшие методы типа mysql_query
- Всегда используйте подготовленные запросы (prepare/execute)

---

## 🧩 Как работать с ApiHandler.php

Все эндпоинты должны наследоваться от ApiHandler:

```php
require_once __DIR__ . '/../../utils/ApiHandler.php';

class MyEndpoint extends ApiHandler {
    protected function process() {
        $this->checkAccess('member');
        $userId = $this->getAuth('user_id');
        $value = $this->requireField('some_field');
        // ...логика...
        return $this->success(['result' => 'ok']);
    }
}
$endpoint = new MyEndpoint();
$endpoint->handle();
```

- Не реализуйте ручную обработку JSON, ошибок, прав — только через методы класса.
- Для успешного ответа используйте $this->success($data, $message)
- Для ошибок — $this->error($message, $code, $type, $details)
- Для проверки прав — $this->checkAccess('role')
- Для обязательных полей — $this->requireField('field')
- Для получения auth/data — $this->getAuth('key'), $this->getData('key')

--- 

---

## 🧪 Ручное тестирование эндпоинтов

- Для каждого нового эндпоинта обязательно создаётся отдельная ручная тестовая страница в каталоге `backend/_test/` (или подпапке по сущности).
- Тестовая страница должна содержать:
    - Форму для ввода всех необходимых полей запроса
    - Предпросмотр JSON-запроса
    - Кнопку отправки и вывод ответа API
    - Подробные комментарии для не программиста (что тестируется, как пользоваться)
- После создания теста обязательно добавить ссылку на тестовую страницу в меню `backend/_test/index.php` в соответствующий раздел (например, "🚗 Автомобили", "👤 Пользователи" и т.д.).
- После успешного ручного тестирования эндпоинта — добавить автотест (если применимо) и обновить документацию.
- Пример оформления тестовой страницы см. в `backend/_test/photos/add_test.php`, `backend/_test/cars/update_test.php` и других.

--- 
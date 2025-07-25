# Инструкция по использованию класса ApiHandler

**Назначение:**
Базовый класс для всех API-эндпоинтов. Инкапсулирует парсинг запроса, валидацию, проверку прав, формирование ответа, логирование.

**Когда использовать:**
Всегда при создании нового эндпоинта (класс-наследник).

**Как подключать:**
```php
require_once __DIR__ . '/../../utils/ApiHandler.php';
```

**Публичные методы:**
- `handle()` — основной запуск обработки запроса (вызывает process, логирует, обрабатывает ошибки)
- `checkAccess($role)` — проверка минимальной роли пользователя
- `getAuth($key)` — получить значение из блока auth
- `getData($key)` — получить значение из блока data
- `requireField($field)` — проверить обязательное поле
- `success($data, $message)` — вернуть успешный ответ
- `error($message, $code, $type, $details)` — вернуть ошибку

**Пример:**
```php
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

**Best practices:**
- Не реализуйте ручную обработку JSON, ошибок, прав — только через методы класса.
- Не переопределяйте handle(), кроме как для расширения стандартного поведения.
- Все критичные действия логируйте через logRequest(). 
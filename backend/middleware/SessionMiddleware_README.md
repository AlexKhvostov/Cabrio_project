# SessionMiddleware — миниинструкция по использованию

---

## Назначение

Класс `SessionMiddleware` централизованно проверяет авторизацию пользователя по токену, валидирует сессию и права доступа.

---

## Как использовать

1. **Подключите класс:**
   ```php
   require_once __DIR__ . '/../../middleware/SessionMiddleware.php';
   ```

2. **Создайте экземпляр:**
   ```php
   $middleware = new SessionMiddleware($db, $config);
   ```
   - `$db` — PDO-подключение (через Database.php)
   - `$config` — массив с конфигом (если требуется)

3. **Вызовите handle:**
   ```php
   $result = $middleware->handle($auth);
   if ($result && isset($result['error'])) {
       // Ошибка авторизации
       // ...
   }
   ```
   - `$auth` — массив с полями user_id, role, token (из запроса)

4. **Получите пользователя и сессию:**
   ```php
   $user = $middleware->getUser();
   $session = $middleware->getSession();
   ```
   - `$user` — массив с данными пользователя
   - `$session` — массив сессии

---

## Пример
```php
$middleware = new SessionMiddleware($db, $config);
$result = $middleware->handle($auth);
if ($result && isset($result['error'])) {
    // Возврат ошибки
    return $this->error($result['error'], 401);
}
$user = $middleware->getUser();
$session = $middleware->getSession();
// ... дальнейшая бизнес-логика ...
```

---

## Важно
- Не обращайтесь к приватным методам напрямую!
- Используйте только публичные методы `getUser()` и `getSession()` для получения результата.
- Все проверки доступа и логика авторизации должны идти через этот middleware. 
# 📖 Инструкция по созданию новых команд Telegram-бота CabrioRide

---

> ⚠️ **Внимание!**
> С 2024-07-23 для логирования используем только класс `Logger`, а не функцию `writeToLog()`. Пример:
> ```php
> $logger = new Logger();
> $logger->info('Текст сообщения', [ 'ключ' => 'значение' ]);
> ```

---

## 🏗️ Архитектура и структура
- Каждая команда — отдельный PHP-класс в каталоге `bot/commands/`
- Имя файла: `ИмяКомандыCommand.php` (CamelCase)
- Имя класса: `ИмяКомандыCommand`
- Класс должен реализовывать публичный метод `execute($message)`
- Для доступа к Telegram API используйте сервис `BotService`
- Для логирования используйте класс `Logger`

---

## 📚 Обязательные подключения
```php
require_once __DIR__ . '/../services/BotService.php';
require_once __DIR__ . '/../utils/Logger.php';
```

---

## 🧩 Шаблон класса команды
```php
class ExampleCommand {
    private $botService;
    private $logger;
    public function __construct($botService) {
        $this->botService = $botService;
        $this->logger = new Logger();
    }
    public function execute($message) {
        try {
            $this->logger->info('ExampleCommand: called', $message);
            // ...логика команды...
            $this->botService->sendMessage($message['chat']['id'], 'Выполнено!');
        } catch (Exception $e) {
            $this->logger->error('ExampleCommand: Ошибка', ['error' => $e->getMessage()]);
            $this->botService->sendMessage($message['chat']['id'], '❌ Произошла ошибка.');
        }
    }
}
```

---

## ✅ Best practices
- Всегда логируйте старт, ключевые этапы и ошибки через `$this->logger->info()`/`error()`
- Все внешние запросы (API, Telegram) оборачивайте в try/catch
- Не используйте глобальные переменные
- Не дублируйте логику — выносите повторяющееся в сервисы
- Не храните чувствительные данные в логах
- Не используйте exit/echo — только return и sendMessage
- Для работы с API используйте методы BotService (postJson, callBackendApi и др.)

---

## 📝 Рекомендации по обработке ошибок
- Всегда отправляйте пользователю понятное сообщение об ошибке
- В логах фиксируйте stack trace для отладки через `$this->logger->error(..., ['trace' => $e->getTraceAsString()])`
- Не раскрывайте внутренние детали ошибок пользователю

---

## 🚦 Пример шаблона новой команды
```php
require_once __DIR__ . '/../services/BotService.php';
require_once __DIR__ . '/../utils/Logger.php';

class MyNewCommand {
    private $botService;
    private $logger;
    public function __construct($botService) {
        $this->botService = $botService;
        $this->logger = new Logger();
    }
    public function execute($message) {
        try {
            $this->logger->info('MyNewCommand: called', $message);
            // 1. Ваша логика
            // 2. Внешние запросы через $this->botService
            // 3. Ответ пользователю
            $this->botService->sendMessage($message['chat']['id'], 'Ваша команда выполнена!');
        } catch (Exception $e) {
            $this->logger->error('MyNewCommand: Ошибка', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            $this->botService->sendMessage($message['chat']['id'], '❌ Произошла ошибка. Попробуйте позже.');
        }
    }
}
```

---

## 🟢 Рекомендации по стилю
- Используйте CamelCase для классов и snake_case для файлов
- Оставляйте подробные комментарии к ключевым этапам
- Следуйте PSR-12 и внутренним стандартам CabrioRide
- Не добавляйте лишнюю логику — только то, что нужно по ТЗ

---

## 📋 Чек-лист для новой команды
- [ ] Класс и файл названы по шаблону
- [ ] Подключён BotService и Logger
- [ ] Вся логика в execute()
- [ ] Все ошибки и этапы логируются через Logger
- [ ] Внешние запросы в try/catch
- [ ] Пользователь получает понятный ответ
- [ ] Нет дублирования кода
- [ ] Код соответствует стандарту проекта 

---

## 🛡️ Логирование и обработка ошибок

### 1. Логирование этапов и ошибок
Используйте функцию `writeToLog()` для всех ключевых этапов, ошибок и внешних запросов:

```php
writeToLog('MyCommand: старт', $message);
writeToLog('MyCommand: отправка запроса к API', ['url' => $url, 'payload' => $data]);
writeToLog('MyCommand: успешное завершение');
```

### 2. Обработка ошибок через try/catch
Оборачивайте всю логику команды в try/catch. В случае ошибки:
- Логируйте текст ошибки и stack trace
- Пользователю отправляйте только понятное сообщение

```php
try {
    // ...логика...
} catch (Exception $e) {
    writeToLog('MyCommand: Ошибка', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    $this->botService->sendMessage($message['chat']['id'], '❌ Произошла ошибка. Попробуйте позже.');
}
```

### 3. Логирование stack trace
В случае исключения обязательно пишите в лог stack trace:

```php
writeToLog('Ошибка', [
    'error' => $e->getMessage(),
    'trace' => $e->getTraceAsString()
]);
```

### 4. Логирование внешних запросов
Перед каждым внешним запросом (API, Telegram) пишите в лог:

```php
writeToLog('MyCommand: Запрос к API', ['url' => $url, 'payload' => $data]);
// ...
writeToLog('MyCommand: Ответ API', ['response' => $result]);
```

### 5. Логирование успешного завершения
В конце успешного выполнения команды:

```php
writeToLog('MyCommand: успешно завершено');
```

---

## 📋 Чек-лист по логированию для команды
- [ ] Логируется старт команды
- [ ] Логируются все внешние запросы (до и после)
- [ ] Логируются ключевые этапы
- [ ] Все ошибки и stack trace пишутся в лог
- [ ] Пользователь получает только понятное сообщение
- [ ] В конце пишется успешное завершение

--- 
# ✅ ValidationHelper

> Утилита для валидации входных данных в CabrioRide

## 📋 Назначение

`ValidationHelper` — утилита для централизованной валидации входных данных. Обеспечивает:

- Проверку наличия обязательных полей
- Валидацию email адресов
- Проверку типов данных
- Централизованную обработку ошибок валидации

## 🏗️ Архитектура

### Основные методы

#### `requireFields($data, $fields)`
Проверяет наличие обязательных полей в массиве данных:

```php
$data = [
    'first_name' => 'Иван',
    'last_name' => 'Иванов',
    'email' => 'ivan@example.com'
];

ValidationHelper::requireFields($data, ['first_name', 'email']);
// Если поле отсутствует - выбрасывает исключение
```

#### `validateEmail($email)`
Проверяет корректность email адреса:

```php
ValidationHelper::validateEmail('ivan@example.com'); // ✅ Успешно
ValidationHelper::validateEmail('invalid-email');     // ❌ Исключение
```

#### `validateInt($value, $fieldName)`
Проверяет, что значение является целым числом:

```php
ValidationHelper::validateInt(123, 'user_id');     // ✅ Успешно
ValidationHelper::validateInt('abc', 'user_id');   // ❌ Исключение
ValidationHelper::validateInt(12.5, 'user_id');    // ❌ Исключение
```

## 📝 Примеры использования

### В контроллерах
```php
// Валидация данных при создании пользователя
public function createUser($data) {
    try {
        // Проверяем обязательные поля
        ValidationHelper::requireFields($data, [
            'first_name',
            'last_name',
            'telegram_id'
        ]);
        
        // Валидируем email если передан
        if (isset($data['email'])) {
            ValidationHelper::validateEmail($data['email']);
        }
        
        // Валидируем telegram_id
        ValidationHelper::validateInt($data['telegram_id'], 'telegram_id');
        
        // Создаем пользователя
        $user = User::create($data);
        echo ResponseHelper::success($user);
        
    } catch (Exception $e) {
        echo ResponseHelper::error('VALIDATION_ERROR', $e->getMessage());
    }
}
```

### В Actions
```php
// Валидация в бизнес-логике
public static function handle($data) {
    try {
        // Проверяем обязательные поля
        ValidationHelper::requireFields($data, [
            'car_model',
            'owner_user_id'
        ]);
        
        // Валидируем ID владельца
        ValidationHelper::validateInt($data['owner_user_id'], 'owner_user_id');
        
        // Выполняем бизнес-логику
        $result = self::processCarCreation($data);
        return $result;
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => [
                'code' => 'VALIDATION_ERROR',
                'message' => $e->getMessage()
            ]
        ];
    }
}
```

### В моделях
```php
// Валидация в модели
public static function create($data) {
    // Проверяем обязательные поля
    ValidationHelper::requireFields($data, [
        'first_name',
        'telegram_id'
    ]);
    
    // Валидируем telegram_id
    ValidationHelper::validateInt($data['telegram_id'], 'telegram_id');
    
    // Валидируем email если есть
    if (isset($data['email'])) {
        ValidationHelper::validateEmail($data['email']);
    }
    
    // Создаем запись
    $pdo = Database::getInstance();
    $stmt = $pdo->prepare('INSERT INTO users (first_name, telegram_id, email) VALUES (?, ?, ?)');
    $stmt->execute([
        $data['first_name'],
        $data['telegram_id'],
        $data['email'] ?? null
    ]);
    
    return $pdo->lastInsertId();
}
```

## 🚨 Обработка ошибок

### Типы исключений
```php
// Отсутствующее обязательное поле
throw new Exception("Missing required field: email");

// Некорректный email
throw new Exception("Некорректный email: invalid-email");

// Некорректный тип данных
throw new Exception("Поле user_id должно быть целым числом");
```

### Пример обработки
```php
try {
    ValidationHelper::requireFields($data, ['email', 'password']);
    ValidationHelper::validateEmail($data['email']);
    
    // Продолжаем обработку
    $result = processData($data);
    
} catch (Exception $e) {
    // Логируем ошибку валидации
    Logger::warning('Validation failed', [
        'data' => $data,
        'error' => $e->getMessage()
    ]);
    
    // Возвращаем ошибку клиенту
    return ResponseHelper::error('VALIDATION_ERROR', $e->getMessage());
}
```

## 🔧 Конфигурация

### Настройка валидации
```php
// Можно настроить строгость валидации
$strictMode = getenv('VALIDATION_STRICT_MODE') === 'true';

// Кастомные правила валидации
$customRules = [
    'telegram_id' => 'required|integer|min:1',
    'email' => 'required|email',
    'first_name' => 'required|string|max:50'
];
```

### Расширение функционала
```php
// Добавление новых методов валидации
public static function validateString($value, $fieldName, $maxLength = null) {
    if (!is_string($value)) {
        throw new Exception("Поле $fieldName должно быть строкой");
    }
    
    if ($maxLength && strlen($value) > $maxLength) {
        throw new Exception("Поле $fieldName не должно превышать $maxLength символов");
    }
}

public static function validateArray($value, $fieldName) {
    if (!is_array($value)) {
        throw new Exception("Поле $fieldName должно быть массивом");
    }
}
```

## 📊 Структура валидации

### Обязательные поля
```php
$requiredFields = [
    'first_name',    // Имя пользователя
    'telegram_id',   // ID в Telegram
    'car_model'      // Модель автомобиля
];

ValidationHelper::requireFields($data, $requiredFields);
```

### Опциональные поля с валидацией
```php
// Email (опциональный, но если передан - должен быть валидным)
if (isset($data['email'])) {
    ValidationHelper::validateEmail($data['email']);
}

// ID (обязательный, должен быть числом)
ValidationHelper::validateInt($data['user_id'], 'user_id');
```

## 🔄 Интеграция

### С контроллерами
```php
// Все контроллеры используют ValidationHelper
class UserController extends BaseController {
    public function create($data) {
        try {
            ValidationHelper::requireFields($data, ['first_name', 'telegram_id']);
            // ... создание пользователя
        } catch (Exception $e) {
            echo ResponseHelper::error('VALIDATION_ERROR', $e->getMessage());
        }
    }
}
```

### С Actions
```php
// Actions валидируют входные данные
class CreateCarAction {
    public static function handle($data) {
        try {
            ValidationHelper::requireFields($data, ['model', 'owner_id']);
            ValidationHelper::validateInt($data['owner_id'], 'owner_id');
            // ... бизнес-логика
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
```

### С ResponseHelper
```php
// Интеграция с ResponseHelper для ошибок
try {
    ValidationHelper::requireFields($data, $requiredFields);
    // ... обработка
} catch (Exception $e) {
    echo ResponseHelper::error('VALIDATION_ERROR', $e->getMessage());
}
```

## 📈 Мониторинг

### Метрики валидации
- Количество ошибок валидации
- Популярные ошибки
- Поля с наибольшим количеством ошибок
- Время валидации

### Логирование
```php
Logger::warning('Validation failed', [
    'field' => 'email',
    'value' => 'invalid-email',
    'error' => 'Некорректный email: invalid-email'
]);
```

## 🔗 Связанные компоненты

- **Controllers** — используют ValidationHelper для валидации входных данных
- **Actions** — валидируют данные перед обработкой
- **Models** — используют для валидации при создании/обновлении
- **ResponseHelper** — интегрируется для возврата ошибок валидации

---

**📚 См. также:** [Controllers](../CONTROLLERS/OVERVIEW.md), [Actions](../ACTIONS/OVERVIEW.md), [ResponseHelper](RESPONSE_HELPER.md) 
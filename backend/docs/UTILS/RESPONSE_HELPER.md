# 📤 ResponseHelper

> Утилита для формирования стандартных ответов API CabrioRide

## 📋 Назначение

`ResponseHelper` — утилита для создания консистентных API ответов. Обеспечивает:

- Стандартизированный формат ответов
- Автоматическое установление заголовков
- Поддержку пагинации
- Детализированную обработку ошибок

## 🏗️ Архитектура

### Основные методы

#### `success($data = null, $pagination = null)`
Формирует успешный ответ API:

```php
// Простой успешный ответ
echo ResponseHelper::success(['id' => 123, 'name' => 'BMW Z4']);

// Ответ с пагинацией
echo ResponseHelper::success($cars, [
    'page' => 1,
    'per_page' => 20,
    'total' => 150
]);
```

#### `error($code, $message, $details = null)`
Формирует ответ с ошибкой:

```php
// Простая ошибка
echo ResponseHelper::error('NOT_FOUND', 'Ресурс не найден');

// Ошибка с деталями
echo ResponseHelper::error('VALIDATION_ERROR', 'Некорректные данные', [
    'field' => 'email',
    'message' => 'Неверный формат email'
]);
```

## 📊 Форматы ответов

### Успешный ответ
```json
{
  "success": true,
  "data": {
    "id": 123,
    "model": "BMW Z4",
    "owner": {
      "id": 456,
      "first_name": "Иван"
    }
  },
  "error": null
}
```

### Успешный ответ с пагинацией
```json
{
  "success": true,
  "data": [
    {"id": 1, "model": "BMW Z4"},
    {"id": 2, "model": "Mercedes SLK"}
  ],
  "pagination": {
    "page": 1,
    "per_page": 20,
    "total": 150,
    "pages": 8
  },
  "error": null
}
```

### Ответ с ошибкой
```json
{
  "success": false,
  "data": null,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Некорректные данные",
    "details": {
      "field": "email",
      "message": "Неверный формат email"
    }
  }
}
```

## 📝 Примеры использования

### В контроллерах
```php
// Успешное получение автомобиля
public function getCar($id) {
    $car = Car::findById($id);
    if (!$car) {
        echo ResponseHelper::error('NOT_FOUND', 'Автомобиль не найден');
        return;
    }
    
    echo ResponseHelper::success($car);
}

// Получение списка с пагинацией
public function getCars($page = 1, $perPage = 20) {
    $cars = Car::findAll($page, $perPage);
    $total = Car::count();
    
    $pagination = [
        'page' => $page,
        'per_page' => $perPage,
        'total' => $total,
        'pages' => ceil($total / $perPage)
    ];
    
    echo ResponseHelper::success($cars, $pagination);
}
```

### В Actions
```php
// Успешный результат Action
public static function handle($data) {
    $result = self::processData($data);
    
    if ($result['success']) {
        return ResponseHelper::success($result['data']);
    } else {
        return ResponseHelper::error(
            $result['error']['code'],
            $result['error']['message'],
            $result['error']['details'] ?? null
        );
    }
}
```

### Обработка ошибок валидации
```php
// Валидация входных данных
public function createCar($data) {
    $errors = ValidationHelper::validateCar($data);
    
    if (!empty($errors)) {
        echo ResponseHelper::error('VALIDATION_ERROR', 'Некорректные данные', $errors);
        return;
    }
    
    $car = Car::create($data);
    echo ResponseHelper::success($car);
}
```

### Обработка ошибок доступа
```php
// Проверка прав доступа
public function updateCar($id) {
    if (!AppContext::canEditCar($id)) {
        echo ResponseHelper::error('ACCESS_DENIED', 'Недостаточно прав');
        return;
    }
    
    // Обновление автомобиля
    $car = Car::update($id, $data);
    echo ResponseHelper::success($car);
}
```

## 🔧 Конфигурация

### Заголовки ответа
```php
// Автоматически устанавливает
header('Content-Type: application/json');
```

### Кодировка
```php
// Использует JSON_UNESCAPED_UNICODE для корректного отображения русских символов
json_encode($response, JSON_UNESCAPED_UNICODE);
```

## 🚨 Коды ошибок

### Стандартные коды
- `NOT_FOUND` — ресурс не найден
- `VALIDATION_ERROR` — ошибка валидации
- `ACCESS_DENIED` — недостаточно прав
- `INTERNAL_ERROR` — внутренняя ошибка сервера
- `AUTH_ERROR` — ошибка авторизации
- `RATE_LIMIT` — превышен лимит запросов

### Кастомные коды
```php
// Специфичные для бизнес-логики
echo ResponseHelper::error('CAR_ALREADY_EXISTS', 'Автомобиль уже существует');
echo ResponseHelper::error('USER_NOT_MEMBER', 'Пользователь не является участником');
echo ResponseHelper::error('EVENT_FULL', 'Событие полностью заполнено');
```

## 📊 Пагинация

### Структура пагинации
```php
$pagination = [
    'page' => 1,           // Текущая страница
    'per_page' => 20,      // Элементов на странице
    'total' => 150,        // Общее количество
    'pages' => 8,          // Общее количество страниц
    'has_next' => true,    // Есть следующая страница
    'has_prev' => false    // Есть предыдущая страница
];
```

### Пример использования
```php
// В контроллере
public function getUsers($page = 1, $perPage = 20) {
    $offset = ($page - 1) * $perPage;
    $users = User::findAll($offset, $perPage);
    $total = User::count();
    
    $pagination = [
        'page' => $page,
        'per_page' => $perPage,
        'total' => $total,
        'pages' => ceil($total / $perPage),
        'has_next' => $page < ceil($total / $perPage),
        'has_prev' => $page > 1
    ];
    
    echo ResponseHelper::success($users, $pagination);
}
```

## 🔄 Интеграция

### С контроллерами
```php
// Все контроллеры используют ResponseHelper
class CarController extends BaseController {
    public function index() {
        $cars = Car::findAll();
        echo ResponseHelper::success($cars);
    }
}
```

### С Actions
```php
// Actions возвращают данные через ResponseHelper
class CreateCarAction {
    public static function handle($data) {
        $result = self::process($data);
        return ResponseHelper::success($result);
    }
}
```

### С middleware
```php
// Middleware использует ResponseHelper для ошибок
if (!$authResult['success']) {
    echo ResponseHelper::error('AUTH_ERROR', $authResult['error']['message']);
    exit;
}
```

## 📈 Мониторинг

### Метрики ответов
- Количество успешных ответов
- Количество ошибок по типам
- Время формирования ответа
- Размер ответа

### Логирование
```php
Logger::info('API Response', [
    'endpoint' => '/api/cars',
    'method' => 'GET',
    'success' => true,
    'response_time' => 0.05
]);
```

## 🔗 Связанные компоненты

- **Controllers** — используют ResponseHelper для всех ответов
- **Actions** — возвращают данные через ResponseHelper
- **Middleware** — используют для ошибок авторизации
- **ValidationHelper** — интегрируется с ResponseHelper для ошибок валидации

---

**📚 См. также:** [Controllers](../CONTROLLERS/OVERVIEW.md), [Actions](../ACTIONS/OVERVIEW.md) 
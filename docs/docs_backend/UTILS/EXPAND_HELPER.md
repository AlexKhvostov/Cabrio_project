# 🔄 ExpandHelper

> Утилита для развертывания связанных данных в API ответах

## 📋 Назначение

`ExpandHelper` — утилита для обогащения API ответов связанными данными. Обеспечивает:

- Замену ID на полные объекты в ответах API
- Автоматическое определение типа данных
- Консистентное представление данных
- Упрощение работы с API для клиентов

## 🏗️ Архитектура

### Основные методы

#### `expandCarData($carData)`
Развертывает данные автомобиля, заменяя ID на полные объекты:

```php
$carData = [
    'id' => 123,
    'model' => 'BMW Z4',
    'status_id' => 1,
    'owner_user_id' => 456
];

$expandedCar = ExpandHelper::expandCarData($carData);
// Результат:
// [
//     'id' => 123,
//     'model' => 'BMW Z4',
//     'status' => ['id' => 1, 'code' => 'active', 'name' => 'Активен'],
//     'owner' => ['id' => 456, 'first_name' => 'Иван', ...]
// ]
```

#### `expandUserData($userData)`
Развертывает данные пользователя:

```php
$userData = [
    'id' => 456,
    'first_name' => 'Иван',
    'role_id' => 3
];

$expandedUser = ExpandHelper::expandUserData($userData);
// Результат:
// [
//     'id' => 456,
//     'first_name' => 'Иван',
//     'role' => ['id' => 3, 'code' => 'member', 'name' => 'Участник']
// ]
```

#### `autoExpand($data, $type = null)`
Автоматически определяет тип данных и развертывает их:

```php
$expandedData = ExpandHelper::autoExpand($data, 'car');
// Автоматически вызывает expandCarData если тип 'car'
```

## 📊 Типы развертывания

### 1. Автомобили (Cars)
```php
// До развертывания
[
    'id' => 123,
    'model' => 'BMW Z4',
    'status_id' => 1,
    'owner_user_id' => 456,
    'brand_id' => 2,
    'creator_user_id' => 789
]

// После развертывания
[
    'id' => 123,
    'model' => 'BMW Z4',
    'status' => [
        'id' => 1,
        'code' => 'active',
        'name' => 'Активен',
        'color' => '#28a745'
    ],
    'owner' => [
        'id' => 456,
        'first_name' => 'Иван',
        'last_name' => 'Иванов',
        'role' => ['id' => 3, 'code' => 'member']
    ],
    'brand' => [
        'id' => 2,
        'name' => 'BMW',
        'logo_url' => 'https://...'
    ],
    'creator' => [
        'id' => 789,
        'first_name' => 'Петр',
        'last_name' => 'Петров'
    ]
]
```

### 2. Пользователи (Users)
```php
// До развертывания
[
    'id' => 456,
    'first_name' => 'Иван',
    'last_name' => 'Иванов',
    'role_id' => 3,
    'photo_id' => 10
]

// После развертывания
[
    'id' => 456,
    'first_name' => 'Иван',
    'last_name' => 'Иванов',
    'role' => [
        'id' => 3,
        'code' => 'member',
        'name' => 'Участник',
        'color' => '#007bff'
    ],
    'photo' => [
        'id' => 10,
        'url' => 'https://t.me/i/userpic/320/...',
        'file_name' => 'avatar.jpg'
    ]
]
```

### 3. События (Events)
```php
// До развертывания
[
    'id' => 789,
    'title' => 'Поездка в Сочи',
    'event_type_id' => 1,
    'org_user_id' => 456,
    'status_id' => 2
]

// После развертывания
[
    'id' => 789,
    'title' => 'Поездка в Сочи',
    'event_type' => [
        'id' => 1,
        'code' => 'trip',
        'name' => 'Поездка',
        'color' => '#ffc107'
    ],
    'organizer' => [
        'id' => 456,
        'first_name' => 'Иван',
        'last_name' => 'Иванов'
    ],
    'status' => [
        'id' => 2,
        'code' => 'planned',
        'name' => 'Запланировано'
    ]
]
```

## 🔧 Конфигурация

### Настройка развертывания
```php
// Включение/отключение развертывания
$enableExpansion = getenv('ENABLE_DATA_EXPANSION') !== 'false';

// Максимальная глубина развертывания
$maxExpansionDepth = 3;
```

### Кастомные поля
```php
// Добавление кастомных полей для развертывания
$customFields = [
    'car' => ['custom_field_id' => 'custom_field'],
    'user' => ['preference_id' => 'preference']
];
```

## 📝 Примеры использования

### Развертывание одного автомобиля
```php
$carData = Car::findById(123);
$expandedCar = ExpandHelper::expandCarData($carData);

echo json_encode($expandedCar);
```

### Развертывание списка автомобилей
```php
$cars = Car::findAll();
$expandedCars = [];

foreach ($cars as $car) {
    $expandedCars[] = ExpandHelper::expandCarData($car);
}

echo json_encode($expandedCars);
```

### Автоматическое развертывание
```php
$data = SomeModel::findById(123);
$expandedData = ExpandHelper::autoExpand($data, 'car');
```

### Условное развертывание
```php
$carData = Car::findById(123);

// Развертываем только если запрошено
if ($request->getParam('expand') === 'true') {
    $carData = ExpandHelper::expandCarData($carData);
}

echo json_encode($carData);
```

## 🔄 Интеграция

### С моделями
```php
// В модели User
public static function findByIdWithDetails($id) {
    $user = self::findById($id);
    return ExpandHelper::expandUserData($user);
}
```

### С контроллерами
```php
// В контроллере
public function getCar($id) {
    $car = Car::findById($id);
    $expandedCar = ExpandHelper::expandCarData($car);
    
    return ResponseHelper::success($expandedCar);
}
```

### С Actions
```php
// В L2 Action
$result = [
    'success' => true,
    'data' => ExpandHelper::expandCarData($carData)
];
```

## 🚨 Обработка ошибок

### Отсутствующие связанные данные
```php
// Если связанные данные не найдены, поле остается как ID
$carData = [
    'id' => 123,
    'owner_user_id' => 999 // Пользователь не найден
];

$expandedCar = ExpandHelper::expandCarData($carData);
// Результат: owner_user_id остается 999
```

### Циклические зависимости
```php
// Защита от циклических зависимостей
$maxDepth = 3;
$currentDepth = 0;

if ($currentDepth >= $maxDepth) {
    return $data; // Возвращаем без развертывания
}
```

## 📊 Производительность

### Кэширование
```php
// Кэширование справочных данных
$cachedRoles = ReferenceData::getRoles();
$cachedStatuses = ReferenceData::getCarStatuses();
```

### Оптимизация запросов
```php
// Batch загрузка связанных данных
$userIds = array_unique(array_column($cars, 'owner_user_id'));
$users = User::findByIds($userIds);
```

### Условное развертывание
```php
// Развертываем только запрошенные поля
$requestedFields = $request->getParam('expand', []);
if (in_array('owner', $requestedFields)) {
    $carData = ExpandHelper::expandCarData($carData);
}
```

## 🔗 Связанные компоненты

- **ReferenceData** — предоставляет справочные данные для развертывания
- **Models** — используют ExpandHelper для обогащения ответов
- **Controllers** — применяют ExpandHelper в API эндпоинтах
- **Actions** — используют ExpandHelper в бизнес-логике

## 📈 Мониторинг

### Метрики развертывания
- Количество развернутых объектов
- Время развертывания
- Типы развертываемых данных
- Частота использования

### Логирование
```php
Logger::info('Data expanded', [
    'type' => 'car',
    'original_id' => $carData['id'],
    'expanded_fields' => ['status', 'owner', 'brand']
]);
```

---

**📚 См. также:** [ReferenceData](REFERENCE_DATA.md), [Models](../MODELS/OVERVIEW.md) 
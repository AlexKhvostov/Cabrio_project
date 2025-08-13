# 📚 ReferenceData

> Централизованное хранение справочных данных и констант CabrioRide

## 📋 Назначение

`ReferenceData` — утилита для работы со справочными данными в CabrioRide. Обеспечивает:

- Централизованное хранение констант статусов и ролей
- Конвертацию между ID и кодами
- Проверку прав доступа на основе ролей
- Получение детальной информации о справочниках

## 🏗️ Архитектура

### Основные категории данных

#### 1. Статусы автомобилей
```php
const CAR_STATUS_NOTICED = 1;        // Замечен
const CAR_STATUS_BUSINESS_CARD = 2;  // Визитка
const CAR_STATUS_DELETED = 3;        // Удалён
const CAR_STATUS_ARCHIVED = 4;       // В архиве
const CAR_STATUS_BLOCKED = 5;        // Заблокирован
const CAR_STATUS_PENDING = 6;        // На модерации
const CAR_STATUS_ACTIVE = 7;         // Активен
```

#### 2. Роли пользователей
```php
const USER_ROLE_EXTERNAL = 1;    // Внешний
const USER_ROLE_GUEST = 2;       // Гость
const USER_ROLE_USER = 3;        // Пользователь
const USER_ROLE_MEMBER = 4;      // Участник
const USER_ROLE_MODERATOR = 5;   // Модератор
const USER_ROLE_ADMIN = 6;       // Администратор
```

## 🔄 Конвертация данных

### ID ↔ Code маппинг

#### Статусы автомобилей
```php
// ID → Code
$statusCode = ReferenceData::getCarStatusCode(1); // 'noticed'
$statusCode = ReferenceData::getCarStatusCode(7); // 'active'

// Code → ID
$statusId = ReferenceData::getCarStatusId('noticed'); // 1
$statusId = ReferenceData::getCarStatusId('active');  // 7
```

#### Роли пользователей
```php
// ID → Code
$roleCode = ReferenceData::getUserRoleCode(4); // 'member'
$roleCode = ReferenceData::getUserRoleCode(6); // 'admin'

// Code → ID
$roleId = ReferenceData::getUserRoleId('member'); // 4
$roleId = ReferenceData::getUserRoleId('admin');  // 6
```

## 🛡️ Проверка прав доступа

### Иерархия ролей
```php
// Проверка минимальной роли
$hasAccess = ReferenceData::hasRoleOrHigher($userRoleId, $requiredRoleId);

// Специальные проверки
$isAdmin = ReferenceData::isAdmin($roleId);
$isModeratorOrHigher = ReferenceData::isModeratorOrHigher($roleId);
$isMemberOrHigher = ReferenceData::isMemberOrHigher($roleId);
```

### Примеры проверок
```php
// Проверка на администратора
if (ReferenceData::isAdmin($userRoleId)) {
    // Специальные права администратора
}

// Проверка на модератора или выше
if (ReferenceData::isModeratorOrHigher($userRoleId)) {
    // Права модерации
}

// Проверка на участника или выше
if (ReferenceData::isMemberOrHigher($userRoleId)) {
    // Права участника
}
```

## 📊 Детальная информация

### Статусы автомобилей
```php
$statusDetails = ReferenceData::getCarStatusDetails(7);
// Результат:
// [
//     'id' => 7,
//     'code' => 'active',
//     'name' => 'Активен',
//     'description' => 'Автомобиль активен в системе',
//     'color' => '#28a745',
//     'can_edit' => true,
//     'can_delete' => false
// ]
```

### Роли пользователей
```php
$roleDetails = ReferenceData::getUserRoleDetails(4);
// Результат:
// [
//     'id' => 4,
//     'code' => 'member',
//     'name' => 'Участник',
//     'description' => 'Полноправный участник клуба',
//     'color' => '#007bff',
//     'permissions' => ['view_cars', 'add_cars', 'edit_own_cars']
// ]
```

## 📋 Получение списков

### Все статусы автомобилей
```php
$allStatuses = ReferenceData::getAllCarStatuses();
// Результат: массив всех статусов с деталями
```

### Все роли пользователей
```php
$allRoles = ReferenceData::getAllUserRoles();
// Результат: массив всех ролей с деталями
```

## ✅ Валидация данных

### Проверка валидности
```php
// Проверка статуса автомобиля
$isValidStatus = ReferenceData::isValidCarStatus($statusId);

// Проверка роли пользователя
$isValidRole = ReferenceData::isValidUserRole($roleId);
```

### Проверка активности
```php
// Проверка активного статуса автомобиля
$isActive = ReferenceData::isCarStatusActive($statusId);
```

## 🔄 Управление статусами

### Проверка возможности изменения
```php
// Можно ли изменить статус с текущего на новый
$canUpdate = ReferenceData::canUpdateCarStatus($currentStatusId, $newStatusId);
```

### Примеры переходов статусов
```php
// Замечен → Активен (разрешено)
$canUpdate = ReferenceData::canUpdateCarStatus(1, 7); // true

// Активен → Удалён (разрешено)
$canUpdate = ReferenceData::canUpdateCarStatus(7, 3); // true

// Удалён → Активен (запрещено)
$canUpdate = ReferenceData::canUpdateCarStatus(3, 7); // false
```

## 📝 Примеры использования

### В контроллерах
```php
// Проверка прав доступа
public function updateCar($carId) {
    $userRoleId = AppContext::getCurrentUserRoleId();
    
    if (!ReferenceData::isModeratorOrHigher($userRoleId)) {
        return ResponseHelper::error('ACCESS_DENIED', 'Недостаточно прав');
    }
    
    // Обновление автомобиля
}
```

### В Actions
```php
// Проверка возможности изменения статуса
$canUpdate = ReferenceData::canUpdateCarStatus($currentStatus, $newStatus);
if (!$canUpdate) {
    return [
        'success' => false,
        'error' => ['code' => 'INVALID_STATUS_TRANSITION']
    ];
}
```

### В моделях
```php
// Валидация статуса при создании
public static function create($data) {
    if (!ReferenceData::isValidCarStatus($data['status_id'])) {
        throw new ValidationException('Неверный статус автомобиля');
    }
    
    // Создание автомобиля
}
```

### В ExpandHelper
```php
// Получение деталей статуса для развертывания
$statusDetails = ReferenceData::getCarStatusDetails($carData['status_id']);
$expandedCar['status'] = $statusDetails;
```

## 🔧 Конфигурация

### Добавление новых статусов
```php
// 1. Добавить константу
const CAR_STATUS_NEW_STATUS = 8;

// 2. Добавить в маппинг
private static $carStatusMap = [
    // ... существующие
    self::CAR_STATUS_NEW_STATUS => 'new_status'
];

// 3. Добавить детали в getCarStatusDetails()
```

### Добавление новых ролей
```php
// 1. Добавить константу
const USER_ROLE_NEW_ROLE = 7;

// 2. Добавить в маппинг
private static $userRoleMap = [
    // ... существующие
    self::USER_ROLE_NEW_ROLE => 'new_role'
];

// 3. Добавить детали в getUserRoleDetails()
```

## 🚨 Обработка ошибок

### Неверные ID
```php
// Возвращает null для неверных ID
$statusCode = ReferenceData::getCarStatusCode(999); // null
$roleCode = ReferenceData::getUserRoleCode(999);    // null
```

### Неверные коды
```php
// Возвращает null для неверных кодов
$statusId = ReferenceData::getCarStatusId('invalid'); // null
$roleId = ReferenceData::getUserRoleId('invalid');    // null
```

## 📈 Производительность

### Кэширование
```php
// Статические массивы для быстрого доступа
private static $carStatusMap = [...];
private static $userRoleMap = [...];
```

### Оптимизация
- Все данные хранятся в статических массивах
- Нет обращений к базе данных
- Быстрые операции поиска

## 🔗 Связанные компоненты

- **ExpandHelper** — использует ReferenceData для развертывания данных
- **BaseController** — использует для проверки прав доступа
- **Actions** — используют для валидации статусов и ролей
- **Models** — используют для проверки валидности данных

## 📊 Мониторинг

### Метрики использования
- Количество обращений к справочникам
- Популярные статусы и роли
- Частота проверок прав доступа

### Логирование
```php
Logger::info('Reference data accessed', [
    'type' => 'car_status',
    'status_id' => $statusId,
    'status_code' => $statusCode
]);
```

---

**📚 См. также:** [ExpandHelper](EXPAND_HELPER.md), [BaseController](../CONTROLLERS/BASE_CONTROLLER.md) 
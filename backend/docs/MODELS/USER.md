# 👤 Модель User

> **Назначение:** Документация модели User — работа с пользователями системы  
> **Таблица:** `users`  
> **Версия:** 1.0.0

---

## 🎯 **Обзор модели User**

### **Назначение**
Модель User представляет пользователя платформы CabrioRide. Используется для авторизации, профиля, связей с автомобилями, событиями, фото и другими сущностями.

### **Ключевые возможности**
- **CRUD операции** — создание, чтение, обновление, удаление
- **Поиск по различным критериям** — ID, Telegram ID, username
- **Развёртывание связанных данных** — роли, автомобили, события, фото
- **Валидация данных** — проверка корректности входных данных

---

## 📊 **Структура данных**

### **Основные поля**
| Поле | Тип | Обязательный | Описание |
|------|-----|--------------|----------|
| id | integer | Да | Уникальный идентификатор (AUTO_INCREMENT) |
| telegram_id | bigint | Да | Telegram ID пользователя |
| first_name | varchar(64) | Да | Имя пользователя |
| last_name | varchar(64) | Нет | Фамилия пользователя |
| username | varchar(64) | Нет | Username в Telegram |
| role_id | integer | Да | FK на ref_roles (роль пользователя) |
| email | varchar(255) | Нет | Email пользователя |
| phone | varchar(20) | Нет | Телефон пользователя |
| city | varchar(100) | Нет | Город пользователя |
| created_at | timestamp | Да | Дата создания записи |
| updated_at | timestamp | Да | Дата обновления записи |

### **Связи с другими таблицами**
- **Role** — `role_id → ref_roles.id` (роль пользователя)
- **Cars** — через `link_user_cars` (автомобили пользователя)
- **Events** — через `link_event_participants` (участие в событиях)
- **Photos** — `entity_type = 'user'` (фотографии пользователя)
- **Sessions** — `sessions.user_id` (сессии пользователя)

---

## 🔍 **Методы поиска**

### **findById($id)**
Поиск пользователя по ID.

```php
$user = User::findById(1);
if ($user) {
    echo $user->first_name; // Иван
}
```

### **findByIdWithDetails($id)**
Поиск пользователя по ID с развёрнутыми данными.

```php
$userData = User::findByIdWithDetails(1);
if ($userData) {
    echo $userData['first_name']; // Иван
    echo $userData['role']['name']; // Участник
    echo count($userData['cars']); // Количество автомобилей
}
```

### **findByTelegramId($telegram_id)**
Поиск пользователя по Telegram ID.

```php
$user = User::findByTelegramId(123456789);
if ($user) {
    echo $user->username; // ivan
}
```

### **findByTelegramIdWithDetails($telegramId)**
Поиск пользователя по Telegram ID с развёрнутыми данными.

```php
$userData = User::findByTelegramIdWithDetails(123456789);
if ($userData) {
    echo $userData['first_name']; // Иван
    echo $userData['role']['code']; // member
}
```

### **getAll()**
Получение всех пользователей с пагинацией.

```php
$users = User::getAll([
    'page' => 1,
    'limit' => 10,
    'role' => 'member',
    'search' => 'ivan'
]);
```

---

## ➕ **Методы создания**

### **create($data)**
Создание нового пользователя.

```php
$userData = [
    'telegram_id' => 123456789,
    'first_name' => 'Иван',
    'last_name' => 'Иванов',
    'username' => 'ivan',
    'role_id' => 4,
    'email' => 'ivan@example.com'
];

$user = User::create($userData);
if ($user) {
    echo $user->id; // ID созданного пользователя
}
```

### **createWithDetails($data)**
Создание пользователя с развёрнутыми данными.

```php
$userData = [
    'telegram_id' => 123456789,
    'first_name' => 'Иван',
    'last_name' => 'Иванов',
    'username' => 'ivan',
    'role_id' => 4
];

$result = User::createWithDetails($userData);
if ($result['success']) {
    echo $result['data']['id']; // ID созданного пользователя
    echo $result['data']['role']['name']; // Название роли
}
```

---

## 🔄 **Методы обновления**

### **update($data)**
Обновление данных пользователя.

```php
$updateData = [
    'user_id' => 1,
    'first_name' => 'Иван Петрович',
    'last_name' => 'Иванов',
    'email' => 'ivan.petrovich@example.com'
];

$result = User::update($updateData);
if ($result['success']) {
    echo "Пользователь обновлён";
}
```

### **updateWithDetails($id, $data)**
Обновление пользователя с развёрнутыми данными.

```php
$updateData = [
    'first_name' => 'Иван Петрович',
    'last_name' => 'Иванов',
    'email' => 'ivan.petrovich@example.com'
];

$result = User::updateWithDetails(1, $updateData);
if ($result['success']) {
    echo $result['data']['first_name']; // Обновлённое имя
    echo $result['data']['role']['name']; // Роль
}
```

### **updateRole($userId, $roleId)**
Обновление роли пользователя.

```php
$result = User::updateRole(1, 5); // Изменить роль на member
if ($result['success']) {
    echo "Роль пользователя обновлена";
}
```

---

## 🗑️ **Методы удаления**

### **delete()**
Удаление пользователя.

```php
$user = User::findById(1);
if ($user) {
    $result = $user->delete();
    if ($result['success']) {
        echo "Пользователь удалён";
    }
}
```

---

## 📊 **Развёртывание данных**

### **Структура развёрнутых данных**
```php
$userData = [
    'id' => 1,
    'telegram_id' => 123456789,
    'first_name' => 'Иван',
    'last_name' => 'Иванов',
    'username' => 'ivan',
    'role' => [
        'id' => 4,
        'code' => 'member',
        'name' => 'Участник'
    ],
    'email' => 'ivan@example.com',
    'phone' => '+79001234567',
    'city' => 'Москва',
    'photo' => [
        'id' => 1,
        'url' => 'https://example.com/photos/user_1.jpg',
        'filename' => 'user_1.jpg'
    ],
    'cars' => [
        [
            'id' => 1,
            'brand' => 'BMW',
            'model' => 'Z4',
            'year' => 2020,
            'plate_number' => 'A123BC'
        ]
    ],
    'events' => [
        [
            'id' => 1,
            'name' => 'Встреча клуба',
            'date' => '2024-01-15T18:00:00Z',
            'status' => 'active'
        ]
    ],
    'created_at' => '2024-01-01T12:00:00Z',
    'updated_at' => '2024-01-01T12:00:00Z'
];
```

### **Используемые хелперы**
- **ExpandHelper** — развёртывание связанных данных
- **ValidationHelper** — валидация входных данных
- **Logger** — логирование операций

---

## ✅ **Валидация данных**

### **Обязательные поля**
- `telegram_id` — Telegram ID пользователя
- `first_name` — имя пользователя
- `role_id` — ID роли пользователя

### **Правила валидации**
```php
// Проверка обязательных полей
ValidationHelper::requireFields($data, ['telegram_id', 'first_name']);

// Валидация типов данных
ValidationHelper::validateInt($data['telegram_id'], 'telegram_id');
ValidationHelper::validateString($data['first_name'], 'first_name', 1, 64);

// Проверка уникальности
if (User::findByTelegramId($data['telegram_id'])) {
    return ['success' => false, 'error' => ['code' => 'DUPLICATE_ENTRY']];
}
```

---

## 🚨 **Обработка ошибок**

### **Типы ошибок**
- `VALIDATION_ERROR` — ошибка валидации данных
- `DUPLICATE_ENTRY` — дублирование записи
- `USER_NOT_FOUND` — пользователь не найден
- `INVALID_ROLE_ID` — некорректный ID роли
- `INTERNAL_ERROR` — внутренняя ошибка

### **Структура ошибки**
```php
return [
    'success' => false,
    'error' => [
        'code' => 'ERROR_CODE',
        'message' => 'Человекочитаемое описание ошибки',
        'details' => [
            'field' => 'Детали ошибки'
        ]
    ]
];
```

---

## 📊 **Примеры использования**

### **Создание пользователя**
```php
$userData = [
    'telegram_id' => 123456789,
    'first_name' => 'Иван',
    'last_name' => 'Иванов',
    'username' => 'ivan',
    'role_id' => 4,
    'email' => 'ivan@example.com'
];

$user = User::create($userData);
if ($user) {
    echo "Пользователь создан с ID: " . $user->id;
}
```

### **Поиск и обновление**
```php
$user = User::findByTelegramId(123456789);
if ($user) {
    $updateData = [
        'user_id' => $user->id,
        'first_name' => 'Иван Петрович',
        'email' => 'ivan.petrovich@example.com'
    ];
    
    $result = User::update($updateData);
    if ($result['success']) {
        echo "Данные пользователя обновлены";
    }
}
```

### **Получение профиля с данными**
```php
$userData = User::findByIdWithDetails(1);
if ($userData) {
    echo "Имя: " . $userData['first_name'];
    echo "Роль: " . $userData['role']['name'];
    echo "Автомобилей: " . count($userData['cars']);
    echo "Событий: " . count($userData['events']);
}
```

---

## 📊 **Производительность**

### **Оптимизация запросов**
- Использование индексов на `telegram_id`, `username`
- Подготовленные запросы для безопасности
- Ленивая загрузка связанных данных

### **Кэширование**
- Кэширование часто запрашиваемых пользователей
- Кэширование ролей и справочных данных
- Инвалидация кэша при обновлении

---

## 📚 **Связанная документация**

- [Модель Car](../CAR.md) — автомобили пользователей
- [Модель Event](../EVENT.md) — события пользователей
- [Модель Photo](../PHOTO.md) — фотографии пользователей
- [Actions пользователей](../../ACTIONS/L2_ACTIONS.md) — бизнес-логика
- [Авторизация](../../AUTHENTICATION/OVERVIEW.md) — система безопасности

---

> **💡 Совет:** Используйте методы с развёрнутыми данными (`WithDetails`) для получения полной информации о пользователе и его связях. 
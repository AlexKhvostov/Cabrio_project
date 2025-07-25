# 📋 Готовые примеры конфигураций тестов

## 🚀 Быстрое копирование для AI

### **GET запросы (получение списков)**

#### 👤 Пользователи
```php
$test_config = [
    'id' => 'users_list',
    'name' => 'Список пользователей',
    'description' => 'Получение списка пользователей с ролями и фото',
    'endpoint' => '/api/users',
    'method' => 'GET',
    'icon' => '👤',
    'data_name' => 'пользователей'
];
```

#### 🚗 Автомобили
```php
$test_config = [
    'id' => 'cars_list',
    'name' => 'Список автомобилей',
    'description' => 'Получение списка автомобилей с марками, владельцами и фото',
    'endpoint' => '/api/cars',
    'method' => 'GET',
    'icon' => '🚗',
    'data_name' => 'автомобилей'
];
```

#### 📅 События
```php
$test_config = [
    'id' => 'events_list',
    'name' => 'Список событий',
    'description' => 'Получение списка событий с типами и организаторами',
    'endpoint' => '/api/events',
    'method' => 'GET',
    'icon' => '📅',
    'data_name' => 'событий'
];
```

#### 🗺️ Гид-объекты
```php
$test_config = [
    'id' => 'guide_objects_list',
    'name' => 'Список гид-объектов',
    'description' => 'Получение списка гид-объектов с типами и отзывами',
    'endpoint' => '/api/guide-objects',
    'method' => 'GET',
    'icon' => '🗺️',
    'data_name' => 'гид-объектов'
];
```

#### 💳 Визитки
```php
$test_config = [
    'id' => 'business_cards_list',
    'name' => 'Список визиток',
    'description' => 'Получение списка визиток с контактами и фото',
    'endpoint' => '/api/business-cards',
    'method' => 'GET',
    'icon' => '💳',
    'data_name' => 'визиток'
];
```

### **POST запросы (создание)**

#### 👤 Создание пользователя
```php
$test_config = [
    'id' => 'users_create',
    'name' => 'Создание пользователя',
    'description' => 'POST запрос для создания нового пользователя с фото',
    'endpoint' => '/api/users',
    'method' => 'POST',
    'icon' => '👤',
    'data_name' => 'пользователей'
];
```

#### 🚗 Создание автомобиля
```php
$test_config = [
    'id' => 'cars_create',
    'name' => 'Создание автомобиля',
    'description' => 'POST запрос для добавления нового автомобиля',
    'endpoint' => '/api/cars',
    'method' => 'POST',
    'icon' => '🚗',
    'data_name' => 'автомобилей'
];
```

#### 📅 Создание события
```php
$test_config = [
    'id' => 'events_create',
    'name' => 'Создание события',
    'description' => 'POST запрос для создания нового события',
    'endpoint' => '/api/events',
    'method' => 'POST',
    'icon' => '📅',
    'data_name' => 'событий'
];
```

### **PUT запросы (обновление)**

#### 👤 Обновление пользователя
```php
$test_config = [
    'id' => 'users_update',
    'name' => 'Обновление пользователя',
    'description' => 'PUT запрос для обновления данных пользователя',
    'endpoint' => '/api/users/1',
    'method' => 'PUT',
    'icon' => '👤',
    'data_name' => 'пользователей'
];
```

#### 🚗 Обновление автомобиля
```php
$test_config = [
    'id' => 'cars_update',
    'name' => 'Обновление автомобиля',
    'description' => 'PUT запрос для обновления данных автомобиля',
    'endpoint' => '/api/cars/1',
    'method' => 'PUT',
    'icon' => '🚗',
    'data_name' => 'автомобилей'
];
```

### **DELETE запросы (удаление)**

#### 👤 Удаление пользователя
```php
$test_config = [
    'id' => 'users_delete',
    'name' => 'Удаление пользователя',
    'description' => 'DELETE запрос для удаления пользователя по ID',
    'endpoint' => '/api/users/1',
    'method' => 'DELETE',
    'icon' => '👤',
    'data_name' => 'пользователей'
];
```

#### 🚗 Удаление автомобиля
```php
$test_config = [
    'id' => 'cars_delete',
    'name' => 'Удаление автомобиля',
    'description' => 'DELETE запрос для удаления автомобиля по ID',
    'endpoint' => '/api/cars/1',
    'method' => 'DELETE',
    'icon' => '🚗',
    'data_name' => 'автомобилей'
];
```

## 📋 Соответствующие записи в tests_config.json

### **GET запросы**
```json
{
  "id": "users_list",
  "name": "Список пользователей",
  "description": "Получение списка пользователей с ролями и фото",
  "endpoint": "/api/users",
  "method": "GET",
  "expected": {
    "success": true,
    "has_data": true,
    "data_type": "array"
  },
  "icon": "👤",
  "category": "users"
}
```

### **POST запросы**
```json
{
  "id": "users_create",
  "name": "Создание пользователя",
  "description": "POST запрос для создания нового пользователя с фото",
  "endpoint": "/api/users",
  "method": "POST",
  "expected": {
    "success": true,
    "has_data": true,
    "data_type": "object"
  },
  "icon": "👤",
  "category": "users"
}
```

### **PUT запросы**
```json
{
  "id": "users_update",
  "name": "Обновление пользователя",
  "description": "PUT запрос для обновления данных пользователя",
  "endpoint": "/api/users/1",
  "method": "PUT",
  "expected": {
    "success": true,
    "has_data": true,
    "data_type": "object"
  },
  "icon": "👤",
  "category": "users"
}
```

### **DELETE запросы**
```json
{
  "id": "users_delete",
  "name": "Удаление пользователя",
  "description": "DELETE запрос для удаления пользователя по ID",
  "endpoint": "/api/users/1",
  "method": "DELETE",
  "expected": {
    "success": true,
    "has_data": false,
    "data_type": "object"
  },
  "icon": "👤",
  "category": "users"
}
```

## 🎯 Шаблоны для разных типов данных

### **data_type: "array"** (для списков)
```json
"expected": {
  "success": true,
  "has_data": true,
  "data_type": "array"
}
```

### **data_type: "object"** (для создания/обновления)
```json
"expected": {
  "success": true,
  "has_data": true,
  "data_type": "object"
}
```

### **has_data: false** (для удаления)
```json
"expected": {
  "success": true,
  "has_data": false,
  "data_type": "object"
}
```

## 🔧 Иконки по категориям

### **Пользователи:** 👤
### **Автомобили:** 🚗
### **События:** 📅
### **Гид-объекты:** 🗺️
### **Визитки:** 💳
### **Фото:** 📸
### **Отзывы:** ⭐
### **Общие:** 🔧

---

**Использование:** Скопируйте нужный пример и замените параметры под ваш тест! 
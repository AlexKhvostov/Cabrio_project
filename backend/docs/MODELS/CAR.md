# Car Model - Автомобили 🚗

> **Назначение:** Модель для работы с автомобилями в системе CabrioRide

---

## 🎯 Назначение

Модель Car предоставляет функционал для работы с автомобилями:
- CRUD операции для автомобилей
- Поиск автомобилей по различным критериям
- Управление связями с пользователями
- Расширение данных через ExpandHelper

---

## 🏗️ Архитектура

### **Таблица:** `cars`

### **Основные поля:**
```php
protected $fillable = [
    'car_brand_id',
    'model',
    'engine_power',
    'engine_volume',
    'color',
    'year',
    'reg_number',
    'show_reg_number',
    'vin',
    'description',
    'create_user_id',
    'owner_user_id',
    'roof_type',
    'notes',
    'status_id'
];
```

### **Скрытые поля:**
```php
protected $hidden = [
    'notes' // Административные заметки
];
```

---

## 🔧 Основные методы

### **CRUD операции:**

#### **Создание автомобиля:**
```php
$car = Car::create([
    'car_brand_id' => 2,
    'model' => 'BMW Z4',
    'color' => 'red',
    'year' => 2020,
    'reg_number' => 'A123BC',
    'owner_user_id' => 123,
    'status_id' => 7
]);
```

#### **Получение автомобиля:**
```php
// По ID
$car = Car::find($id);

// С расширенными данными
$car = Car::findWithDetails($id);
// Включает: owner, brand, photos, business_cards, status

// По номеру
$car = Car::findByPlateNumber('A123BC');
```

#### **Обновление автомобиля:**
```php
$car = Car::find($id);
$car->update([
    'color' => 'blue',
    'year' => 2021
]);
```

#### **Удаление автомобиля:**
```php
$car = Car::find($id);
$car->delete();
```

### **Поиск и фильтрация:**

#### **Поиск по статусу:**
```php
$activeCars = Car::where('status_id', 7)->get();
$pendingCars = Car::where('status_id', 6)->get();
```

#### **Поиск по владельцу:**
```php
$userCars = Car::where('owner_user_id', $userId)->get();
```

#### **Поиск по марке:**
```php
$bmwCars = Car::where('car_brand_id', 2)->get();
```

#### **Поиск по году:**
```php
$recentCars = Car::where('year', '>=', 2020)->get();
```

#### **Комбинированный поиск:**
```php
$cars = Car::where('status_id', 7)
    ->where('car_brand_id', 2)
    ->where('year', '>=', 2020)
    ->get();
```

### **Расширенные методы:**

#### **findWithDetails():**
```php
$car = Car::findWithDetails($id);
// Результат включает:
// - owner (User)
// - brand (CarBrand)
// - photos (Photo[])
// - business_cards (BusinessCard[])
// - status (Status)
// - creator (User)
```

#### **findByTelegramUser():**
```php
$cars = Car::findByTelegramUser($telegramId);
// Найти все автомобили пользователя по Telegram ID
```

#### **findActiveByUser():**
```php
$activeCars = Car::findActiveByUser($userId);
// Найти активные автомобили пользователя
```

---

## 🔗 Связи с другими моделями

### **One-to-Many (1:N):**

#### **Owner (Владелец):**
```php
public function owner()
{
    return $this->belongsTo(User::class, 'owner_user_id');
}

// Использование:
$car = Car::find($id);
$owner = $car->owner; // User объект
```

#### **Creator (Создатель):**
```php
public function creator()
{
    return $this->belongsTo(User::class, 'create_user_id');
}

// Использование:
$car = Car::find($id);
$creator = $car->creator; // User объект
```

#### **Brand (Марка):**
```php
public function brand()
{
    return $this->belongsTo(CarBrand::class, 'car_brand_id');
}

// Использование:
$car = Car::find($id);
$brand = $car->brand; // CarBrand объект
```

#### **Status (Статус):**
```php
public function status()
{
    return $this->belongsTo(Status::class, 'status_id');
}

// Использование:
$car = Car::find($id);
$status = $car->status; // Status объект
```

### **One-to-Many (1:N) - Обратные связи:**

#### **Photos (Фотографии):**
```php
public function photos()
{
    return $this->hasMany(Photo::class, 'entity_id')
        ->where('entity_type', 'car');
}

// Использование:
$car = Car::find($id);
$photos = $car->photos; // Photo[] массив
```

#### **Business Cards (Визитки):**
```php
public function businessCards()
{
    return $this->hasMany(BusinessCard::class, 'car_id');
}

// Использование:
$car = Car::find($id);
$businessCards = $car->businessCards; // BusinessCard[] массив
```

### **Many-to-Many (M:N):**

#### **Users (Пользователи):**
```php
public function users()
{
    return $this->belongsToMany(User::class, 'link_user_cars', 'car_id', 'user_id')
        ->withPivot('role_id');
}

// Использование:
$car = Car::find($id);
$users = $car->users; // User[] массив с pivot данными
```

---

## 📊 Расширение данных

### **Автоматическое расширение через ExpandHelper:**

#### **Базовое расширение:**
```php
$car = Car::findWithDetails($id);
// Автоматически расширяет:
// - owner_id -> owner (User)
// - car_brand_id -> brand (CarBrand)
// - status_id -> status (Status)
// - create_user_id -> creator (User)
```

#### **Расширение с фотографиями:**
```php
$car = Car::findWithDetailsAndPhotos($id);
// Дополнительно включает:
// - photos (Photo[])
```

#### **Расширение с визитками:**
```php
$car = Car::findWithDetailsAndBusinessCards($id);
// Дополнительно включает:
// - business_cards (BusinessCard[])
```

---

## 🔐 Валидация

### **Правила валидации:**
```php
protected $rules = [
    'car_brand_id' => 'required|exists:ref_car_brands,id',
    'model' => 'required|string|max:100',
    'color' => 'required|string|max:50',
    'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
    'reg_number' => 'required|string|max:20|unique:cars,reg_number',
    'vin' => 'nullable|string|max:17|unique:cars,vin',
    'owner_user_id' => 'required|exists:users,id',
    'status_id' => 'required|exists:ref_statuses,id'
];
```

### **Кастомная валидация:**
```php
public function validateRegNumber($value)
{
    // Проверка формата номера
    if (!preg_match('/^[АВЕКМНОРСТУХ]\d{3}[АВЕКМНОРСТУХ]{2}$/', $value)) {
        throw new ValidationException('Неверный формат номера');
    }
    return $value;
}
```

---

## 📈 Производительность

### **Оптимизация запросов:**

#### **Eager Loading:**
```php
// Загрузить автомобили с владельцами и марками
$cars = Car::with(['owner', 'brand', 'status'])->get();
```

#### **Селективные поля:**
```php
// Только нужные поля
$cars = Car::select(['id', 'model', 'color', 'reg_number', 'owner_user_id'])
    ->with(['owner:id,first_name,last_name'])
    ->get();
```

#### **Индексы для поиска:**
```php
// Использование индексов
$cars = Car::where('reg_number', $plateNumber)
    ->where('status_id', 7)
    ->get();
```

---

## 🧪 Тестирование

### **Unit тесты:**
```php
public function test_car_creation()
{
    $carData = [
        'car_brand_id' => 2,
        'model' => 'BMW Z4',
        'color' => 'red',
        'year' => 2020,
        'reg_number' => 'A123BC',
        'owner_user_id' => 1,
        'status_id' => 7
    ];
    
    $car = Car::create($carData);
    
    $this->assertDatabaseHas('cars', $carData);
    $this->assertEquals('BMW Z4', $car->model);
}
```

### **Integration тесты:**
```php
public function test_car_owner_relationship()
{
    $user = User::factory()->create();
    $car = Car::factory()->create(['owner_user_id' => $user->id]);
    
    $this->assertEquals($user->id, $car->owner->id);
    $this->assertTrue($user->cars->contains($car));
}
```

---

## 🔗 Связанные документы

- [User Model](USER.md) — модель пользователей
- [CarBrand Model](CAR_BRAND.md) — модель марок автомобилей
- [Status Model](STATUS.md) — модель статусов
- [Photo Model](PHOTO.md) — модель фотографий
- [BusinessCard Model](BUSINESS_CARD.md) — модель визиток
- [Database Schema](../../DATABASE/SCHEMA.md) — структура БД
- [ExpandHelper](../../UTILS/EXPAND_HELPER.md) — расширение данных

---

> **Примечание:** Модель Car является центральной в системе и имеет множество связей с другими сущностями. Все методы интегрированы с ExpandHelper для автоматического расширения связанных данных в API ответах. 
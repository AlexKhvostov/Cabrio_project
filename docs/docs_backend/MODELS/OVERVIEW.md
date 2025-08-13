# Models - Модели данных 📊

> **Назначение:** Документация моделей данных для работы с базой данных

---

## 🎯 Назначение

Модели данных предоставляют абстракцию для работы с базой данных:
- CRUD операции для всех сущностей
- Валидация данных
- Связи между моделями
- Расширение данных через ExpandHelper

---

## 🏗️ Архитектура

### **Принципы:**
- **Active Record Pattern** — каждая модель представляет таблицу БД
- **ExpandHelper Integration** — автоматическое расширение связанных данных
- **Validation** — встроенная валидация данных
- **Relationships** — управление связями между моделями

### **Базовая структура:**
```php
class Model extends BaseModel
{
    protected $table = 'table_name';
    protected $fillable = ['field1', 'field2'];
    protected $hidden = ['password', 'token'];
    
    // Связи с другими моделями
    public function relatedModel()
    {
        return $this->belongsTo(RelatedModel::class);
    }
}
```

---

## 📋 Список моделей

### **Основные модели:**
- **[User](USER.md)** — пользователи системы
- **[Car](CAR.md)** — автомобили
- **[Event](EVENT.md)** — события
- **[GuideObject](GUIDE_OBJECT.md)** — гид-объекты
- **[Review](REVIEW.md)** — отзывы
- **[BusinessCard](BUSINESS_CARD.md)** — визитки
- **[Photo](PHOTO.md)** — фотографии

### **Справочные модели:**
- **[Role](ROLE.md)** — роли пользователей
- **[Status](STATUS.md)** — статусы сущностей
- **[EventType](EVENT_TYPE.md)** — типы событий
- **[GuideObjectType](GUIDE_OBJECT_TYPE.md)** — типы гид-объектов
- **[CarBrand](CAR_BRAND.md)** — марки автомобилей

### **Связующие модели:**
- **[UserCar](USER_CAR.md)** — связь пользователей и автомобилей
- **[EventParticipant](EVENT_PARTICIPANT.md)** — участники событий

---

## 🔧 Основные операции

### **CRUD операции:**
```php
// Создание
$model = Model::create($data);

// Чтение
$model = Model::find($id);
$models = Model::where('field', 'value')->get();

// Обновление
$model->update($data);

// Удаление
$model->delete();
```

### **Расширение данных:**
```php
// Автоматическое расширение связанных данных
$user = User::findWithDetails($id);
// Результат включает role, photo, cars и другие связанные данные
```

### **Валидация:**
```php
// Встроенная валидация
$model = Model::create($data); // Автоматическая валидация
```

---

## 🔗 Связи между моделями

### **One-to-Many (1:N):**
```php
// User -> Cars
$user->cars; // Получить все автомобили пользователя

// Car -> BusinessCards
$car->businessCards; // Получить все визитки автомобиля
```

### **Many-to-Many (M:N):**
```php
// Users <-> Cars (через link_user_cars)
$user->cars; // Автомобили пользователя
$car->users; // Пользователи автомобиля

// Events <-> Users (через link_event_participants)
$event->participants; // Участники события
$user->events; // События пользователя
```

### **Polymorphic (Универсальные связи):**
```php
// Photo -> User/Car/Event/GuideObject/Review
$photo->entity; // Получить связанную сущность
```

---

## 📊 Расширение данных

### **ExpandHelper Integration:**
```php
// Автоматическое расширение role_id в role объект
$user = User::findWithDetails($id);
// $user->role содержит полный объект роли

// Расширение связанных данных
$car = Car::findWithDetails($id);
// $car->owner, $car->brand, $car->photos расширены
```

### **Кастомные методы расширения:**
```php
// Расширение с дополнительными данными
$user = User::findWithDetailsAndStats($id);
// Включает статистику активности, количество автомобилей и т.д.
```

---

## 🔐 Безопасность

### **Защищённые поля:**
```php
protected $hidden = [
    'password',
    'session_token',
    'telegram_data'
];
```

### **Валидация данных:**
```php
// Автоматическая валидация при создании/обновлении
protected $rules = [
    'email' => 'required|email|unique:users',
    'telegram_id' => 'required|unique:users'
];
```

---

## 📈 Производительность

### **Оптимизация запросов:**
```php
// Eager loading для избежания N+1 проблем
$users = User::with(['role', 'photo', 'cars'])->get();

// Селективные поля
$users = User::select(['id', 'first_name', 'last_name'])->get();
```

### **Кэширование:**
```php
// Кэширование справочных данных
$roles = Role::cached()->get();
```

---

## 🧪 Тестирование

### **Unit тесты:**
```php
// Тестирование CRUD операций
public function test_user_creation()
{
    $user = User::create($this->userData);
    $this->assertDatabaseHas('users', $user->toArray());
}
```

### **Integration тесты:**
```php
// Тестирование связей между моделями
public function test_user_car_relationship()
{
    $user = User::factory()->create();
    $car = Car::factory()->create(['owner_user_id' => $user->id]);
    
    $this->assertTrue($user->cars->contains($car));
}
```

---

## 🔗 Связанные документы

- [Database Schema](../../DATABASE/SCHEMA.md) — структура базы данных
- [Database Relations](../../DATABASE/RELATIONS.md) — связи между таблицами
- [ExpandHelper](../../UTILS/EXPAND_HELPER.md) — расширение данных
- [ValidationHelper](../../UTILS/VALIDATION_HELPER.md) — валидация данных

---

> **Примечание:** Все модели следуют единому паттерну и интегрированы с ExpandHelper для автоматического расширения связанных данных в API ответах. 
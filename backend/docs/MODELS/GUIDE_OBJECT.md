# GuideObject Model - Гид-объекты 🏛️

> **Назначение:** Модель для работы с объектами гида (кафе, сервисы, достопримечательности)

---

## 🎯 Назначение

Модель GuideObject предоставляет функционал для работы с гид-объектами:
- CRUD операции для гид-объектов
- Управление отзывами и рейтингами
- Поиск объектов по различным критериям
- Расширение данных через ExpandHelper

---

## 🏗️ Архитектура

### **Таблица:** `guide_objects`

### **Основные поля:**
```php
protected $fillable = [
    'guide_object_type_id',
    'guide_object_kind_id',
    'name',
    'city',
    'address',
    'website',
    'phone',
    'Instagram',
    'Telegram',
    'Viber',
    'WhatsApp',
    'description',
    'service_list',
    'price',
    'brand',
    'add_user_id',
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

#### **Создание гид-объекта:**
```php
$guideObject = GuideObject::create([
    'guide_object_type_id' => 1,
    'guide_object_kind_id' => 3,
    'name' => 'Автосервис BMW',
    'city' => 'Москва',
    'address' => 'ул. Тверская, 15',
    'website' => 'https://bmw-service.ru',
    'phone' => '+7 (495) 123-45-67',
    'Instagram' => 'https://instagram.com/bmw_service',
    'description' => 'Специализированный сервис для автомобилей BMW',
    'service_list' => ['Диагностика', 'Ремонт двигателя', 'Замена масла'],
    'price' => 5000.00,
    'brand' => 'BMW',
    'add_user_id' => 123,
    'status_id' => 2
]);
```

#### **Получение гид-объекта:**
```php
// По ID
$guideObject = GuideObject::find($id);

// С расширенными данными
$guideObject = GuideObject::findWithDetails($id);
// Включает: creator, guide_object_type, guide_object_kind, status, photo, reviews

// По названию и городу
$guideObject = GuideObject::findByNameAndCity('Автосервис BMW', 'Москва');
```

#### **Обновление гид-объекта:**
```php
$guideObject = GuideObject::find($id);
$guideObject->update([
    'phone' => '+7 (495) 987-65-43',
    'price' => 6000.00
]);
```

#### **Удаление гид-объекта:**
```php
$guideObject = GuideObject::find($id);
$guideObject->delete();
```

### **Поиск и фильтрация:**

#### **Поиск по статусу:**
```php
$approvedObjects = GuideObject::where('status_id', 2)->get();
$pendingObjects = GuideObject::where('status_id', 1)->get();
```

#### **Поиск по типу:**
```php
$serviceObjects = GuideObject::where('guide_object_type_id', 1)->get();
$cafeObjects = GuideObject::where('guide_object_type_id', 2)->get();
```

#### **Поиск по городу:**
```php
$moscowObjects = GuideObject::where('city', 'Москва')->get();
```

#### **Поиск по создателю:**
```php
$userObjects = GuideObject::where('add_user_id', $userId)->get();
```

#### **Поиск по цене:**
```php
$expensiveObjects = GuideObject::where('price', '>', 10000)->get();
$cheapObjects = GuideObject::where('price', '<', 1000)->get();
```

#### **Комбинированный поиск:**
```php
$objects = GuideObject::where('status_id', 2)
    ->where('guide_object_type_id', 1)
    ->where('city', 'Москва')
    ->where('price', '<=', 5000)
    ->get();
```

### **Расширенные методы:**

#### **findWithDetails():**
```php
$guideObject = GuideObject::findWithDetails($id);
// Результат включает:
// - creator (User)
// - guide_object_type (GuideObjectType)
// - guide_object_kind (GuideObjectKind)
// - status (Status)
// - photo (Photo)
// - reviews (Review[])
```

#### **findByType():**
```php
$serviceObjects = GuideObject::findByType('service');
// Найти все объекты определённого типа
```

#### **findByCity():**
```php
$cityObjects = GuideObject::findByCity('Москва');
// Найти все объекты в городе
```

#### **findWithReviews():**
```php
$guideObject = GuideObject::findWithReviews($id);
// Объект с полной информацией об отзывах
```

---

## 🔗 Связи с другими моделями

### **One-to-Many (1:N):**

#### **Creator (Создатель):**
```php
public function creator()
{
    return $this->belongsTo(User::class, 'add_user_id');
}

// Использование:
$guideObject = GuideObject::find($id);
$creator = $guideObject->creator; // User объект
```

#### **GuideObjectType (Тип объекта):**
```php
public function guideObjectType()
{
    return $this->belongsTo(GuideObjectType::class, 'guide_object_type_id');
}

// Использование:
$guideObject = GuideObject::find($id);
$type = $guideObject->guideObjectType; // GuideObjectType объект
```

#### **GuideObjectKind (Вид объекта):**
```php
public function guideObjectKind()
{
    return $this->belongsTo(GuideObjectKind::class, 'guide_object_kind_id');
}

// Использование:
$guideObject = GuideObject::find($id);
$kind = $guideObject->guideObjectKind; // GuideObjectKind объект
```

#### **Status (Статус):**
```php
public function status()
{
    return $this->belongsTo(Status::class, 'status_id');
}

// Использование:
$guideObject = GuideObject::find($id);
$status = $guideObject->status; // Status объект
```

### **One-to-Many (1:N) - Обратные связи:**

#### **Photo (Фотография):**
```php
public function photo()
{
    return $this->hasOne(Photo::class, 'entity_id')
        ->where('entity_type', 'guide_object');
}

// Использование:
$guideObject = GuideObject::find($id);
$photo = $guideObject->photo; // Photo объект
```

#### **Reviews (Отзывы):**
```php
public function reviews()
{
    return $this->hasMany(Review::class, 'guide_object_id');
}

// Использование:
$guideObject = GuideObject::find($id);
$reviews = $guideObject->reviews; // Review[] массив
```

---

## 📊 Расширение данных

### **Автоматическое расширение через ExpandHelper:**

#### **Базовое расширение:**
```php
$guideObject = GuideObject::findWithDetails($id);
// Автоматически расширяет:
// - add_user_id -> creator (User)
// - guide_object_type_id -> guide_object_type (GuideObjectType)
// - guide_object_kind_id -> guide_object_kind (GuideObjectKind)
// - status_id -> status (Status)
```

#### **Расширение с отзывами:**
```php
$guideObject = GuideObject::findWithReviews($id);
// Дополнительно включает:
// - reviews (Review[]) с авторами
```

#### **Расширение с фотографией:**
```php
$guideObject = GuideObject::findWithDetailsAndPhoto($id);
// Дополнительно включает:
// - photo (Photo)
```

---

## 🔐 Валидация

### **Правила валидации:**
```php
protected $rules = [
    'guide_object_type_id' => 'required|exists:ref_guide_object_types,id',
    'guide_object_kind_id' => 'required|exists:ref_guide_object_kinds,id',
    'name' => 'required|string|max:255',
    'city' => 'required|string|max:100',
    'address' => 'required|string',
    'website' => 'nullable|url',
    'phone' => 'nullable|string|max:20',
    'Instagram' => 'nullable|url',
    'Telegram' => 'nullable|url',
    'Viber' => 'nullable|string|max:20',
    'WhatsApp' => 'nullable|string|max:20',
    'description' => 'required|string',
    'service_list' => 'nullable|array',
    'price' => 'nullable|numeric|min:0',
    'brand' => 'nullable|string|max:100',
    'add_user_id' => 'required|exists:users,id',
    'status_id' => 'required|exists:ref_statuses,id'
];
```

### **Кастомная валидация:**
```php
public function validateName($value)
{
    // Проверка уникальности названия в городе
    $existing = GuideObject::where('name', $value)
        ->where('city', $this->city)
        ->where('id', '!=', $this->id)
        ->exists();
    
    if ($existing) {
        throw new ValidationException('Объект с таким названием уже существует в этом городе');
    }
    return $value;
}

public function validateServiceList($value)
{
    // Проверка формата списка услуг
    if (!is_array($value)) {
        throw new ValidationException('Список услуг должен быть массивом');
    }
    return $value;
}
```

---

## 📈 Производительность

### **Оптимизация запросов:**

#### **Eager Loading:**
```php
// Загрузить объекты с создателями и типами
$guideObjects = GuideObject::with(['creator', 'guideObjectType', 'status'])->get();
```

#### **Селективные поля:**
```php
// Только нужные поля
$guideObjects = GuideObject::select(['id', 'name', 'city', 'price', 'add_user_id'])
    ->with(['creator:id,first_name,last_name'])
    ->get();
```

#### **Индексы для поиска:**
```php
// Использование индексов
$guideObjects = GuideObject::where('city', $city)
    ->where('status_id', 2)
    ->get();
```

---

## 🧪 Тестирование

### **Unit тесты:**
```php
public function test_guide_object_creation()
{
    $objectData = [
        'guide_object_type_id' => 1,
        'guide_object_kind_id' => 3,
        'name' => 'Автосервис BMW',
        'city' => 'Москва',
        'address' => 'ул. Тверская, 15',
        'description' => 'Специализированный сервис',
        'service_list' => ['Диагностика', 'Ремонт'],
        'price' => 5000.00,
        'add_user_id' => 1,
        'status_id' => 2
    ];
    
    $guideObject = GuideObject::create($objectData);
    
    $this->assertDatabaseHas('guide_objects', $objectData);
    $this->assertEquals('Автосервис BMW', $guideObject->name);
}

public function test_guide_object_creator_relationship()
{
    $user = User::factory()->create();
    $guideObject = GuideObject::factory()->create(['add_user_id' => $user->id]);
    
    $this->assertEquals($user->id, $guideObject->creator->id);
    $this->assertTrue($user->guideObjects->contains($guideObject));
}

public function test_guide_object_reviews_relationship()
{
    $guideObject = GuideObject::factory()->create();
    $review = Review::factory()->create(['guide_object_id' => $guideObject->id]);
    
    $this->assertTrue($guideObject->reviews->contains($review));
    $this->assertEquals($guideObject->id, $review->guide_object_id);
}
```

---

## 🔗 Связанные документы

- [User Model](USER.md) — модель пользователей
- [GuideObjectType Model](GUIDE_OBJECT_TYPE.md) — модель типов гид-объектов
- [GuideObjectKind Model](GUIDE_OBJECT_KIND.md) — модель видов гид-объектов
- [Status Model](STATUS.md) — модель статусов
- [Photo Model](PHOTO.md) — модель фотографий
- [Review Model](REVIEW.md) — модель отзывов
- [Database Schema](../../DATABASE/SCHEMA.md) — структура БД
- [ExpandHelper](../../UTILS/EXPAND_HELPER.md) — расширение данных

---

> **Примечание:** Модель GuideObject управляет объектами гида (кафе, сервисы, достопримечательности). Все методы интегрированы с ExpandHelper для автоматического расширения связанных данных в API ответах. 
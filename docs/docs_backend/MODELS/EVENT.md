# Event Model - События 🎉

> **Назначение:** Модель для работы с событиями и мероприятиями в системе CabrioRide

---

## 🎯 Назначение

Модель Event предоставляет функционал для работы с событиями:
- CRUD операции для событий
- Управление участниками событий
- Поиск событий по различным критериям
- Расширение данных через ExpandHelper

---

## 🏗️ Архитектура

### **Таблица:** `events`

### **Основные поля:**
```php
protected $fillable = [
    'event_date',
    'event_time',
    'event_type_id',
    'title',
    'description',
    'location',
    'city',
    'price',
    'max_participants',
    'org_user_id',
    'registration_type',
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

#### **Создание события:**
```php
$event = Event::create([
    'event_date' => '2024-06-15',
    'event_time' => '14:00:00',
    'event_type_id' => 1,
    'title' => 'Встреча кабриолетов',
    'description' => 'Ежегодная встреча владельцев кабриолетов',
    'location' => 'Парк Горького',
    'city' => 'Москва',
    'price' => 0.00,
    'max_participants' => 50,
    'org_user_id' => 123,
    'registration_type' => 'free',
    'status_id' => 2
]);
```

#### **Получение события:**
```php
// По ID
$event = Event::find($id);

// С расширенными данными
$event = Event::findWithDetails($id);
// Включает: organizer, event_type, status, participants, photo

// По дате
$events = Event::findByDate('2024-06-15');
```

#### **Обновление события:**
```php
$event = Event::find($id);
$event->update([
    'title' => 'Обновлённое название',
    'max_participants' => 60
]);
```

#### **Удаление события:**
```php
$event = Event::find($id);
$event->delete();
```

### **Поиск и фильтрация:**

#### **Поиск по статусу:**
```php
$publishedEvents = Event::where('status_id', 2)->get();
$draftEvents = Event::where('status_id', 1)->get();
```

#### **Поиск по организатору:**
```php
$userEvents = Event::where('org_user_id', $userId)->get();
```

#### **Поиск по типу:**
```php
$meetupEvents = Event::where('event_type_id', 1)->get();
$tripEvents = Event::where('event_type_id', 2)->get();
```

#### **Поиск по городу:**
```php
$moscowEvents = Event::where('city', 'Москва')->get();
```

#### **Поиск по дате:**
```php
$upcomingEvents = Event::where('event_date', '>=', date('Y-m-d'))->get();
$pastEvents = Event::where('event_date', '<', date('Y-m-d'))->get();
```

#### **Комбинированный поиск:**
```php
$events = Event::where('status_id', 2)
    ->where('event_type_id', 1)
    ->where('city', 'Москва')
    ->where('event_date', '>=', date('Y-m-d'))
    ->get();
```

### **Расширенные методы:**

#### **findWithDetails():**
```php
$event = Event::findWithDetails($id);
// Результат включает:
// - organizer (User)
// - event_type (EventType)
// - status (Status)
// - participants (User[])
// - photo (Photo)
```

#### **findUpcoming():**
```php
$upcomingEvents = Event::findUpcoming($limit = 10);
// Найти предстоящие события
```

#### **findByOrganizer():**
```php
$userEvents = Event::findByOrganizer($userId);
// Найти все события пользователя
```

#### **findWithParticipants():**
```php
$event = Event::findWithParticipants($id);
// Событие с полной информацией об участниках
```

---

## 🔗 Связи с другими моделями

### **One-to-Many (1:N):**

#### **Organizer (Организатор):**
```php
public function organizer()
{
    return $this->belongsTo(User::class, 'org_user_id');
}

// Использование:
$event = Event::find($id);
$organizer = $event->organizer; // User объект
```

#### **EventType (Тип события):**
```php
public function eventType()
{
    return $this->belongsTo(EventType::class, 'event_type_id');
}

// Использование:
$event = Event::find($id);
$eventType = $event->eventType; // EventType объект
```

#### **Status (Статус):**
```php
public function status()
{
    return $this->belongsTo(Status::class, 'status_id');
}

// Использование:
$event = Event::find($id);
$status = $event->status; // Status объект
```

### **One-to-Many (1:N) - Обратные связи:**

#### **Photo (Фотография):**
```php
public function photo()
{
    return $this->hasOne(Photo::class, 'entity_id')
        ->where('entity_type', 'event');
}

// Использование:
$event = Event::find($id);
$photo = $event->photo; // Photo объект
```

### **Many-to-Many (M:N):**

#### **Participants (Участники):**
```php
public function participants()
{
    return $this->belongsToMany(User::class, 'link_event_participants', 'event_id', 'user_id')
        ->withPivot(['confidence', 'plus_one', 'created_at']);
}

// Использование:
$event = Event::find($id);
$participants = $event->participants; // User[] массив с pivot данными
```

---

## 📊 Расширение данных

### **Автоматическое расширение через ExpandHelper:**

#### **Базовое расширение:**
```php
$event = Event::findWithDetails($id);
// Автоматически расширяет:
// - org_user_id -> organizer (User)
// - event_type_id -> event_type (EventType)
// - status_id -> status (Status)
```

#### **Расширение с участниками:**
```php
$event = Event::findWithParticipants($id);
// Дополнительно включает:
// - participants (User[]) с pivot данными
```

#### **Расширение с фотографией:**
```php
$event = Event::findWithDetailsAndPhoto($id);
// Дополнительно включает:
// - photo (Photo)
```

---

## 🔐 Валидация

### **Правила валидации:**
```php
protected $rules = [
    'event_date' => 'required|date|after:today',
    'event_time' => 'required|date_format:H:i:s',
    'event_type_id' => 'required|exists:ref_event_types,id',
    'title' => 'required|string|max:255',
    'description' => 'required|string',
    'location' => 'required|string',
    'city' => 'required|string|max:100',
    'price' => 'required|numeric|min:0',
    'max_participants' => 'required|integer|min:1',
    'org_user_id' => 'required|exists:users,id',
    'registration_type' => 'required|in:free,invitation,confirmation',
    'status_id' => 'required|exists:ref_statuses,id'
];
```

### **Кастомная валидация:**
```php
public function validateEventDate($value)
{
    // Проверка, что событие не в прошлом
    if (strtotime($value) < time()) {
        throw new ValidationException('Дата события не может быть в прошлом');
    }
    return $value;
}

public function validateMaxParticipants($value)
{
    // Проверка лимита участников
    if ($value > 1000) {
        throw new ValidationException('Максимальное количество участников: 1000');
    }
    return $value;
}
```

---

## 📈 Производительность

### **Оптимизация запросов:**

#### **Eager Loading:**
```php
// Загрузить события с организаторами и типами
$events = Event::with(['organizer', 'eventType', 'status'])->get();
```

#### **Селективные поля:**
```php
// Только нужные поля
$events = Event::select(['id', 'title', 'event_date', 'city', 'org_user_id'])
    ->with(['organizer:id,first_name,last_name'])
    ->get();
```

#### **Индексы для поиска:**
```php
// Использование индексов
$events = Event::where('event_date', $date)
    ->where('status_id', 2)
    ->get();
```

---

## 🧪 Тестирование

### **Unit тесты:**
```php
public function test_event_creation()
{
    $eventData = [
        'event_date' => '2024-06-15',
        'event_time' => '14:00:00',
        'event_type_id' => 1,
        'title' => 'Встреча кабриолетов',
        'description' => 'Ежегодная встреча',
        'location' => 'Парк Горького',
        'city' => 'Москва',
        'price' => 0.00,
        'max_participants' => 50,
        'org_user_id' => 1,
        'registration_type' => 'free',
        'status_id' => 2
    ];
    
    $event = Event::create($eventData);
    
    $this->assertDatabaseHas('events', $eventData);
    $this->assertEquals('Встреча кабриолетов', $event->title);
}
```

### **Integration тесты:**
```php
public function test_event_organizer_relationship()
{
    $user = User::factory()->create();
    $event = Event::factory()->create(['org_user_id' => $user->id]);
    
    $this->assertEquals($user->id, $event->organizer->id);
    $this->assertTrue($user->organizedEvents->contains($event));
}

public function test_event_participants_relationship()
{
    $event = Event::factory()->create();
    $user = User::factory()->create();
    
    $event->participants()->attach($user->id, [
        'confidence' => 'yes',
        'plus_one' => false
    ]);
    
    $this->assertTrue($event->participants->contains($user));
    $this->assertEquals('yes', $event->participants->first()->pivot->confidence);
}
```

---

## 🔗 Связанные документы

- [User Model](USER.md) — модель пользователей
- [EventType Model](EVENT_TYPE.md) — модель типов событий
- [Status Model](STATUS.md) — модель статусов
- [Photo Model](PHOTO.md) — модель фотографий
- [EventParticipant Model](EVENT_PARTICIPANT.md) — модель участников событий
- [Database Schema](../../DATABASE/SCHEMA.md) — структура БД
- [ExpandHelper](../../UTILS/EXPAND_HELPER.md) — расширение данных

---

> **Примечание:** Модель Event управляет событиями и мероприятиями клуба. Все методы интегрированы с ExpandHelper для автоматического расширения связанных данных в API ответах. 
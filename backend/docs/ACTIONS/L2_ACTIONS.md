# 🎯 L2 Actions (бизнес-операции)

> **Назначение:** Документация L2 Actions — бизнес-операций с проверками и правилами  
> **Префикс:** `__` (два подчёркивания)  
> **Версия:** 1.0.0

---

## 🎯 **Обзор L2 Actions**

### **Принципы L2 Actions**
- **Используют L1 Actions** — комбинируют базовые операции
- **Добавляют бизнес-правила** — проверки и валидацию
- **Проверки прав доступа** — контроль доступа к операциям
- **Валидация бизнес-логики** — проверка бизнес-правил

### **Структура L2 Action**
```php
class __ActionName {
    public static function handle($data) {
        try {
            // 1. Валидация входных данных
            ValidationHelper::requireFields($data, ['required_field']);
            
            // 2. Проверки бизнес-правил
            if (!self::checkBusinessRule($data)) {
                return ['success' => false, 'error' => ['code' => 'BUSINESS_RULE_VIOLATION']];
            }
            
            // 3. Вызов L1 Actions
            $result = _L1Action::handle($data);
            
            // 4. Обработка результата
            return [
                'success' => true,
                'data' => $result['data'],
                'meta' => ['action' => 'performed']
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => ['code' => 'INTERNAL_ERROR', 'message' => $e->getMessage()]
            ];
        }
    }
}
```

---

## 🔄 **__SyncUserDataAction**

### **Назначение**
Синхронизация данных пользователя из Telegram с базой данных.

### **Логика работы**
1. **Проверка существования** — поиск пользователя по Telegram ID
2. **Создание/обновление** — создание нового или обновление существующего
3. **Сохранение фото** — обработка аватара пользователя
4. **Возврат данных** — полная информация о пользователе

### **Входные данные**
```php
$data = [
    'telegram_id' => 123456789,        // Обязательно
    'first_name' => 'Иван',            // Опционально
    'last_name' => 'Иванов',           // Опционально
    'username' => 'ivan',              // Опционально
    'photo' => $_FILES['photo']        // Опционально
];
```

### **Выходные данные**
```php
// Успешный результат
return [
    'success' => true,
    'data' => [
        'id' => 1,
        'telegram_id' => 123456789,
        'first_name' => 'Иван',
        'last_name' => 'Иванов',
        'username' => 'ivan',
        'role' => [
            'id' => 2,
            'code' => 'guest',
            'name' => 'Гость'
        ],
        'created_at' => '2024-01-01T12:00:00Z',
        'updated_at' => '2024-01-01T12:00:00Z'
    ],
    'meta' => [
        'action' => 'created',  // или 'updated', 'no_changes'
        'message' => 'Пользователь создан'
    ]
];

// Ошибка
return [
    'success' => false,
    'error' => [
        'code' => 'VALIDATION_ERROR',
        'message' => 'telegram_id обязателен'
    ]
];
```

### **Используемые L1 Actions**
- `_CheckUserByTelegramIdAction` — проверка существования пользователя
- `_CreateUserAction` — создание нового пользователя
- `_UpdateUserAction` — обновление данных пользователя
- `_CreatePhotoAction` — создание записи о фото

### **Код реализации**
```php
public static function handle($data) {
    try {
        // Валидация обязательных полей
        ValidationHelper::requireFields($data, ['telegram_id']);
        ValidationHelper::validateInt($data['telegram_id'], 'telegram_id');
        
        $telegramId = $data['telegram_id'];
        
        // 1. Проверяем существование пользователя
        $checkResult = _CheckUserByTelegramIdAction::handle(['telegram_id' => $telegramId]);
        
        if ($checkResult['success'] && $checkResult['data'] !== null) {
            // Пользователь найден - обновляем данные
            $userData = $checkResult['data'];
            $userId = $userData['id'];
            
            // Подготавливаем данные для обновления
            $updateData = [];
            $changedFields = [];
            
            // Проверяем какие поля изменились
            if (isset($data['first_name']) && $data['first_name'] !== $userData['first_name']) {
                $updateData['first_name'] = $data['first_name'];
                $changedFields[] = 'first_name';
            }
            
            if (isset($data['last_name']) && $data['last_name'] !== $userData['last_name']) {
                $updateData['last_name'] = $data['last_name'];
                $changedFields[] = 'last_name';
            }
            
            if (isset($data['username']) && $data['username'] !== $userData['username']) {
                $updateData['username'] = $data['username'];
                $changedFields[] = 'username';
            }
            
            // Если есть изменения - обновляем
            if (!empty($updateData)) {
                $updateResult = _UpdateUserAction::handle([
                    'user_id' => $userId,
                    'first_name' => $updateData['first_name'] ?? null,
                    'last_name' => $updateData['last_name'] ?? null,
                    'username' => $updateData['username'] ?? null
                ]);
                
                if ($updateResult['success']) {
                    $action = 'updated';
                    $userData = $updateResult['data'];
                } else {
                    return $updateResult;
                }
            } else {
                $action = 'no_changes';
            }
            
        } else {
            // Пользователь не найден - создаём нового
            $createData = [
                'telegram_id' => $telegramId,
                'first_name' => $data['first_name'] ?? '',
                'last_name' => $data['last_name'] ?? null,
                'username' => $data['username'] ?? null,
                'role_id' => 2 // guest по умолчанию
            ];
            
            $createResult = _CreateUserAction::handle($createData);
            
            if ($createResult['success']) {
                $action = 'created';
                $userData = $createResult['data'];
            } else {
                return $createResult;
            }
        }
        
        // Обработка фото, если передано
        if (isset($data['photo']) && $data['photo']['error'] === UPLOAD_ERR_OK) {
            $photoResult = self::handleUserPhoto($userData['id'], $data['photo']);
            if (!$photoResult['success']) {
                Logger::warning('Failed to save user photo', $photoResult);
            }
        }
        
        return [
            'success' => true,
            'data' => $userData,
            'meta' => [
                'action' => $action,
                'message' => self::getActionMessage($action)
            ]
        ];
        
    } catch (Exception $e) {
        Logger::error('SyncUserDataAction error', [
            'error' => $e->getMessage(),
            'telegram_id' => $data['telegram_id'] ?? 'unknown'
        ]);
        
        return [
            'success' => false,
            'error' => [
                'code' => 'INTERNAL_ERROR',
                'message' => $e->getMessage()
            ]
        ];
    }
}
```

---

## 🔍 **__SearchCarAction**

### **Назначение**
Поиск автомобиля по номеру с созданием, если не найден.

### **Логика работы**
1. **Поиск по номеру** — поиск существующего автомобиля
2. **Создание при необходимости** — создание нового автомобиля
3. **Связывание с пользователем** — привязка к текущему пользователю
4. **Возврат данных** — полная информация об автомобиле

### **Входные данные**
```php
$data = [
    'plate_number' => 'A123BC',        // Обязательно
    'brand' => 'BMW',                  // Опционально
    'model' => 'Z4',                   // Опционально
    'year' => 2020,                    // Опционально
    'user_id' => 1                     // Опционально
];
```

### **Выходные данные**
```php
return [
    'success' => true,
    'data' => [
        'id' => 1,
        'plate_number' => 'A123BC',
        'brand' => 'BMW',
        'model' => 'Z4',
        'year' => 2020,
        'owner' => [
            'id' => 1,
            'first_name' => 'Иван',
            'last_name' => 'Иванов'
        ],
        'created_at' => '2024-01-01T12:00:00Z'
    ],
    'meta' => [
        'action' => 'found',  // или 'created'
        'message' => 'Автомобиль найден'
    ]
];
```

---

## ➕ **__AddCarToUserAction**

### **Назначение**
Добавление автомобиля пользователю с проверками.

### **Логика работы**
1. **Проверка существования** — проверка автомобиля и пользователя
2. **Проверка прав** — проверка возможности добавления
3. **Создание связи** — связывание автомобиля с пользователем
4. **Возврат результата** — информация об операции

### **Входные данные**
```php
$data = [
    'user_id' => 1,                    // Обязательно
    'car_id' => 1,                     // Обязательно
    'is_owner' => true,                // Опционально
    'notes' => 'Мой основной автомобиль' // Опционально
];
```

### **Выходные данные**
```php
return [
    'success' => true,
    'data' => [
        'id' => 1,
        'user_id' => 1,
        'car_id' => 1,
        'is_owner' => true,
        'notes' => 'Мой основной автомобиль',
        'created_at' => '2024-01-01T12:00:00Z'
    ],
    'meta' => [
        'action' => 'added',
        'message' => 'Автомобиль добавлен пользователю'
    ]
];
```

---

## 👥 **__HandleUserJoinedAction**

### **Назначение**
Обработка входа пользователя в чат/клуб.

### **Логика работы**
1. **Синхронизация данных** — обновление данных пользователя
2. **Обновление роли** — изменение роли на member
3. **Логирование** — запись события входа
4. **Уведомления** — отправка уведомлений

### **Входные данные**
```php
$data = [
    'telegram_id' => 123456789,        // Обязательно
    'first_name' => 'Иван',            // Опционально
    'last_name' => 'Иванов',           // Опционально
    'username' => 'ivan',              // Опционально
    'chat_id' => -1001234567890        // Опционально
];
```

---

## 👋 **__HandleUserLeftAction**

### **Назначение**
Обработка выхода пользователя из чата/клуба.

### **Логика работы**
1. **Обновление роли** — изменение роли на guest
2. **Логирование** — запись события выхода
3. **Очистка данных** — удаление временных данных
4. **Уведомления** — отправка уведомлений

### **Входные данные**
```php
$data = [
    'telegram_id' => 123456789,        // Обязательно
    'chat_id' => -1001234567890        // Опционально
];
```

---

## 🗑️ **__DropBusinessCardAction**

### **Назначение**
Оставление визитной карточки с фото.

### **Логика работы**
1. **Валидация фото** — проверка качества изображения
2. **OCR распознавание** — извлечение текста с фото
3. **Создание визитки** — сохранение данных визитки
4. **Связывание с пользователем** — привязка к автору

### **Входные данные**
```php
$data = [
    'user_id' => 1,                    // Обязательно
    'photo' => $_FILES['photo'],       // Обязательно
    'location' => 'Москва',            // Опционально
    'notes' => 'Встреча в кафе'        // Опционально
];
```

---

## 🚨 **Обработка ошибок**

### **Типы ошибок L2 Actions**
- `VALIDATION_ERROR` — ошибка валидации входных данных
- `BUSINESS_RULE_VIOLATION` — нарушение бизнес-правил
- `USER_NOT_FOUND` — пользователь не найден
- `CAR_NOT_FOUND` — автомобиль не найден
- `DUPLICATE_ENTRY` — дублирование записи
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

## 📊 **Мониторинг и логирование**

### **Логируемые события**
- Выполнение L2 Actions
- Ошибки валидации
- Нарушения бизнес-правил
- Время выполнения операций

### **Примеры логов**
```php
Logger::info('SyncUserDataAction executed', [
    'telegram_id' => $telegramId,
    'action' => $action,
    'execution_time' => $executionTime
]);

Logger::warning('Business rule violation', [
    'action' => '__AddCarToUserAction',
    'user_id' => $userId,
    'car_id' => $carId,
    'rule' => 'user_already_has_car'
]);
```

---

## 🧪 **Тестирование L2 Actions**

### **Unit тесты**
```php
public function testSyncUserDataAction()
{
    // Тест создания нового пользователя
    $data = [
        'telegram_id' => 123456789,
        'first_name' => 'Иван',
        'last_name' => 'Иванов'
    ];
    
    $result = __SyncUserDataAction::handle($data);
    
    assert($result['success'] === true);
    assert($result['meta']['action'] === 'created');
    assert($result['data']['telegram_id'] === 123456789);
}
```

### **Интеграционные тесты**
```php
public function testSearchCarAction()
{
    // Тест поиска и создания автомобиля
    $data = [
        'plate_number' => 'A123BC',
        'brand' => 'BMW',
        'model' => 'Z4'
    ];
    
    $result = __SearchCarAction::handle($data);
    
    assert($result['success'] === true);
    assert($result['data']['plate_number'] === 'A123BC');
}
```

---

## 📚 **Связанная документация**

- [L1 Actions](L1_ACTIONS.md) — базовые операции
- [L3 Actions](L3_ACTIONS.md) — сложные сценарии
- [Взаимодействие Actions](../ACTIONS_INTERACTION.md) — правила взаимодействия
- [Модели данных](../../MODELS/OVERVIEW.md) — структура данных

---

> **💡 Совет:** L2 Actions — это основной уровень бизнес-логики. Используйте их для комбинирования L1 Actions с добавлением проверок и правил. 
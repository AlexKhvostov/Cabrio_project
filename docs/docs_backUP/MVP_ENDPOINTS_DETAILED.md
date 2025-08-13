# 🚗 Подробное описание MVP эндпоинтов CabrioRide

> **Назначение:** Детальное описание всех эндпоинтов, экшенов, контроллеров и моделей для MVP функций управления автомобилями.

---

## 📋 **Обзор MVP функций**

Система CabrioRide MVP включает 4 критически важные функции:

1. **👤 Создание/обновление пользователя** - регистрация и синхронизация пользователя
2. **🔍 Проверка авто** - узнать есть ли авто в базе клуба
3. **🏷️ Оставление визитки** - оставить визитку в авто  
4. **🚙 Добавление в гараж** - добавить авто в свой гараж

---

## 👤 **Функция 1: Создание/обновление пользователя**

### **Endpoint**
```
POST /api/users/sync-from-telegram
```

### **L3 Action**
```
___SyncUserFromTelegramAction
```

### **Описание**
**Критически важная функция MVP:** Пользователь обращается к системе через Telegram, нужно создать или обновить его профиль в базе данных.

### **Логика обработки**
1. **Проверка существования пользователя** (`_CheckUserByTelegramIdAction`)
   - Если пользователь найден → обновление данных (`_UpdateUserAction`)
   - Если пользователь не найден → создание нового (`_CreateUserAction`)
2. **Создание пользователя** (если не найден)
   - Роль по умолчанию: `guest` (устанавливается в `_CreateUserAction`)
3. **Обновление пользователя** (если найден)
   - Обновление только изменившихся полей
   - Сохранение существующей роли

### **Входные данные**
```json
{
  "telegram_id": 123456789,
  "first_name": "Иван",
  "last_name": "Иванов",
  "username": "ivan_ivanov",
  "language_code": "ru"
}
```

### **Возможные ответы**

**✅ Пользователь создан:**
```json
{
  "success": true,
  "data": {
    "user_id": 15,
    "action": "created",
    "role": "guest",
    "status": "active",
    "message": "Пользователь успешно создан"
  }
}
```

**✅ Пользователь обновлен:**
```json
{
  "success": true,
  "data": {
    "user_id": 15,
    "action": "updated",
    "role": "member",
    "status": "active",
    "updated_fields": ["first_name", "username"],
    "message": "Данные пользователя обновлены"
  }
}
```

**✅ Пользователь найден без изменений:**
```json
{
  "success": true,
  "data": {
    "user_id": 15,
    "action": "no_changes",
    "role": "member",
    "status": "active",
    "message": "Данные пользователя актуальны"
  }
}
```

---

## 🔍 **Функция 2: Проверка авто**

### **Endpoint**
```
POST /api/cars/check-in-club
```

### **Контроллер**
```php
// backend/controllers/CarController.php
class CarController extends BaseController {
    public function checkInClub() {
        try {
            // 1. Валидация входных данных
            $this->requireAuth();
            $this->validatePhotoFile($_FILES['photo'] ?? null);
            
            // 2. Вызов L3 экшена
            $result = ___CheckCarInClubAction::handle(
                $_FILES['photo'],
                $_POST['telegram_id']
            );
            
            $this->json($result);
            
        } catch (ValidationException $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 400);
        } catch (Exception $e) {
            $this->json(['success' => false, 'error' => 'Internal error'], 500);
        }
    }
}
```

### **L3 Action - Полный сценарий**
```php
// backend/actions/level3/___CheckCarInClubAction.php
class ___CheckCarInClubAction {
    public static function handle($photoFile, $telegramId) {
        try {
            // 1. Распознавание номера (utils)
            $plateNumber = RecognizeCarNumberFromPhotoAction::handle($photoFile);
            
            // 2. Поиск авто в клубе (L2)
            $result = __SearchCarAction::handle($plateNumber, $telegramId);
            
            return $result;
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
```

### **L2 Action - Бизнес-операция**
```php
// backend/actions/level2/__SearchCarAction.php
class __SearchCarAction {
    public static function handle($plateNumber, $telegramId) {
        // 1. Проверка существования авто (L1)
        $car = _CheckCarInDbAction::handle($plateNumber);
        
        if ($car) {
            // Авто есть в клубе
            return ['success' => true, 'data' => [
                'plate_number' => $plateNumber,
                'status' => $car->status,
                'message' => 'Автомобиль найден в базе клуба'
            ]];
        } else {
            // Создание авто со статусом "замечена" (L1)
            $carData = [
                'plate_number' => $plateNumber,
                'status_id' => 1, // "Замечена" (ID=1)
                'created_by' => $telegramId
            ];
            $carId = _CreateCarAction::handle($carData);
            
            // Установка статуса (L1)
            _UpdateStatusAction::handle('car', $carId, 1); // "Замечена" (ID=1)
            
            return ['success' => true, 'data' => [
                'plate_number' => $plateNumber,
                'status' => 'Замечена',
                'message' => 'Автомобиль не в клубе. Добавлен в базу'
            ]];
        }
    }
}
```

### **L1 Actions - Атомарные операции**
```php
// backend/actions/level1/_CheckCarInDbAction.php
class _CheckCarInDbAction {
    public static function handle($plateNumber) {
        return Car::findByPlateNumber($plateNumber);
    }
}

// backend/actions/level1/_CreateCarAction.php
class _CreateCarAction {
    public static function handle($carData) {
        return Car::create($carData);
    }
}

// backend/actions/level1/_UpdateStatusAction.php
class _UpdateStatusAction {
    public static function handle($entityType, $entityId, $statusId) {
        switch ($entityType) {
            case 'car':
                return Car::updateStatus($entityId, $statusId);
            // другие сущности...
        }
    }
}
```

### **Utils - Внешние интеграции**
```php
// backend/utils/RecognizeCarNumberFromPhotoAction.php
class RecognizeCarNumberFromPhotoAction {
    public static function handle($photoFile) {
        // Интеграция с OCR API
        $result = OCRHelper::recognizePlate($photoFile);
        
        if (!$result['success']) {
            throw new ValidationException('Не удалось распознать номер автомобиля');
        }
        
        return $result['data']['plate_number'];
    }
}
```

### **Модели**
```php
// backend/models/Car.php
class Car {
    public static function findByPlateNumber($plateNumber) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('SELECT * FROM cars WHERE plate_number = ?');
        $stmt->execute([$plateNumber]);
        $data = $stmt->fetch();
        return $data ? new self($data) : null;
    }
    
    public static function create($data) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('INSERT INTO cars (...) VALUES (...)');
        $stmt->execute($data);
        return $pdo->lastInsertId();
    }
    
    public static function updateStatus($carId, $statusId) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('UPDATE cars SET status_id = ? WHERE id = ?');
        return $stmt->execute([$statusId, $carId]);
    }
}
```

---

## 🏷️ **Функция 3: Оставление визитки**

### **Endpoint**
```
POST /api/cars/leave-business-card
```

### **Контроллер**
```php
// backend/controllers/CarController.php
class CarController extends BaseController {
    public function leaveBusinessCard() {
        try {
            // 1. Валидация входных данных
            $this->requireAuth();
            $this->validatePhotoFile($_FILES['photo'] ?? null);
            
            // 2. Вызов L3 экшена
            $result = ___LeaveBusinessCardAction::handle(
                $_FILES['photo'],
                $_POST['telegram_id'],
                $_POST['message'] ?? null
            );
            
            $this->json($result);
            
        } catch (ValidationException $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 400);
        } catch (Exception $e) {
            $this->json(['success' => false, 'error' => 'Internal error'], 500);
        }
    }
}
```

### **L3 Action - Полный сценарий**
```php
// backend/actions/level3/___LeaveBusinessCardAction.php
class ___LeaveBusinessCardAction {
    public static function handle($photoFile, $telegramId, $message = null) {
        try {
            // 1. Распознавание номера (utils)
            $plateNumber = RecognizeCarNumberFromPhotoAction::handle($photoFile);
            
            // 2. Оставление визитки (L2)
            $result = __DropBusinessCardAction::handle($plateNumber, $telegramId, $message, $photoFile);
            
            return $result;
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
```

### **L2 Action - Бизнес-операция**
```php
// backend/actions/level2/__DropBusinessCardAction.php
class __DropBusinessCardAction {
    public static function handle($plateNumber, $telegramId, $message, $photoFile) {
        // 1. Проверка существования авто (L1)
        $car = _CheckCarInDbAction::handle($plateNumber);
        
        if (!$car) {
            // Создание авто со статусом "визитка" (L1)
            $carData = [
                'plate_number' => $plateNumber,
                'status_id' => 2, // "Визитка" (ID=2)
                'created_by' => $telegramId
            ];
            $carId = _CreateCarAction::handle($carData);
            
            // Установка статуса (L1)
            _UpdateStatusAction::handle('car', $carId, 2); // "Визитка" (ID=2)
        } else {
            $carId = $car->id;
            
            // Обновление статуса если "замечена" (L1)
            if ($car->status_id == 1) { // "Замечена" (ID=1)
                _UpdateStatusAction::handle('car', $carId, 2); // "Визитка" (ID=2)
            }
        }
        
        // 2. Создание визитки (L1)
        $businessCardData = [
            'car_id' => $carId,
            'user_id' => $telegramId,
            'message' => $message,
            'created_at' => date('Y-m-d H:i:s')
        ];
        $businessCardId = _CreateBusinessCardAction::handle($businessCardData);
        
        // 3. Создание фото визитки (L1)
        if ($photoFile) {
            $photoData = [
                'entity_type' => 'business_card',
                'entity_id' => $businessCardId,
                'file_name' => $photoFile['name'],
                'url' => self::savePhoto($photoFile)
            ];
            _CreatePhotoAction::handle($photoData);
        }
        
        return ['success' => true, 'data' => [
            'plate_number' => $plateNumber,
            'car_status' => 'Визитка',
            'business_card_id' => $businessCardId,
            'message' => 'Визитка успешно оставлена в автомобиле'
        ]];
    }
}
```

### **L1 Actions - Атомарные операции**
```php
// backend/actions/level1/_CreateBusinessCardAction.php
class _CreateBusinessCardAction {
    public static function handle($businessCardData) {
        return BusinessCard::create($businessCardData);
    }
}

// backend/actions/level1/_CreatePhotoAction.php
class _CreatePhotoAction {
    public static function handle($photoData) {
        return Photo::create($photoData);
    }
}
```

### **Модели**
```php
// backend/models/BusinessCard.php
class BusinessCard {
    public static function create($data) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('INSERT INTO business_cards (...) VALUES (...)');
        $stmt->execute($data);
        return $pdo->lastInsertId();
    }
}

// backend/models/Photo.php
class Photo {
    public static function create($data) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('INSERT INTO photos (...) VALUES (...)');
        $stmt->execute($data);
        return $pdo->lastInsertId();
    }
}
```

---

## 🚙 **Функция 4: Добавление авто в гараж**

### **Endpoint**
```
POST /api/cars/add-to-garage
```

### **Контроллер**
```php
// backend/controllers/CarController.php
class CarController extends BaseController {
    public function addToGarage() {
        try {
            // 1. Валидация входных данных
            $this->requireAuth();
            $this->validatePhotoFile($_FILES['photo'] ?? null);
            
            // 2. Вызов L3 экшена
            $result = ___AddCarToGarageAction::handle(
                $_FILES['photo'],
                $_POST['telegram_id']
            );
            
            $this->json($result);
            
        } catch (ValidationException $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 400);
        } catch (Exception $e) {
            $this->json(['success' => false, 'error' => 'Internal error'], 500);
        }
    }
}
```

### **L3 Action - Полный сценарий**
```php
// backend/actions/level3/___AddCarToGarageAction.php
class ___AddCarToGarageAction {
    public static function handle($photoFile, $telegramId) {
        try {
            // 1. Распознавание номера (utils)
            $plateNumber = RecognizeCarNumberFromPhotoAction::handle($photoFile);
            
            // 2. Добавление авто пользователю (L2)
            $result = __AddCarToUserAction::handle($plateNumber, $telegramId, $photoFile);
            
            return $result;
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
```

### **L2 Action - Бизнес-операция**
```php
// backend/actions/level2/__AddCarToUserAction.php
class __AddCarToUserAction {
    public static function handle($plateNumber, $telegramId, $photoFile = null) {
        // 1. Проверка существования авто (L1)
        $car = _CheckCarInDbAction::handle($plateNumber);
        
        if ($car) {
            // Авто есть в БД
            if ($car->owner_user_id) {
                // Уже есть владелец
                if ($car->owner_user_id == $telegramId) {
                    // Наш владелец - обновляем статус
                    _UpdateStatusAction::handle('car', $car->id, 7); // "Активный" (ID=7)
                    
                    return ['success' => true, 'data' => [
                        'plate_number' => $plateNumber,
                        'car_status' => 'Активный',
                        'owner_status' => 'Уже владелец',
                        'message' => 'Вы уже являетесь владельцем этого автомобиля'
                    ]];
                } else {
                    // Другой владелец - ошибка
                    return ['success' => false, 'error' => [
                        'code' => 'CAR_HAS_OTHER_OWNER',
                        'message' => 'У этого автомобиля уже есть владелец',
                        'data' => ['current_owner_id' => $car->owner_user_id]
                    ]];
                }
            } else {
                // Нет владельца - устанавливаем
                _UpdateOwnerToCarAction::handle($car->id, $telegramId);
                _UpdateStatusAction::handle('car', $car->id, 7); // "Активный" (ID=7)
                
                return ['success' => true, 'data' => [
                    'plate_number' => $plateNumber,
                    'car_status' => 'Активный',
                    'owner_status' => 'Установлен владелец',
                    'message' => 'Автомобиль успешно добавлен в ваш гараж'
                ]];
            }
        } else {
            // Авто нет в БД - создаем
            $carData = [
                'plate_number' => $plateNumber,
                'status_id' => 7, // "Активный" (ID=7)
                'owner_user_id' => $telegramId,
                'created_by' => $telegramId
            ];
            $carId = _CreateCarAction::handle($carData);
            
            // Установка статуса (L1)
            _UpdateStatusAction::handle('car', $carId, 7); // "Активный" (ID=7)
            
            // Установка владельца (L1)
            _UpdateOwnerToCarAction::handle($carId, $telegramId);
            
            return ['success' => true, 'data' => [
                'plate_number' => $plateNumber,
                'car_status' => 'Активный',
                'owner_status' => 'Установлен владелец',
                'message' => 'Автомобиль успешно добавлен в ваш гараж'
            ]];
        }
    }
}
```

### **L1 Actions - Атомарные операции**
```php
// backend/actions/level1/_UpdateOwnerToCarAction.php
class _UpdateOwnerToCarAction {
    public static function handle($carId, $userId) {
        return Car::updateOwner($carId, $userId);
    }
}
```

### **Модели**
```php
// backend/models/Car.php (дополнительные методы)
class Car {
    public static function updateOwner($carId, $userId) {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('UPDATE cars SET owner_user_id = ? WHERE id = ?');
        return $stmt->execute([$userId, $carId]);
    }
}
```

---

## 🔧 **Технические детали**

### **Маршрутизация**
```php
// backend/routes/api.php
// Создание/обновление пользователя
elseif ($route === '/api/users/sync-from-telegram' && $method === 'POST') {
    require_once __DIR__ . '/../controllers/UserController.php';
    (new UserController())->syncFromTelegram();
}
// Проверка авто
elseif ($route === '/api/cars/check-in-club' && $method === 'POST') {
    require_once __DIR__ . '/../controllers/CarController.php';
    (new CarController())->checkInClub();
}
// Оставление визитки
elseif ($route === '/api/cars/leave-business-card' && $method === 'POST') {
    require_once __DIR__ . '/../controllers/CarController.php';
    (new CarController())->leaveBusinessCard();
}
// Добавление в гараж
elseif ($route === '/api/cars/add-to-garage' && $method === 'POST') {
    require_once __DIR__ . '/../controllers/CarController.php';
    (new CarController())->addToGarage();
}
```

### **Валидация в контроллере**
```php
// backend/controllers/CarController.php
class CarController extends BaseController {
    private function validatePhotoFile($photoFile) {
        if (!$photoFile || !isset($photoFile['tmp_name'])) {
            throw new ValidationException('Photo file is required');
        }
        
        // Использование централизованной валидации
        ValidationHelper::validateImageFile($photoFile);
    }
}
```

### **Обработка ошибок**
```php
// backend/controllers/CarController.php
class CarController extends BaseController {
    public function checkInClub() {
        try {
            // Логика...
        } catch (ValidationException $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 400);
        } catch (BusinessRuleException $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 422);
        } catch (Exception $e) {
            Logger::error('Car check error: ' . $e->getMessage());
            $this->json(['success' => false, 'error' => 'Internal error'], 500);
        }
    }
}
```

---

## 📊 **Сводная таблица всех компонентов**

### **Endpoints**
| Функция | Endpoint | Метод | Контроллер | L3 Action |
|---------|----------|-------|------------|-----------|
| Создание/обновление пользователя | `/api/users/sync-from-telegram` | POST | UserController | ___SyncUserFromTelegramAction |
| Проверка авто | `/api/cars/check-in-club` | POST | CarController | ___CheckCarInClubAction |
| Оставление визитки | `/api/cars/leave-business-card` | POST | CarController | ___LeaveBusinessCardAction |
| Добавление в гараж | `/api/cars/add-to-garage` | POST | CarController | ___AddCarToGarageAction |

### **Actions по уровням**
| Уровень | Action | Назначение |
|---------|--------|------------|
| **utils** | `RecognizeCarNumberFromPhotoAction` | Распознавание номера авто |
| **L1** | `_CheckUserByTelegramIdAction` | Проверка существования пользователя |
| **L1** | `_CreateUserAction` | Создание пользователя |
| **L1** | `_UpdateUserAction` | Обновление данных пользователя |
| **L1** | `_CheckCarInDbAction` | Проверка существования авто |
| **L1** | `_CreateCarAction` | Создание авто |
| **L1** | `_UpdateStatusAction` | Обновление статуса |
| **L1** | `_UpdateOwnerToCarAction` | Обновление владельца |
| **L1** | `_CreateBusinessCardAction` | Создание визитки |
| **L1** | `_CreatePhotoAction` | Создание фото |
| **L2** | `__SyncUserDataAction` | Синхронизация данных пользователя |
| **L2** | `__SearchCarAction` | Поиск авто в клубе |
| **L2** | `__DropBusinessCardAction` | Оставление визитки |
| **L2** | `__AddCarToUserAction` | Добавление авто пользователю |
| **L3** | `___SyncUserFromTelegramAction` | Полный сценарий синхронизации пользователя |
| **L3** | `___CheckCarInClubAction` | Полный сценарий проверки |
| **L3** | `___LeaveBusinessCardAction` | Полный сценарий визитки |
| **L3** | `___AddCarToGarageAction` | Полный сценарий гаража |

### **Модели**
| Модель | Методы | Назначение |
|--------|--------|------------|
| `User` | `findByTelegramId()`, `create()`, `update()`, `updateRole()` | Работа с пользователями |
| `Car` | `findByPlateNumber()`, `create()`, `updateStatus()`, `updateOwner()` | Работа с автомобилями |
| `BusinessCard` | `create()` | Работа с визитками |
| `Photo` | `create()` | Работа с фотографиями |

### **Утилиты**
| Утилита | Назначение |
|---------|------------|
| `ValidationHelper` | Валидация данных |
| `OCRHelper` | Интеграция с OCR API |
| `ResponseHelper` | Формирование ответов |
| `AuthHelper` | Проверка авторизации |

---

## 🎯 **Архитектурные принципы**

### **✅ Соблюдены принципы:**
1. **Разделение ответственности** - каждый уровень имеет свою роль
2. **Атомарность L1** - простые операции с одной сущностью
3. **Комбинированность L2** - бизнес-операции с несколькими сущностями
4. **Полнота L3** - полные пользовательские сценарии
5. **Переиспользование** - L1 используются в L2, L2 в L3
6. **Централизованная валидация** - через ValidationHelper
7. **Единообразная обработка ошибок** - через контроллеры

### **✅ Преимущества архитектуры:**
- **Масштабируемость** - легко добавлять новые функции
- **Тестируемость** - каждый уровень тестируется отдельно
- **Поддерживаемость** - четкое разделение ответственности
- **Переиспользование** - компоненты используются в разных сценариях

**Архитектура MVP полностью готова к реализации!** 🚀 
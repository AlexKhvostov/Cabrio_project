# Testing - Тестирование 🧪

> **Назначение:** Документация по тестированию backend системы CabrioRide

---

## 🎯 Назначение

Тестирование обеспечивает надёжность и качество backend системы:
- Unit тесты для отдельных компонентов
- Integration тесты для взаимодействия компонентов
- API тесты для эндпоинтов
- Автоматизированное тестирование CI/CD

---

## 🏗️ Архитектура тестирования

### **Принципы:**
- **AAA Pattern** — Arrange, Act, Assert
- **Isolation** — каждый тест независим
- **Coverage** — покрытие критических путей
- **Maintainability** — простота поддержки тестов

### **Структура тестов:**
```
backend/_tests/
├── unit/           # Unit тесты
├── integration/    # Integration тесты
├── api/           # API тесты
├── fixtures/      # Тестовые данные
└── helpers/       # Вспомогательные функции
```

---

## 📋 Типы тестов

### **Unit тесты:**
- **Модели** — CRUD операции, валидация, связи
- **Утилиты** — Helper классы, валидация
- **Actions** — Бизнес-логика L1-L4
- **Middleware** — Авторизация, обработка запросов

### **Integration тесты:**
- **Контроллеры** — Взаимодействие с моделями
- **API Endpoints** — Полные запросы
- **Database** — Связи между таблицами
- **Authentication** — Авторизация и сессии

### **API тесты:**
- **HTTP Requests** — GET, POST, PUT, DELETE
- **Response Format** — JSON структура
- **Error Handling** — Обработка ошибок
- **Authorization** — Права доступа

---

## 🔧 Настройка окружения

### **Конфигурация тестов:**
```php
// phpunit.xml
<php>
    <env name="APP_ENV" value="testing"/>
    <env name="DB_DATABASE" value="cabriotide_test"/>
    <env name="CACHE_DRIVER" value="array"/>
    <env name="SESSION_DRIVER" value="array"/>
</php>
```

### **Тестовая база данных:**
```sql
-- Создание тестовой БД
CREATE DATABASE cabriotide_test;
USE cabriotide_test;

-- Импорт схемы
SOURCE database/schema.sql;

-- Тестовые данные
SOURCE _tests/fixtures/test_data.sql;
```

---

## 🧪 Unit тесты

### **Тестирование моделей:**

#### **User Model:**
```php
class UserTest extends TestCase
{
    public function test_user_creation()
    {
        $userData = [
            'telegram_id' => 123456789,
            'username' => 'test_user',
            'first_name' => 'Test',
            'last_name' => 'User',
            'role_id' => 4
        ];
        
        $user = User::create($userData);
        
        $this->assertDatabaseHas('users', $userData);
        $this->assertEquals('test_user', $user->username);
    }
    
    public function test_user_validation()
    {
        $this->expectException(ValidationException::class);
        
        User::create([
            'telegram_id' => 'invalid',
            'username' => '',
            'role_id' => 999
        ]);
    }
}
```

#### **Car Model:**
```php
class CarTest extends TestCase
{
    public function test_car_owner_relationship()
    {
        $user = User::factory()->create();
        $car = Car::factory()->create(['owner_user_id' => $user->id]);
        
        $this->assertEquals($user->id, $car->owner->id);
        $this->assertTrue($user->cars->contains($car));
    }
    
    public function test_car_search_by_plate()
    {
        $car = Car::factory()->create(['reg_number' => 'A123BC']);
        
        $foundCar = Car::findByPlateNumber('A123BC');
        
        $this->assertEquals($car->id, $foundCar->id);
    }
}
```

### **Тестирование утилит:**

#### **AuthHelper:**
```php
class AuthHelperTest extends TestCase
{
    public function test_extract_telegram_data()
    {
        $telegramData = [
            'id' => 123456789,
            'first_name' => 'Test',
            'username' => 'test_user',
            'hash' => 'valid_hash'
        ];
        
        $extracted = AuthHelper::extractTelegramData($telegramData);
        
        $this->assertEquals(123456789, $extracted['id']);
        $this->assertEquals('Test', $extracted['first_name']);
    }
    
    public function test_hash_validation()
    {
        $data = ['id' => 123, 'first_name' => 'Test'];
        $hash = AuthHelper::generateHash($data);
        
        $isValid = AuthHelper::isHashValid($data, $hash);
        
        $this->assertTrue($isValid);
    }
}
```

#### **ExpandHelper:**
```php
class ExpandHelperTest extends TestCase
{
    public function test_expand_user_data()
    {
        $userData = [
            'id' => 1,
            'role_id' => 4,
            'first_name' => 'Test'
        ];
        
        $expanded = ExpandHelper::expandUserData($userData);
        
        $this->assertArrayHasKey('role', $expanded);
        $this->assertEquals('member', $expanded['role']['code']);
    }
}
```

---

## 🔗 Integration тесты

### **Тестирование контроллеров:**

#### **UserController:**
```php
class UserControllerTest extends TestCase
{
    public function test_get_users_list()
    {
        $user = User::factory()->create(['role_id' => 4]);
        
        $response = $this->actingAs($user)
            ->get('/api/users');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'first_name', 'last_name', 'role']
                ]
            ]);
    }
    
    public function test_create_user()
    {
        $userData = [
            'telegram_id' => 123456789,
            'username' => 'new_user',
            'first_name' => 'New',
            'last_name' => 'User'
        ];
        
        $response = $this->post('/api/users', $userData);
        
        $response->assertStatus(201);
        $this->assertDatabaseHas('users', $userData);
    }
}
```

#### **CarController:**
```php
class CarControllerTest extends TestCase
{
    public function test_get_car_details()
    {
        $user = User::factory()->create();
        $car = Car::factory()->create(['owner_user_id' => $user->id]);
        
        $response = $this->actingAs($user)
            ->get("/api/cars/{$car->id}");
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id', 'model', 'color', 'owner', 'brand', 'status'
                ]
            ]);
    }
}
```

### **Тестирование авторизации:**

#### **AuthMiddleware:**
```php
class AuthMiddlewareTest extends TestCase
{
    public function test_telegram_authentication()
    {
        $telegramData = [
            'id' => 123456789,
            'first_name' => 'Test',
            'hash' => 'valid_hash'
        ];
        
        $request = $this->createRequest($telegramData);
        
        $result = AuthMiddleware::process($request);
        
        $this->assertTrue($result['success']);
        $this->assertEquals(123456789, $result['user_id']);
    }
    
    public function test_system_token_authentication()
    {
        $request = $this->createRequest([], 'SYSTEM_TOKEN');
        
        $result = AuthMiddleware::authenticate('/api/system/stats', 'GET');
        
        $this->assertTrue($result['success']);
        $this->assertEquals(0, $result['user_id']);
    }
}
```

---

## 🌐 API тесты

### **Тестирование эндпоинтов:**

#### **Users API:**
```php
class UsersApiTest extends TestCase
{
    public function test_get_users_endpoint()
    {
        $user = User::factory()->create();
        
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$user->session_token}",
            'Content-Type' => 'application/json'
        ])->get('/api/users');
        
        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ])
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'first_name', 'last_name', 'username',
                        'role', 'city', 'email', 'photo'
                    ]
                ],
                'pagination'
            ]);
    }
    
    public function test_create_user_endpoint()
    {
        $userData = [
            'telegram_id' => 123456789,
            'username' => 'test_user',
            'first_name' => 'Test',
            'last_name' => 'User'
        ];
        
        $response = $this->post('/api/users', $userData);
        
        $response->assertStatus(201)
            ->assertJson([
                'success' => true
            ]);
        
        $this->assertDatabaseHas('users', $userData);
    }
}
```

#### **Cars API:**
```php
class CarsApiTest extends TestCase
{
    public function test_get_cars_endpoint()
    {
        $user = User::factory()->create();
        $car = Car::factory()->create(['owner_user_id' => $user->id]);
        
        $response = $this->actingAs($user)
            ->get('/api/cars');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'model', 'color', 'year', 'plate_number',
                        'status', 'owner', 'brand'
                    ]
                ]
            ]);
    }
    
    public function test_upload_car_photo()
    {
        $user = User::factory()->create();
        $car = Car::factory()->create(['owner_user_id' => $user->id]);
        
        $file = UploadedFile::fake()->image('car.jpg');
        
        $response = $this->actingAs($user)
            ->post("/api/cars/{$car->id}/photos", [
                'photo' => $file
            ]);
        
        $response->assertStatus(201);
        $this->assertDatabaseHas('photos', [
            'entity_type' => 'car',
            'entity_id' => $car->id
        ]);
    }
}
```

---

## 🔐 Тестирование безопасности

### **Тестирование авторизации:**

#### **Access Control:**
```php
class AccessControlTest extends TestCase
{
    public function test_unauthorized_access()
    {
        $response = $this->get('/api/users');
        
        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'error' => 'unauthorized'
            ]);
    }
    
    public function test_insufficient_permissions()
    {
        $user = User::factory()->create(['role_id' => 1]); // guest
        
        $response = $this->actingAs($user)
            ->get('/api/system/stats');
        
        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'error' => 'access_denied'
            ]);
    }
    
    public function test_admin_access()
    {
        $admin = User::factory()->create(['role_id' => 6]); // admin
        
        $response = $this->actingAs($admin)
            ->get('/api/system/stats');
        
        $response->assertStatus(200);
    }
}
```

### **Тестирование валидации:**

#### **Input Validation:**
```php
class ValidationTest extends TestCase
{
    public function test_invalid_email()
    {
        $userData = [
            'email' => 'invalid-email',
            'telegram_id' => 123456789
        ];
        
        $response = $this->post('/api/users', $userData);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }
    
    public function test_duplicate_telegram_id()
    {
        $existingUser = User::factory()->create(['telegram_id' => 123456789]);
        
        $userData = [
            'telegram_id' => 123456789,
            'username' => 'new_user'
        ];
        
        $response = $this->post('/api/users', $userData);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['telegram_id']);
    }
}
```

---

## 📊 Покрытие тестами

### **Метрики покрытия:**
- **Models** — 95% покрытие
- **Controllers** — 90% покрытие
- **Utils** — 100% покрытие
- **Middleware** — 85% покрытие
- **Actions** — 80% покрытие

### **Критические пути:**
- ✅ Авторизация и аутентификация
- ✅ CRUD операции моделей
- ✅ API эндпоинты
- ✅ Валидация данных
- ✅ Обработка ошибок
- ✅ Расширение данных

---

## 🚀 CI/CD интеграция

### **GitHub Actions:**
```yaml
name: Backend Tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.1'
      - name: Install dependencies
        run: composer install
      - name: Run tests
        run: vendor/bin/phpunit --coverage-clover=coverage.xml
      - name: Upload coverage
        uses: codecov/codecov-action@v1
        with:
          file: ./coverage.xml
```

---

## 🔗 Связанные документы

- [Models Documentation](../MODELS/OVERVIEW.md) — документация моделей
- [API Documentation](../API/OVERVIEW.md) — документация API
- [Authentication](../AUTHENTICATION/OVERVIEW.md) — авторизация
- [Database Schema](../DATABASE/SCHEMA.md) — структура БД

---

> **Примечание:** Тестирование является критически важной частью разработки. Все изменения должны сопровождаться соответствующими тестами для обеспечения надёжности системы. 
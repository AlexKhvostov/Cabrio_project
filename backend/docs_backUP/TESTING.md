# Тестирование backend CabrioRide

> Подробная стратегия тестирования для обеспечения качества и надёжности backend API.
> 
> **Важно:** Каждое изменение должно сопровождаться соответствующими тестами!

---

## 🎯 Типы тестов

### 1. Unit тесты (модульные)
- **Цель:** тестирование отдельных функций и методов
- **Объекты:** модели, утилиты, хелперы
- **Инструменты:** PHPUnit
- **Частота:** при каждом изменении кода

### 2. Integration тесты (интеграционные)
- **Цель:** тестирование API эндпоинтов
- **Объекты:** контроллеры, actions, маршруты
- **Инструменты:** HTML/JS тесты, Postman
- **Частота:** при изменении API

### 3. E2E тесты (end-to-end)
- **Цель:** тестирование полных пользовательских сценариев
- **Объекты:** весь backend + внешние сервисы
- **Инструменты:** Selenium, Cypress
- **Частота:** перед релизом

---

## 🛠️ Инструменты тестирования

### PHPUnit (для unit тестов)
```bash
# Установка
composer require --dev phpunit/phpunit

# Запуск тестов
./vendor/bin/phpunit tests/
```

### HTML/JS тесты (для integration тестов)
```html
<!-- Пример: backend/_tests/users_test.html -->
<!DOCTYPE html>
<html>
<head>
    <title>Тест API пользователей</title>
</head>
<body>
    <script>
        // Тестирование GET /api/users
        fetch('/api/users')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('✅ Тест пройден');
                } else {
                    console.log('❌ Тест не пройден');
                }
            });
    </script>
</body>
</html>
```

### Postman (для ручного тестирования)
- **Коллекции:** готовые наборы запросов
- **Переменные окружения:** для разных сред
- **Автоматизация:** запуск тестов через CLI

---

## 📁 Структура тестов

```
backend/
├── tests/                    # Unit тесты
│   ├── Unit/
│   │   ├── Models/
│   │   │   ├── UserTest.php
│   │   │   └── CarTest.php
│   │   ├── Utils/
│   │   │   ├── AuthHelperTest.php
│   │   │   └── ValidationHelperTest.php
│   │   └── Actions/
│   │       └── CreateUserActionTest.php
│   └── Integration/
│       └── ApiTest.php
├── _tests/                   # HTML/JS тесты
│   ├── index.html            # Оглавление тестов
│   ├── users_test.html       # Тесты пользователей
│   ├── cars_test.html        # Тесты автомобилей
│   └── auth_test.html        # Тесты авторизации
└── test_data/                # Тестовые данные
    ├── users.json
    ├── cars.json
    └── events.json
```

---

## 🧪 Unit тесты

### Пример теста модели
```php
<?php
// tests/Unit/Models/UserTest.php
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testFindByIdReturnsUser()
    {
        // Arrange
        $userId = 1;
        
        // Act
        $user = User::findById($userId);
        
        // Assert
        $this->assertNotNull($user);
        $this->assertEquals($userId, $user['id']);
        $this->assertArrayHasKey('first_name', $user);
    }
    
    public function testFindByIdReturnsNullForInvalidId()
    {
        // Arrange
        $invalidId = 99999;
        
        // Act
        $user = User::findById($invalidId);
        
        // Assert
        $this->assertNull($user);
    }
}
```

### Пример теста утилиты
```php
<?php
// tests/Unit/Utils/ValidationHelperTest.php
class ValidationHelperTest extends TestCase
{
    public function testValidateEmailWithValidEmail()
    {
        $this->assertTrue(ValidationHelper::validateEmail('test@example.com'));
    }
    
    public function testValidateEmailWithInvalidEmail()
    {
        $this->expectException(Exception::class);
        ValidationHelper::validateEmail('invalid-email');
    }
    
    public function testRequireFieldsWithValidData()
    {
        $data = ['email' => 'test@example.com', 'password' => '123456'];
        $fields = ['email', 'password'];
        
        // Не должно выбрасывать исключение
        ValidationHelper::requireFields($data, $fields);
        $this->assertTrue(true);
    }
}
```

---

## 🔗 Integration тесты

### Пример HTML/JS теста
```html
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Тест: Создание пользователя</title>
    <style>
        body { font-family: sans-serif; margin: 2em; }
        .test-result { margin: 1em 0; padding: 1em; border-radius: 4px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <h2>Тест: Создание пользователя (POST /api/users)</h2>
    <button onclick="runTest()">Запустить тест</button>
    <div id="results"></div>
    
    <script>
        async function runTest() {
            const results = document.getElementById('results');
            results.innerHTML = '<div class="test-result">🔄 Выполняется тест...</div>';
            
            try {
                // Тест 1: Создание пользователя
                const response = await fetch('/api/users', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + getTestToken()
                    },
                    body: JSON.stringify({
                        first_name: 'Тест',
                        last_name: 'Пользователь',
                        email: 'test@example.com'
                    })
                });
                
                const data = await response.json();
                
                if (data.success && data.data.id) {
                    results.innerHTML += '<div class="test-result success">✅ Пользователь создан успешно</div>';
                    
                    // Тест 2: Получение созданного пользователя
                    const getUserResponse = await fetch(`/api/users/${data.data.id}`, {
                        headers: {
                            'Authorization': 'Bearer ' + getTestToken()
                        }
                    });
                    
                    const userData = await getUserResponse.json();
                    
                    if (userData.success) {
                        results.innerHTML += '<div class="test-result success">✅ Пользователь получен успешно</div>';
                    } else {
                        results.innerHTML += '<div class="test-result error">❌ Ошибка получения пользователя</div>';
                    }
                } else {
                    results.innerHTML += '<div class="test-result error">❌ Ошибка создания пользователя</div>';
                }
            } catch (error) {
                results.innerHTML += '<div class="test-result error">❌ Ошибка теста: ' + error.message + '</div>';
            }
        }
        
        function getTestToken() {
            // Получаем тестовый токен из localStorage или генерируем
            return localStorage.getItem('test_token') || 'test_token_123';
        }
    </script>
</body>
</html>
```

---

## 📊 Покрытие тестами

### Целевые показатели
- **Unit тесты:** 80%+ покрытие кода
- **Integration тесты:** все API эндпоинты
- **E2E тесты:** основные пользовательские сценарии

### Метрики покрытия
```bash
# Генерация отчёта о покрытии
./vendor/bin/phpunit --coverage-html coverage/

# Просмотр отчёта
open coverage/index.html
```

---

## 🚀 Автоматизация тестов

### CI/CD Pipeline
```yaml
# .github/workflows/tests.yml
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
        extensions: pdo_mysql
        
    - name: Install dependencies
      run: composer install --no-dev
      
    - name: Run unit tests
      run: ./vendor/bin/phpunit tests/Unit/
      
    - name: Run integration tests
      run: |
        # Запуск HTML тестов через браузер
        npm install -g http-server
        http-server -p 8000 &
        sleep 5
        # Здесь можно добавить автоматизацию браузера
```

### Локальный запуск
```bash
#!/bin/bash
# run_tests.sh

echo "🧪 Запуск тестов backend..."

# Unit тесты
echo "📦 Unit тесты..."
./vendor/bin/phpunit tests/Unit/

# Integration тесты (через браузер)
echo "🔗 Integration тесты..."
echo "Откройте http://localhost:8000/backend/_tests/ в браузере"

# Проверка покрытия
echo "📊 Отчёт о покрытии..."
./vendor/bin/phpunit --coverage-text
```

---

## 🧩 Тестовые данные

### Фикстуры для тестов
```json
// test_data/users.json
{
  "valid_user": {
    "first_name": "Иван",
    "last_name": "Иванов",
    "email": "ivan@example.com",
    "telegram_id": 123456789
  },
  "invalid_user": {
    "first_name": "",
    "email": "invalid-email"
  }
}
```

### Фабрики для создания тестовых данных
```php
<?php
// tests/Factories/UserFactory.php
class UserFactory
{
    public static function create($overrides = [])
    {
        $defaults = [
            'first_name' => 'Тест',
            'last_name' => 'Пользователь',
            'email' => 'test' . rand(1000, 9999) . '@example.com',
            'telegram_id' => rand(100000000, 999999999)
        ];
        
        return array_merge($defaults, $overrides);
    }
}
```

---

## 🔍 Отладка тестов

### Логирование в тестах
```php
public function testComplexOperation()
{
    // Включаем логирование для теста
    Logger::info('Test: Starting complex operation');
    
    try {
        $result = $this->complexOperation();
        Logger::info('Test: Operation completed successfully');
        $this->assertNotNull($result);
    } catch (Exception $e) {
        Logger::error('Test: Operation failed: ' . $e->getMessage());
        throw $e;
    }
}
```

### Отладочная информация
```php
public function testWithDebugInfo()
{
    $user = User::findById(1);
    
    // Выводим отладочную информацию
    if (!$user) {
        $this->fail('User not found. Debug info: ' . json_encode([
            'available_users' => User::getAll(),
            'database_connection' => Database::getInstance() ? 'OK' : 'FAILED'
        ]));
    }
    
    $this->assertNotNull($user);
}
```

---

## 📋 Чек-лист тестирования

### Перед коммитом
- [ ] Все unit тесты проходят
- [ ] Все integration тесты проходят
- [ ] Покрытие кода не уменьшилось
- [ ] Новый код покрыт тестами

### Перед релизом
- [ ] Все E2E тесты проходят
- [ ] Тесты на продакшен данных
- [ ] Нагрузочное тестирование
- [ ] Тестирование безопасности

### Регулярно
- [ ] Обновление тестовых данных
- [ ] Рефакторинг тестов
- [ ] Оптимизация времени выполнения
- [ ] Обновление инструментов

---

## 🚨 Частые проблемы и решения

### Проблема: Тесты зависят от состояния БД
**Решение:** Использовать транзакции и откат изменений
```php
public function setUp(): void
{
    $this->pdo = Database::getInstance();
    $this->pdo->beginTransaction();
}

public function tearDown(): void
{
    $this->pdo->rollBack();
}
```

### Проблема: Медленные тесты
**Решение:** Мокирование внешних сервисов
```php
public function testWithMockedService()
{
    $mockService = $this->createMock(ExternalService::class);
    $mockService->method('call')->willReturn(['success' => true]);
    
    $result = $this->service->process($mockService);
    $this->assertTrue($result);
}
```

### Проблема: Тесты нестабильны
**Решение:** Изоляция тестов и очистка состояния
```php
public function testIsolated()
{
    // Очищаем состояние перед тестом
    $this->cleanupTestData();
    
    // Выполняем тест
    $result = $this->testOperation();
    
    // Проверяем результат
    $this->assertTrue($result);
    
    // Очищаем после теста
    $this->cleanupTestData();
}
```

---

> **Дата последнего обновления:** 2024-12-19  
> **Версия:** 1.0.0 
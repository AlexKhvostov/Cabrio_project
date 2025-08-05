# 🔧 DEV-режим для разработки

> **Назначение:** Руководство по использованию DEV-режима для разработки и тестирования  
> **Версия:** 1.0.0  
> **Последнее обновление:** 2024-01-01

---

## 🎯 **Обзор DEV-режима**

### **Назначение**
DEV-режим позволяет тестировать API без реальных Telegram данных, что значительно упрощает разработку и отладку.

### **Принципы работы**
- **Байпас авторизации** — пропуск Telegram авторизации
- **Подмена ролей** — установка любой роли для тестирования
- **Фиктивные пользователи** — использование тестовых ID
- **Безопасность** — работает только вне production

---

## ⚙️ **Активация DEV-режима**

### **Переменные окружения**
```bash
# В .env файле
DEV_AUTH=true                    # Активация DEV-режима
DEV_USER_ID=999                  # ID пользователя для тестирования
DEV_ROLE=admin                   # Роль для подмены
APP_ENV=development              # Окружение (не production)
```

### **Условия активации**
```php
// DEV-мод работает только вне production
if (getenv('APP_ENV') !== 'production') {
    // DEV-логика
}
```

---

## 🔄 **Логика работы DEV-режима**

### **Алгоритм активации**
1. **Проверка окружения** — только вне production
2. **Проверка DEV_AUTH** — должна быть true
3. **Проверка неуспешной авторизации** — если стандартная авторизация не прошла
4. **Активация байпаса** — создание фиктивного пользователя

### **Код активации**
```php
// DEV-МОД (работает только вне production)
if (getenv('APP_ENV') !== 'production') {
    // Полный байпас авторизации, если предыдущая попытка НЕ была успешной
    if (getenv('DEV_AUTH') && (!($result['success'] ?? false))) {
        Logger::info('DEV AUTH ACTIVE: bypass enabled');

        // Если указан DEV_USER_ID (число >0) — используем его; иначе 999
        $devIdRaw = getenv('DEV_USER_ID') ?: '';
        $devId = (ctype_digit($devIdRaw) && intval($devIdRaw) > 0) ? intval($devIdRaw) : 999;

        AppContext::setCurrentUser([
            'id'       => $devId,
            'role_id'  => Roles::ROLE_IDS['guest'],
            'role'     => 'guest',
            'username' => 'dev_tester'
        ]);
        AppContext::setSessionId('dev');
        $result = [
            'success'    => true,
            'user_id'    => $devId,
            'session_id' => 'dev',
            'message'    => 'DEV AUTH bypass'
        ];
    }

    // Подмена роли
    $override = getenv('DEV_ROLE');
    if ($override && isset(Roles::ROLE_IDS[$override])) {
        $user = AppContext::getCurrentUser() ?? [ 'id' => 999 ];
        $user['role_id'] = Roles::ROLE_IDS[$override];
        $user['role']    = $override;
        AppContext::setCurrentUser($user);
        Logger::info('DEV ROLE OVERRIDE', ['role' => $override]);
    }
}
```

---

## 🎭 **Функции DEV-режима**

### **1. Байпас авторизации**
- Пропуск Telegram авторизации
- Создание фиктивного пользователя
- Установка базовой роли `guest`

### **2. Подмена ролей**
- Установка любой роли для тестирования
- Динамическое изменение прав доступа
- Тестирование различных сценариев

### **3. Фиктивные пользователи**
- Использование тестового ID пользователя
- Настраиваемый ID через `DEV_USER_ID`
- По умолчанию используется ID `999`

---

## 📋 **Конфигурация DEV-режима**

### **Переменные окружения**

#### **DEV_AUTH**
- **Тип:** boolean
- **Значение:** `true` для активации
- **Описание:** Основной переключатель DEV-режима

#### **DEV_USER_ID**
- **Тип:** integer
- **Значение:** ID пользователя для тестирования
- **По умолчанию:** `999`
- **Ограничения:** Должно быть > 0

#### **DEV_ROLE**
- **Тип:** string
- **Значение:** Роль для подмены
- **Доступные роли:** `external`, `guest`, `new`, `registered`, `member`, `moderator`, `admin`
- **Описание:** Роль, которая будет установлена для тестового пользователя

#### **APP_ENV**
- **Тип:** string
- **Значение:** `development`, `staging`, `testing`
- **Описание:** Окружение (не `production`)

### **Примеры конфигурации**

#### **Базовый DEV-режим**
```bash
DEV_AUTH=true
DEV_USER_ID=999
DEV_ROLE=guest
APP_ENV=development
```

#### **Тестирование с ролью admin**
```bash
DEV_AUTH=true
DEV_USER_ID=1
DEV_ROLE=admin
APP_ENV=development
```

#### **Тестирование с ролью member**
```bash
DEV_AUTH=true
DEV_USER_ID=123
DEV_ROLE=member
APP_ENV=development
```

---

## 🧪 **Примеры использования**

### **Тестирование API без авторизации**
```bash
# Без DEV-режима (требует Telegram данные)
curl -X GET "https://api.cabrioride.com/api/users" \
  -H "Content-Type: application/json"
# Результат: 401 Unauthorized

# С DEV-режимом (работает без Telegram данных)
curl -X GET "https://api.cabrioride.com/api/users" \
  -H "Content-Type: application/json"
# Результат: 200 OK с данными
```

### **Тестирование различных ролей**
```bash
# Тест с ролью guest
DEV_ROLE=guest
curl -X GET "https://api.cabrioride.com/api/users"

# Тест с ролью member
DEV_ROLE=member
curl -X GET "https://api.cabrioride.com/api/users"

# Тест с ролью admin
DEV_ROLE=admin
curl -X GET "https://api.cabrioride.com/api/users"
```

### **Тестирование в коде**
```php
// Проверка DEV-режима в коде
if (getenv('DEV_AUTH') && getenv('APP_ENV') !== 'production') {
    echo "DEV-режим активен";
    echo "Пользователь ID: " . getenv('DEV_USER_ID');
    echo "Роль: " . getenv('DEV_ROLE');
}
```

---

## 🔍 **Логирование DEV-операций**

### **Логируемые события**
- Активация DEV-режима
- Подмена ролей
- Создание фиктивных пользователей
- Использование DEV-функций

### **Примеры логов**
```php
Logger::info('DEV AUTH ACTIVE: bypass enabled');
Logger::info('DEV ROLE OVERRIDE', ['role' => $override]);
Logger::info('DEV user created', [
    'user_id' => $devId,
    'role' => $role,
    'session_id' => 'dev'
]);
```

---

## 🛡️ **Безопасность DEV-режима**

### **Ограничения безопасности**
1. **Работает только вне production** — защита от активации в продакшене
2. **Подробное логирование** — отслеживание всех DEV-операций
3. **Изоляция данных** — использование фиктивных данных
4. **Контроль доступа** — ограничение по окружению

### **Рекомендации по безопасности**
- **Никогда не активируйте в production**
- **Используйте отдельные тестовые данные**
- **Мониторьте логи DEV-операций**
- **Ограничивайте доступ к DEV-переменным**

### **Проверки безопасности**
```php
// Проверка окружения
if (getenv('APP_ENV') === 'production') {
    Logger::error('DEV-режим попытался активироваться в production');
    return false;
}

// Проверка валидности DEV_USER_ID
$devId = getenv('DEV_USER_ID');
if (!ctype_digit($devId) || intval($devId) <= 0) {
    Logger::warning('Invalid DEV_USER_ID', ['dev_user_id' => $devId]);
    $devId = 999; // Используем безопасное значение по умолчанию
}
```

---

## 🚨 **Обработка ошибок**

### **Типичные ошибки DEV-режима**

#### **Некорректный DEV_USER_ID**
```php
// Ошибка
DEV_USER_ID=abc

// Решение
DEV_USER_ID=999
```

#### **Некорректная роль**
```php
// Ошибка
DEV_ROLE=invalid_role

// Решение
DEV_ROLE=admin
```

#### **Активация в production**
```php
// Ошибка
APP_ENV=production
DEV_AUTH=true

// Решение
APP_ENV=development
DEV_AUTH=true
```

---

## 📊 **Мониторинг DEV-режима**

### **Метрики для отслеживания**
- Количество активаций DEV-режима
- Используемые роли в DEV-режиме
- Попытки активации в production
- Время использования DEV-функций

### **Алерты**
- Попытки активации в production
- Длительное использование DEV-режима
- Подозрительная активность

---

## 🧪 **Тестирование DEV-режима**

### **Unit тесты**
```php
public function testDevModeActivation()
{
    // Устанавливаем DEV-переменные
    putenv('DEV_AUTH=true');
    putenv('DEV_USER_ID=999');
    putenv('DEV_ROLE=admin');
    putenv('APP_ENV=development');
    
    // Тестируем активацию
    $result = AuthMiddleware::authenticate('/api/test', 'GET');
    
    assert($result['success'] === true);
    assert($result['user_id'] === 999);
    assert($result['session_id'] === 'dev');
}
```

### **Интеграционные тесты**
```php
public function testDevModeWithAPI()
{
    // Тест API с DEV-режимом
    $response = $this->get('/api/users');
    
    $this->assertEquals(200, $response->getStatusCode());
    $this->assertJson($response->getContent());
}
```

---

## 📚 **Связанная документация**

- [Обзор авторизации](OVERVIEW.md) — общая система авторизации
- [Telegram авторизация](TELEGRAM_AUTH.md) — работа с Telegram
- [Сессии](SESSIONS.md) — управление сессиями
- [Безопасность](SECURITY.md) — принципы безопасности

---

## 💡 **Советы по использованию**

### **Для разработки**
1. **Используйте DEV-режим для быстрого тестирования**
2. **Тестируйте различные роли** для проверки прав доступа
3. **Логируйте все операции** для отладки
4. **Не забывайте отключать** перед коммитом

### **Для тестирования**
1. **Создавайте отдельные конфигурации** для разных тестов
2. **Используйте фиксированные ID** для воспроизводимых тестов
3. **Проверяйте все роли** для полного покрытия
4. **Документируйте тестовые сценарии**

---

> **💡 Совет:** DEV-режим — отличный инструмент для разработки, но всегда помните о безопасности и не используйте его в production! 
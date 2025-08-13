# System API - Системные операции ⚙️

> **Назначение:** API для системных операций и административных функций

---

## 🎯 Назначение

API System предоставляет функционал для системных операций:
- Синхронизация данных пользователей
- Управление ролями пользователей
- Управление статусами сущностей
- Системная аналитика и мониторинг

---

## 🏗️ Архитектура

### **Базовые URL:**
- **Короткий:** `http://localhost/app/api/system`
- **Длинный:** `http://localhost/app/backend/routes/api.php`

### **JavaScript примеры:**
```javascript
// Синхронизация пользователя
const response = await fetch('http://localhost/app/api/system/user-sync', {
  method: 'POST',
  body: JSON.stringify(userData)
});

// Изменение роли пользователя
const roleChange = await fetch('http://localhost/app/api/system/user-role', {
  method: 'POST',
  body: JSON.stringify(roleData)
});
```

---

## 🔐 Авторизация

### **Требуемые роли:**
- **Просмотр:** `moderator`, `admin`
- **Операции:** `admin` (большинство операций)
- **Синхронизация:** `SYSTEM_TOKEN` или `admin`

### **Проверка доступа:**
```php
// В контроллере
$this->requireAccess('system_user_sync'); // Синхронизация пользователей
$this->requireAccess('system_user_role'); // Управление ролями
```

---

## 📋 Эндпоинты

### **Основные операции:**
- `POST http://localhost/app/api/system/user-sync` — синхронизация пользователя
- `POST http://localhost/app/api/system/user-role` — изменение роли пользователя
- `POST http://localhost/app/api/system/entity-status` — изменение статуса сущности

### **Специальные операции:**
- `GET http://localhost/app/api/system/stats` — системная статистика
- `GET http://localhost/app/api/system/logs` — системные логи
- `POST http://localhost/app/api/system/backup` — создание резервной копии

---

## 📝 Примеры запросов

### **1. Синхронизация пользователя**

#### **Запрос:**
```http
POST http://localhost/app/api/system/user-sync
Content-Type: application/json
Authorization: Bearer {SYSTEM_TOKEN}

{
  "telegram_id": 123456789,
  "username": "ivan_user",
  "first_name": "Иван",
  "last_name": "Иванов",
  "photo_url": "https://t.me/i/userpic/320/ivan_user.jpg"
}
```

#### **Ответ:**
```json
{
  "success": true,
  "data": {
    "user_id": 123,
    "action": "updated",
    "changes": {
      "username": "ivan_user",
      "first_name": "Иван",
      "last_name": "Иванов",
      "photo_url": "https://t.me/i/userpic/320/ivan_user.jpg"
    },
    "user": {
      "id": 123,
      "telegram_id": 123456789,
      "username": "ivan_user",
      "first_name": "Иван",
      "last_name": "Иванов",
      "role": {
        "id": 4,
        "code": "member",
        "name": "Участник"
      },
      "photo": {
        "id": 10,
        "url": "http://localhost/app/uploads/user/user_123_avatar.jpg"
      },
      "updated_at": "2024-01-15T16:00:00Z"
    }
  }
}
```

### **2. Изменение роли пользователя**

#### **Запрос:**
```http
POST http://localhost/app/api/system/user-role
Content-Type: application/json
Authorization: Bearer {admin_token}

{
  "user_id": 123,
  "role_id": 5,
  "reason": "Назначен модератором за активность"
}
```

#### **Ответ:**
```json
{
  "success": true,
  "data": {
    "user_id": 123,
    "old_role": {
      "id": 4,
      "code": "member",
      "name": "Участник"
    },
    "new_role": {
      "id": 5,
      "code": "moderator",
      "name": "Модератор"
    },
    "changed_by": {
      "id": 1,
      "first_name": "Администратор",
      "last_name": "Системы"
    },
    "reason": "Назначен модератором за активность",
    "changed_at": "2024-01-15T16:30:00Z"
  }
}
```

### **3. Изменение статуса сущности**

#### **Запрос:**
```http
POST http://localhost/app/api/system/entity-status
Content-Type: application/json
Authorization: Bearer {admin_token}

{
  "entity_type": "car",
  "entity_id": 456,
  "status_id": 7,
  "reason": "Автомобиль подтверждён владельцем"
}
```

#### **Ответ:**
```json
{
  "success": true,
  "data": {
    "entity_type": "car",
    "entity_id": 456,
    "old_status": {
      "id": 2,
      "code": "business_card",
      "name": "Визитка"
    },
    "new_status": {
      "id": 7,
      "code": "active",
      "name": "Активен"
    },
    "changed_by": {
      "id": 1,
      "first_name": "Администратор",
      "last_name": "Системы"
    },
    "reason": "Автомобиль подтверждён владельцем",
    "changed_at": "2024-01-15T17:00:00Z"
  }
}
```

### **4. Получение системной статистики**

#### **Запрос:**
```http
GET http://localhost/app/api/system/stats
Authorization: Bearer {admin_token}
```

#### **Ответ:**
```json
{
  "success": true,
  "data": {
    "users": {
      "total": 1250,
      "active": 890,
      "new_today": 15,
      "by_role": {
        "external": 50,
        "guest": 100,
        "user": 200,
        "member": 800,
        "moderator": 80,
        "admin": 20
      }
    },
    "cars": {
      "total": 2340,
      "active": 1890,
      "new_today": 25,
      "by_status": {
        "noticed": 150,
        "business_card": 200,
        "active": 1890,
        "archived": 100
      }
    },
    "events": {
      "total": 156,
      "upcoming": 23,
      "completed": 133
    },
    "guide_objects": {
      "total": 890,
      "approved": 750,
      "pending": 140
    },
    "reviews": {
      "total": 2340,
      "approved": 2100,
      "pending": 240
    },
    "system": {
      "storage_used": "2.5 GB",
      "database_size": "1.2 GB",
      "last_backup": "2024-01-15T00:00:00Z",
      "uptime": "15 days"
    }
  }
}
```

---

## 🚨 Обработка ошибок

### **Ошибка доступа:**
```json
{
  "success": false,
  "error": "access_denied",
  "message": "Недостаточно прав для выполнения системной операции"
}
```

### **Пользователь не найден:**
```json
{
  "success": false,
  "error": "user_not_found",
  "message": "Пользователь с ID 999 не найден"
}
```

### **Некорректная роль:**
```json
{
  "success": false,
  "error": "invalid_role",
  "message": "Роль с ID 999 не существует"
}
```

### **Некорректный статус:**
```json
{
  "success": false,
  "error": "invalid_status",
  "message": "Статус с ID 999 не существует для данного типа сущности"
}
```

### **Ошибка синхронизации:**
```json
{
  "success": false,
  "error": "sync_error",
  "message": "Ошибка при синхронизации данных пользователя",
  "details": {
    "telegram_id": "Telegram ID обязателен"
  }
}
```

---

## 🔐 Права доступа

### **Синхронизация пользователей:**
- **SYSTEM_TOKEN** — полный доступ к синхронизации
- **admin** — полный доступ к синхронизации

### **Управление ролями:**
- **admin** — изменение ролей пользователей
- **moderator** — просмотр ролей (только чтение)

### **Управление статусами:**
- **admin** — изменение статусов любых сущностей
- **moderator** — изменение статусов в рамках своих полномочий

### **Системная статистика:**
- **admin** — полная статистика
- **moderator** — базовая статистика

---

## 📊 Структура данных

### **UserSync (Синхронизация пользователя):**
```typescript
interface UserSync {
  telegram_id: number;
  username?: string;
  first_name?: string;
  last_name?: string;
  photo_url?: string;
}
```

### **RoleChange (Изменение роли):**
```typescript
interface RoleChange {
  user_id: number;
  role_id: number;
  reason?: string;
}
```

### **StatusChange (Изменение статуса):**
```typescript
interface StatusChange {
  entity_type: string;       // 'user', 'car', 'event', 'guide_object', 'review'
  entity_id: number;
  status_id: number;
  reason?: string;
}
```

### **SystemStats (Системная статистика):**
```typescript
interface SystemStats {
  users: UserStats;
  cars: CarStats;
  events: EventStats;
  guide_objects: GuideObjectStats;
  reviews: ReviewStats;
  system: SystemInfo;
}
```

---

## 🔗 Интеграция

### **С Telegram Bot:**
```javascript
// Синхронизация пользователя через бота
const userData = {
  telegram_id: 123456789,
  username: "ivan_user",
  first_name: "Иван",
  last_name: "Иванов",
  photo_url: "https://t.me/i/userpic/320/ivan_user.jpg"
};

const response = await fetch('http://localhost/app/api/system/user-sync', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${SYSTEM_TOKEN}`
  },
  body: JSON.stringify(userData)
});
```

### **С Admin Panel:**
```javascript
// Изменение роли пользователя
const roleData = {
  user_id: 123,
  role_id: 5,
  reason: "Назначен модератором"
};

const response = await fetch('http://localhost/app/api/system/user-role', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${adminToken}`
  },
  body: JSON.stringify(roleData)
});

// Получение системной статистики
const stats = await fetch('http://localhost/app/api/system/stats', {
  headers: {
    'Authorization': `Bearer ${adminToken}`
  }
});
```

### **С Monitoring System:**
```javascript
// Мониторинг системы
const logs = await fetch('http://localhost/app/api/system/logs?level=error&limit=100', {
  headers: {
    'Authorization': `Bearer ${adminToken}`
  }
});

// Создание резервной копии
const backup = await fetch('http://localhost/app/api/system/backup', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${adminToken}`
  }
});
```

---

## 📈 Мониторинг

### **Метрики для отслеживания:**
- Количество синхронизаций пользователей
- Изменения ролей и статусов
- Системная производительность
- Использование ресурсов
- Ошибки синхронизации

### **Логирование:**
```php
// В контроллере
Logger::info('User role changed', [
    'user_id' => $userId,
    'old_role' => $oldRole,
    'new_role' => $newRole,
    'changed_by' => $changedBy
]);

Logger::info('Entity status changed', [
    'entity_type' => $entityType,
    'entity_id' => $entityId,
    'old_status' => $oldStatus,
    'new_status' => $newStatus,
    'changed_by' => $changedBy
]);
```

---

## 🔧 Конфигурация

### **SYSTEM_TOKEN:**
- **Назначение:** Для системных операций без пользовательского контекста
- **Использование:** Синхронизация данных, автоматические операции
- **Безопасность:** Токен хранится в защищённом месте

### **Автоматические операции:**
- **Синхронизация:** Автоматическая синхронизация данных Telegram
- **Очистка:** Автоматическая очистка устаревших данных
- **Резервное копирование:** Автоматическое создание резервных копий

---

## 🔗 Связанные документы

- [API Overview](../OVERVIEW.md) — общие принципы API
- [Authentication](../../AUTHENTICATION/OVERVIEW.md) — авторизация
- [DEV Mode](../../AUTHENTICATION/DEV_MODE.md) — режим разработки
- [Database Schema](../../DATABASE/SCHEMA.md) — структура БД

---

> **Примечание:** Системные операции требуют повышенных прав доступа и должны выполняться только авторизованными администраторами или системными токенами. 
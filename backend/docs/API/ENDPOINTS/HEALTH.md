# Health API - Проверка состояния 🏥

> **Назначение:** API для мониторинга состояния системы и проверки работоспособности

---

## 🎯 Назначение

API Health предоставляет функционал для мониторинга системы:
- Проверка работоспособности API
- Мониторинг состояния базы данных
- Проверка доступности сервисов
- Системная диагностика

---

## 🏗️ Архитектура

### **Базовые URL:**
- **Короткий:** `http://localhost/app/api/health`
- **Длинный:** `http://localhost/app/backend/routes/api.php`

### **JavaScript примеры:**
```javascript
// Проверка состояния API
const response = await fetch('http://localhost/app/api/health');

// Проверка состояния с деталями
const detailedHealth = await fetch('http://localhost/app/api/health?detailed=true');
```

---

## 🔐 Авторизация

### **Требуемые роли:**
- **Просмотр:** Публичный доступ (без авторизации)
- **Детальная информация:** `moderator`, `admin`

### **Проверка доступа:**
```php
// В контроллере
// Health endpoint не требует авторизации
// Детальная информация требует прав администратора
```

---

## 📋 Эндпоинты

### **Основные операции:**
- `GET http://localhost/app/api/health` — проверка состояния API
- `GET http://localhost/app/api/status` — альтернативная проверка состояния

### **Специальные операции:**
- `GET http://localhost/app/api/health?detailed=true` — детальная информация
- `GET http://localhost/app/api/health?check=db` — проверка только БД
- `GET http://localhost/app/api/health?check=services` — проверка сервисов

---

## 📝 Примеры запросов

### **1. Базовая проверка состояния**

#### **Запрос:**
```http
GET http://localhost/app/api/health
```

#### **Ответ:**
```json
{
  "success": true,
  "data": {
    "status": "healthy",
    "timestamp": "2024-01-15T18:00:00Z",
    "uptime": "15 days, 6 hours, 30 minutes",
    "version": "1.0.0",
    "environment": "production"
  }
}
```

### **2. Детальная проверка состояния**

#### **Запрос:**
```http
GET http://localhost/app/api/health?detailed=true
Authorization: Bearer {admin_token}
```

#### **Ответ:**
```json
{
  "success": true,
  "data": {
    "status": "healthy",
    "timestamp": "2024-01-15T18:00:00Z",
    "uptime": "15 days, 6 hours, 30 minutes",
    "version": "1.0.0",
    "environment": "production",
    "services": {
      "database": {
        "status": "healthy",
        "response_time": "2.5ms",
        "connections": 5,
        "max_connections": 100
      },
      "storage": {
        "status": "healthy",
        "used_space": "2.5 GB",
        "total_space": "50 GB",
        "free_space": "47.5 GB"
      },
      "memory": {
        "status": "healthy",
        "used_memory": "128 MB",
        "peak_memory": "256 MB",
        "memory_limit": "512 MB"
      },
      "disk": {
        "status": "healthy",
        "read_speed": "50 MB/s",
        "write_speed": "30 MB/s"
      }
    },
    "performance": {
      "average_response_time": "45ms",
      "requests_per_minute": 120,
      "error_rate": "0.1%"
    },
    "last_errors": [
      {
        "timestamp": "2024-01-15T17:45:00Z",
        "error": "Database connection timeout",
        "severity": "warning"
      }
    ]
  }
}
```

### **3. Проверка только базы данных**

#### **Запрос:**
```http
GET http://localhost/app/api/health?check=db
```

#### **Ответ:**
```json
{
  "success": true,
  "data": {
    "database": {
      "status": "healthy",
      "response_time": "2.5ms",
      "connections": 5,
      "max_connections": 100,
      "version": "MySQL 8.0.33",
      "tables": {
        "total": 19,
        "size": "1.2 GB"
      }
    }
  }
}
```

### **4. Проверка сервисов**

#### **Запрос:**
```http
GET http://localhost/app/api/health?check=services
Authorization: Bearer {admin_token}
```

#### **Ответ:**
```json
{
  "success": true,
  "data": {
    "services": {
      "database": {
        "status": "healthy",
        "response_time": "2.5ms"
      },
      "storage": {
        "status": "healthy",
        "used_space": "2.5 GB"
      },
      "memory": {
        "status": "healthy",
        "used_memory": "128 MB"
      },
      "disk": {
        "status": "healthy",
        "read_speed": "50 MB/s"
      },
      "external_apis": {
        "telegram_api": {
          "status": "healthy",
          "response_time": "150ms"
        },
        "ocr_service": {
          "status": "healthy",
          "response_time": "500ms"
        }
      }
    }
  }
}
```

### **5. Альтернативная проверка состояния**

#### **Запрос:**
```http
GET http://localhost/app/api/status
```

#### **Ответ:**
```json
{
  "success": true,
  "data": {
    "status": "ok",
    "message": "API is running",
    "timestamp": "2024-01-15T18:00:00Z"
  }
}
```

---

## 🚨 Обработка ошибок

### **Система недоступна:**
```json
{
  "success": false,
  "error": "service_unavailable",
  "message": "Система временно недоступна",
  "data": {
    "status": "unhealthy",
    "timestamp": "2024-01-15T18:00:00Z",
    "error": "Database connection failed"
  }
}
```

### **База данных недоступна:**
```json
{
  "success": false,
  "error": "database_error",
  "message": "Ошибка подключения к базе данных",
  "data": {
    "database": {
      "status": "unhealthy",
      "error": "Connection timeout"
    }
  }
}
```

### **Недостаточно прав:**
```json
{
  "success": false,
  "error": "access_denied",
  "message": "Недостаточно прав для получения детальной информации"
}
```

### **Сервис перегружен:**
```json
{
  "success": false,
  "error": "service_overloaded",
  "message": "Сервис перегружен",
  "data": {
    "status": "degraded",
    "performance": {
      "average_response_time": "2000ms",
      "requests_per_minute": 500
    }
  }
}
```

---

## 🔐 Права доступа

### **Базовая проверка:**
- **Все пользователи** — доступ к базовой проверке состояния
- **Без авторизации** — публичный доступ

### **Детальная информация:**
- **admin** — полная детальная информация
- **moderator** — базовая детальная информация
- **Остальные роли** — только базовая информация

---

## 📊 Структура данных

### **HealthStatus (Статус здоровья):**
```typescript
interface HealthStatus {
  status: 'healthy' | 'degraded' | 'unhealthy';
  timestamp: string;
  uptime: string;
  version: string;
  environment: string;
}
```

### **ServiceStatus (Статус сервиса):**
```typescript
interface ServiceStatus {
  status: 'healthy' | 'degraded' | 'unhealthy';
  response_time?: string;
  error?: string;
  details?: any;
}
```

### **PerformanceMetrics (Метрики производительности):**
```typescript
interface PerformanceMetrics {
  average_response_time: string;
  requests_per_minute: number;
  error_rate: string;
  memory_usage: string;
  cpu_usage: string;
}
```

### **SystemError (Системная ошибка):**
```typescript
interface SystemError {
  timestamp: string;
  error: string;
  severity: 'info' | 'warning' | 'error' | 'critical';
  details?: any;
}
```

---

## 🔗 Интеграция

### **С Monitoring System:**
```javascript
// Мониторинг состояния системы
const health = await fetch('http://localhost/app/api/health');

const result = await health.json();
if (result.data.status === 'healthy') {
  console.log('System is healthy');
} else {
  console.error('System is unhealthy:', result.data);
}
```

### **С Load Balancer:**
```javascript
// Проверка для балансировщика нагрузки
const health = await fetch('http://localhost/app/api/health');
const status = await health.json();

if (status.data.status === 'healthy') {
  // Сервер готов принимать трафик
  return 200;
} else {
  // Сервер не готов
  return 503;
}
```

### **С Admin Dashboard:**
```javascript
// Детальная информация для админ-панели
const detailedHealth = await fetch('http://localhost/app/api/health?detailed=true', {
  headers: {
    'Authorization': `Bearer ${adminToken}`
  }
});

const result = await detailedHealth.json();
console.log('Database status:', result.data.services.database.status);
console.log('Memory usage:', result.data.services.memory.used_memory);
```

### **С Telegram Bot:**
```javascript
// Проверка состояния через бота
const health = await fetch('http://localhost/app/api/health');
const result = await health.json();

if (result.data.status === 'healthy') {
  await bot.sendMessage(chatId, '✅ Система работает нормально');
} else {
  await bot.sendMessage(chatId, '⚠️ Проблемы с системой');
}
```

---

## 📈 Мониторинг

### **Метрики для отслеживания:**
- Время отклика API
- Состояние базы данных
- Использование памяти и диска
- Количество ошибок
- Время работы системы

### **Логирование:**
```php
// В контроллере
Logger::info('Health check performed', [
    'status' => $status,
    'response_time' => $responseTime,
    'memory_usage' => $memoryUsage
]);

Logger::error('Health check failed', [
    'error' => $error,
    'service' => $service
]);
```

---

## 🔧 Конфигурация

### **Проверки по умолчанию:**
- **База данных:** Подключение и простой запрос
- **Память:** Использование памяти PHP
- **Диск:** Доступность директории uploads
- **Внешние API:** Telegram API, OCR сервис

### **Пороги предупреждений:**
- **Время отклика БД:** > 100ms
- **Использование памяти:** > 80%
- **Использование диска:** > 90%
- **Ошибки:** > 5% от общего количества запросов

---

## 🔗 Связанные документы

- [API Overview](../OVERVIEW.md) — общие принципы API
- [System API](SYSTEM.md) — системные операции
- [Authentication](../../AUTHENTICATION/OVERVIEW.md) — авторизация
- [Database Schema](../../DATABASE/SCHEMA.md) — структура БД

---

> **Примечание:** Health API предназначен для мониторинга и не должен использоваться для критических операций. Для проверки работоспособности в продакшене рекомендуется использовать специализированные инструменты мониторинга. 
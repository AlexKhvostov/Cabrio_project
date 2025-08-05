# 📚 Оглавление документации Backend

> **Навигация по документации backend системы CabrioRide**

---

## 🚀 Быстрая навигация

### **Основные разделы:**
- [🏗️ Архитектура](ARCHITECTURE/OVERVIEW.md) — общая архитектура системы
- [🔐 Авторизация](AUTHENTICATION/OVERVIEW.md) — система авторизации
- [🌐 API](API/OVERVIEW.md) — REST API документация
- [📊 Модели](MODELS/OVERVIEW.md) — модели данных
- [🔧 Утилиты](UTILS/OVERVIEW.md) — вспомогательные классы
- [🗄️ База данных](DATABASE/OVERVIEW.md) — структура БД
- [🧪 Тестирование](TESTING/OVERVIEW.md) — тестирование системы

### **Популярные документы:**
- [API Endpoints](API/ENDPOINTS/) — все эндпоинты API
- [Database Schema](DATABASE/SCHEMA.md) — схема базы данных
- [Authentication](AUTHENTICATION/TELEGRAM_AUTH.md) — Telegram авторизация
- [Models](MODELS/) — все модели данных

---

## 📋 Полное оглавление

### **🏗️ АРХИТЕКТУРА**
- [Overview](ARCHITECTURE/OVERVIEW.md) — общая архитектура системы
- [L0-L4 Actions](ACTIONS/ACTIONS_INTERACTION.MD) — архитектура действий
- [Conventions](CONVENTIONS.md) — соглашения и стандарты

### **🔐 АВТОРИЗАЦИЯ**
- [Overview](AUTHENTICATION/OVERVIEW.md) — обзор системы авторизации
- [Telegram Auth](AUTHENTICATION/TELEGRAM_AUTH.md) — Telegram авторизация
- [Dev Mode](AUTHENTICATION/DEV_MODE.md) — режим разработки

### **🌐 API**
- [Overview](API/OVERVIEW.md) — общие принципы API
- **ENDPOINTS:**
  - [Users](API/ENDPOINTS/USERS.md) — управление пользователями
  - [Cars](API/ENDPOINTS/CARS.md) — управление автомобилями
  - [Events](API/ENDPOINTS/EVENTS.md) — управление событиями
  - [Guide Objects](API/ENDPOINTS/GUIDE_OBJECTS.md) — управление гид-объектами
  - [Business Cards](API/ENDPOINTS/BUSINESS_CARDS.md) — управление визитками
  - [Photos](API/ENDPOINTS/PHOTOS.md) — управление фотографиями
  - [Reviews](API/ENDPOINTS/REVIEWS.md) — управление отзывами
  - [System](API/ENDPOINTS/SYSTEM.md) — системные операции
  - [Health](API/ENDPOINTS/HEALTH.md) — проверка состояния
  - [Actions](API/ENDPOINTS/ACTIONS.md) — специальные действия

### **📊 МОДЕЛИ**
- [Overview](MODELS/OVERVIEW.md) — обзор моделей данных
- [User](MODELS/USER.md) — модель пользователей
- [Car](MODELS/CAR.md) — модель автомобилей
- [Event](MODELS/EVENT.md) — модель событий
- [GuideObject](MODELS/GUIDE_OBJECT.md) — модель гид-объектов

### **🔧 УТИЛИТЫ**
- [Overview](UTILS/OVERVIEW.md) — обзор всех утилит
- [AuthHelper](UTILS/AUTH_HELPER.md) — авторизация и аутентификация
- [SessionHelper](UTILS/SESSION_HELPER.md) — управление сессиями
- [ExpandHelper](UTILS/EXPAND_HELPER.md) — расширение данных
- [ReferenceData](UTILS/REFERENCE_DATA.md) — справочные данные
- [ResponseHelper](UTILS/RESPONSE_HELPER.md) — форматирование ответов
- [Database](UTILS/DATABASE.md) — работа с базой данных
- [Logger](UTILS/LOGGER.md) — логирование
- [ValidationHelper](UTILS/VALIDATION_HELPER.md) — валидация данных
- [AppContext](UTILS/APP_CONTEXT.md) — глобальный контекст

### **🗄️ БАЗА ДАННЫХ**
- [Overview](DATABASE/OVERVIEW.md) — обзор базы данных
- [Schema](DATABASE/SCHEMA.md) — схема базы данных
- [Relations](DATABASE/RELATIONS.md) — связи между таблицами
- [Statuses and Roles](DATABASE/STATUSES_AND_ROLES.md) — статусы и роли

### **🧪 ТЕСТИРОВАНИЕ**
- [Overview](TESTING/OVERVIEW.md) — обзор тестирования

### **📚 СПРАВОЧНИКИ**
- [Error Codes](REFERENCES/ERROR_CODES.md) — коды ошибок

---

## 🔗 Внешние ссылки

### **Проект:**
- **Frontend:** `http://localhost/app/` — веб-интерфейс
- **Backend API:** `http://localhost/app/api/` — REST API
- **Bot:** `http://localhost/app/bot/` — Telegram бот
- **Uploads:** `http://localhost/app/uploads/` — загруженные файлы

### **Документация:**
- **Frontend Docs:** `../frontend/docs/` — документация фронтенда
- **Bot Docs:** `../bot/docs/` — документация бота
- **Project Docs:** `../docs/` — общая документация проекта

---

## 📝 Журнал изменений

### **Версия 1.0.0** (2024-01-15)
- ✅ Создана полная структура документации
- ✅ Документированы все API эндпоинты
- ✅ Документированы все утилиты
- ✅ Документированы основные модели
- ✅ Документирована база данных
- ✅ Добавлено тестирование
- ✅ Обновлены URL адреса для локальной разработки

---

## 🎯 Следующие шаги

### **Планируемые разделы:**
1. **📊 Models** — остальные модели (Review, BusinessCard, Photo, etc.)
2. **🚀 Deployment** — развертывание и деплой
3. **📈 Performance** — производительность и оптимизация
4. **🔒 Security** — безопасность системы
5. **📋 Actions** — детальная документация L0-L4 действий

### **Улучшения:**
- Добавить OpenAPI спецификацию
- Создать интерактивную документацию
- Добавить примеры кода для всех эндпоинтов
- Расширить раздел тестирования

---

> **Примечание:** Документация постоянно обновляется. При внесении изменений в код обязательно обновляйте соответствующую документацию. 
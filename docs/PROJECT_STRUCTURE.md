# Структура проекта CabrioRide

> **Актуальная структура проекта с новой архитектурой backend (уровни абстракции L0-L4)**

---

## 📁 Общая структура проекта

```
app/
├── backend/                    # Backend API (PHP 8.1)
│   ├── controllers/           # HTTP-слой (точка входа)
│   ├── actions/              # Бизнес-логика (уровни 1-4)
│   │   ├── level1/           # Базовые операции
│   │   ├── level2/           # Бизнес-операции
│   │   ├── level3/           # Сложные сценарии
│   │   └── level4/           # Комплексные сценарии
│   ├── models/               # Уровень 0 - работа с БД
│   ├── utils/                # Общие утилиты
│   ├── routes/               # Маршрутизация
│   ├── docs/                 # Документация backend
│   └── _tests/               # Интеграционные тесты
├── frontend/                  # Frontend (Vue.js 3 + TypeScript)
│   ├── src/
│   │   ├── components/       # Vue компоненты
│   │   ├── views/            # Страницы приложения
│   │   ├── stores/           # Pinia stores
│   │   └── router/           # Vue Router
│   ├── docs/                 # Документация frontend
│   └── dist/                 # Собранные файлы
├── database/                  # Скрипты и данные БД
│   └── scripts/              # SQL скрипты
├── docs/                      # Общая документация проекта
├── uploads/                   # Загружаемые файлы
├── config/                    # Конфигурация
├── .env                       # Переменные окружения
└── README.md                  # Главный README
```

---

## 🎯 Уровни абстракции backend

### **L0 (Модели)** — работа с БД
- Только прямые CRUD операции
- Маппинг данных из БД в объекты
- Базовая валидация данных
- **НЕ содержит бизнес-логику**

### **L1 (Базовые операции)** — простые комбинации
- Комбинация 2-3 операций L0
- Простая бизнес-логика
- Базовая валидация данных
- Обработка файлов (фото, документы)
- **Префикс: 1 подчёркивание (_)**

### **L2 (Бизнес-операции)** — проверки и правила
- Используют Actions L1
- Проверки прав доступа
- Бизнес-правила
- Валидация бизнес-логики
- **Префикс: 2 подчёркивания (__)**

### **L3 (Сложные сценарии)** — транзакции и интеграции
- Используют Actions L2
- Транзакции БД
- Интеграции с внешними API
- Множественные операции
- **Префикс: 3 подчёркивания (___)**

### **L4 (Комплексные сценарии)** — полные пользовательские сценарии
- Используют Actions L3
- Полные пользовательские сценарии
- Оркестрация множественных операций
- **Префикс: 4 подчёркивания (____)**

---

## 📋 Детальная структура

### **backend/** — Серверная часть
```
backend/
├── controllers/              # HTTP-слой (точка входа)
│   ├── BaseController.php    # Базовый класс контроллеров
│   ├── UserController.php    # Контроллер пользователей
│   ├── CarController.php     # Контроллер автомобилей
│   ├── EventController.php   # Контроллер событий
│   ├── GuideObjectController.php # Контроллер гид-объектов
│   ├── ReviewController.php  # Контроллер отзывов
│   ├── MapHintController.php # Контроллер подсказок на карте
│   ├── BusinessCardController.php # Контроллер визиток
│   ├── PhotoController.php   # Контроллер фото
│   ├── ReferenceController.php # Контроллер справочников
│   ├── AuthController.php    # Контроллер авторизации
│   ├── ActivityController.php # Контроллер активности
│   └── MeController.php      # Контроллер текущего пользователя
├── actions/                  # Бизнес-логика (уровни 1-4)
│   ├── level1/              # Базовые операции (префикс: _)
│   │   ├── _CreateUserWithPhotoAction.php
│   │   ├── _CreateCarWithPhotoAction.php
│   │   ├── _CheckUserExistsAction.php
│   │   └── ...
│   ├── level2/              # Бизнес-операции (префикс: __)
│   │   ├── __AddCarForUserAction.php
│   │   ├── __AddUserToEventAction.php
│   │   ├── __EditUserProfileAction.php
│   │   └── ...
│   ├── level3/              # Сложные сценарии (префикс: ___)
│   │   ├── ___ProcessCarRegistrationAction.php
│   │   ├── ___TransferCarOwnershipAction.php
│   │   ├── ___SyncUserFromTelegramAction.php
│   │   └── ...
│   └── level4/              # Комплексные сценарии (префикс: ____)
│       ├── ____ExecuteUserOnboardingAction.php
│       ├── ____HandleEventCreationAction.php
│       └── ...
├── models/                   # Уровень 0 - работа с БД
│   ├── User.php             # Модель пользователя
│   ├── Car.php              # Модель автомобиля
│   ├── Event.php            # Модель события
│   ├── GuideObject.php      # Модель гид-объекта
│   ├── Review.php           # Модель отзыва
│   ├── MapHint.php          # Модель подсказки на карте
│   ├── BusinessCard.php     # Модель визитки
│   ├── Photo.php            # Модель фото
│   ├── Role.php             # Модель роли (справочник)
│   ├── Status.php           # Модель статуса (справочник)
│   ├── EventType.php        # Модель типа события
│   ├── GuideObjectType.php  # Модель типа гид-объекта
│   ├── GuideObjectKind.php  # Модель вида гид-объекта
│   ├── CarBrand.php         # Модель марки автомобиля
│   ├── LinkUserCar.php      # Модель связи пользователь-авто
│   ├── LinkEventParticipant.php # Модель связи событие-участник
│   ├── ModerationLog.php    # Модель лога модерации
│   ├── ActivityLog.php      # Модель лога активности
│   └── Session.php          # Модель сессии
├── utils/                    # Общие утилиты
│   ├── Database.php         # Подключение к БД (Singleton)
│   ├── ResponseHelper.php   # Формирование ответов API
│   ├── AuthHelper.php       # Авторизация и проверка токенов
│   ├── ValidationHelper.php # Валидация входных данных
│   ├── Logger.php           # Логирование событий
│   ├── load_env.php         # Загрузка переменных окружения
│   └── README.md            # Документация утилит
├── routes/                   # Маршрутизация
│   └── api.php              # Основной файл маршрутов
├── docs/                     # Документация backend
│   ├── ARCHITECTURE_GUIDE.md # Руководство по архитектуре
│   ├── CONVENTIONS.md        # Стандарты и соглашения
│   ├── ENDPOINTS.md          # Полный перечень эндпоинтов
│   ├── ENVIRONMENT.md        # Переменные окружения
│   ├── ERROR_CODES.md        # Коды ошибок API
│   ├── DEPLOYMENT.md         # Развёртывание
│   ├── SECURITY.md           # Безопасность
│   ├── TESTING.md            # Тестирование
│   ├── PERFORMANCE.md        # Производительность
│   ├── MODELS.md             # Модели
│   ├── DEPENDENCIES.md       # Зависимости
│   ├── DEPENDENCIES_DIAGRAM.md # Диаграмма зависимостей
│   └── openapi.yaml          # OpenAPI спецификация
├── _tests/                   # Интеграционные тесты
│   ├── README.md             # Оглавление и инструкция
│   ├── users_test.html       # Тесты пользователей
│   ├── cars_test.html        # Тесты автомобилей
│   ├── events_test.html      # Тесты событий
│   ├── guide_objects_test.html # Тесты гид-объектов
│   ├── auth_test.html        # Тесты авторизации
│   └── ...
├── logs/                     # Логи
│   ├── app.log              # Логи приложения
│   ├── error.log            # Логи ошибок
│   ├── api.log              # Логи API
│   ├── auth.log             # Логи авторизации
│   ├── cars.log             # Логи автомобилей
│   ├── users.log            # Логи пользователей
│   ├── cards.log            # Логи визиток
│   └── test_router.log      # Логи роутера (для отладки)
└── README.md                 # Документация backend
```

### **frontend/** — Клиентская часть
```
frontend/
├── src/                      # Исходный код
│   ├── components/           # Vue компоненты
│   │   ├── cars/            # Компоненты автомобилей
│   │   │   ├── CarCard.vue
│   │   │   └── CarDetailModal.vue
│   │   ├── events/          # Компоненты событий
│   │   │   ├── EventCard.vue
│   │   │   └── EventDetailModal.vue
│   │   ├── members/         # Компоненты участников
│   │   │   ├── MemberCard.vue
│   │   │   └── MemberDetailModal.vue
│   │   ├── map/             # Компоненты карты
│   │   │   ├── MapComponent.vue
│   │   │   ├── MembersDropdown.vue
│   │   │   └── QuickMarkers.vue
│   │   ├── services/        # Компоненты гид-объектов
│   │   │   ├── ServiceCard.vue
│   │   │   └── GuideObjectDetailModal.vue
│   │   └── common/          # Общие компоненты
│   │       ├── AppHeader.vue
│   │       ├── BottomNavigation.vue
│   │       └── FiltersSection.vue
│   ├── views/               # Страницы приложения
│   │   ├── DashboardView.vue
│   │   ├── CarsView.vue
│   │   ├── EventsView.vue
│   │   ├── MembersView.vue
│   │   ├── MapView.vue
│   │   ├── ServicesView.vue
│   │   ├── ProfileView.vue
│   │   └── AccessDeniedView.vue
│   ├── stores/              # Pinia stores
│   │   ├── data.ts          # Основные данные
│   │   └── telegram.ts      # Telegram WebApp данные
│   ├── router/              # Vue Router
│   │   └── index.ts         # Конфигурация маршрутов
│   ├── assets/              # Статические ресурсы
│   │   └── main.css         # Основные стили
│   ├── App.vue              # Корневой компонент
│   └── main.ts              # Точка входа
├── docs/                     # Документация frontend
│   ├── FRONTEND_GUIDE.md    # Руководство по frontend
│   ├── AUTH_SYSTEM.md       # Система авторизации
│   ├── FRONTEND_MIGRATION_PLAN.md # План миграции
│   └── ...
├── dist/                     # Собранные файлы
├── package.json              # Зависимости
├── vite.config.ts           # Конфигурация Vite
├── tsconfig.json            # Конфигурация TypeScript
└── README.md                # Документация frontend
```

### **database/** — База данных
```
database/
├── scripts/                  # SQL скрипты
│   ├── 001_create_all_tables.sql # Создание всех таблиц
│   ├── 002_fill_catalogs.sql     # Заполнение справочников
│   ├── 003_fill_test_data.sql    # Тестовые данные
│   ├── 004_add_indexes.sql       # Индексы для производительности
│   ├── 005_add_triggers.sql      # Триггеры для автоматизации
│   ├── 006_add_views.sql         # Представления для отчётов
│   ├── 007_add_procedures.sql    # Хранимые процедуры
│   ├── 008_add_functions.sql     # Пользовательские функции
│   ├── 009_add_events.sql        # События БД
│   ├── 010_add_grants.sql        # Права доступа
│   ├── 011_add_backup.sql        # Резервное копирование
│   ├── 012_add_restore.sql       # Восстановление
│   └── 013_add_cleanup.sql       # Очистка старых данных
├── test_data/                # Тестовые данные
│   ├── users.json            # Пользователи
│   ├── cars.json             # Автомобили
│   ├── events.json           # События
│   ├── guide_objects.json    # Гид-объекты
│   ├── reviews.json          # Отзывы
│   ├── photos.json           # Фото
│   └── ...
├── README_DOWNLOAD_IMAGES.md # Инструкция по загрузке изображений
├── README_TEST_DATA.md       # Инструкция по тестовым данным
├── QUICK_START_TEST_DATA.md  # Быстрый старт с тестовыми данными
├── test_data_tables.md       # Описание таблиц тестовых данных
├── test_data.md              # Описание тестовых данных
├── test_photos_data.md       # Описание тестовых фото
└── SOLVE_IMAGE_PROBLEM.md    # Решение проблем с изображениями
```

### **docs/** — Общая документация
```
docs/
├── ABOUT.md                  # О проекте и миссия
├── TECHNICAL_SPECIFICATION.md # Техническое задание
├── PROJECT_STRUCTURE.md      # Структура проекта (этот файл)
├── DEVELOPMENT.md            # Процесс разработки
├── DEPLOYMENT.md             # Развёртывание
├── ENVIRONMENT.md            # Переменные окружения
├── NAMING_CONVENTIONS.md     # Правила именования
├── DATABASE_SCHEMA.md        # Схема базы данных
├── DATABASE_RELATIONS.md     # Связи базы данных
├── API_METHODS.md            # API методы
├── BACKEND_SPEC.md           # Спецификация backend
├── FRONTEND_SPEC.md          # Спецификация frontend
├── BOT_SPEC.md               # Спецификация Telegram бота
├── ACCESS_SCHEME.md          # Схема доступа
├── USER_ROLES.md             # Роли пользователей
├── USER_FLOWS.md             # Пользовательские сценарии
├── APP_COMPONENTS.md         # Компоненты приложения
├── APP_SECTIONS.md           # Разделы приложения
├── AUTH_ARCHITECTURE.md      # Архитектура авторизации
├── catalogs/                 # Справочники
│   ├── car_brands.md         # Марки автомобилей
│   ├── event_types.md        # Типы событий
│   ├── guide_object_kinds.md # Виды гид-объектов
│   ├── guide_object_types.md # Типы гид-объектов
│   ├── roles.md              # Роли пользователей
│   └── statuses.md           # Статусы сущностей
├── DESIGN/                   # Дизайн и UI/UX
│   ├── COLORS.md             # Цветовая схема
│   ├── COMPONENTS/           # Компоненты дизайна
│   ├── SCREENS/              # Экраны приложения
│   ├── ADAPTIVITY.md         # Адаптивность
│   ├── ANIMATION.md          # Анимации
│   └── ...
├── USER_FLOWS/               # Пользовательские сценарии
│   ├── 01_registration.md    # Регистрация
│   ├── 02_profile_edit.md    # Редактирование профиля
│   ├── 03_car_management.md  # Управление автомобилями
│   ├── 04_event_creation.md  # Создание событий
│   ├── 05_event_participation.md # Участие в событиях
│   ├── 06_map_interaction.md # Работа с картой
│   ├── 07_guide_objects.md   # Работа с гид-объектами
│   ├── 08_reviews.md         # Отзывы
│   ├── 09_business_cards.md  # Визитки
│   ├── 10_activity_system.md # Система активности
│   ├── 11_telegram_bot.md    # Telegram бот
│   ├── 12_authorization.md   # Авторизация
│   ├── 13_notifications.md   # Уведомления
│   ├── 14_admin_tools.md     # Инструменты администратора
│   └── 15_moderation.md      # Модерация
├── reference/                # Справочные материалы
│   └── README.md             # Оглавление справочников
├── test_OCR/                 # Тестирование OCR
│   ├── test.html             # Тестовая страница
│   └── plate-proxy.php       # Прокси для тестирования
└── _archive/                 # Архивные документы
    ├── _backend_old/         # Старая версия backend
    ├── _bot_old/             # Старая версия бота
    ├── API_METHODS_2024-07-14.md
    ├── BACKEND_SPEC_2024-07-14.md
    ├── BOT_SPEC_2024-07-14.md
    └── ...
```

### **uploads/** — Загружаемые файлы
```
uploads/
├── users/                    # Файлы участников
│   ├── avatars/             # Аватарки
│   └── gallery/             # Фотографии
├── cars/                     # Файлы автомобилей
├── guide_objects/            # Файлы гид-объектов
├── events/                   # Файлы мероприятий
└── reviews/                  # Файлы отзывов
```

### **config/** — Конфигурация
```
config/
├── app.config.php            # Основная конфигурация приложения
├── bot.config.php            # Конфигурация Telegram бота
├── sectionGroups.php         # Группы разделов и права доступа
└── archive/                  # Архивные конфигурации
    ├── app.config.ts         # Старая TypeScript конфигурация
    ├── bot.config.ts         # Старая конфигурация бота
    └── sectionGroups.ts      # Старая конфигурация разделов
```

---

## 🔄 Схема взаимодействия компонентов

```
Frontend (Vue.js) ←→ Backend (PHP) ←→ Database (MySQL)
     ↓                    ↓                    ↓
Telegram WebApp    Telegram Bot API    File System
     ↓                    ↓                    ↓
User Interface    Business Logic    Data Storage
```

---

## 📋 Ключевые принципы архитектуры

### **1. Разделение ответственности**
- **Frontend** — пользовательский интерфейс
- **Backend** — бизнес-логика и API
- **Database** — хранение данных
- **Utils** — общие утилиты

### **2. Уровни абстракции backend**
- **L0 (Модели)** — работа с БД
- **L1 (Базовые операции)** — простые комбинации
- **L2 (Бизнес-операции)** — проверки и правила
- **L3 (Сложные сценарии)** — транзакции и интеграции
- **L4 (Комплексные сценарии)** — полные пользовательские сценарии

### **3. Использование утилит**
- **Database.php** — подключение к БД
- **AuthHelper.php** — авторизация
- **ValidationHelper.php** — валидация
- **ResponseHelper.php** — ответы API
- **Logger.php** — логирование
- **load_env.php** — переменные окружения

### **4. Документация**
- Каждый компонент имеет документацию
- Актуальность документации поддерживается
- Примеры использования включены

---

> **Важно:** Следуйте архитектуре с уровнями абстракции и используйте существующие утилиты для общих операций! 
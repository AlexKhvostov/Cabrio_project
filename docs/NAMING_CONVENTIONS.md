# Правила именования

> См. также: [Техническое задание](TECHNICAL_SPECIFICATION.md), [Структура проекта](PROJECT_STRUCTURE.md)

## 🎨 Frontend

### Vue компоненты
- Формат: PascalCase
- Примеры:
  ```
  UserProfile.vue
  CarGallery.vue
  EventCard.vue
  BottomNavigation.vue
  ```

### Файлы стилей
- Формат: kebab-case
- Примеры:
  ```
  user-profile.scss
  car-gallery.scss
  event-card.scss
  bottom-navigation.scss
  ```

### TypeScript
- Интерфейсы/Типы: PascalCase
- Переменные/Функции: camelCase
- Примеры:
  ```typescript
  interface UserData {}
  type CarStatus = 'active' | 'inactive'
  const userData = {}
  function getUserProfile() {}
  ```

### Vue Router
- Файлы: kebab-case
- Роуты: kebab-case
- Примеры:
  ```
  user-profile.vue
  /user-profile
  /car-details/:id
  ```

## ⚙️ Backend

### PHP Классы
- Формат: PascalCase
- Примеры:
  ```php
  UserController.php
  CarRepository.php
  EventService.php
  ```

### PHP Методы
- Формат: camelCase
- Примеры:
  ```php
  getUserById()
  createNewCar()
  updateEventStatus()
  ```

### API Endpoints
- Формат: kebab-case
- Примеры:
  ```
  /api/user-profile
  /api/car-details
  /api/event-participants
  ```

### Конфигурации
- Формат: snake_case
- Примеры:
  ```
  database_config.php
  telegram_config.php
  app_config.php
  ```

## 📁 Файлы и директории

### Загружаемые файлы
- Формат: `{тип}_{id}_{подтип}_{id}.{ext}`
- Примеры:
  ```
  user_123_avatar_124.jpg
  car_456_main_987.jpg
  event_789_cover_77.png
  ```

### Директории
- Формат: snake_case
- Примеры:
  ```
  user_uploads/
  car_images/
  event_photos/
  ```

## 🔄 Git

### Ветки
- Формат: `тип/описание-в-kebab-case`
- Примеры:
  ```
  feature/user-authentication
  fix/car-gallery-loading
  refactor/event-handling
  ```

### Коммиты
- Формат: `тип: краткое описание`
- Примеры:
  ```
  feat: добавлена авторизация через Telegram
  fix: исправлена загрузка изображений
  refactor: оптимизирована работа с данными
  ``` 
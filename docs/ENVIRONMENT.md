# Настройка окружения

> См. также: [Техническое задание](TECHNICAL_SPECIFICATION.md), [Структура проекта](PROJECT_STRUCTURE.md)

## 💻 Системные требования

### Операционная система
- Windows 10/11
- PowerShell последней версии
- Git для Windows

### Сервер
- XAMPP (PHP 8.1)
- MySQL 8.0
- Apache 2.4

### Node.js
- Версия 18+
- npm последней версии

## 🛠️ Установка

### 1. XAMPP
1. Скачать XAMPP с PHP 8.1
2. Установить в `C:\xampp`
3. Настроить PHP:
   ```ini
   memory_limit = 256M
   post_max_size = 100M
   upload_max_filesize = 100M
   max_execution_time = 300
   ```

### 2. MySQL
1. Проверить версию 8.0
2. Настроить конфигурацию:
   ```ini
   character-set-server = utf8mb4
   collation-server = utf8mb4_unicode_ci
   ```

### 3. Node.js
1. Установить Node.js 18+
2. Проверить установку:
   ```bash
   node --version
   npm --version
   ```

### 4. Git
1. Установить Git для Windows
2. Настроить Git:
   ```bash
   git config --global core.autocrlf true
   ```

## 📂 Структура директорий

### Создание директорий
```powershell
# Создать рабочую директорию
Set-Location -Path C:\xampp\htdocs
New-Item -ItemType Directory -Path "app"
Set-Location -Path app

# Создать поддиректории
New-Item -ItemType Directory -Path "frontend","backend","bot","shared","docs"
```

### Права доступа
- Дать права на запись в uploads
- Настроить права на логи
- Проверить права на кэш

## 🔧 Конфигурация

### Apache
1. Виртуальные хосты:
   ```apache
   <VirtualHost *:80>
       DocumentRoot "C:/xampp/htdocs/app"
       ServerName app.local
   </VirtualHost>
   ```

2. SSL настройки:
   ```apache
   SSLEngine on
   SSLCertificateFile "conf/ssl.crt"
   SSLCertificateKeyFile "conf/ssl.key"
   ```

### PHP
1. Расширения:
   - pdo_mysql
   - gd
   - fileinfo
   - openssl

2. Настройки:
   ```ini
   error_reporting = E_ALL
   display_errors = On
   log_errors = On
   ```

### MySQL
1. Создать базу:
   ```sql
   CREATE DATABASE app_db
   CHARACTER SET utf8mb4
   COLLATE utf8mb4_unicode_ci;
   ```

2. Создать пользователя:
   ```sql
   CREATE USER 'app_user'@'localhost'
   IDENTIFIED BY 'password';
   ```

## 🚀 Запуск проекта

### Подготовка
1. Клонировать репозиторий
2. Установить зависимости
3. Настроить окружение

### Frontend
```powershell
# Перейти в директорию
Set-Location -Path C:\xampp\htdocs\app\frontend

# Установить зависимости
npm install

# Запустить
npm run dev
```

### Backend
1. Настроить .env
2. Запустить миграции
3. Проверить API

### Проверка
- Frontend: http://localhost:8080
- Backend: http://app.local/api
- PhpMyAdmin: http://localhost/phpmyadmin 

## Файл .env (пример структуры)

Файл .env должен находиться в корне проекта и содержать все чувствительные параметры:

```
# Доступы к базе данных
DB_HOST=localhost
DB_PORT=3306
DB_USER=your_user
DB_PASSWORD=your_password
DB_NAME=cabrioride

# Telegram Bot
BOT_TOKEN=your_telegram_bot_token

# Идентификаторы чатов и пользователей (можно перечислять через запятую)
ADMIN_IDS=123456789,987654321   # список ID админов
ROOT_IDS=111111111              # список ID root-админов (через запятую, если несколько)
MODERATOR_IDS=222222222,333333333 # список ID модераторов
CLUB_CHAT_ID=-1001234567890     # ID основного чата (можно несколько через запятую)

# Настройки приложения
APP_NAME=CabrioRide
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

# Настройки интеграции с Telegram
TELEGRAM_WEBHOOK_URL=https://your-domain.com/api/webhook
TELEGRAM_API_URL=https://api.telegram.org
TELEGRAM_APP_URL=https://t.me/your_bot
```

> Все реальные значения должны храниться только локально и не попадать в репозиторий. Пример структуры приведён для ориентира. 

## 🛠️ Централизованная конфигурация приложения

Часть параметров, не являющихся секретами (лимиты, настройки отзывов, параметры системы активности и др.), теперь хранятся централизованно в файле:

```
config/app.config.ts
```

- Все чувствительные данные (пароли, токены, ключи) по-прежнему хранятся только в .env.
- Все изменяемые параметры приложения, не относящиеся к безопасности, вынесены в config/app.config.ts и доступны для редактирования без перезапуска сервера.
- Подробнее о структуре и назначении — см. [docs/PROJECT_STRUCTURE.md](PROJECT_STRUCTURE.md).

## 🛡️ Политика хранения координат (privacy)

- Координаты пользователей хранятся только для отображения на карте и не используются для других целей.
- Координаты автоматически удаляются или становятся недоступны для отображения через 60 минут после последнего обновления.
- Пользователь может в любой момент отключить передачу координат (перестать отображаться на карте).
- Данные не передаются третьим лицам и не используются для аналитики.
- При необходимости соблюдения GDPR/ФЗ-152 — реализовать механизм полного удаления координат по запросу пользователя. 
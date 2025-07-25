# Развёртывание backend CabrioRide

> Подробные инструкции по развёртыванию backend на различных окружениях.
> 
> **Важно:** Все чувствительные данные хранятся только в .env файле!

---

## 🖥️ Требования к серверу

### Минимальные требования
- **PHP:** 8.1 или выше
- **MySQL:** 8.0 или выше
- **Веб-сервер:** Apache 2.4+ или Nginx 1.18+
- **Память:** минимум 512 MB RAM
- **Диск:** минимум 1 GB свободного места

### Рекомендуемые требования
- **PHP:** 8.2+
- **MySQL:** 8.0+
- **Веб-сервер:** Apache с mod_rewrite или Nginx
- **Память:** 2 GB RAM
- **Диск:** 5 GB свободного места
- **SSL:** обязателен для продакшена

---

## 📦 Подготовка к развёртыванию

### 1. Клонирование репозитория
```bash
# Клонируем репозиторий
git clone https://github.com/your-repo/cabrioride.git
cd cabrioride

# Переходим в директорию backend
cd backend
```

### 2. Настройка прав доступа
```bash
# Создаём папки для логов и загрузок
mkdir -p logs
mkdir -p ../uploads

# Устанавливаем права
chmod 755 logs
chmod 755 ../uploads
chmod 644 .env
```

### 3. Создание .env файла
```bash
# Копируем пример конфигурации
cp .env_example .env

# Редактируем .env с реальными данными
nano .env
```

**Обязательные переменные для продакшена:**
```env
# База данных
DB_HOST=localhost
DB_PORT=3306
DB_USER=cabrioride_user
DB_PASSWORD=secure_password
DB_NAME=cabrioride_db

# Безопасность
SYSTEM_TOKEN=very_long_random_string
JWT_SECRET=another_very_long_random_string

# Telegram
BOT_TOKEN=your_bot_token
ADMIN_IDS=123,456,789

# Окружение
APP_ENV=production
APP_DEBUG=false
DEBUG=false
```

---

## 🗄️ Настройка базы данных

### 1. Создание БД и пользователя
```sql
-- Подключаемся к MySQL
mysql -u root -p

-- Создаём базу данных
CREATE DATABASE cabrioride_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Создаём пользователя
CREATE USER 'cabrioride_user'@'localhost' IDENTIFIED BY 'secure_password';

-- Даём права
GRANT ALL PRIVILEGES ON cabrioride_db.* TO 'cabrioride_user'@'localhost';
FLUSH PRIVILEGES;
```

### 2. Создание таблиц
```bash
# Импортируем схему БД
mysql -u cabrioride_user -p cabrioride_db < database/schema.sql

# Импортируем начальные данные
mysql -u cabrioride_user -p cabrioride_db < database/initial_data.sql
```

### 3. Проверка подключения
```bash
# Тестируем подключение к БД
php -r "
require_once 'utils/load_env.php';
require_once 'utils/Database.php';
try {
    \$pdo = Database::getInstance();
    echo '✅ Подключение к БД успешно\n';
} catch (Exception \$e) {
    echo '❌ Ошибка подключения: ' . \$e->getMessage() . '\n';
}
"
```

---

## 🌐 Настройка веб-сервера

### Apache (рекомендуется)

#### 1. Создание виртуального хоста
```apache
# /etc/apache2/sites-available/cabrioride.conf
<VirtualHost *:80>
    ServerName cabrioride.by
    ServerAlias www.cabrioride.by
    DocumentRoot /var/www/cabrioride
    
    <Directory /var/www/cabrioride>
        AllowOverride All
        Require all granted
    </Directory>
    
    # Логи
    ErrorLog ${APACHE_LOG_DIR}/cabrioride_error.log
    CustomLog ${APACHE_LOG_DIR}/cabrioride_access.log combined
</VirtualHost>
```

#### 2. Включение модулей
```bash
sudo a2enmod rewrite
sudo a2enmod ssl
sudo systemctl restart apache2
```

#### 3. Активация сайта
```bash
sudo a2ensite cabrioride
sudo systemctl reload apache2
```

### Nginx

#### 1. Конфигурация сервера
```nginx
# /etc/nginx/sites-available/cabrioride
server {
    listen 80;
    server_name cabrioride.by www.cabrioride.by;
    root /var/www/cabrioride;
    index index.php index.html;

    # API маршруты
    location /api/ {
        try_files $uri $uri/ /backend/routes/api.php?$query_string;
    }

    # Статические файлы
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # PHP обработка
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

#### 2. Активация
```bash
sudo ln -s /etc/nginx/sites-available/cabrioride /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

---

## 🔒 Настройка SSL (обязательно для продакшена)

### Let's Encrypt (бесплатно)
```bash
# Устанавливаем Certbot
sudo apt install certbot python3-certbot-apache

# Получаем сертификат
sudo certbot --apache -d cabrioride.by -d www.cabrioride.by

# Автообновление
sudo crontab -e
# Добавляем: 0 12 * * * /usr/bin/certbot renew --quiet
```

---

## 🧪 Проверка работоспособности

### 1. Тест API
```bash
# Проверяем базовый эндпоинт
curl -X GET "https://cabrioride.by/api/users" \
  -H "Content-Type: application/json"
```

### 2. Проверка логов
```bash
# Проверяем логи приложения
tail -f backend/logs/app.log

# Проверяем логи ошибок
tail -f backend/logs/error.log
```

### 3. Тест загрузки файлов
```bash
# Проверяем права на папку uploads
ls -la uploads/
```

---

## 📊 Мониторинг и обслуживание

### 1. Логирование
```bash
# Ротация логов
sudo logrotate /etc/logrotate.d/cabrioride

# Мониторинг размера логов
du -sh backend/logs/
```

### 2. Резервное копирование
```bash
#!/bin/bash
# backup.sh
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backups/cabrioride"

# Бэкап БД
mysqldump -u cabrioride_user -p cabrioride_db > $BACKUP_DIR/db_$DATE.sql

# Бэкап файлов
tar -czf $BACKUP_DIR/files_$DATE.tar.gz uploads/ backend/logs/

# Удаляем старые бэкапы (старше 30 дней)
find $BACKUP_DIR -name "*.sql" -mtime +30 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +30 -delete
```

### 3. Мониторинг производительности
```bash
# Проверка использования памяти
free -h

# Проверка дискового пространства
df -h

# Проверка нагрузки
htop
```

---

## 🚨 Устранение неполадок

### Частые проблемы

#### 1. Ошибка подключения к БД
```bash
# Проверяем статус MySQL
sudo systemctl status mysql

# Проверяем подключение
mysql -u cabrioride_user -p -e "SELECT 1;"
```

#### 2. Ошибки прав доступа
```bash
# Проверяем владельца файлов
ls -la backend/
ls -la uploads/

# Исправляем права
sudo chown -R www-data:www-data backend/
sudo chown -R www-data:www-data uploads/
```

#### 3. Ошибки PHP
```bash
# Проверяем версию PHP
php -v

# Проверяем расширения
php -m | grep -E "(pdo|mysql|json|curl)"
```

#### 4. Ошибки веб-сервера
```bash
# Apache
sudo tail -f /var/log/apache2/error.log

# Nginx
sudo tail -f /var/log/nginx/error.log
```

---

## 🔄 Обновление приложения

### 1. Бэкап перед обновлением
```bash
# Создаём бэкап
./backup.sh
```

### 2. Обновление кода
```bash
# Переходим в режим обслуживания
echo "Site is under maintenance" > maintenance.html

# Обновляем код
git pull origin main

# Обновляем зависимости (если есть)
composer install --no-dev --optimize-autoloader
```

### 3. Обновление БД (если нужно)
```bash
# Применяем миграции
mysql -u cabrioride_user -p cabrioride_db < database/migrations/latest.sql
```

### 4. Проверка после обновления
```bash
# Тестируем API
curl -X GET "https://cabrioride.by/api/users"

# Убираем режим обслуживания
rm maintenance.html
```

---

## 📋 Чек-лист развёртывания

- [ ] Код загружен на сервер
- [ ] .env файл настроен с реальными данными
- [ ] База данных создана и заполнена
- [ ] Веб-сервер настроен и работает
- [ ] SSL сертификат установлен
- [ ] Права доступа настроены
- [ ] Логирование работает
- [ ] API тестируется успешно
- [ ] Резервное копирование настроено
- [ ] Мониторинг настроен

---

> **Дата последнего обновления:** 2024-12-19  
> **Версия:** 1.0.0 
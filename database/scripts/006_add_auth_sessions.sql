-- =====================================================
-- Скрипт для добавления авторизации через Telegram
-- Дата: 20 июля 2025
-- Описание: Добавляет таблицу sessions и поля для Telegram авторизации
-- Требования: MySQL 8.0+
-- =====================================================

-- 1. Добавляем поле telegram_photo_url (если не существует)
SET @sql = (SELECT COUNT(*) FROM information_schema.columns 
            WHERE table_schema = DATABASE() AND table_name = 'users' 
            AND column_name = 'telegram_photo_url');

SET @sql = IF(@sql = 0, 
    'ALTER TABLE users ADD COLUMN telegram_photo_url VARCHAR(255) NULL COMMENT ''URL фото из Telegram'';',
    'SELECT ''Поле telegram_photo_url уже существует'' AS status;');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2. Добавляем поле last_telegram_auth (если не существует)
SET @sql = (SELECT COUNT(*) FROM information_schema.columns 
            WHERE table_schema = DATABASE() AND table_name = 'users' 
            AND column_name = 'last_telegram_auth');

SET @sql = IF(@sql = 0, 
    'ALTER TABLE users ADD COLUMN last_telegram_auth TIMESTAMP NULL COMMENT ''Время последней авторизации через Telegram'';',
    'SELECT ''Поле last_telegram_auth уже существует'' AS status;');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 3. Создаём таблицу sessions (если не существует)
SET @sql = (SELECT COUNT(*) FROM information_schema.tables 
            WHERE table_schema = DATABASE() AND table_name = 'sessions');

SET @sql = IF(@sql = 0, 
    'CREATE TABLE sessions (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT UNSIGNED NOT NULL,
        session_token VARCHAR(255) NOT NULL,
        telegram_data JSON NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        expires_at TIMESTAMP NOT NULL COMMENT ''30 минут от создания'',
        is_active BOOLEAN DEFAULT TRUE,
        
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY uk_session_token (session_token),
        INDEX idx_session_token (session_token),
        INDEX idx_expires_at (expires_at),
        INDEX idx_user_id (user_id),
        INDEX idx_is_active (is_active)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;',
    'SELECT ''Таблица sessions уже существует'' AS status;');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 4. Добавляем комментарий к таблице sessions (если таблица была создана)
SET @sql = (SELECT COUNT(*) FROM information_schema.tables 
            WHERE table_schema = DATABASE() AND table_name = 'sessions');

SET @sql = IF(@sql > 0, 
    'ALTER TABLE sessions COMMENT = ''Сессии авторизации пользователей через Telegram WebApp'';',
    'SELECT ''Таблица sessions не существует'' AS status;');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 5. Создаём дополнительные индексы (если таблица существует)
SET @sql = (SELECT COUNT(*) FROM information_schema.tables 
            WHERE table_schema = DATABASE() AND table_name = 'sessions');

SET @sql = IF(@sql > 0, 
    'CREATE INDEX idx_sessions_user_active ON sessions(user_id, is_active);
     CREATE INDEX idx_sessions_expired ON sessions(expires_at, is_active);',
    'SELECT ''Таблица sessions не существует'' AS status;');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 6. Проверяем добавление полей в users
SELECT 
    COLUMN_NAME,
    DATA_TYPE,
    IS_NULLABLE,
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'users' 
AND COLUMN_NAME IN ('telegram_photo_url', 'last_telegram_auth');

-- 7. Проверяем структуру таблицы sessions
DESCRIBE sessions; 
-- =====================================================
-- Простой скрипт для добавления авторизации через Telegram
-- Дата: 20 июля 2025
-- Описание: Добавляет таблицу sessions и поля для Telegram авторизации
-- Требования: MySQL 8.0+
-- =====================================================

-- 1. Добавляем поля в таблицу users
ALTER TABLE users 
ADD COLUMN telegram_photo_url VARCHAR(255) NULL COMMENT 'URL фото из Telegram',
ADD COLUMN last_telegram_auth TIMESTAMP NULL COMMENT 'Время последней авторизации через Telegram';

-- 2. Создаём таблицу sessions
CREATE TABLE sessions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    session_token VARCHAR(255) NOT NULL,
    telegram_data JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL COMMENT '30 минут от создания',
    is_active BOOLEAN DEFAULT TRUE,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uk_session_token (session_token),
    INDEX idx_session_token (session_token),
    INDEX idx_expires_at (expires_at),
    INDEX idx_user_id (user_id),
    INDEX idx_is_active (is_active),
    INDEX idx_sessions_user_active (user_id, is_active),
    INDEX idx_sessions_expired (expires_at, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Добавляем комментарий к таблице
ALTER TABLE sessions COMMENT = 'Сессии авторизации пользователей через Telegram WebApp';

-- 4. Проверяем результат
SELECT 'Скрипт выполнен успешно' AS status;
DESCRIBE sessions; 
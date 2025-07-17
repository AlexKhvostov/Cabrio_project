-- CabrioRide: Полный скрипт создания структуры БД (MySQL 8.0)
-- Версия: 2025-07-17
-- Описание: Создание всех справочников, основных таблиц, связей, индексов и ограничений

-- =====================
-- 1. Справочники (catalogs)
-- =====================

CREATE TABLE ref_roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(32) NOT NULL UNIQUE,
    name VARCHAR(64) NOT NULL,
    description TEXT,
    color VARCHAR(16)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE ref_statuses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(32) NOT NULL,
    name VARCHAR(64) NOT NULL,
    description TEXT,
    color VARCHAR(16),
    entity_type VARCHAR(32),
    UNIQUE KEY uq_entity_code (entity_type, code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE ref_event_types (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(32) NOT NULL UNIQUE,
    name VARCHAR(64) NOT NULL,
    description TEXT,
    color VARCHAR(16)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE ref_guide_object_types (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(32) NOT NULL UNIQUE,
    name VARCHAR(64) NOT NULL,
    description TEXT,
    color VARCHAR(16)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE ref_car_brands (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    brand VARCHAR(100) NOT NULL,
    INDEX idx_brand (brand)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE ref_guide_object_kinds (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type_id BIGINT UNSIGNED NOT NULL,
    code VARCHAR(64) NOT NULL,
    name VARCHAR(64) NOT NULL,
    description TEXT,
    UNIQUE KEY uq_type_name (type_id, name),
    INDEX idx_type_id (type_id),
    CONSTRAINT fk_kind_type FOREIGN KEY (type_id) REFERENCES ref_guide_object_types(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================
-- 2. Основные сущности
-- =====================

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    join_date DATE,
    left_date DATE,
    telegram_id BIGINT UNIQUE,
    username VARCHAR(100),
    first_name_tg VARCHAR(100),
    last_name_tg VARCHAR(100),
    first_name_app VARCHAR(100),
    last_name_app VARCHAR(100),
    birth_date DATE,
    city VARCHAR(100),
    country VARCHAR(100),
    email VARCHAR(255) UNIQUE,
    phone VARCHAR(20) UNIQUE,
    about TEXT,
    notes TEXT,
    role_id BIGINT UNSIGNED,
    activity INTEGER DEFAULT 0,
    weight INTEGER,
    messages_count INTEGER,
    last_activity TIMESTAMP,
    host_user_id BIGINT UNSIGNED,
    referrer_id BIGINT UNSIGNED,
    INDEX idx_role_id (role_id),
    CONSTRAINT fk_user_role FOREIGN KEY (role_id) REFERENCES ref_roles(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE cars (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    car_brand_id BIGINT UNSIGNED,
    model VARCHAR(100),
    engine_power INTEGER,
    engine_volume DECIMAL(3,1),
    color VARCHAR(50),
    year INTEGER,
    reg_number VARCHAR(20) UNIQUE,
    show_reg_number BOOLEAN,
    vin VARCHAR(17) UNIQUE,
    description TEXT,
    create_user_id BIGINT UNSIGNED,
    owner_user_id BIGINT UNSIGNED,
    roof_type VARCHAR(50),
    notes TEXT,
    status_id BIGINT UNSIGNED,
    INDEX idx_car_brand_id (car_brand_id),
    INDEX idx_create_user_id (create_user_id),
    INDEX idx_owner_user_id (owner_user_id),
    INDEX idx_status_id (status_id),
    CONSTRAINT fk_car_brand FOREIGN KEY (car_brand_id) REFERENCES ref_car_brands(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_car_create_user FOREIGN KEY (create_user_id) REFERENCES users(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_car_owner_user FOREIGN KEY (owner_user_id) REFERENCES users(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_car_status FOREIGN KEY (status_id) REFERENCES ref_statuses(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    event_date DATE,
    event_time TIME,
    event_type_id BIGINT UNSIGNED,
    title VARCHAR(255),
    description TEXT,
    location TEXT,
    city VARCHAR(100),
    price DECIMAL(10,2),
    max_participants INTEGER,
    org_user_id BIGINT UNSIGNED,
    registration_type VARCHAR(20),
    status_id BIGINT UNSIGNED,
    INDEX idx_event_type_id (event_type_id),
    INDEX idx_org_user_id (org_user_id),
    INDEX idx_status_id (status_id),
    INDEX idx_event_date (event_date),
    CONSTRAINT fk_event_type FOREIGN KEY (event_type_id) REFERENCES ref_event_types(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_event_org_user FOREIGN KEY (org_user_id) REFERENCES users(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_event_status FOREIGN KEY (status_id) REFERENCES ref_statuses(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE business_cards (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    car_id BIGINT UNSIGNED,
    location TEXT,
    notes TEXT,
    inviter_user_id BIGINT UNSIGNED,
    INDEX idx_car_id (car_id),
    INDEX idx_inviter_user_id (inviter_user_id),
    CONSTRAINT fk_bc_car FOREIGN KEY (car_id) REFERENCES cars(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_bc_inviter_user FOREIGN KEY (inviter_user_id) REFERENCES users(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE guide_objects (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    guide_object_type_id BIGINT UNSIGNED,
    guide_object_kind_id BIGINT UNSIGNED,
    name VARCHAR(255),
    city VARCHAR(100),
    address TEXT,
    website VARCHAR(255),
    phone VARCHAR(20),
    Instagram VARCHAR(255),
    Telegram VARCHAR(255),
    Viber VARCHAR(255),
    WhatsApp VARCHAR(255),
    description TEXT,
    service_list TEXT,
    price DECIMAL(10,2),
    brand VARCHAR(100),
    add_user_id BIGINT UNSIGNED,
    status_id BIGINT UNSIGNED,
    INDEX idx_type_id (guide_object_type_id),
    INDEX idx_kind_id (guide_object_kind_id),
    INDEX idx_add_user_id (add_user_id),
    INDEX idx_status_id (status_id),
    INDEX idx_city (city),
    UNIQUE KEY uq_name_city (name, city),
    CONSTRAINT fk_guide_object_type FOREIGN KEY (guide_object_type_id) REFERENCES ref_guide_object_types(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_guide_object_kind FOREIGN KEY (guide_object_kind_id) REFERENCES ref_guide_object_kinds(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_guide_object_add_user FOREIGN KEY (add_user_id) REFERENCES users(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_guide_object_status FOREIGN KEY (status_id) REFERENCES ref_statuses(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE reviews (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    guide_object_id BIGINT UNSIGNED,
    rating INTEGER,
    quality_rating TINYINT,
    speed_rating TINYINT,
    price_rating TINYINT,
    feedback TEXT,
    author_user_id BIGINT UNSIGNED,
    status_id BIGINT UNSIGNED,
    INDEX idx_guide_object_id (guide_object_id),
    INDEX idx_author_user_id (author_user_id),
    INDEX idx_status_id (status_id),
    UNIQUE KEY uq_guide_object_author (guide_object_id, author_user_id),
    CONSTRAINT fk_review_guide_object FOREIGN KEY (guide_object_id) REFERENCES guide_objects(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_review_author_user FOREIGN KEY (author_user_id) REFERENCES users(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_review_status FOREIGN KEY (status_id) REFERENCES ref_statuses(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE photos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    entity_type VARCHAR(32) NOT NULL,
    entity_id BIGINT UNSIGNED NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    url VARCHAR(255) NOT NULL,
    description TEXT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    uploaded_by BIGINT UNSIGNED,
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_uploaded_by (uploaded_by),
    CONSTRAINT fk_photo_uploaded_by FOREIGN KEY (uploaded_by) REFERENCES users(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE user_locations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    latitude DECIMAL(9,6) NOT NULL,
    longitude DECIMAL(9,6) NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_updated_at (updated_at),
    CONSTRAINT fk_user_location_user FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE map_hints (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    type VARCHAR(32) NOT NULL,
    latitude DECIMAL(9,6) NOT NULL,
    longitude DECIMAL(9,6) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP,
    active BOOLEAN DEFAULT TRUE,
    removed_by BIGINT UNSIGNED,
    removed_at TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_type (type),
    INDEX idx_created_at (created_at),
    INDEX idx_expires_at (expires_at),
    INDEX idx_active (active),
    INDEX idx_removed_by (removed_by),
    CONSTRAINT fk_map_hint_user FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_map_hint_removed_by FOREIGN KEY (removed_by) REFERENCES users(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE moderation_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    moderator_id BIGINT UNSIGNED NOT NULL,
    action ENUM('activate','decline','block','unblock','edit') NOT NULL,
    reason VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_moderator_id (moderator_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at),
    CONSTRAINT fk_modlog_user FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_modlog_moderator FOREIGN KEY (moderator_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE activity_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    from_user_id BIGINT UNSIGNED NOT NULL,
    to_user_id BIGINT UNSIGNED NOT NULL,
    date DATE NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_from_user_id (from_user_id),
    INDEX idx_to_user_id (to_user_id),
    INDEX idx_created_at (created_at),
    UNIQUE KEY uq_from_to_date (from_user_id, to_user_id, date),
    CONSTRAINT fk_activity_from_user FOREIGN KEY (from_user_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_activity_to_user FOREIGN KEY (to_user_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================
-- 3. Таблицы связей
-- =====================

CREATE TABLE link_user_cars (
    user_id BIGINT UNSIGNED NOT NULL,
    car_id BIGINT UNSIGNED NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (user_id, car_id, role_id),
    INDEX idx_user_id (user_id),
    INDEX idx_car_id (car_id),
    INDEX idx_role_id (role_id),
    CONSTRAINT fk_luc_user FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_luc_car FOREIGN KEY (car_id) REFERENCES cars(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_luc_role FOREIGN KEY (role_id) REFERENCES ref_roles(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE link_event_participants (
    event_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    confidence VARCHAR(20),
    plus_one BOOLEAN,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (event_id, user_id),
    INDEX idx_event_id (event_id),
    INDEX idx_user_id (user_id),
    CONSTRAINT fk_lep_event FOREIGN KEY (event_id) REFERENCES events(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_lep_user FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===============
-- Конец скрипта
-- =============== 
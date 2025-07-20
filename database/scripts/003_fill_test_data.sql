-- SQL скрипт для заполнения базы данных тестовыми данными
-- CabrioRide Test Data
-- Версия: 1.0
-- Дата: 2024-07-19

-- =====================================================
-- Пользователи (users)
-- =====================================================

-- 1. Lex (Администратор)
INSERT INTO users (
    telegram_id, username, first_name_tg, last_name_tg, 
    first_name_app, last_name_app, birth_date, city, country,
    email, phone, about, role_id, activity, weight, 
    join_date, created_at, updated_at
) VALUES (
    287536885, 'lex', 'Lex', 'Admin', 
    'Александр', 'Петров', '1990-05-15', 'Минск', 'Беларусь',
    'lex@cabrioride.by', '+375291234567', 'Основатель клуба CabrioRide. Люблю кабриолеты и открытые дороги.', 
    1, 1000, 500, 
    '2023-01-01', NOW(), NOW()
);

-- 2. Иван (Модератор)
INSERT INTO users (
    telegram_id, username, first_name_tg, last_name_tg, 
    first_name_app, last_name_app, birth_date, city, country,
    email, phone, about, role_id, activity, weight, 
    join_date, created_at, updated_at
) VALUES (
    123456789, 'ivan_moder', 'Иван', 'Модератор', 
    'Иван', 'Сидоров', '1988-03-20', 'Минск', 'Беларусь',
    'ivan@cabrioride.by', '+375291234568', 'Модератор клуба. Помогаю новым участникам.', 
    2, 750, 300, 
    '2023-02-15', NOW(), NOW()
);

-- 3. Мария (Участник)
INSERT INTO users (
    telegram_id, username, first_name_tg, last_name_tg, 
    first_name_app, last_name_app, birth_date, city, country,
    email, phone, about, role_id, activity, weight, 
    join_date, created_at, updated_at
) VALUES (
    987654321, 'maria_cabrio', 'Мария', 'Кабрио', 
    'Мария', 'Иванова', '1992-07-10', 'Минск', 'Беларусь',
    'maria@cabrioride.by', '+375291234569', 'Люблю кабриолеты и путешествия. Часто езжу на встречи клуба.', 
    3, 450, 200, 
    '2023-03-10', NOW(), NOW()
);

-- 4. Дмитрий (Участник)
INSERT INTO users (
    telegram_id, username, first_name_tg, last_name_tg, 
    first_name_app, last_name_app, birth_date, city, country,
    email, phone, about, role_id, activity, weight, 
    join_date, created_at, updated_at
) VALUES (
    555666777, 'dmitry_road', 'Дмитрий', 'Дорога', 
    'Дмитрий', 'Козлов', '1985-11-25', 'Брест', 'Беларусь',
    'dmitry@cabrioride.by', '+375291234570', 'Люблю длинные поездки на кабриолете. Часто езжу в Европу.', 
    3, 600, 250, 
    '2023-04-05', NOW(), NOW()
);

-- 5. Анна (Участник)
INSERT INTO users (
    telegram_id, username, first_name_tg, last_name_tg, 
    first_name_app, last_name_app, birth_date, city, country,
    email, phone, about, role_id, activity, weight, 
    join_date, created_at, updated_at
) VALUES (
    111222333, 'anna_sun', 'Анна', 'Солнце', 
    'Анна', 'Смирнова', '1995-09-08', 'Гродно', 'Беларусь',
    'anna@cabrioride.by', '+375291234571', 'Люблю ездить с открытой крышей в солнечную погоду.', 
    3, 350, 150, 
    '2023-05-20', NOW(), NOW()
);

-- 6. Сергей (Участник)
INSERT INTO users (
    telegram_id, username, first_name_tg, last_name_tg, 
    first_name_app, last_name_app, birth_date, city, country,
    email, phone, about, role_id, activity, weight, 
    join_date, created_at, updated_at
) VALUES (
    444555666, 'sergey_wind', 'Сергей', 'Ветер', 
    'Сергей', 'Волков', '1987-12-03', 'Витебск', 'Беларусь',
    'sergey@cabrioride.by', '+375291234572', 'Люблю скорость и ветер в волосах. Часто участвую в гонках.', 
    3, 800, 400, 
    '2023-06-12', NOW(), NOW()
);

-- 7. Елена (Участник)
INSERT INTO users (
    telegram_id, username, first_name_tg, last_name_tg, 
    first_name_app, last_name_app, birth_date, city, country,
    email, phone, about, role_id, activity, weight, 
    join_date, created_at, updated_at
) VALUES (
    777888999, 'elena_style', 'Елена', 'Стиль', 
    'Елена', 'Новикова', '1993-04-18', 'Могилев', 'Беларусь',
    'elena@cabrioride.by', '+375291234573', 'Люблю стильные кабриолеты и красивые фото.', 
    3, 400, 180, 
    '2023-07-08', NOW(), NOW()
);

-- 8. Павел (Новый участник)
INSERT INTO users (
    telegram_id, username, first_name_tg, last_name_tg, 
    first_name_app, last_name_app, birth_date, city, country,
    email, phone, about, role_id, activity, weight, 
    join_date, created_at, updated_at
) VALUES (
    999000111, 'pavel_new', 'Павел', 'Новый', 
    'Павел', 'Морозов', '1991-06-30', 'Минск', 'Беларусь',
    'pavel@cabrioride.by', '+375291234574', 'Новый участник клуба. Ищу свой первый кабриолет.', 
    3, 50, 0, 
    '2024-01-15', NOW(), NOW()
);

-- =====================================================
-- Автомобили (cars)
-- =====================================================

-- 1. BMW 3 Series (Lex)
INSERT INTO cars (
    car_brand_id, model, engine_power, engine_volume, color, year,
    reg_number, show_reg_number, vin, description, create_user_id, owner_user_id,
    roof_type, status_id, created_at, updated_at
) VALUES (
    1, '3 Series', 184, 2.0, 'Черный', 2020,
    '9588MI1', 1, 'WBA8E9G50LNT12345', 'BMW 3 Series кабриолет. Отличное состояние, полный пакет опций.',
    1, 1, 'Электроскладывающаяся', 1, NOW(), NOW()
);

-- 2. Mercedes-Benz SLK (Иван)
INSERT INTO cars (
    car_brand_id, model, engine_power, engine_volume, color, year,
    reg_number, show_reg_number, vin, description, create_user_id, owner_user_id,
    roof_type, status_id, created_at, updated_at
) VALUES (
    2, 'SLK', 156, 1.8, 'Серебристый', 2018,
    '1234AB7', 1, 'WDD1724421F123456', 'Mercedes-Benz SLK. Компактный спортивный кабриолет.',
    2, 2, 'Электроскладывающаяся', 1, NOW(), NOW()
);

-- 3. Audi A3 (Мария)
INSERT INTO cars (
    car_brand_id, model, engine_power, engine_volume, color, year,
    reg_number, show_reg_number, vin, description, create_user_id, owner_user_id,
    roof_type, status_id, created_at, updated_at
) VALUES (
    3, 'A3', 140, 1.6, 'Белый', 2019,
    '5678CD7', 1, 'WAUZZZ8V9K1234567', 'Audi A3 кабриолет. Элегантный дизайн и комфорт.',
    3, 3, 'Электроскладывающаяся', 1, NOW(), NOW()
);

-- 4. Porsche Boxster (Дмитрий)
INSERT INTO cars (
    car_brand_id, model, engine_power, engine_volume, color, year,
    reg_number, show_reg_number, vin, description, create_user_id, owner_user_id,
    roof_type, status_id, created_at, updated_at
) VALUES (
    4, 'Boxster', 300, 2.5, 'Красный', 2021,
    '9999EF7', 1, 'WP0AA2987LS123456', 'Porsche Boxster. Спортивный кабриолет для истинных ценителей.',
    4, 4, 'Электроскладывающаяся', 1, NOW(), NOW()
);

-- 5. Mazda MX-5 (Анна)
INSERT INTO cars (
    car_brand_id, model, engine_power, engine_volume, color, year,
    reg_number, show_reg_number, vin, description, create_user_id, owner_user_id,
    roof_type, status_id, created_at, updated_at
) VALUES (
    5, 'MX-5', 150, 2.0, 'Синий', 2020,
    '1111GH7', 1, 'JM1NDAD7XK1234567', 'Mazda MX-5. Легендарный кабриолет для удовольствия от вождения.',
    5, 5, 'Ручная', 1, NOW(), NOW()
);

-- 6. Volkswagen Beetle (Сергей)
INSERT INTO cars (
    car_brand_id, model, engine_power, engine_volume, color, year,
    reg_number, show_reg_number, vin, description, create_user_id, owner_user_id,
    roof_type, status_id, created_at, updated_at
) VALUES (
    6, 'Beetle', 105, 1.4, 'Желтый', 2017,
    '2222IJ7', 1, '3VWLL7AJ5HM123456', 'Volkswagen Beetle кабриолет. Классический дизайн и надежность.',
    6, 6, 'Электроскладывающаяся', 1, NOW(), NOW()
);

-- =====================================================
-- События (events)
-- =====================================================

-- 1. Весенняя встреча кабриолетов
INSERT INTO events (
    event_date, event_time, event_type_id, title, description, location, city,
    price, max_participants, org_user_id, registration_type, status_id,
    created_at, updated_at
) VALUES (
    '2024-04-15', '14:00:00', 1, 'Весенняя встреча кабриолетов',
    'Традиционная весенняя встреча владельцев кабриолетов. Маршрут по живописным местам Минска.',
    'Парк Победы, Минск', 'Минск', 0.00, 20, 1, 'free', 1,
    NOW(), NOW()
);

-- 2. Летний пикник
INSERT INTO events (
    event_date, event_time, event_type_id, title, description, location, city,
    price, max_participants, org_user_id, registration_type, status_id,
    created_at, updated_at
) VALUES (
    '2024-07-20', '12:00:00', 2, 'Летний пикник кабриолетов',
    'Летний пикник на природе. Барбекю, общение, фотосессия кабриолетов.',
    'Озеро Нарочь', 'Мядель', 50.00, 15, 2, 'confirmation', 1,
    NOW(), NOW()
);

-- 3. Осенний тур
INSERT INTO events (
    event_date, event_time, event_type_id, title, description, location, city,
    price, max_participants, org_user_id, registration_type, status_id,
    created_at, updated_at
) VALUES (
    '2024-09-28', '09:00:00', 1, 'Осенний тур по Беларуси',
    'Трехдневный тур по живописным местам Беларуси. Маршрут: Минск-Брест-Гродно-Минск.',
    'Старт: Минск', 'Минск', 200.00, 10, 3, 'invitation', 1,
    NOW(), NOW()
);

-- 4. Зимняя встреча
INSERT INTO events (
    event_date, event_time, event_type_id, title, description, location, city,
    price, max_participants, org_user_id, registration_type, status_id,
    created_at, updated_at
) VALUES (
    '2024-12-15', '16:00:00', 3, 'Зимняя встреча клуба',
    'Закрытая встреча в теплом помещении. Планы на следующий год, общение.',
    'Ресторан "Кабриолет"', 'Минск', 0.00, 25, 1, 'free', 1,
    NOW(), NOW()
);

-- 5. Фотосессия кабриолетов
INSERT INTO events (
    event_date, event_time, event_type_id, title, description, location, city,
    price, max_participants, org_user_id, registration_type, status_id,
    created_at, updated_at
) VALUES (
    '2024-06-08', '10:00:00', 2, 'Фотосессия кабриолетов',
    'Профессиональная фотосессия кабриолетов в городской среде. Лучшие фото будут в календаре клуба.',
    'Центр Минска', 'Минск', 30.00, 12, 5, 'confirmation', 1,
    NOW(), NOW()
);

-- =====================================================
-- Гид-объекты (guide_objects)
-- =====================================================

-- 1. Автосервис "Кабриолет"
INSERT INTO guide_objects (
    guide_object_type_id, guide_object_kind_id, name, city, address, website, phone,
    description, service_list, price, brand, add_user_id, status_id,
    created_at, updated_at
) VALUES (
    1, 1, 'Автосервис "Кабриолет"', 'Минск', 'ул. Ленина, 15',
    'https://cabrio-service.by', '+375291234500',
    'Специализированный сервис для кабриолетов. Диагностика, ремонт, обслуживание.',
    'Диагностика,Ремонт,Обслуживание,Тюнинг', 0.00, 'Все марки', 1, 1,
    NOW(), NOW()
);

-- 2. Кафе "Ветер в волосах"
INSERT INTO guide_objects (
    guide_object_type_id, guide_object_kind_id, name, city, address, website, phone,
    description, service_list, price, brand, add_user_id, status_id,
    created_at, updated_at
) VALUES (
    2, 3, 'Кафе "Ветер в волосах"', 'Минск', 'пр. Независимости, 25',
    'https://wind-cafe.by', '+375291234501',
    'Кафе для владельцев кабриолетов. Специальные парковочные места, меню для автолюбителей.',
    'Завтрак,Обед,Ужин,Кофе', 0.00, 'Собственное', 2, 1,
    NOW(), NOW()
);

-- 3. Автомойка "Блеск"
INSERT INTO guide_objects (
    guide_object_type_id, guide_object_kind_id, name, city, address, website, phone,
    description, service_list, price, brand, add_user_id, status_id,
    created_at, updated_at
) VALUES (
    1, 2, 'Автомойка "Блеск"', 'Минск', 'ул. Тимирязева, 8',
    'https://blisk-wash.by', '+375291234502',
    'Автомойка с специальными программами для кабриолетов. Безопасная мойка мягкой крыши.',
    'Мойка,Полировка,Химчистка,Консервация', 0.00, 'Все марки', 3, 1,
    NOW(), NOW()
);

-- 4. Отель "Кабриолет"
INSERT INTO guide_objects (
    guide_object_type_id, guide_object_kind_id, name, city, address, website, phone,
    description, service_list, price, brand, add_user_id, status_id,
    created_at, updated_at
) VALUES (
    3, 4, 'Отель "Кабриолет"', 'Брест', 'ул. Московская, 12',
    'https://cabrio-hotel.by', '+375291234503',
    'Отель для путешественников на кабриолетах. Крытая парковка, специальные услуги.',
    'Проживание,Парковка,Завтрак,WiFi', 0.00, 'Собственный', 4, 1,
    NOW(), NOW()
);

-- 5. Заправка "Экспресс"
INSERT INTO guide_objects (
    guide_object_type_id, guide_object_kind_id, name, city, address, website, phone,
    description, service_list, price, brand, add_user_id, status_id,
    created_at, updated_at
) VALUES (
    1, 5, 'Заправка "Экспресс"', 'Минск', 'пр. Победителей, 45',
    'https://express-fuel.by', '+375291234504',
    'Заправка с качественным топливом. Скидки для участников клуба CabrioRide.',
    'АИ-92,АИ-95,АИ-98,Дизель', 0.00, 'А-100', 5, 1,
    NOW(), NOW()
);

-- =====================================================
-- Отзывы (reviews)
-- =====================================================

-- 1. Отзыв о автосервисе
INSERT INTO reviews (
    guide_object_id, quality_rating, speed_rating, price_rating, feedback,
    author_user_id, status_id, created_at, updated_at
) VALUES (
    1, 9, 8, 7, 'Отличный сервис! Быстро и качественно отремонтировали крышу кабриолета. Рекомендую всем владельцам кабриолетов.',
    1, 1, NOW(), NOW()
);

-- 2. Отзыв о кафе
INSERT INTO reviews (
    guide_object_id, quality_rating, speed_rating, price_rating, feedback,
    author_user_id, status_id, created_at, updated_at
) VALUES (
    2, 8, 9, 8, 'Уютное кафе с отличной кухней. Есть специальные места для парковки кабриолетов. Обязательно вернемся!',
    3, 1, NOW(), NOW()
);

-- 3. Отзыв об автомойке
INSERT INTO reviews (
    guide_object_id, quality_rating, speed_rating, price_rating, feedback,
    author_user_id, status_id, created_at, updated_at
) VALUES (
    3, 10, 9, 8, 'Лучшая автомойка для кабриолетов! Бережно моют мягкую крышу, машина блестит как новая.',
    6, 1, NOW(), NOW()
);

-- =====================================================
-- Координаты пользователей (user_locations)
-- =====================================================

-- 1. Lex (Минск)
INSERT INTO user_locations (user_id, latitude, longitude, updated_at) VALUES (1, 53.9023, 27.5619, NOW());

-- 2. Иван (Минск)
INSERT INTO user_locations (user_id, latitude, longitude, updated_at) VALUES (2, 53.9083, 27.5689, NOW());

-- 3. Мария (Минск)
INSERT INTO user_locations (user_id, latitude, longitude, updated_at) VALUES (3, 53.8963, 27.5549, NOW());

-- 4. Дмитрий (Брест)
INSERT INTO user_locations (user_id, latitude, longitude, updated_at) VALUES (4, 52.0976, 23.7341, NOW());

-- 5. Анна (Гродно)
INSERT INTO user_locations (user_id, latitude, longitude, updated_at) VALUES (5, 53.6694, 23.8131, NOW());

-- =====================================================
-- Участие в событиях (link_event_participants)
-- =====================================================

-- 1. Весенняя встреча
INSERT INTO link_event_participants (event_id, user_id, confidence, plus_one, created_at) VALUES (1, 1, 'yes', 0, NOW());
INSERT INTO link_event_participants (event_id, user_id, confidence, plus_one, created_at) VALUES (1, 2, 'yes', 0, NOW());
INSERT INTO link_event_participants (event_id, user_id, confidence, plus_one, created_at) VALUES (1, 3, 'yes', 1, NOW());
INSERT INTO link_event_participants (event_id, user_id, confidence, plus_one, created_at) VALUES (1, 4, 'maybe', 0, NOW());

-- 2. Летний пикник
INSERT INTO link_event_participants (event_id, user_id, confidence, plus_one, created_at) VALUES (2, 2, 'yes', 0, NOW());
INSERT INTO link_event_participants (event_id, user_id, confidence, plus_one, created_at) VALUES (2, 5, 'yes', 0, NOW());
INSERT INTO link_event_participants (event_id, user_id, confidence, plus_one, created_at) VALUES (2, 6, 'yes', 1, NOW());

-- =====================================================
-- Связи пользователей и автомобилей (link_user_cars)
-- =====================================================

-- Lex - владелец BMW
INSERT INTO link_user_cars (user_id, car_id, role_id) VALUES (1, 1, 1);

-- Иван - владелец Mercedes
INSERT INTO link_user_cars (user_id, car_id, role_id) VALUES (2, 2, 1);

-- Мария - владелец Audi
INSERT INTO link_user_cars (user_id, car_id, role_id) VALUES (3, 3, 1);

-- Дмитрий - владелец Porsche
INSERT INTO link_user_cars (user_id, car_id, role_id) VALUES (4, 4, 1);

-- Анна - владелец Mazda
INSERT INTO link_user_cars (user_id, car_id, role_id) VALUES (5, 5, 1);

-- Сергей - владелец Volkswagen
INSERT INTO link_user_cars (user_id, car_id, role_id) VALUES (6, 6, 1);

-- =====================================================
-- Завершение скрипта
-- =====================================================

-- Выводим статистику
SELECT 'Тестовые данные успешно добавлены!' as message;
SELECT COUNT(*) as users_count FROM users;
SELECT COUNT(*) as cars_count FROM cars;
SELECT COUNT(*) as events_count FROM events;
SELECT COUNT(*) as guide_objects_count FROM guide_objects;
SELECT COUNT(*) as reviews_count FROM reviews; 
-- SQL скрипт для добавления тестовых фото в CabrioRide
-- Версия: 1.0
-- Дата: 2024-07-19

-- =====================================================
-- ВАЖНО: Сначала выполните скрипт 004_insert_test_data.sql
-- =====================================================

-- =====================================================
-- 1. Аватары пользователей (entity_type = 'user')
-- =====================================================

INSERT INTO photos (entity_type, entity_id, file_name, url, description, uploaded_by, uploaded_at) VALUES
('user', 1, 'lex_avatar.jpg', '/uploads/avatars/lex_avatar.jpg', 'Аватар Lex - основателя клуба', 1, NOW()),
('user', 2, 'ivan_avatar.jpg', '/uploads/avatars/ivan_avatar.jpg', 'Аватар Ивана - модератора', 2, NOW()),
('user', 3, 'maria_avatar.jpg', '/uploads/avatars/maria_avatar.jpg', 'Аватар Марии', 3, NOW()),
('user', 4, 'dmitry_avatar.jpg', '/uploads/avatars/dmitry_avatar.jpg', 'Аватар Дмитрия', 4, NOW()),
('user', 5, 'anna_avatar.jpg', '/uploads/avatars/anna_avatar.jpg', 'Аватар Анны', 5, NOW()),
('user', 6, 'sergey_avatar.jpg', '/uploads/avatars/sergey_avatar.jpg', 'Аватар Сергея', 6, NOW()),
('user', 7, 'elena_avatar.jpg', '/uploads/avatars/elena_avatar.jpg', 'Аватар Елены', 7, NOW()),
('user', 8, 'pavel_avatar.jpg', '/uploads/avatars/pavel_avatar.jpg', 'Аватар Павла', 8, NOW());

-- =====================================================
-- 2. Фото автомобилей (entity_type = 'car')
-- =====================================================

INSERT INTO photos (entity_type, entity_id, file_name, url, description, uploaded_by, uploaded_at) VALUES
('car', 1, 'bmw_3series_front.jpg', '/uploads/cars/bmw_3series_front.jpg', 'BMW 3 Series - вид спереди', 1, NOW()),
('car', 1, 'bmw_3series_side.jpg', '/uploads/cars/bmw_3series_side.jpg', 'BMW 3 Series - вид сбоку', 1, NOW()),
('car', 1, 'bmw_3series_interior.jpg', '/uploads/cars/bmw_3series_interior.jpg', 'BMW 3 Series - салон', 1, NOW()),
('car', 2, 'mercedes_slk_front.jpg', '/uploads/cars/mercedes_slk_front.jpg', 'Mercedes SLK - вид спереди', 2, NOW()),
('car', 2, 'mercedes_slk_top.jpg', '/uploads/cars/mercedes_slk_top.jpg', 'Mercedes SLK - с открытой крышей', 2, NOW()),
('car', 3, 'audi_a3_front.jpg', '/uploads/cars/audi_a3_front.jpg', 'Audi A3 - вид спереди', 3, NOW()),
('car', 3, 'audi_a3_side.jpg', '/uploads/cars/audi_a3_side.jpg', 'Audi A3 - вид сбоку', 3, NOW()),
('car', 4, 'porsche_boxster_front.jpg', '/uploads/cars/porsche_boxster_front.jpg', 'Porsche Boxster - вид спереди', 4, NOW()),
('car', 4, 'porsche_boxster_side.jpg', '/uploads/cars/porsche_boxster_side.jpg', 'Porsche Boxster - вид сбоку', 4, NOW()),
('car', 5, 'mazda_mx5_front.jpg', '/uploads/cars/mazda_mx5_front.jpg', 'Mazda MX-5 - вид спереди', 5, NOW()),
('car', 5, 'mazda_mx5_top.jpg', '/uploads/cars/mazda_mx5_top.jpg', 'Mazda MX-5 - с открытой крышей', 5, NOW()),
('car', 6, 'vw_beetle_front.jpg', '/uploads/cars/vw_beetle_front.jpg', 'Volkswagen Beetle - вид спереди', 6, NOW()),
('car', 6, 'vw_beetle_side.jpg', '/uploads/cars/vw_beetle_side.jpg', 'Volkswagen Beetle - вид сбоку', 6, NOW());

-- =====================================================
-- 3. Фото событий (entity_type = 'event')
-- =====================================================

INSERT INTO photos (entity_type, entity_id, file_name, url, description, uploaded_by, uploaded_at) VALUES
('event', 1, 'spring_meet_2024.jpg', '/uploads/events/spring_meet_2024.jpg', 'Весенняя встреча 2024 - общий вид', 1, NOW()),
('event', 1, 'spring_meet_cars.jpg', '/uploads/events/spring_meet_cars.jpg', 'Весенняя встреча - кабриолеты', 1, NOW()),
('event', 2, 'summer_picnic_2024.jpg', '/uploads/events/summer_picnic_2024.jpg', 'Летний пикник - место проведения', 2, NOW()),
('event', 2, 'summer_picnic_bbq.jpg', '/uploads/events/summer_picnic_bbq.jpg', 'Летний пикник - барбекю', 2, NOW()),
('event', 3, 'autumn_tour_start.jpg', '/uploads/events/autumn_tour_start.jpg', 'Осенний тур - старт', 3, NOW()),
('event', 4, 'winter_meet_2024.jpg', '/uploads/events/winter_meet_2024.jpg', 'Зимняя встреча - помещение', 1, NOW()),
('event', 5, 'photosession_2024.jpg', '/uploads/events/photosession_2024.jpg', 'Фотосессия - процесс съемки', 5, NOW());

-- =====================================================
-- 4. Фото гид-объектов (entity_type = 'guide_object')
-- =====================================================

INSERT INTO photos (entity_type, entity_id, file_name, url, description, uploaded_by, uploaded_at) VALUES
('guide_object', 1, 'cabrio_service_exterior.jpg', '/uploads/guide_objects/cabrio_service_exterior.jpg', 'Автосервис "Кабриолет" - фасад', 1, NOW()),
('guide_object', 1, 'cabrio_service_interior.jpg', '/uploads/guide_objects/cabrio_service_interior.jpg', 'Автосервис "Кабриолет" - мастерская', 1, NOW()),
('guide_object', 2, 'wind_cafe_exterior.jpg', '/uploads/guide_objects/wind_cafe_exterior.jpg', 'Кафе "Ветер в волосах" - фасад', 2, NOW()),
('guide_object', 2, 'wind_cafe_interior.jpg', '/uploads/guide_objects/wind_cafe_interior.jpg', 'Кафе "Ветер в волосах" - интерьер', 2, NOW()),
('guide_object', 3, 'blisk_wash_exterior.jpg', '/uploads/guide_objects/blisk_wash_exterior.jpg', 'Автомойка "Блеск" - фасад', 3, NOW()),
('guide_object', 4, 'cabrio_hotel_exterior.jpg', '/uploads/guide_objects/cabrio_hotel_exterior.jpg', 'Отель "Кабриолет" - фасад', 4, NOW()),
('guide_object', 4, 'cabrio_hotel_room.jpg', '/uploads/guide_objects/cabrio_hotel_room.jpg', 'Отель "Кабриолет" - номер', 4, NOW()),
('guide_object', 5, 'express_fuel_station.jpg', '/uploads/guide_objects/express_fuel_station.jpg', 'Заправка "Экспресс" - АЗС', 5, NOW());

-- =====================================================
-- 5. Фото к отзывам (entity_type = 'review')
-- =====================================================

INSERT INTO photos (entity_type, entity_id, file_name, url, description, uploaded_by, uploaded_at) VALUES
('review', 1, 'review_cabrio_service_work.jpg', '/uploads/reviews/review_cabrio_service_work.jpg', 'Работа автосервиса "Кабриолет"', 1, NOW()),
('review', 1, 'review_cabrio_service_result.jpg', '/uploads/reviews/review_cabrio_service_result.jpg', 'Результат ремонта крыши', 1, NOW()),
('review', 2, 'review_wind_cafe_food.jpg', '/uploads/reviews/review_wind_cafe_food.jpg', 'Блюда в кафе "Ветер в волосах"', 3, NOW()),
('review', 2, 'review_wind_cafe_parking.jpg', '/uploads/reviews/review_wind_cafe_parking.jpg', 'Парковка кабриолетов у кафе', 3, NOW()),
('review', 3, 'review_blisk_wash_process.jpg', '/uploads/reviews/review_blisk_wash_process.jpg', 'Процесс мойки кабриолета', 6, NOW()),
('review', 3, 'review_blisk_wash_result.jpg', '/uploads/reviews/review_blisk_wash_result.jpg', 'Результат мойки', 6, NOW());

-- =====================================================
-- Завершение скрипта
-- =====================================================

-- Выводим статистику
SELECT 'Тестовые фото успешно добавлены!' as message;
SELECT COUNT(*) as total_photos FROM photos;
SELECT entity_type, COUNT(*) as count FROM photos GROUP BY entity_type ORDER BY entity_type;

-- Проверяем главные фото (максимальный ID для каждой сущности)
SELECT 
    'Главные фото:' as info,
    entity_type,
    entity_id,
    file_name,
    url
FROM photos p1
WHERE id = (
    SELECT MAX(id) 
    FROM photos p2 
    WHERE p2.entity_type = p1.entity_type 
    AND p2.entity_id = p1.entity_id
)
ORDER BY entity_type, entity_id; 
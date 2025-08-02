-- CabrioRide: Полное обновление справочника статусов
-- Дата: 2025-07-25
-- Описание: Удаление всех статусов и создание заново ТОЛЬКО для автомобилей

-- 1. Удаляем все существующие статусы
DELETE FROM ref_statuses;

-- 2. Сбрасываем автоинкремент
ALTER TABLE ref_statuses AUTO_INCREMENT = 1;

-- 3. Вставляем статусы ТОЛЬКО для автомобилей
INSERT INTO ref_statuses (code, name, description, color, entity_type) VALUES
  ('noticed', 'Замечен', 'Автомобиль замечен ботом по фото, владелец не найден, приглашение не отправлено', '#FFD700', 'car'),
  ('business_card', 'Визитка', 'Авто добавлено по визитке, владелец не подтверждён', '#17a2b8', 'car'),
  ('deleted', 'Удалён', 'Удалён из системы', '#9E9E9E', 'car'),
  ('archived', 'В архиве', 'В архиве (продан, не используется)', '#BDBDBD', 'car'),
  ('blocked', 'Заблокирован', 'Заблокирован (подозрительный номер, нарушение правил)', '#F44336', 'car'),
  ('pending', 'На модерации', 'Ожидает проверки модератором', '#FFC107', 'car'),
  ('active', 'Активен', 'Активный автомобиль участника', '#4CAF50', 'car');


-- 4. Проверяем результат
SELECT 
    s.id as status_id,
    s.code as status_code,
    s.name as status_name,
    s.entity_type,
    COUNT(c.id) as cars_count
FROM ref_statuses s
LEFT JOIN cars c ON s.id = c.status_id AND s.entity_type = 'car'
GROUP BY s.id, s.code, s.name, s.entity_type
ORDER BY s.id;

-- 5. Показываем статистику
SELECT 
    'Статистика по статусам автомобилей' as info,
    COUNT(*) as total_statuses
FROM ref_statuses;

SELECT 
    'Автомобили по статусам' as info,
    s.code as status_code,
    s.name as status_name,
    COUNT(c.id) as cars_count
FROM ref_statuses s
LEFT JOIN cars c ON s.id = c.status_id
WHERE s.entity_type = 'car'
GROUP BY s.id, s.code, s.name
ORDER BY s.id; 
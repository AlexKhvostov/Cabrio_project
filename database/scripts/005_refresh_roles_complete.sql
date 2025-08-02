-- CabrioRide: Полное обновление справочника ролей пользователей
-- Дата: 2025-07-25
-- Описание: Удаление всех ролей и создание заново с правильным порядком ID

-- 1. Удаляем все существующие роли
DELETE FROM ref_roles;

-- 2. Сбрасываем автоинкремент
ALTER TABLE ref_roles AUTO_INCREMENT = 1;

-- 3. Вставляем роли в правильном порядке (ID будут 1, 2, 3, 4, 5, 6)
INSERT INTO ref_roles (code, name, description, color) VALUES
  ('external',   'Внешний',         'Не член клуба, не состоит в чате. Может видеть только приглашение/заглушку.', '#b0b0b0'),
  ('guest',      'Гость',           'Состоит в чате, не начал регистрацию, запись создаётся когда бот увидел, что участник добавился в чат или когда участник обратился к боту.', '#6c757d'),
  ('user',       'Пользователь',    'Завершил базовую регистрацию (выбрал авто или "я без авто" и главного гостя), но не подтверждён модератором.', '#ffc107'),
  ('member',     'Участник',        'Подтверждён модератором, имеет доступ к основному функционалу.', '#28a745'),
  ('moderator',  'Модератор',       'Может подтверждать участников, модерировать профили, управлять подсказками и событиями.', '#007bff'),
  ('admin',      'Администратор',   'Полный доступ к управлению клубом, настройкам, пользователями и контентом.', '#dc3545');

-- 4. Обновляем роли пользователей в таблице users
-- Сначала обновляем всех пользователей с ролью 'registered' на 'user'
UPDATE users 
SET role_id = (SELECT id FROM ref_roles WHERE code = 'user')
WHERE role_id IN (SELECT id FROM ref_roles WHERE code = 'registered');

-- Затем обновляем всех пользователей с ролью 'new' на 'guest'
UPDATE users 
SET role_id = (SELECT id FROM ref_roles WHERE code = 'guest')
WHERE role_id IN (SELECT id FROM ref_roles WHERE code = 'new');

-- 5. Проверяем результат
SELECT 
    r.id as role_id,
    r.code as role_code,
    r.name as role_name,
    COUNT(u.id) as users_count
FROM ref_roles r
LEFT JOIN users u ON r.id = u.role_id
GROUP BY r.id, r.code, r.name
ORDER BY r.id;

-- 6. Показываем статистику по ролям
SELECT 
    'Статистика по ролям' as info,
    COUNT(*) as total_roles
FROM ref_roles;

SELECT 
    'Пользователи без роли' as info,
    COUNT(*) as count
FROM users 
WHERE role_id IS NULL OR role_id NOT IN (SELECT id FROM ref_roles); 
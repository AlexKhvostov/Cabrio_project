-- CabrioRide: Миграция ролей пользователей
-- Дата: 2025-07-25
-- Описание: Удаление роли 'new' и переименование 'registered' в 'user'

-- 1. Обновляем роли пользователей в таблице users
UPDATE users 
SET role_id = (SELECT id FROM ref_roles WHERE code = 'user')
WHERE role_id = (SELECT id FROM ref_roles WHERE code = 'registered');

-- 2. Удаляем роль 'new' из справочника (если есть пользователи с ролью 'new', они станут 'guest')
UPDATE users 
SET role_id = (SELECT id FROM ref_roles WHERE code = 'guest')
WHERE role_id = (SELECT id FROM ref_roles WHERE code = 'new');

-- 3. Удаляем записи ролей 'new' и 'registered' из справочника
DELETE FROM ref_roles WHERE code IN ('new', 'registered');

-- 4. Добавляем новую роль 'user' в справочник
INSERT INTO ref_roles (code, name, description, color) VALUES
  ('user', 'Пользователь', 'Завершил базовую регистрацию (выбрал авто или "я без авто" и главного гостя), но не подтверждён модератором.', '#ffc107');

-- 5. Проверяем результат
SELECT 
    r.code as role_code,
    r.name as role_name,
    COUNT(u.id) as users_count
FROM ref_roles r
LEFT JOIN users u ON r.id = u.role_id
GROUP BY r.id, r.code, r.name
ORDER BY r.id; 
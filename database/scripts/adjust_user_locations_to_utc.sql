-- Коррекция исторических значений user_locations.updated_at к UTC
-- Использовать ТОЛЬКО если ранее время записывалось в локальной таймзоне Europe/Minsk (UTC+3)
-- Перед запуском сделайте резервную копию таблицы!

-- ВАРИАНТ 1: Если значения в столбце были записаны как локальное время (Europe/Minsk),
-- и нужно перевести их в UTC (минус 3 часа). При летнем времени проверьте фактический сдвиг.

-- Пример (фикс. сдвиг -3 часа):
-- UPDATE user_locations SET updated_at = DATE_SUB(updated_at, INTERVAL 3 HOUR);

-- ВАРИАНТ 2: Если в MySQL включены time_zone таблицы/сессии и корректно настроены таблицы tzdata,
-- можно использовать CONVERT_TZ. Требует загруженных таймзон на сервере БД.
-- UPDATE user_locations SET updated_at = CONVERT_TZ(updated_at, 'Europe/Minsk', 'UTC');

-- ПРИМЕЧАНИЕ: Перед применением выберите небольшой диапазон для проверки результата
-- SELECT id, user_id, updated_at FROM user_locations ORDER BY id DESC LIMIT 50;



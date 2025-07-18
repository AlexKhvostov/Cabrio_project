# Использование раздела "Карта"

## 1. Название сценария
Использование раздела "Карта"

## 2. Цель сценария
Позволить пользователям видеть себя и других участников на карте, делиться геолокацией, получать актуальную информацию о присутствии клуба, а также обеспечивать приватность и безопасность данных.

## 3. Участники и роли
- Пользователь
- Backend
- Telegram-бот

## 4. Предусловия
- Пользователь авторизован и имеет доступ к разделу "Карта".
- Профиль пользователя не заблокирован.

## 5. Основной поток (пошагово)
1. Пользователь открывает раздел "Карта" в приложении.
2. Система запрашивает разрешение на использование геолокации.
3. Если пользователь разрешает — его координаты отправляются на сервер каждые 10 секунд.
4. На карте отображаются все участники с актуальными координатами (обновлены не более 20 минут назад).
5. На аватарке каждого участника отображается цветной индикатор статуса (зелёный, оранжевый, серый).
6. Если пользователь не дал разрешение — карта скрывается через 20 секунд, появляется уведомление о необходимости разрешить геолокацию. По умолчанию карта центрируется на Минске.
7. После разрешения передачи координат появляется переключатель "Показывать меня на карте" — пользователь может в любой момент отключить передачу координат.
8. Если пользователь отключает передачу координат, карта исчезает через 20 секунд с плавным переходом (ч/б → прозрачная → уведомление).
9. Доступ к разделу ограничен по ролям (например, только member или выше).

## 6. Альтернативные потоки / Edge-cases
- Если пользователь не дал разрешение — карта скрывается, появляется уведомление.
- Если координаты устарели (>20 мин) — пользователь не отображается на карте.
- Ошибка геолокации — отображается уведомление.
- Если профиль заблокирован — раздел недоступен.
- Приватность: координаты не сохраняются дольше 20 минут.

## 7. Статусы и переходы
- При разрешении геолокации — пользователь появляется на карте.
- При отключении — исчезает с карты через 20 секунд.
- Статус активности отображается цветом индикатора (онлайн/недавно/отсутствует).

## 8. UX-детали
- Прогрессивное скрытие карты при отключении геолокации (ч/б → прозрачная → уведомление).
- Переключатель "Показывать меня на карте" всегда доступен.
- Центрирование карты на Минске по умолчанию.
- Индикаторы статуса на аватарках.
- Все уведомления и ошибки отображаются явно.

## 9. Интеграции и API
- Backend: приём и хранение координат, выдача списка участников с координатами
- API: /api/map/location, /api/map/locations

## 10. Уведомления
- В приложении:
  - Уведомление о необходимости разрешить геолокацию
  - Уведомление об ошибке геолокации
  - Уведомление о приватности

## 11. Ограничения и лимиты
- Координаты не сохраняются дольше 20 минут
- Доступ к разделу ограничен по ролям
- Без разрешения геолокации карта недоступна
- Лимит: см. параметр `map.maxLocationLifetimeMinutes` в [config/app.config.ts](../../config/app.config.ts)

## 12. Связанные сценарии и документы
- [Регистрация и первый вход](01_registration.md)
- [Схема БД: user_locations](../DATABASE_SCHEMA.md)
- [API: /api/map/location, /api/map/locations](../API_METHODS.md)

## 13. Тест-кейсы (минимум 3)
- Пользователь разрешает геолокацию, появляется на карте, видит других участников, индикаторы статуса отображаются корректно.
- Пользователь не даёт разрешение — карта скрывается, появляется уведомление.
- Пользователь отключает передачу координат — карта исчезает через 20 секунд, появляется уведомление.
- Если координаты устарели — пользователь исчезает с карты.

---

> Все шаги и статусы согласованы с бизнес-логикой и схемой БД CabrioRide. UX-детали, приватность и уведомления должны быть реализованы согласно этому сценарию. 
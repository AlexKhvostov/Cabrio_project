# 14. Инструменты администратора

## 1. Название сценария
Инструменты администратора (просмотр и редактирование профиля участника админом/root)

## 2. Цель сценария
Обеспечить админам и root-пользователям возможность просматривать и изменять роли, статусы и флаги участников для эффективного управления клубом и поддержания порядка.

## 3. Участники и роли
- Админ
- Root
- Пользователь (чей профиль редактируется)

## 4. Предусловия
- Админ или root авторизован в приложении
- Открыта подробная карточка участника

## 5. Основной поток (пошагово)
1. Админ или root открывает подробную карточку любого участника.
2. В самом низу карточки появляется секция "Роли, статусы и флаги" (видна только админам и root).
3. В секции отображаются:
   - Текущая роль пользователя (user, moderator, admin, root)
   - Текущий статус (active, pending, left, blocked, external и др.)
   - Флаги (block, have_auto, другие служебные флаги)
4. Все поля доступны для редактирования (select, checkbox и т.д.).
5. После внесения изменений появляется кнопка "Сохранить" (активна только если были изменения).
6. После нажатия "Сохранить":
   - Изменения отправляются на backend и сохраняются в базе.
   - Пользователь получает уведомление о смене статуса/роли.
   - Действие админа логируется.

## 6. Альтернативные потоки / Edge-cases
- Блок виден только пользователям с ролью admin или root.
- Изменять можно любые параметры, кроме root-аккаунтов (root может менять только root).
- Все действия админа фиксируются в логах модерации.
- Если нет изменений — кнопка "Сохранить" неактивна.

## 7. Статусы и переходы
- Роль и статус пользователя могут быть изменены админом/root
- Все изменения фиксируются в логах

## 8. UX-детали
- Блок всегда находится в самом низу карточки пользователя, визуально отделён от основной информации
- Кнопка "Сохранить" появляется только при изменении данных
- Все изменения применяются мгновенно после сохранения

## 9. Интеграции и API
- Backend: эндпоинты для изменения ролей, статусов, флагов
- Логи модерации
- Проверка прав пользователя (admin/root)

## 10. Уведомления
- Пользователь получает уведомление о смене роли/статуса
- Все действия админа фиксируются в логах

## 11. Ограничения и лимиты
- Только admin/root видят и могут редактировать блок
- Root-аккаунты может менять только root
- Все действия логируются
- Лимит: см. параметр `adminTools.editRights.allowRootEdit` в [config/app.config.ts](../../config/app.config.ts)

## 12. Связанные сценарии и документы
- [Роли и статусы](../USER_ROLES_STATUSES.md)
- [Модерация и логирование](../DATABASE_SCHEMA.md)

## 13. Тест-кейсы (минимум 3)
- Админ меняет статус пользователя, пользователь получает уведомление, действие фиксируется в логах
- Админ пытается изменить root-аккаунт — операция недоступна
- Кнопка "Сохранить" неактивна, если изменений не было 
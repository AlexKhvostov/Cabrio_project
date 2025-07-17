/**
 * sectionGroups.ts (упрощённая версия)
 *
 * Единая точка правды для схемы доступа (roles/functions).
 * Для каждой функции/эндпоинта указывается минимальная роль для доступа.
 * Используется и на frontend, и на backend. Все изменения — только здесь!
 *
 * Визуальная схема и подробное описание принципа доступа — см. docs/ACCESS_SCHEME.md
 *
 * ---
 *
 * 📋 Что такое минимальная роль?
 * Минимальная роль — это наименьшая роль, с которой разрешён доступ к функции.
 * Если у пользователя роль равна или выше минимальной (по порядку в ROLES), доступ разрешён.
 *
 * Например, если минимальная роль "member":
 *   - member, moderator, admin — имеют доступ
 *   - registered, guest, external, new — не имеют доступа
 *
 * Проверка доступа (пример):
 *   const userRoleIndex = ROLES.indexOf(user.role);
 *   const minRoleIndex = ROLES.indexOf(FUNCTION_ROLES[functionName]);
 *   if (userRoleIndex >= minRoleIndex) { // доступ разрешён }
 *
 * ---
 */

// Список всех ролей по возрастанию прав
export const ROLES = [
  "external",    // Внешний пользователь, не в чате
  "guest",       // Гость, только что добавился в чат
  "new",         // Новый, начал регистрацию
  "registered",  // Завершил базовую регистрацию
  "member",      // Участник клуба
  "moderator",   // Модератор
  "admin"        // Администратор
];

/**
 * Привязка функций/эндпоинтов к минимальной роли доступа
 * Ключ — имя функции (логическое или эндпоинт), значение — минимальная роль
 */
export const FUNCTION_ROLES = {
  // landing
  viewLanding:      "external",
  viewAbout:        "external",
  viewInvite:       "external",

  // auth
  login:            "guest",
  registration:     "guest",
  restorePassword:  "guest",

  // profile
  profileView:      "registered",
  profileEdit:      "registered",
  profileSettings:  "registered",

  // dashboard
  dashboardView:    "registered",
  notificationsView:"registered",

  // cars
  carList:          "member",
  carAdd:           "member",
  carEdit:          "member",
  carView:          "member",

  // events
  eventView:        "member",
  eventJoin:        "member",

  // map
  mapView:          "member",

  // businessCard
  businessCard:     "member",

  // moderation
  moderationPanel:  "moderator",

  // admin
  adminPanel:       "admin",

  // support
  support:          "guest",
  faq:              "guest",
  feedback:         "guest"
  // ...добавляйте новые функции по мере роста проекта
};

/**
 * Вся визуализация и подробное описание схемы доступа вынесены в docs/ACCESS_SCHEME.md
 * Здесь — только исходные данные для кода (roles, functionRoles).
 * Для обсуждения и визуализации используйте docs/ACCESS_SCHEME.md
 */ 
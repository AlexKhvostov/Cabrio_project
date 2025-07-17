// config/app.config.ts

/**
 * Конфигурация системы активности пользователя
 * Все параметры можно менять централизованно для гибкой настройки механики.
 */
export interface ActivityRewardsConfig {
  /**
   * Баллы за регистрацию (создание профиля), только один раз
   */
  registration: number;
  /**
   * Баллы за первую активацию профиля (статус 'active' впервые), только один раз
   */
  firstActivation: number;
  /**
   * За каждое добавление автомобиля
   */
  addCar: number;
  /**
   * За указание основного гостя (host)
   */
  addHost: number;
  /**
   * За каждое добавление события
   */
  addEvent: number;
  /**
   * За каждый новый отзыв
   */
  addReview: number;
  /**
   * За каждый новый сервис или продукт
   */
  addService: number;
}

/**
 * Конфигурация отзывов о гид-объектах (GuideObject)
 */
export interface ReviewsConfig {
  /**
   * Сколько отзывов может оставить один пользователь на один гид-объект (GuideObject)
   */
  maxPerGuideObject: number;
}

/**
 * Конфигурация событий (мероприятий)
 */
export interface EventsConfig {
  /**
   * Сколько событий может создать один пользователь
   */
  maxPerUser: number;
}

/**
 * Конфигурация приглашений (визиток) и проверок авто
 */
export interface InvitesConfig {
  /**
   * Максимум проверок авто для визитки в сутки на пользователя
   */
  maxCarChecksPerDay: number;
  /**
   * Минимальная длина номера для поиска авто
   */
  minPlateLengthForCheck: number;
  /**
   * Минимальная длина номера для отправки визитки
   */
  minPlateLengthForInvite: number;
  // В будущем: лимит визиток в сутки и др.
}

/**
 * Конфигурация подсказок на карте (map hints)
 */
export interface MapHintsConfig {
  /**
   * Максимум активных подсказок, которые может добавить один пользователь одновременно
   */
  maxPerUser: number;
}

/**
 * Конфигурация раздела "Карта"
 */
export interface MapConfig {
  /**
   * Максимальное время хранения координат пользователя (в минутах)
   */
  maxLocationLifetimeMinutes: number;
}

/**
 * Конфигурация голосовых сообщений на карте
 */
export interface VoiceMessagesConfig {
  /**
   * Максимальная длительность голосового сообщения (сек)
   */
  maxDurationSeconds: number;
  /**
   * Время хранения голосового сообщения (мин)
   */
  maxLifetimeMinutes: number;
  /**
   * Максимум активных голосовых сообщений на пользователя (если нужно)
   */
  maxPerUser: number;
}

/**
 * Конфигурация лимитов уведомлений
 */
export interface NotificationsConfig {
  /**
   * Максимум уведомлений в сутки на пользователя
   */
  maxPerUserPerDay: number;
}

/**
 * Конфигурация прав админов
 */
export interface AdminToolsConfig {
  /**
   * Права на редактирование (пример: кто может менять root)
   */
  editRights: {
    allowRootEdit: boolean;
    // ... другие флаги
  };
}

/**
 * Главная конфигурация приложения CabrioRide
 * Все параметры и лимиты централизованы здесь
 */
export interface AppConfig {
  /**
   * Награды за активности пользователя
   */
  activityRewards: ActivityRewardsConfig;
  /**
   * Настройки отзывов
   */
  reviews: ReviewsConfig;
  /**
   * Настройки событий
   */
  events: EventsConfig;
  /**
   * Настройки приглашений/визиток
   */
  invites: InvitesConfig;
  /**
   * Настройки подсказок на карте
   */
  mapHints: MapHintsConfig;
  /**
   * Настройки раздела "Карта"
   */
  map: MapConfig;
  /**
   * Настройки голосовых сообщений
   */
  voiceMessages: VoiceMessagesConfig;
  /**
   * Настройки лимитов уведомлений
   */
  notifications: NotificationsConfig;
  /**
   * Настройки прав админов
   */
  adminTools: AdminToolsConfig;
  // ... другие секции по мере необходимости
}

/**
 * Основной объект конфигурации приложения
 * Используется для доступа к глобальным параметрам и лимитам
 */
const config: AppConfig = {
  activityRewards: {
    registration: 20,      // Только один раз, при создании профиля
    firstActivation: 20,   // Только один раз, при первой активации профиля
    addCar: 30,            // За каждое добавление авто
    addHost: 10,           // За каждого нового гостя
    addEvent: 30,          // За каждое добавление события
    addReview: 10,         // За каждый отзыв
    addService: 20         // За каждый сервис/продукт
  },
  reviews: {
    maxPerGuideObject: 1,    // Один пользователь — один отзыв на гид-объект (GuideObject)
  },
  events: {
    maxPerUser: 10,      // Максимум событий, которые может создать пользователь
  },
  invites: {
    maxCarChecksPerDay: 6,         // Лимит проверок авто для визитки в сутки
    minPlateLengthForCheck: 3,     // Минимум символов для поиска авто
    minPlateLengthForInvite: 6,    // Минимум символов для отправки визитки
  },
  mapHints: {
    maxPerUser: 3, // Максимум активных подсказок на пользователя (примерное значение, можно менять)
  },
  map: {
    maxLocationLifetimeMinutes: 20, // Координаты хранятся не более 20 минут
  },
  voiceMessages: {
    maxDurationSeconds: 30,      // Максимальная длительность голосового сообщения (сек)
    maxLifetimeMinutes: 60,      // Сообщения хранятся не более 60 минут
    maxPerUser: 30,              // Максимум активных голосовых сообщений на пользователя
  },
  notifications: {
    maxPerUserPerDay: 100,       // Максимум уведомлений в сутки на пользователя
  },
  adminTools: {
    editRights: {
      allowRootEdit: true,      // Только root может менять root-аккаунты
      // ... другие флаги
    },
  },
  // ... другие параметры
};

export default config; 
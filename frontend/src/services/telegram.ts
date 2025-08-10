export type TelegramWebApp = {
  ready: () => void;
  expand: () => void;
  colorScheme?: 'light' | 'dark';
  initData?: string;
  initDataUnsafe?: any;
  showAlert?: (message: string) => void;
  MainButton?: { text: string; show: () => void; hide: () => void; onClick: (cb: () => void) => void };
};

declare global {
  interface Window {
    Telegram: { WebApp: TelegramWebApp };
  }
}

export const useTelegram = (): TelegramWebApp => {
  return window.Telegram?.WebApp as TelegramWebApp;
};

export const getTelegramHeaders = () => {
  const tg = useTelegram();
  const user = tg?.initDataUnsafe?.user;
  const headers: Record<string, string> = {};
  if (user?.id) headers['X-Telegram-User-Id'] = String(user.id);
  if (user?.first_name) headers['X-Telegram-First-Name'] = user.first_name;
  if (user?.last_name) headers['X-Telegram-Last-Name'] = user.last_name;
  if (user?.username) headers['X-Telegram-Username'] = user.username;
  if (tg?.initData) headers['X-Telegram-Init-Data'] = tg.initData;
  return headers;
};

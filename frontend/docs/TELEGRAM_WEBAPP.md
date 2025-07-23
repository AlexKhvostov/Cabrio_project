# Интеграция Telegram WebApp в CabrioRide

## 🎯 Цель
Настройка корректного отображения веб-приложения внутри Telegram, включая поддержку полноэкранного режима.

## 📚 Необходимые зависимости

1. Подключите Telegram WebApp SDK:
```html
<script src="https://telegram.org/js/telegram-web-app.js"></script>
```

## 🛠 Базовая настройка

### 1. Инициализация WebApp

```typescript
// Проверяем, что приложение открыто в Telegram
if (window.Telegram?.WebApp) {
    const webApp = window.Telegram.WebApp;
    
    // Сообщаем приложению, что оно готово к отображению
    webApp.ready();
    
    // Включаем расширение на весь экран
    webApp.expand();
}
```

### 2. Настройка Vue приложения

```typescript
// src/main.ts
import { createApp } from 'vue';
import App from './App.vue';

// Добавляем WebApp в глобальные свойства
const app = createApp(App);
app.config.globalProperties.$webApp = window.Telegram?.WebApp;

app.mount('#app');
```

### 3. Компонент для проверки окружения

```typescript
// src/components/TelegramWrapper.vue
<template>
  <div class="telegram-wrapper">
    <template v-if="isTelegram">
      <slot></slot>
    </template>
    <template v-else>
      <div class="telegram-warning">
        ⚠️ Приложение доступно только через Telegram
      </div>
    </template>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'TelegramWrapper',
  computed: {
    isTelegram(): boolean {
      return !!window.Telegram?.WebApp;
    }
  },
  mounted() {
    if (this.isTelegram) {
      // Инициализируем WebApp
      const webApp = window.Telegram.WebApp;
      webApp.ready();
      webApp.expand();
      
      // Настраиваем тему
      document.documentElement.className = webApp.colorScheme;
    }
  }
});
</script>
```

## 🎨 Стилизация

### 1. Основные стили

```scss
// src/assets/telegram.scss

// Используем переменные от Telegram WebApp
:root {
  --tg-theme-bg-color: var(--tg-theme-secondary-bg-color, #fff);
  --tg-theme-text-color: var(--tg-theme-text-color, #000);
  --tg-theme-hint-color: var(--tg-theme-hint-color, #999);
  --tg-theme-link-color: var(--tg-theme-link-color, #2481cc);
  --tg-theme-button-color: var(--tg-theme-button-color, #2481cc);
  --tg-theme-button-text-color: var(--tg-theme-button-text-color, #fff);
}

// Базовые стили для WebApp
body {
  margin: 0;
  padding: 0;
  background-color: var(--tg-theme-bg-color);
  color: var(--tg-theme-text-color);
}

// Стили для кнопок
.tg-button {
  background-color: var(--tg-theme-button-color);
  color: var(--tg-theme-button-text-color);
  border: none;
  border-radius: 8px;
  padding: 12px 20px;
  cursor: pointer;
  
  &:hover {
    opacity: 0.9;
  }
}
```

## 🔄 Работа с MainButton

```typescript
// Показать главную кнопку
window.Telegram.WebApp.MainButton
  .setText('Продолжить')
  .show()
  .onClick(() => {
    // Обработка клика
  });

// Скрыть кнопку
window.Telegram.WebApp.MainButton.hide();
```

## 📱 Получение данных пользователя

```typescript
// Получаем данные пользователя
const user = window.Telegram.WebApp.initDataUnsafe?.user;
if (user) {
  console.log('User ID:', user.id);
  console.log('Username:', user.username);
  console.log('First Name:', user.first_name);
}
```

## 🔒 Безопасность

1. Всегда проверяйте `initData` на бэкенде
2. Используйте HTTPS
3. Проверяйте источник запуска приложения

```typescript
// Проверка инициализации из Telegram
if (!window.Telegram?.WebApp) {
  console.error('Приложение должно быть открыто в Telegram');
  // Показать сообщение об ошибке
}
```

## 📋 Чек-лист интеграции

1. [ ] Подключен SDK Telegram WebApp
2. [ ] Настроена инициализация приложения
3. [ ] Реализовано расширение на весь экран
4. [ ] Поддержка цветовых тем Telegram
5. [ ] Обработка данных пользователя
6. [ ] Настроена работа с MainButton
7. [ ] Реализована проверка окружения
8. [ ] Добавлены стили для WebApp

## 🚀 Пример использования

```typescript
// src/App.vue
<template>
  <telegram-wrapper>
    <div class="app-container">
      <h1>{{ greeting }}</h1>
      <button class="tg-button" @click="showMainButton">
        Показать кнопку
      </button>
    </div>
  </telegram-wrapper>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import TelegramWrapper from './components/TelegramWrapper.vue';

export default defineComponent({
  name: 'App',
  components: {
    TelegramWrapper
  },
  computed: {
    greeting(): string {
      const user = window.Telegram?.WebApp?.initDataUnsafe?.user;
      return user ? `Привет, ${user.first_name}!` : 'Привет!';
    }
  },
  methods: {
    showMainButton() {
      const webApp = window.Telegram?.WebApp;
      if (webApp) {
        webApp.MainButton
          .setText('Тестовая кнопка')
          .show()
          .onClick(() => {
            webApp.showAlert('Кнопка работает!');
          });
      }
    }
  }
});
</script>

<style lang="scss">
@import './assets/telegram.scss';

.app-container {
  padding: 16px;
  min-height: 100vh;
  box-sizing: border-box;
}
</style>
```

## ⚠️ Важные замечания

1. WebApp автоматически подстраивается под тему Telegram
2. Для тестирования используйте режим разработчика в Telegram
3. Всегда проверяйте работу на разных устройствах
4. Учитывайте особенности разных платформ (iOS/Android)

## 🔗 Полезные ссылки

- [Официальная документация Telegram WebApp](https://core.telegram.org/bots/webapps)
- [Telegram WebApp SDK](https://core.telegram.org/bots/webapps#initializing-web-apps)
- [Примеры реализации](https://core.telegram.org/bots/webapps#examples) 
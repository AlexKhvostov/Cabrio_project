# Переменные окружения (.env) для CabrioRide

Все чувствительные и конфигурационные данные хранятся только в файле .env в корне проекта.
Пример структуры — в .env_example.

## Основные переменные

| Переменная                | Описание                                      | Пример значения                |
|---------------------------|-----------------------------------------------|--------------------------------|
| DB_HOST                   | Адрес сервера БД                              | localhost                      |
| DB_PORT                   | Порт БД                                       | 3308                           |
| DB_USER                   | Пользователь БД                               | cabrioride_user                |
| DB_PASSWORD               | Пароль БД                                     | password123                    |
| DB_NAME                   | Имя БД                                        | cabrioride                     |
| BOT_TOKEN                 | Токен Telegram-бота                           | 123456:ABC-DEF                 |
| BOT_NAME                  | Имя Telegram-бота                             | CabrioControl_bot              |
| ADMIN_IDS                 | id админов (через запятую)                    | 123,456                        |
| ROOT_IDS                  | id root-пользователей                         | 287536885                      |
| MODERATOR_IDS             | id модераторов                                | 5625181605                     |
| CLUB_CHAT_ID              | id клубного чата                              | -1002873258290                 |
| CLUB_CHAT_NAME            | название клубного чата                        | CabrioRide                     |
| APP_NAME                  | название приложения                           | CabrioRideAPP                  |
| APP_URL                   | URL фронтенда                                 | https://app.example.com        |
| BACKEND_API_URL           | URL backend API                               | http://localhost/app/backend/api|
| CHAT_INVITE_LINK          | ссылка-приглашение в чат                      | https://t.me/+Iwe_Bi1rZWI5Yjcy |
| APP_ENV                   | окружение (local, production и т.д.)          | local                          |
| APP_DEBUG                 | режим отладки (true/false)                    | true                           |
| DEBUG                     | дополнительный флаг отладки                   | true                           |
| SYSTEM_TOKEN              | токен для системных запросов                  | supersecrettoken               |
| JWT_SECRET                | секрет для подписи JWT-токенов                | longrandomstring               |
| TEST_TOKEN                | тестовый токен пользователя (опционально)     | token                          |
| TELEGRAM_WEBHOOK_URL      | URL для webhook Telegram-бота                 | https://app.example.com/webhook |
| TELEGRAM_API_URL          | URL Telegram API                              | https://app.cabrioride.by/api  |
| TELEGRAM_APP_URL          | URL Telegram WebApp                           | https://app.cabrioride.by/     |
| OCR_TOKEN                 | токен для сервиса распознавания номеров       | token                          |
| BOT_SEND_MESSAGE_API_KEY  | ключ для отправки сообщений ботом (если есть) | key                            |

## Примечания

- Никогда не публикуйте и не коммитьте реальный .env!
- Для новых разработчиков используйте .env_example как шаблон.
- Если добавляете новую переменную — обязательно обновляйте этот файл. 
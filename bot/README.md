# Telegram Bot CabrioRide

## Структура
- **commands/** — Команды бота (отдельные классы/скрипты)
- **handlers/** — Обработчики событий и сообщений
- **services/** — Сервисы для работы с БД, Telegram API и бизнес-логикой бота
- **config.php** — Основная конфигурация бота
- **webhook.php** — Точка входа для Telegram Webhook

## Стек технологий
- Язык: PHP 8.1 (PSR-12)
- Архитектура: Webhook (без постоянного процесса)
- Интеграция: Telegram Bot API
- Взаимодействие с БД через backend API или модели

## Задача Telegram-бота
- Интеграция Telegram-группы с WebApp
- Авторизация и проверка членства
- Автоматизация уведомлений, поиска, приветствий
- Взаимодействие с БД и API (в том числе с гид-объектами и отзывами)
- Логирование и безопасность

## Обязательные файлы-заглушки

В каждой ключевой папке бота уже созданы стартовые файлы-заглушки:
- **commands/StartCommand.php** — заглушка команды /start
- **handlers/MessageHandler.php** — заглушка обработчика сообщений
- **services/BotService.php** — заглушка основного сервиса бота
- **config.php** — заглушка конфигурации бота
- **webhook.php** — заглушка точки входа для Telegram Webhook

Эти файлы можно использовать как стартовые точки для реализации логики. 
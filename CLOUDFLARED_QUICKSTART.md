# Cloudflared Quickstart

Быстрый старт для проброса локального сервера (XAMPP/Apache, PHP) в интернет через HTTPS с помощью cloudflared.

---

## 1. Скачайте cloudflared
- [Скачать для Windows](https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-windows-amd64.exe)
- Переименуйте файл в `cloudflared.exe` и положите в папку, например, `C:\cloudflared\`

## 2. Откройте PowerShell и перейдите в папку
```powershell
Set-Location C:\cloudflared
```

## 3. Запустите локальный сервер (XAMPP/Apache)
- Убедитесь, что сайт работает по адресу: `http://localhost/`

## 4. Запустите туннель cloudflared
```powershell
.\cloudflared.exe tunnel --url http://localhost:80
```

## 5. Получите публичный HTTPS-адрес
- В консоли появится адрес вида: `https://your-tunnel-name.trycloudflare.com`
- Используйте этот адрес для Telegram WebApp или webhook.

---

Туннель работает, пока открыта консоль с cloudflared.

Для подробной инструкции см. `docs/DEVELOPMENT.md` раздел "Cloudflare Tunnel". 
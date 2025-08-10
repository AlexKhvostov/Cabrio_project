<?php require __DIR__ . '/../partials/meta.php'; ?>
<!doctype html>
<html lang="ru">
  <head>
    <?php render_meta('Профиль — CabrioRide'); ?>
    <link rel="stylesheet" href="/app/frontend/assets/css/styles.css" />
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
  </head>
  <body>
    <?php include __DIR__ . '/../components/header.php'; ?>
    <?php include __DIR__ . '/../components/nav.php'; ?>
    <main class="page">
      <h2>Профиль</h2>
      <div id="me">Загрузка...</div>
      <details id="debug-wrap" style="margin-top:12px;">
        <summary>Диагностика (временная)</summary>
        <pre id="debug" style="white-space:pre-wrap;background:rgba(255,255,255,0.05);padding:8px;border-radius:8px;border:1px solid var(--border-color);"></pre>
      </details>
    </main>
    <?php include __DIR__ . '/../components/footer.php'; ?>
    <script type="module">
      import '/app/frontend/assets/js/app.js'
      const meEl = document.getElementById('me')
      const dbg = document.getElementById('debug')
      const tg = window.Telegram?.WebApp
      const u = tg?.initDataUnsafe?.user
      const clientInfo = {
        telegram_present: !!u,
        telegram_user: u ? { id: u.id, username: u.username, first_name: u.first_name, last_name: u.last_name } : null,
      }
      fetch(`${(window.VITE_BACKEND_API_URL || (window.location.origin + '/app')).replace(/\/$/, '')}/backend/routes/api.php?route=${encodeURIComponent('/api/users/profile')}`, { headers: (window.CabrioAPI ? undefined : {}) })
      CabrioAPI.apiGet('/api/users/profile').then(json=>{
        if(!json){ meEl.textContent='Нет ответа'; return }
        if(json.__httpStatus===401){ meEl.textContent='Не авторизован'; }
        else if(json.__httpStatus===403){ meEl.textContent='Недостаточно прав'; }
        if(json.success){
          const u = json.data
          meEl.innerHTML = `<div class=\"card\">@${u.username||''} — роль: ${u.role?.name||u.role?.code||''}<br/>Машин: ${(u.cars||[]).length}</div>`
        }
        dbg.textContent = JSON.stringify({ httpStatus: json.__httpStatus||200, success: json.success, error: json.error||null, clientInfo }, null, 2)
      }).catch((e)=>{ meEl.textContent='Ошибка загрузки'; dbg.textContent = String(e) })
    </script>
  </body>
  </html>


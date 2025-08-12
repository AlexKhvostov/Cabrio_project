<?php
// Простая точка входа. Рендерим главную страницу.
require __DIR__ . '/partials/meta.php';
?>
<!doctype html>
<html lang="ru">
  <head>
    <?php render_meta('CabrioRide'); ?>
    <link rel="stylesheet" href="/app/frontend/assets/css/styles.css" />
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
  </head>
  <body>
    <?php include __DIR__ . '/components/header.php'; ?>
    <?php include __DIR__ . '/components/nav.php'; ?>
    <main class="page">
      <section class="dashboard">
        <div class="welcome" id="welcome">Привет! Добро пожаловать в CabrioRide</div>
        <h2>Статистика клуба</h2>
        <div class="stats-grid" id="stats">
          <div class="card stat"><div class="stat-value" id="stat-members">—</div><div class="stat-label">Пользователи</div></div>
          <div class="card stat"><div class="stat-value" id="stat-cars">—</div><div class="stat-label">Автомобили</div></div>
          <div class="card stat"><div class="stat-value" id="stat-events">—</div><div class="stat-label">События</div></div>
        </div>

        <h2>Разделы</h2>
        <!--
        <div class="card" style="margin-bottom:12px; display:flex; gap:8px; align-items:center; justify-content:space-between;">
          <div style="font-size:14px;color:#aaa">Открыть на весь экран для лучшего опыта</div>
          <button id="expandBtn" class="btn-secondary">Развернуть</button>
        </div>
        -->
        <div class="menu-grid">
          <a class="menu-item card" href="/app/frontend/pages/users.php">
            <div class="menu-icon" aria-hidden="true">👥</div>
            <div class="menu-title">Пользователи</div>
            <div class="menu-desc">Список участников клуба</div>
          </a>
          <a class="menu-item card" href="/app/frontend/pages/cars.php">
            <div class="menu-icon" aria-hidden="true">🚗</div>
            <div class="menu-title">Автомобили</div>
            <div class="menu-desc">Гараж клуба и авто участников</div>
          </a>
          <a class="menu-item card" href="/app/frontend/pages/map.php">
            <div class="dev-ribbon"></div>
            <div class="dev-badge">в разработке</div>
            <div class="menu-icon" aria-hidden="true">📍</div>
            <div class="menu-title">Карта</div>
            <div class="menu-desc">Локации и активность</div>
          </a>
          <a class="menu-item card" href="/app/frontend/pages/events.php">
            <div class="dev-ribbon"></div>
            <div class="dev-badge">в разработке</div>
            <div class="menu-icon" aria-hidden="true">📅</div>
            <div class="menu-title">События</div>
            <div class="menu-desc">Мероприятия клуба</div>
          </a>
          <a class="menu-item card" href="/app/frontend/pages/services.php">
            <div class="dev-ribbon"></div>
            <div class="dev-badge">в разработке</div>
            <div class="menu-icon" aria-hidden="true">📖</div>
            <div class="menu-title">Гид</div>
            <div class="menu-desc">Полезные места и сервисы</div>
          </a>
          <a class="menu-item card" href="/app/frontend/pages/me.php">
            <div class="menu-icon" aria-hidden="true">👤</div>
            <div class="menu-title">Профиль</div>
            <div class="menu-desc">Ваши данные и авто</div>
          </a>
        </div>
      </section>
    </main>
    <?php include __DIR__ . '/components/footer.php'; ?>
    <script src="/app/frontend/assets/js/app.js" type="module"></script>
    <script type="module">
      import '/app/frontend/assets/js/app.js'
      // Простая статистика: берём размеры списков
      Promise.all([
        CabrioAPI.apiGet('/api/users'),
        CabrioAPI.apiGet('/api/cars'),
        CabrioAPI.apiGet('/api/events')
      ]).then(([u,c,e])=>{
        const members = (u?.data||[]).length || '—'
        const carsActive = (c?.data||[]).filter(x=>{
          const code = (x?.status?.code||'').toString().toLowerCase()
          const name = (x?.status?.name||'').toString().toLowerCase().trim()
          return code==='active' || name==='активен'
        }).length
        const events = (e?.data||[]).length || '—'
        document.getElementById('stat-members').textContent = members
        document.getElementById('stat-cars').textContent = carsActive
        document.getElementById('stat-events').textContent = events
      }).catch(()=>{})

      // Приветствие по имени из Telegram (если доступно)
      try {
        const u = window.Telegram?.WebApp?.initDataUnsafe?.user
        if (u?.first_name) {
          const el = document.getElementById('welcome')
          if (el) el.textContent = `Привет, ${u.first_name}! Добро пожаловать в CabrioRide`
        }
      } catch {}

      /* Кнопка разворачивания WebApp на весь экран — временно отключено
      try{
        const btn = document.getElementById('expandBtn')
        const tg = window.Telegram?.WebApp
        if (btn && tg) {
          btn.addEventListener('click', ()=>{
            try{
              tg.expand()
              tg.disableVerticalSwipes?.()
              // Принудительно обновим высоту после expand
              setTimeout(()=>window.CabrioUI?.updateAppHeight?.(), 50)
              setTimeout(()=>window.CabrioUI?.updateAppHeight?.(), 250)
            }catch{}
          })
        }
      }catch{}
      */
    </script>
  </body>
  </html>



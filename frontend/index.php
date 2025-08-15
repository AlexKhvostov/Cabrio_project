<?php
// Простая точка входа. Рендерим главную страницу.
require __DIR__ . '/partials/meta.php';
?>
<!doctype html>
<html lang="ru">
  <head>
    <?php render_meta('CabrioRide'); ?>
    <link rel="stylesheet" href="/app/frontend/assets/css/styles.css?v=<?php echo filemtime(__DIR__ . '/assets/css/styles.css'); ?>" />
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
  </head>
  <body>
    <?php include __DIR__ . '/components/header.php'; ?>
    <?php include __DIR__ . '/components/nav.php'; ?>
    <main class="page">
      <section class="dashboard">
        <div class="welcome" id="welcome">Привет! Добро пожаловать в CabrioRide</div>
        <h2 id="statsTitle" style="cursor: pointer;">Статистика клуба</h2>
        <div class="stats-grid" id="stats">
          <div class="card stat"><div class="stat-value" id="stat-members">—</div><div class="stat-label">Пользователи</div></div>
          <div class="card stat"><div class="stat-value" id="stat-cars">—</div><div class="stat-label">Автомобили</div></div>
          <div class="card stat"><div class="stat-value" id="stat-events">—</div><div class="stat-label">События</div></div>
        </div>

        <!-- Скрытая панель отладки. Открывается по клику на "Статистика клуба" -->
        <div id="debugPanel" class="card" style="display:none; margin-top:12px; padding:0;">
          <div style="display:flex; align-items:center; justify-content:space-between; padding:10px 12px; border-bottom:1px solid rgba(255,255,255,0.08);">
            <div style="font-weight:600;">Debug</div>
            <div>
              <button id="dbgCopyBtn" class="btn-secondary" type="button" style="margin-right:8px;">Скопировать</button>
              <button id="dbgClearBtn" class="btn-secondary" type="button" style="margin-right:8px;">Очистить</button>
              <button id="dbgCloseBtn" class="btn-secondary" type="button">Свернуть</button>
            </div>
          </div>
          <pre id="debugLog" style="margin:0; padding:10px 12px; max-height:240px; overflow:auto; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace; font-size:12px; line-height:1.4; white-space:pre-wrap; word-break:break-word; background:rgba(0,0,0,0.35);"></pre>
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
          <a class="menu-item card" href="/app/frontend/pages/map.php">
            <div class="dev-ribbon"></div>
            <div class="dev-badge">в разработке</div>
            <div class="menu-icon" aria-hidden="true">📍</div>
            <div class="menu-title">Карта</div>
            <div class="menu-desc">Локации и активность</div>
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
    <script src="/app/frontend/assets/js/app.js?v=<?php echo filemtime(__DIR__ . '/assets/js/app.js'); ?>" type="module"></script>
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

      // Лёгкая панель дебага: скрыта по умолчанию, открывается по клику на заголовок "Статистика клуба"
      (function(){
        try{
          const panel = document.getElementById('debugPanel')
          const logEl = document.getElementById('debugLog')
          const title = document.getElementById('statsTitle')
          const btnClose = document.getElementById('dbgCloseBtn')
          const btnClear = document.getElementById('dbgClearBtn')
          const btnCopy = document.getElementById('dbgCopyBtn')
          if (!panel || !logEl || !title) return

          const ts = () => new Date().toISOString().replace('T',' ').replace('Z','')
          const write = (level, args) => {
            try{
              const line = `[${ts()}] ${level}: ` + args.map(a=>{
                try{
                  if (typeof a === 'string') return a
                  return JSON.stringify(a)
                }catch{ return String(a) }
              }).join(' ')
              logEl.textContent += (logEl.textContent ? '\n' : '') + line
              // автоскролл вниз
              logEl.scrollTop = logEl.scrollHeight
            }catch{}
          }

          // Перехват console.* (с сохранением оригиналов)
          const orig = { log: console.log, warn: console.warn, error: console.error, info: console.info }
          console.log = (...a)=>{ try{ orig.log.apply(console, a) }catch{} write('log', a) }
          console.warn = (...a)=>{ try{ orig.warn.apply(console, a) }catch{} write('warn', a) }
          console.error = (...a)=>{ try{ orig.error.apply(console, a) }catch{} write('error', a) }
          console.info = (...a)=>{ try{ orig.info.apply(console, a) }catch{} write('info', a) }

          // Экспорт упрощённого API
          window.CabrioDebug = {
            show(){ try{ panel.style.display = '' }catch{} },
            hide(){ try{ panel.style.display = 'none' }catch{} },
            toggle(){ try{ panel.style.display = (panel.style.display==='none' || !panel.style.display)? '' : 'none' }catch{} },
            log: (...a)=>write('log', a)
          }

          // Триггер по заголовку
          title.addEventListener('click', ()=> window.CabrioDebug?.toggle?.())
          btnClose?.addEventListener('click', ()=> window.CabrioDebug?.hide?.())
          btnClear?.addEventListener('click', ()=>{ try{ logEl.textContent = '' }catch{} })
          btnCopy?.addEventListener('click', async ()=>{
            try{
              const txt = logEl.textContent || ''
              if (!txt) { write('info', ['Copy','nothing to copy']); return }
              if (navigator.clipboard?.writeText) {
                await navigator.clipboard.writeText(txt)
                write('info', ['Copied to clipboard'])
              } else {
                const ta = document.createElement('textarea')
                ta.value = txt
                ta.style.position = 'fixed'
                ta.style.top = '-1000px'
                document.body.appendChild(ta)
                ta.focus(); ta.select()
                try { document.execCommand('copy'); write('info', ['Copied to clipboard (fallback)']) } finally { document.body.removeChild(ta) }
              }
            }catch(e){ write('error', ['Copy failed', String(e)]) }
          })

          // Первичный снимок окружения
          try{
            const tg = window.Telegram?.WebApp
            const user = tg?.initDataUnsafe?.user || null
            const snapshot = {
              location: window.location.href,
              api_url: window.__API_URL || null,
              base_url: window.__BASE_URL || null,
              tg_present: !!user,
              tg_user_id: user?.id || null,
              tg_username: user?.username || null,
              in_iframe: window.self !== window.top
            }
            write('info', ['Env', snapshot])
          }catch{}
        }catch{}
      })()
    </script>
  </body>
  </html>


